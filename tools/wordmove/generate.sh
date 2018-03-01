#!/usr/bin/env bash

########################################
# Build時にcomposerでインストールしておいた
# ライブラリを移動する
# 引数に 1 を渡すと node_modules, vendorも
# 同期するMovefileを作成する
########################################
cd $(dirname $0)
ENV_FILE=../../.env

if [ ! -f $ENV_FILE ]; then
    echo "FILE '$ENV_FILE' does not exist."
    exit;
fi
source $ENV_FILE

docker exec $WORDPRESS_CONTAINER /bin/bash -c "/usr/local/bin/php /var/www/tools/wordmove/generate.php $1"
 