#!/usr/bin/env bash

APPLICATION=${CI_PROJECT_DIR:-..}
APP_ENV=${APP_ENV:-prod}
ENVIRONMENT=${ENVIRONMENT:-local}
DOCKER_ENV=${DOCKER_ENV:-prod}
COMPOSE_PROJECT_NAME=easyimport
GIT_TAG=${CI_COMMIT_TAG:-$(git describe --tags --exact-match || true)}
GIT_BRANCH=${CI_COMMIT_BRANCH:-$(git rev-parse --abbrev-ref HEAD)}
DATE_ISO=$(date -I'seconds')
VERSION=${GIT_TAG:-$GIT_BRANCH}-${DATE_ISO}

echo "APP_ENV: ${APP_ENV} VERSION: ${VERSION}"

TAG=${CI_COMMIT_REF_SLUG:-latest}
PUID=$(id -u)
PGID=$(id -g)

REGISTRY=${CI_REGISTRY}
REGISTRY_IMAGE=${CI_REGISTRY_IMAGE}
REGISTRY_USER=${CI_REGISTRY_USER}
REGISTRY_PASSWORD=${CI_REGISTRY_PASSWORD}

case "$DOCKER_ENV" in
    "prod")
        COMPOSE_FILE=docker-compose.prod.yml
        ;;
    "test")
        COMPOSE_FILE=docker-compose.test.yml
        ;;
esac

# docker env file

sed -e" \
    s#^DOCKER_ENV=.*#DOCKER_ENV=$DOCKER_ENV#; \
    s#APP_ENV=.*#APP_ENV=$APP_ENV#; \
    s#ENVIRONMENT=.*#ENVIRONMENT=$ENVIRONMENT#; \
    s#APPLICATION=.*#APPLICATION=$APPLICATION#; \
    s#PUID=.*#PUID=$PUID#; \
    s#PGID=.*#PGID=$PGID#; \
    s#REGISTRY=.*#REGISTRY=$REGISTRY#; \
    s#REGISTRY_IMAGE=.*#REGISTRY_IMAGE=$REGISTRY_IMAGE#; \
    s#REGISTRY_USER=.*#REGISTRY_USER=$REGISTRY_USER#; \
    s#REGISTRY_PASSWORD=.*#REGISTRY_PASSWORD=$REGISTRY_PASSWORD#; \
    s#TAG=.*#TAG=$TAG#; \
    s#COMPOSE_FILE=.*#COMPOSE_FILE=$COMPOSE_FILE#; \
    s#COMPOSE_PROJECT_NAME=.*#COMPOSE_PROJECT_NAME=$COMPOSE_PROJECT_NAME#; \

" .env.dist > .env

if [ ! -z "$VIRTUAL_HOST" ] ; then
    sed -i " \
        s#^VIRTUAL_HOST=.*#VIRTUAL_HOST=$VIRTUAL_HOST#; \
    " .env
fi

# app env file

sed -e" \
    s#^APP_ENV=.*#APP_ENV=$APP_ENV#; \
    s#^VERSION=.*#VERSION=$VERSION#; \
    s#COMPOSE_FILE=.*#COMPOSE_FILE=$COMPOSE_FILE#; \

" ${APPLICATION}/.env > .app_env


if [ ! -z "$DATABASE_URL" ] ; then
    sed -i " \
        s#^DATABASE_URL=.*#DATABASE_URL=$DATABASE_URL#; \
    " .app_env
fi

if [ ! -z "$ENVIRONMENT" ] ; then
    sed -i " \
        s#^DEPLOYMENT=.*#DEPLOYMENT=$ENVIRONMENT#; \
    " .app_env
fi

if [ ! -z "$MAILER_DSN" ] ; then
    sed -i " \
        s#^MAILER_DSN=.*#MAILER_DSN=$MAILER_DSN#; \
    " .app_env
fi
