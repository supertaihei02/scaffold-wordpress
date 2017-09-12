#!/usr/bin/env bash

#####################################
# 現在のデータベースの情報をGit管理します
#####################################

cd $(dirname $0)
ENV_FILE=../../.env

if [ ! -f $ENV_FILE ]; then
    echo "[ERROR] FILE '$ENV_FILE' not found."
    exit -1;
fi

source $ENV_FILE
DUMP_FILE=../../$DUMP_PATH
docker exec $MYSQL_CONTAINER /bin/bash -c "mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > $DUMP_CONTAINER_PATH;" \
    && echo "export to '$DUMP_PATH'" && git add -f $DUMP_FILE
