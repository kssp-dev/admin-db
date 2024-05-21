-- PostgreSQL extension
CREATE EXTENSION IF NOT EXISTS citext;

--SET TIMEZONE='Europe/Moscow';


-- IP address may be primary or secondary depending on primary_ip field is NULL or not.
-- Primary ip is a main ip of a network interface, serving to link any services (primary_ip is NULL).
-- Secondary ip is another ip of the network inferface, legacy or test or other purpose configured.
-- In the case primary_ip field points to primary ip of the secondary ip's network interface.
CREATE TABLE IF NOT EXISTS "public"."ip" ( 
  "ip" VARCHAR(15) NOT NULL,
  "primary_ip" VARCHAR(15) NULL,
  CONSTRAINT "ip_pkey" PRIMARY KEY ("ip"),
  CONSTRAINT "fk_primary_ip" FOREIGN KEY("primary_ip") REFERENCES ip("ip"),
  CONSTRAINT "check_ip" CHECK ("ip" ~ '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$')
);
CREATE UNIQUE INDEX IF NOT EXISTS "ip_ip_primary_ip_key" ON "public"."ip" ("ip", "primary_ip");
CREATE OR REPLACE VIEW "public"."primary_ip" AS SELECT "ip" FROM "ip" WHERE "primary_ip" IS NULL;


CREATE TABLE IF NOT EXISTS "public"."scripts" ( 
  "id" SERIAL,
  "name" VARCHAR(80) NOT NULL,
  "script_ip" VARCHAR(15) NOT NULL,
  "script_file" VARCHAR(160) NOT NULL,
  "script_path" VARCHAR(250) NOT NULL,
  "timer_file" VARCHAR(160) NULL,
  "database_ip" VARCHAR(15) NULL,
  "database_name" VARCHAR(80) NULL,
  "database_table" VARCHAR(80) NULL,
  "logic" TEXT NULL,
  "description" TEXT NULL,
  "updated" DATE NOT NULL DEFAULT NOW(),
  CONSTRAINT "scripts_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "fk_script_ip" FOREIGN KEY("script_ip") REFERENCES ip("ip"),
  CONSTRAINT "fk_database_ip" FOREIGN KEY("database_ip") REFERENCES ip("ip")
);
CREATE UNIQUE INDEX IF NOT EXISTS "scripts_name_key" ON "public"."scripts" ("name");
CREATE UNIQUE INDEX IF NOT EXISTS "scripts_sctipt_file_key" ON "public"."scripts" ("script_file");
CREATE INDEX IF NOT EXISTS "scripts_sctipt_path_idx" ON "public"."scripts" ("script_path");
CREATE INDEX IF NOT EXISTS "scripts_timer_file_idx" ON "public"."scripts" ("timer_file");
CREATE INDEX IF NOT EXISTS "scripts_database_name_idx" ON "public"."scripts" ("database_name");
CREATE INDEX IF NOT EXISTS "database_table" ON "public"."scripts" ("database_table");

--- LOGIN ---

CREATE SCHEMA IF NOT EXISTS "login";
COMMENT ON SCHEMA "login" IS 'Login user data and access rights';


CREATE TABLE IF NOT EXISTS "login"."roles" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(27) NOT NULL UNIQUE
);


CREATE TABLE IF NOT EXISTS "login"."users" (
  "id" SERIAL PRIMARY KEY,
  "role_id" INTEGER NULL REFERENCES "login"."roles",
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "login" VARCHAR(31) NOT NULL UNIQUE,
  "email" VARCHAR(47) NULL UNIQUE,
  "password" VARCHAR(60) NOT NULL
);


