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
WK_DUMP_FILE=../../$DUMP_PATH.wk.sql
WK_DUMP_CONTAINER_PATH=$DUMP_CONTAINER_PATH.wk.sql

# 1回バックアップ用にそのままダンプする
docker exec $MYSQL_CONTAINER /bin/bash -c "mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > $DUMP_CONTAINER_PATH && cp -f $DUMP_CONTAINER_PATH $WK_DUMP_CONTAINER_PATH" \
     && echo "- Export to '$DUMP_PATH'" && git add -f $DUMP_FILE

# ドメインの変換処理を行う
# ARGS
case $# in
    1 ) DOMAIN=$1; LOCAL='localhost'; break;;
    2 ) DOMAIN=$1; LOCAL=$2; break;;
    * ) rm $WK_DUMP_FILE; exit 0;;
esac
docker exec $WORDPRESS_CONTAINER /bin/bash -c "bash /var/www/tools/shell-scripts/search-replace.sh $LOCAL $DOMAIN"
echo "- Convert $LOCAL to $DOMAIN"

# 変換後のDBをダンプする
docker exec $MYSQL_CONTAINER /bin/bash -c "mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > $DUMP_CONTAINER_PATH;" \
    && echo "- Export to '$DUMP_PATH'" && git add -f $DUMP_FILE
    
# コピーしておいたDB情報を再インポートする
docker exec $MYSQL_CONTAINER /bin/bash -c "mysql -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE < $WK_DUMP_CONTAINER_PATH;" \
    && echo "- Import from '$WK_DUMP_FILE'"

rm $WK_DUMP_FILE
