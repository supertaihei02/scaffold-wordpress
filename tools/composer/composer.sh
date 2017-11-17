#!/usr/bin/env bash

########################################
# Build時にcomposerでインストールしておいた
# ライブラリを移動する
########################################
cd $(dirname $0)
ENV_FILE=../../.env

if [ ! -f $ENV_FILE ]; then
    echo "FILE '$ENV_FILE' does not exist."
    exit;
fi
source $ENV_FILE

docker exec $WORDPRESS_CONTAINER /bin/bash -c "rm -fr ./vendor; cp -a /tmp/vendor ./ ; cp -f /tmp/composer.json ./composer.json ; cp -f /tmp/composer.lock ./composer.lock ; composer dump-autoload ;"
