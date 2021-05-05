## Run in prod mode
```
APP_ENV=prod bin/copy-env.sh
bin/build.sh
COMPOSE_FILE=docker-compose.prod.yml docker-compose down
COMPOSE_FILE=docker-compose.prod.yml docker-compose up
open http://localhost:8083/login

#cleanup
docker volume rm easyquickimportdev_postgres
docker-compose down --remove-orphans
```

## Tests

```
cd docker
DOCKER_ENV=test APP_ENV=test bin/copy-env.sh
bin/build.sh
docker-compose run --rm php sh -c "bin/run-tests.sh"
#cleanup mysql
docker-compose down --remove-orphans
```


### Push
```
bin/push.sh
```

### Run
```
eval $(docker-machine env scw0)
echo 'password' | docker login registry.optdeal.com -u karser --password-stdin
docker-compose pull
docker-compose down --remove-orphans; docker-compose up -d
docker-compose logs -f

#clear cache (a bug)
docker exec -it -u app easyquickimport_app_1 sh
rm -rf ./var/cache/*
```

### Deployment

```
git remote add gitlab ssh://git@gitlab.dev.easyquickimport.com:2222/karser/trackmagic.git
git push gitlab master -f
#everything:
git fetch --prune
git remote set-head origin -d
git push --prune gitlab +refs/remotes/origin/*:refs/heads/* +refs/tags/*:refs/tags/*
```


### Database in docker
```
docker stop qb_api_mysql || true \
  && docker rm qb_api_mysql || true

sudo rm -rf ./var/db/*

docker run --name qb_api_mysql -d \
  -v /home/karser/dev/projects/easyimport/easyimport/var/db/:/var/lib/mysql \
  -p 3316:3306 \
  -e MYSQL_ROOT_PWD=123 \
  -e MYSQL_USER=dev \
  -e MYSQL_USER_PWD=dev \
  -e MYSQL_USER_DB=qb_api \
  --restart unless-stopped \
  leafney/alpine-mariadb:10.2.22
```
