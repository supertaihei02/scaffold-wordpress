#!/usr/bin/env bash

########################################
# Build時にcomposerでインストールしておいた
# ライブラリを移動する
########################################
cd $(dirname $0)
ENV_FILE=../../.env
CONTAINER_CUSTOMIZER_DIR=/var/www/html/wp-content/plugins/customizer

if [ ! -f $ENV_FILE ]; then
    echo "FILE '$ENV_FILE' does not exist."
    exit;
fi
source $ENV_FILE

docker exec $WORDPRESS_CONTAINER /bin/bash -c "rm -fr $CONTAINER_CUSTOMIZER_DIR/vendor; cp -a /tmp/vendor $CONTAINER_CUSTOMIZER_DIR ; cp -f /tmp/composer.json $CONTAINER_CUSTOMIZER_DIR/composer.json ; cp -f /tmp/composer.lock $CONTAINER_CUSTOMIZER_DIR/composer.lock ; cd $CONTAINER_CUSTOMIZER_DIR; composer dump-autoload ;"
