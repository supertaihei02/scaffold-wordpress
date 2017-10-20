#!/usr/bin/env bash

#####################################
# Git管理されている
# データベースの情報を取り込みます
#####################################

cd $(dirname $0)

# ENV
ENV_FILE=../../.env
if [ ! -f $ENV_FILE ]; then
    echo "FILE '$ENV_FILE' does not exist."
    exit 1;
fi
source $ENV_FILE

# ARGS
case $# in
    1 ) DOMAIN=$1; LOCAL='localhost'; break;;
    2 ) DOMAIN=$1; LOCAL=$2; break;;
    * ) echo 'Argument Invalid.'; exit 2;;
esac

echo "DOMAIN: $DOMAIN"
echo "LOCAL: $LOCAL"
echo "MYSQL_USER: $MYSQL_USER"

PHP=`/usr/bin/which php`
$PHP ./srdb.cli.php \
    -h db -n $MYSQL_DATABASE -u $MYSQL_USER -p $MYSQL_PASSWORD --port $MYSQL_PORT -s $DOMAIN -r $LOCAL;
