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
  "run_count" INTEGER NOT NULL DEFAULT 0,
  "name" VARCHAR(63) NOT NULL UNIQUE
);
COMMENT ON TABLE "monitoring"."instances" IS 'Monitoring instances list';


CREATE TABLE IF NOT EXISTS "monitoring"."types" (
  "id" SERIAL PRIMARY KEY,
  "uid" VARCHAR(31) NOT NULL UNIQUE CHECK ("uid" ~ '^[^@#\s]+$'),
  "is_alert" BOOLEAN NOT NULL DEFAULT FALSE,
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "description" TEXT NULL
);
COMMENT ON TABLE "monitoring"."types" IS 'Types of monitoring';


CREATE TABLE IF NOT EXISTS "monitoring"."scripts" (
  "id" SERIAL PRIMARY KEY,
  "instance_id" INTEGER NOT NULL REFERENCES "monitoring"."instances",
  "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
  "uid" VARCHAR(31) NOT NULL UNIQUE CHECK ("uid" ~ '^[^@#\s]+$'),
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "script" TEXT NOT NULL,
  "updated" DATE NOT NULL DEFAULT NOW()
);
COMMENT ON TABLE "monitoring"."scripts" IS 'Scripts run on monitoring instances';


CREATE TABLE IF NOT EXISTS "monitoring"."targets" (
  "id" SERIAL PRIMARY KEY,
  "script_id" INTEGER NOT NULL REFERENCES "monitoring"."scripts",
  "enabled" BOOLEAN NOT NULL DEFAULT FALSE,
  "uid" VARCHAR(31) NOT NULL CHECK ("uid" ~ '^[^@#\s]+$'),
  "name" VARCHAR(31) NOT NULL,
  "period" SMALLINT NOT NULL DEFAULT 5 CHECK ("period" > 0),
  "target" VARCHAR(127) NOT NULL,
  "script_data" TEXT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "targets_uid_script_id_key" ON "monitoring"."targets" ("uid", "script_id");
CREATE UNIQUE INDEX IF NOT EXISTS "targets_name_script_id_key" ON "monitoring"."targets" ("name", "script_id");
CREATE UNIQUE INDEX IF NOT EXISTS "targets_target_script_id_key" ON "monitoring"."targets" ("target", "script_id");
COMMENT ON TABLE "monitoring"."targets" IS 'Data sources scripts fetch information from';


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
  "target_id" INTEGER NOT NULL REFERENCES "monitoring"."targets",
  "time" TIMESTAMP NOT NULL DEFAULT NOW(),
  "uid" VARCHAR(127) NOT NULL CHECK ("uid" ~ '^[^@#\s]+@[^@#\s]+@[^@#\s]+@?[^@#\s]*$'),
  "is_alert" BOOLEAN NOT NULL,
  "value" BIGINT NOT NULL,
  "repetition" INTEGER NOT NULL DEFAULT 0,
  "name" VARCHAR(127) NOT NULL,
  "short_name" VARCHAR(63) NOT NULL,
  "description" TEXT NULL
);
CREATE INDEX IF NOT EXISTS "series_time_idx" ON "monitoring"."series" ("time");
CREATE INDEX IF NOT EXISTS "series_uid_idx" ON "monitoring"."series" ("uid");
CREATE INDEX IF NOT EXISTS "series_is_alert_idx" ON "monitoring"."series" ("is_alert");
COMMENT ON TABLE "monitoring"."series" IS 'Time series of metrics and alerts';


CREATE OR REPLACE VIEW "monitoring"."alerts" AS
SELECT "id", "time", "value", "repetition", "uid", "name", "short_name", "description" FROM "monitoring"."series" WHERE "is_alert";
COMMENT ON VIEW "monitoring"."alerts" IS 'Time series of alerts only';


CREATE OR REPLACE VIEW "monitoring"."metrics" AS
SELECT "id", "time", "value", "repetition", "uid", "name", "short_name", "description" FROM "monitoring"."series" WHERE NOT "is_alert";
COMMENT ON VIEW "monitoring"."metrics" IS 'Time series of metrics only';


CREATE OR REPLACE VIEW "monitoring"."last_series" AS
WITH "s" AS (SELECT MAX("time") "tm", "uid" "ui" FROM "monitoring"."series" GROUP BY "uid")
SELECT "id", "time", "is_alert", "value", "repetition", "uid", "name", "short_name", "description" FROM "s" LEFT JOIN "monitoring"."series" ON "time"="tm" and "uid"="ui";
COMMENT ON VIEW "monitoring"."last_series" IS 'Last state of every metric or alert';


CREATE OR REPLACE VIEW "monitoring"."last_alerts" AS
WITH "s" AS (SELECT MAX("time") "tm", "uid" "ui" FROM "monitoring"."series" WHERE "is_alert" GROUP BY "uid")
SELECT "id", "time", "value", "repetition", "uid", "name", "short_name", "description" FROM "s" LEFT JOIN "monitoring"."series" ON "time"="tm" and "uid"="ui";
COMMENT ON VIEW "monitoring"."last_alerts" IS 'Last state of every alert';


CREATE OR REPLACE VIEW "monitoring"."last_metrics" AS
WITH "s" AS (SELECT MAX("time") "tm", "uid" "ui" FROM "monitoring"."series" WHERE NOT "is_alert" GROUP BY "uid")
SELECT "id", "time", "value", "repetition", "uid", "name", "short_name", "description" FROM "s" LEFT JOIN "monitoring"."series" ON "time"="tm" and "uid"="ui";
COMMENT ON VIEW "monitoring"."last_metrics" IS 'Last state of every metric';

--- END ---

