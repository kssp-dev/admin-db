CREATE EXTENSION citext;


DROP TABLE "public"."scripts";
DROP VIEW "public"."primary_ip";
DROP TABLE "public"."ip";


-- IP address may be primary or secondary depending on primary_ip field is NULL or not.
-- Primary ip is a main ip of a network interface, serving to link any services (primary_ip is NULL).
-- Secondary ip is another ip of the network inferface, legacy or test or other purpose configured.
-- In the case primary_ip field points to primary ip of the secondary ip's network interface.
CREATE TABLE "public"."ip" ( 
  "ip" VARCHAR(15) NOT NULL,
  "primary_ip" VARCHAR(15) NULL,
  CONSTRAINT "ip_pkey" PRIMARY KEY ("ip"),
  CONSTRAINT "fk_primary_ip" FOREIGN KEY("primary_ip") REFERENCES ip("ip"),
  CONSTRAINT "check_ip" CHECK ("ip" ~ '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$')
);
CREATE UNIQUE INDEX "ip_ip_primary_ip_key" ON "public"."ip" ("ip", "primary_ip");


CREATE VIEW "public"."primary_ip" AS SELECT "ip" FROM "ip" WHERE "primary_ip" IS NULL;


CREATE TABLE "public"."scripts" ( 
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
  CONSTRAINT "scripts_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "fk_script_ip" FOREIGN KEY("script_ip") REFERENCES ip("ip"),
  CONSTRAINT "fk_database_ip" FOREIGN KEY("database_ip") REFERENCES ip("ip")
);
CREATE UNIQUE INDEX "scripts_name_key" ON "public"."scripts" ("name");
CREATE UNIQUE INDEX "scripts_sctipt_file_key" ON "public"."scripts" ("script_file");
CREATE INDEX "scripts_sctipt_path_idx" ON "public"."scripts" ("script_path");
CREATE INDEX "scripts_timer_file_idx" ON "public"."scripts" ("timer_file");
CREATE INDEX "scripts_database_name_idx" ON "public"."scripts" ("database_name");
CREATE INDEX "database_table" ON "public"."scripts" ("database_table");

