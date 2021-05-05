#!/usr/bin/env bash

set -e

set -a
. ./.env
set +a

echo "******DOCKER_ENV=${DOCKER_ENV} TAG: ${TAG} Host ID: " $(id -u)

docker build --build-arg "COMPOSER_AUTH=${COMPOSER_AUTH}" \
             -t ${REGISTRY_IMAGE}/app_php_base:${TAG} \
             -t ${REGISTRY_IMAGE}/app_php_base:latest \
             -f ${APPLICATION}/Dockerfile \
             --target app_php_base \
             ${APPLICATION}

if [ "${DOCKER_ENV}" == "prod" ]; then
    docker build --build-arg "COMPOSER_AUTH=${COMPOSER_AUTH}" \
                 -t ${REGISTRY_IMAGE}/app_php:${TAG} \
                 -t ${REGISTRY_IMAGE}/app_php:latest \
                 -f ${APPLICATION}/Dockerfile \
                 --target app_php \
                 --cache-from ${REGISTRY_IMAGE}/app_php_base:${TAG} \
                 ${APPLICATION}

    docker build -t ${REGISTRY_IMAGE}/app_nginx:${TAG} \
                 -t ${REGISTRY_IMAGE}/app_nginx:latest \
                 -f ${APPLICATION}/Dockerfile \
                 --target app_nginx \
                 --cache-from ${REGISTRY_IMAGE}/app_php:${TAG} \
                 ${APPLICATION}
fi
