CREATE EXTENSION citext;


CREATE TABLE "public"."ip" ( 
  "ip" VARCHAR(15) NOT NULL,
  "primary_ip" VARCHAR(15) NULL,
  CONSTRAINT "ip_pkey" PRIMARY KEY ("ip"),
  CONSTRAINT "fk_primary_ip" FOREIGN KEY("primary_ip") REFERENCES ip("ip"),
  CONSTRAINT "check_ip" CHECK ("ip" ~ '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$')
);

CREATE UNIQUE INDEX "ip_pair_key" ON "public"."ip" ("ip", "primary_ip");


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
  --CONSTRAINT "unique_sctipt_file" UNIQUE ("script_file"),
  CONSTRAINT "fk_script_ip" FOREIGN KEY("script_ip") REFERENCES ip("ip"),
  CONSTRAINT "fk_database_ip" FOREIGN KEY("database_ip") REFERENCES ip("ip")
);

CREATE UNIQUE INDEX "sctipt_file_key" ON "public"."scripts" ("script_file");

