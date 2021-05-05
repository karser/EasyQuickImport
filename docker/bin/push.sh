#!/usr/bin/env bash

set -e

set -a
. ./.env
set +a

echo ${REGISTRY_PASSWORD} | docker login ${REGISTRY} -u ${REGISTRY_USER} --password-stdin
echo Pushing...
docker push ${REGISTRY_IMAGE}/app_php:${TAG}
docker push ${REGISTRY_IMAGE}/app_php:latest
docker push ${REGISTRY_IMAGE}/app_nginx:${TAG}
docker push ${REGISTRY_IMAGE}/app_nginx:latest
