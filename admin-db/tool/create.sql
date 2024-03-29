-- PostgreSQL extension
CREATE EXTENSION IF NOT EXISTS citext;


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


CREATE TABLE IF NOT EXISTS "monitoring"."servers" (
  "id" SERIAL PRIMARY KEY,
  "run_count" INTEGER NOT NULL DEFAULT 0,
  "name" VARCHAR(31) NOT NULL UNIQUE
);


CREATE TABLE IF NOT EXISTS "monitoring"."scripts" (
  "id" SERIAL PRIMARY KEY,
  "server_id" INTEGER NOT NULL REFERENCES "monitoring"."servers",
  "text_id" VARCHAR(15) NOT NULL UNIQUE CHECK ("text_id" ~ '^[^@\s]+$'),
  "name" VARCHAR(31) NOT NULL UNIQUE,
  "script" TEXT NOT NULL,
  "updated" DATE NOT NULL DEFAULT NOW()
);


CREATE TABLE IF NOT EXISTS "monitoring"."targets" (
  "id" SERIAL PRIMARY KEY,
  "script_id" INTEGER NOT NULL REFERENCES "monitoring"."scripts",
  "text_id" VARCHAR(15) NOT NULL CHECK ("text_id" ~ '^[^@\s]+$'),
  "name" VARCHAR(31) NOT NULL,
  "period" SMALLINT NOT NULL DEFAULT 5 CHECK ("period" > 0),
  "target" VARCHAR(127) NOT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "targets_script_id_text_id_key" ON "monitoring"."targets" ("script_id", "text_id");
CREATE UNIQUE INDEX IF NOT EXISTS "targets_script_id_name_key" ON "monitoring"."targets" ("script_id", "name");


CREATE TABLE IF NOT EXISTS "monitoring"."alerts" (
  "id" SERIAL PRIMARY KEY,
  "script_id" INTEGER NOT NULL REFERENCES "monitoring"."scripts",
  "code" SMALLINT NOT NULL,
  "name" VARCHAR(31) NOT NULL,
  "description" TEXT NULL
);
CREATE UNIQUE INDEX IF NOT EXISTS "alerts_script_id_code_key" ON "monitoring"."alerts" ("script_id", "code");
CREATE UNIQUE INDEX IF NOT EXISTS "alerts_script_id_name_key" ON "monitoring"."targets" ("script_id", "name");


CREATE TABLE IF NOT EXISTS "monitoring"."log" (
  "id" SERIAL PRIMARY KEY,
  "target_id" INTEGER NOT NULL REFERENCES "monitoring"."targets",
  "time" TIMESTAMP NOT NULL DEFAULT NOW(),
  "code" SMALLINT NOT NULL,
  "output" TEXT NULL
);
CREATE INDEX IF NOT EXISTS "log_time_idx" ON "monitoring"."log" ("time");

CREATE TABLE IF NOT EXISTS "monitoring"."series" (
  "id" SERIAL PRIMARY KEY,
  "target_id" INTEGER NOT NULL REFERENCES "monitoring"."targets",
  "time" TIMESTAMP NOT NULL DEFAULT NOW(),
  "text_id" VARCHAR(31) NOT NULL CHECK ("text_id" ~ '^[^@\s]+@[^@\s]+$'),
  "metric" VARCHAR(127) NULL,
  "is_alert" BOOLEAN NOT NULL,
  "alert_name" VARCHAR(93) NULL,
  "alert_description" TEXT NULL
);
CREATE INDEX IF NOT EXISTS "series_time_idx" ON "monitoring"."series" ("time");
CREATE INDEX IF NOT EXISTS "series_text_id_idx" ON "monitoring"."series" ("text_id");
CREATE INDEX IF NOT EXISTS "series_metric_idx" ON "monitoring"."series" ("metric");

--- END ---

