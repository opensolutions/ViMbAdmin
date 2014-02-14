#! /bin/bash

echo NOTES ONLY - RUN REQUIRED COMMANDS MANUALLY!!
exit

sudo /etc/init.d/memcached restart
rm ../doctrine2/xml/*
cp ~/Shared/orm/ViMbAdmin/xml/*xml ../doctrine2/xml/
./doctrine2-cli.php orm:generate-entities ../application/
./doctrine2-cli.php orm:generate-proxies
./doctrine2-cli.php orm:generate-repositories ../application/


echo "####   ./doctrine2-cli.php orm:schema-tool:drop --force && ./doctrine2-cli.php orm:schema-tool:create "
echo "####   ./doctrine2-cli.php orm:schema-tool:create "

