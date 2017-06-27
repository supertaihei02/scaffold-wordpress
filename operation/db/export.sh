#!/usr/bin/env bash

#####################################
# 現在のデータベースの情報をGit管理します
#####################################

cd $(dirname $0)
ENV_FILE=../../.env

if [ -f $ENV_FILE ]; then
    source $ENV_FILE
    DUMP_FILE=../../$DUMP_PATH
    docker exec wp-db /bin/bash -c "mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > $DUMP_CONTAINER_PATH;" \
    && echo "export to '$DUMP_PATH'" && git add -f $DUMP_FILE
else
    echo "FILE '$ENV_FILE' does not exist."
fi
