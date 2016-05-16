#!/usr/bin/env bash

cat > ~/.my.cnf << EOF
[client]
user = root
password = 123456
host = localhost
EOF

DB=$1;

mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB\` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci";
