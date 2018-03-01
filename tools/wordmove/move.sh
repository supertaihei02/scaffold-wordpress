#!/usr/bin/env bash
cd $(dirname $0)
ENV_FILE=../../.env

if [ ! -f $ENV_FILE ]; then
    echo "FILE '$ENV_FILE' does not exist."
    exit;
fi
source $ENV_FILE

docker exec $WORDPRESS_CONTAINER /bin/bash -c "/usr/bin/wordmove $2 $3 -e $1"
 