#!/usr/bin/env bash

#####################################
# Git管理されている
# データベースの情報を取り込みます
#####################################

cd $(dirname $0)
ENV_FILE=../../.env

if [ ! -f $ENV_FILE ]; then
    echo "[ERROR] FILE '$ENV_FILE' not found."
    exit -1;
fi

source $ENV_FILE
DUMP_FILE=../../$DUMP_PATH

if [ ! -f $DUMP_FILE ]; then
    echo "[INFO] FILE '$DUMP_FILE' does not exist."
    exit 0;
fi

docker exec $MYSQL_CONTAINER /bin/bash -c "mysql -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < $DUMP_CONTAINER_PATH;" \
    && echo "import from '$DUMP_PATH'"

# ARGS
case $# in
    1 ) DOMAIN=$1; LOCAL='localhost'; break;;
    2 ) DOMAIN=$1; LOCAL=$2; break;;
    * ) exit 0;;
esac

docker exec $WORDPRESS_CONTAINER /bin/bash -c "bash /var/www/tools/shell-scripts/search-replace.sh $DOMAIN $LOCAL"
echo "Convert $DOMAIN to $LOCAL"
