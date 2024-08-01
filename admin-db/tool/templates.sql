INSERT INTO "monitoring"."scripts" ("uid", "name", "script") VALUES ('test', 'Test script', '#!/bin/bash

sleep 3

echo +++ TEST +++
echo METRIC#0#test#METRIC');
