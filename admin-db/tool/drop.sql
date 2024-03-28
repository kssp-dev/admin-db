
DROP TABLE IF EXISTS "public"."scripts";
DROP VIEW IF EXISTS "public"."primary_ip";
DROP TABLE IF EXISTS "public"."ip";

DROP TABLE IF EXISTS "public"."export";

--- MONITORING ---

DROP VIEW IF EXISTS "monitoring"."last_metrics";
DROP VIEW IF EXISTS "monitoring"."last_alerts";
DROP VIEW IF EXISTS "monitoring"."last_series";

DROP VIEW IF EXISTS "monitoring"."metrics";
DROP VIEW IF EXISTS "monitoring"."alerts";

DROP TABLE IF EXISTS "monitoring"."series";
DROP TABLE IF EXISTS "monitoring"."log";
DROP TABLE IF EXISTS "monitoring"."types";
DROP TABLE IF EXISTS "monitoring"."targets";
DROP TABLE IF EXISTS "monitoring"."scripts";
DROP TABLE IF EXISTS "monitoring"."servers";

DROP SCHEMA IF EXISTS "monitoring";
