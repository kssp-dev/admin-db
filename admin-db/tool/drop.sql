
DROP TABLE IF EXISTS "public"."scripts";
DROP VIEW IF EXISTS "public"."primary_ip";
DROP TABLE IF EXISTS "public"."ip";

DROP TABLE IF EXISTS "public"."export";

--- LOGIN ---

DROP VIEW IF EXISTS "login"."rules";
DROP VIEW IF EXISTS "login"."users";
DROP TABLE IF EXISTS "login"."roles";

DROP SCHEMA IF EXISTS "login";

--- MONITORING ---

DROP VIEW IF EXISTS "monitoring"."notifications";

DROP VIEW IF EXISTS "monitoring"."last_metrics";
DROP VIEW IF EXISTS "monitoring"."last_alerts";
DROP VIEW IF EXISTS "monitoring"."last_series";

DROP VIEW IF EXISTS "monitoring"."metrics";
DROP VIEW IF EXISTS "monitoring"."alerts";

DROP TABLE IF EXISTS "monitoring"."series";
DROP TABLE IF EXISTS "monitoring"."log";
DROP TABLE IF EXISTS "monitoring"."targets";
DROP TABLE IF EXISTS "monitoring"."types";
DROP TABLE IF EXISTS "monitoring"."scripts";
DROP TABLE IF EXISTS "monitoring"."instances";

DROP SCHEMA IF EXISTS "monitoring";