CREATE TABLE IF NOT EXISTS "login"."rules" (
  "id" SERIAL PRIMARY KEY,
  "role_id" INTEGER NOT NULL REFERENCES "login"."roles",
  "model" VARCHAR(31) NOT NULL,
  "all_visible" BOOLEAN NOT NULL DEFAULT FALSE,
  "visible_fields" TEXT NULL,
  "all_editable" BOOLEAN NOT NULL DEFAULT FALSE,
  "editable_fields" TEXT NULL,
  "all_actions" BOOLEAN NOT NULL DEFAULT FALSE,
  "actions" TEXT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "rules_model_role_id_key" ON "login"."rules" ("model", "role_id");

--- EXPORT ---

CREATE TABLE IF NOT EXISTS "public"."export" (
  "id" SERIAL PRIMARY KEY,
  "from" VARCHAR(15) NOT NULL,
  "to" VARCHAR(15) NOT NULL,
  "icon" VARCHAR(47) NULL,
  "header" TEXT NULL,
  "row" TEXT NULL,
  "footer" TEXT NULL,
  "details" TEXT NULL,
  "link" TEXT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "export_from_to_key" ON "public"."export" ("from", "to");
INSERT INTO "public"."export" ("from", "to", "icon") VALUES ('scripts', 'wiki', 'wikipedia w') ON CONFLICT DO NOTHING;
INSERT INTO "public"."export" ("from", "to", "icon") VALUES ('ip', 'wiki', 'wikipedia w') ON CONFLICT DO NOTHING;

--- MONITORING ---

CREATE SCHEMA IF NOT EXISTS "monitoring";
COMMENT ON SCHEMA "monitoring" IS 'Data based monitoring system database';


CREATE TABLE IF NOT EXISTS "monitoring"."instances" (
  "id" SERIAL PRIMARY KEY,
  "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
  "instance" VARCHAR(31) NOT NULL UNIQUE,
  "is_running" BOOLEAN NOT NULL DEFAULT FALSE,
  "run_count" INTEGER NOT NULL DEFAULT 0,
  "script_timeout" SMALLINT NOT NULL DEFAULT 30 CHECK ("script_timeout" > 0),
  "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0),
  "name" VARCHAR(63) NOT NULL UNIQUE
);
COMMENT ON TABLE "monitoring"."instances" IS 'Monitoring instances list';

ALTER TABLE IF EXISTS "monitoring"."instances" ADD COLUMN IF NOT EXISTS "script_timeout" SMALLINT NOT NULL DEFAULT 30 CHECK ("script_timeout" > 0);
ALTER TABLE IF EXISTS "monitoring"."instances" ADD COLUMN IF NOT EXISTS "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0);


CREATE TABLE IF NOT EXISTS "monitoring"."types" (
  "id" SERIAL PRIMARY KEY,
  "uid" VARCHAR(31) NOT NULL UNIQUE CHECK ("uid" ~ '^[^@#\s]+$'),
  "is_alert" BOOLEAN NOT NULL DEFAULT FALSE,
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "description" TEXT NULL,
  "notification_delay" SMALLINT NOT NULL CHECK ("notification_delay" >= 0),
  "notification_period" SMALLINT NOT NULL CHECK ("notification_period" >= 0),
  CHECK ("notification_period" = 0 OR "notification_period" > "notification_delay")
);
COMMENT ON TABLE "monitoring"."types" IS 'Types of monitoring';

--ALTER TABLE IF EXISTS "monitoring"."types" ADD COLUMN IF NOT EXISTS "notification_delay" SMALLINT NOT NULL DEFAULT 0 CHECK ("notification_delay" >= 0);
--ALTER TABLE IF EXISTS "monitoring"."types" ALTER COLUMN "notification_delay" DROP DEFAULT;
--ALTER TABLE IF EXISTS "monitoring"."types" ADD COLUMN IF NOT EXISTS "notification_period" SMALLINT NOT NULL DEFAULT 0 CHECK ("notification_period" >= 0);
--ALTER TABLE IF EXISTS "monitoring"."types" ALTER COLUMN "notification_period" DROP DEFAULT;
--ALTER TABLE IF EXISTS "monitoring"."types" ADD CHECK ("notification_period" = 0 OR "notification_period" > "notification_delay");


CREATE TABLE IF NOT EXISTS "monitoring"."scripts" (
  "id" SERIAL PRIMARY KEY,
  "instance_id" INTEGER NOT NULL REFERENCES "monitoring"."instances",
  "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
  "uid" VARCHAR(31) NOT NULL UNIQUE CHECK ("uid" ~ '^[^@#\s]+$'),
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "script" TEXT NOT NULL,
  "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0),
  "updated" DATE NOT NULL DEFAULT NOW(),
  "login" VARCHAR(31) NOT NULL
);
COMMENT ON TABLE "monitoring"."scripts" IS 'Scripts run on monitoring instances';

ALTER TABLE IF EXISTS "monitoring"."scripts" ADD COLUMN IF NOT EXISTS "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0);
--ALTER TABLE IF EXISTS "monitoring"."scripts" ALTER COLUMN "login" DROP DEFAULT;


CREATE TABLE IF NOT EXISTS "monitoring"."targets" (
  "id" SERIAL PRIMARY KEY,
  "script_id" INTEGER NOT NULL REFERENCES "monitoring"."scripts",
  "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
  "uid" VARCHAR(31) NOT NULL CHECK ("uid" ~ '^[^@#\s]+$'),
  "name" VARCHAR(31) NOT NULL,
  "period" SMALLINT NOT NULL DEFAULT 5 CHECK ("period" > 0),
  "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0),
  "target" VARCHAR(127) NOT NULL,
  "script_data" TEXT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "targets_uid_script_id_key" ON "monitoring"."targets" ("uid", "script_id");
CREATE UNIQUE INDEX IF NOT EXISTS "targets_name_script_id_key" ON "monitoring"."targets" ("name", "script_id");
CREATE UNIQUE INDEX IF NOT EXISTS "targets_target_script_id_key" ON "monitoring"."targets" ("target", "script_id");
COMMENT ON TABLE "monitoring"."targets" IS 'Data sources scripts fetch information from';

ALTER TABLE IF EXISTS "monitoring"."targets" ADD COLUMN IF NOT EXISTS "duration" INTEGER NOT NULL DEFAULT 0 CHECK ("duration" >= 0);

CREATE TABLE IF NOT EXISTS "monitoring"."log" (
  "id" SERIAL PRIMARY KEY,
  "target_id" INTEGER NOT NULL REFERENCES "monitoring"."targets",
  "time" TIMESTAMP NOT NULL DEFAULT NOW(),
  "code" SMALLINT NOT NULL,
  "output" TEXT NULL
);
CREATE INDEX IF NOT EXISTS "log_time_idx" ON "monitoring"."log" ("time");
COMMENT ON TABLE "monitoring"."log" IS 'Debug information';


CREATE TABLE IF NOT EXISTS "monitoring"."series" (
  "id" SERIAL PRIMARY KEY,
  "target_id" INTEGER NULL REFERENCES "monitoring"."targets",
  "type_id" INTEGER NOT NULL REFERENCES "monitoring"."types",
  "time" TIMESTAMP NOT NULL DEFAULT NOW(),
  "uid" VARCHAR(127) NOT NULL CHECK ("uid" ~ '^[^@#\s]+@[^@#\s]+@[^@#\s]+@?[^@#\s]*$'),
  "value" BIGINT NOT NULL,
  "repetition" INTEGER NOT NULL DEFAULT 0,
  "name" VARCHAR(127) NOT NULL,
  "short_name" VARCHAR(63) NOT NULL,
  "description" TEXT NULL,
  "notified" BOOLEAN NOT NULL DEFAULT FALSE
);
CREATE INDEX IF NOT EXISTS "series_time_idx" ON "monitoring"."series" ("time");
CREATE INDEX IF NOT EXISTS "series_uid_idx" ON "monitoring"."series" ("uid");
COMMENT ON TABLE "monitoring"."series" IS 'Time series of metrics and alerts';

ALTER TABLE IF EXISTS "monitoring"."series" ALTER COLUMN "target_id" DROP NOT NULL;

--ALTER TABLE IF EXISTS "monitoring"."series" ADD COLUMN IF NOT EXISTS "type_id" INTEGER NOT NULL REFERENCES "monitoring"."types" DEFAULT 1;
--ALTER TABLE IF EXISTS "monitoring"."series" ALTER COLUMN "type_id" DROP DEFAULT;
--ALTER TABLE IF EXISTS "monitoring"."series" ADD COLUMN IF NOT EXISTS "notified" BOOLEAN NOT NULL DEFAULT FALSE;
--ALTER TABLE IF EXISTS "monitoring"."series" DROP COLUMN IF EXISTS "is_alert";


DROP VIEW IF EXISTS "monitoring"."alerts";
CREATE OR REPLACE VIEW "monitoring"."alerts" AS
	SELECT "s"."id", "s"."time", "s"."value", "s"."uid", "s"."name", "s"."short_name", "s"."description"
	FROM "monitoring"."series" "s"
	JOIN "monitoring"."types" "t"
	ON "t"."id" = "s"."type_id"
	WHERE "t"."is_alert";
COMMENT ON VIEW "monitoring"."alerts" IS 'Time series of alerts only';
CREATE OR REPLACE RULE "delete_alerts_rule" AS
	ON DELETE TO "monitoring"."alerts" DO INSTEAD
	DELETE FROM "monitoring"."series"
	WHERE "id" = OLD."id";


DROP VIEW IF EXISTS "monitoring"."metrics";
CREATE OR REPLACE VIEW "monitoring"."metrics" AS
	SELECT "s"."id", "s"."time", "s"."value", "s"."uid", "s"."name", "s"."short_name", "s"."description"
	FROM "monitoring"."series" "s"
	JOIN "monitoring"."types" "t"
	ON "t"."id" = "s"."type_id"
	WHERE NOT "t"."is_alert";
COMMENT ON VIEW "monitoring"."metrics" IS 'Time series of metrics only';
CREATE OR REPLACE RULE "delete_metrics_rule" AS
	ON DELETE TO "monitoring"."metrics" DO INSTEAD
	DELETE FROM "monitoring"."series"
	WHERE "id" = OLD."id";


DROP VIEW IF EXISTS "monitoring"."last_series";
CREATE OR REPLACE VIEW "monitoring"."last_series" AS
	WITH "w" AS (
		SELECT MAX("s"."time") "wtime", "s"."uid" "wuid"
		FROM "monitoring"."series" "s"
		GROUP BY "s"."uid"
	)
	SELECT "s"."id", "s"."time", "s"."uid"
	FROM "w"
	JOIN "monitoring"."series" "s"
	ON "s"."time" = "wtime" AND "s"."uid" = "wuid";
COMMENT ON VIEW "monitoring"."last_series" IS 'Last state of series';


DROP VIEW IF EXISTS "monitoring"."last_alerts";
CREATE OR REPLACE VIEW "monitoring"."last_alerts" AS
	WITH "w" AS (
		SELECT MAX("s"."time") "wtime", "s"."uid" "wuid"
		FROM "monitoring"."series" "s"
		JOIN "monitoring"."types" "t"
		ON "t"."id" = "s"."type_id"
		WHERE "t"."is_alert"
		GROUP BY "s"."uid"
	)
	SELECT "s"."id", "s"."time", "s"."value", "s"."repetition", "s"."uid", "s"."name", "s"."short_name", "s"."description"
	FROM "w"
	JOIN "monitoring"."series" "s"
	ON "s"."time" = "wtime" AND "s"."uid" = "wuid";
COMMENT ON VIEW "monitoring"."last_alerts" IS 'Last state of every alert';
CREATE OR REPLACE RULE "delete_last_alerts_rule" AS
	ON DELETE TO "monitoring"."last_alerts" DO INSTEAD
	DELETE FROM "monitoring"."series"
	WHERE "id" = OLD."id";


DROP VIEW IF EXISTS "monitoring"."last_metrics";
CREATE OR REPLACE VIEW "monitoring"."last_metrics" AS
	WITH "w" AS (
		SELECT MAX("s"."time") "wtime", "s"."uid" "wuid"
		FROM "monitoring"."series" "s"
		JOIN "monitoring"."types" "t"
		ON "t"."id" = "s"."type_id"
		WHERE NOT "t"."is_alert"
		GROUP BY "s"."uid"
	)
	SELECT "s"."id", "s"."time", "s"."value", "s"."repetition", "s"."uid", "s"."name", "s"."short_name", "s"."description"
	FROM "w"
	JOIN "monitoring"."series" "s"
	ON "s"."time" = "wtime" AND "s"."uid" = "wuid";
COMMENT ON VIEW "monitoring"."last_metrics" IS 'Last state of every metric';
CREATE OR REPLACE RULE "delete_last_metrics_rule" AS
	ON DELETE TO "monitoring"."last_metrics" DO INSTEAD
	DELETE FROM "monitoring"."series"
	WHERE "id" = OLD."id";


DROP VIEW IF EXISTS "monitoring"."notifications";
CREATE OR REPLACE VIEW "monitoring"."notifications" AS
	WITH "w" AS (
		SELECT MAX("s"."time") "wtime", "s"."uid" "wuid"
		FROM "monitoring"."series" "s"
    	JOIN "monitoring"."types" "t"
    	ON "t"."id" = "s"."type_id"
		WHERE "t"."is_alert"
		GROUP BY "s"."uid"
	)
	SELECT "s"."id", "s"."time", "s"."uid", "s"."value", "s"."notified", "s"."repetition", "t"."notification_delay", "t"."notification_period"
	FROM "w"
	JOIN "monitoring"."series" "s"
	ON "s"."time" = "wtime" AND "s"."uid" = "wuid"
	JOIN "monitoring"."types" "t"
	ON "t"."id" = "s"."type_id"
	WHERE NOT "s"."notified";
COMMENT ON VIEW "monitoring"."notifications" IS 'Notifications are waiting for sending';
CREATE OR REPLACE RULE "update_notifications_rule" AS
	ON UPDATE TO "monitoring"."notifications" DO INSTEAD
	UPDATE "monitoring"."series"
	SET "notified" = NEW."notified"
	WHERE "uid" = OLD."uid" AND NOT "notified";

--- END ---

