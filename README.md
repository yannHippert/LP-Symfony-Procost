# Projet Procost du cours Frameworks/Symfony de Hippert Yann

Nom: Hippert
Prénom: Yann
Email: yann.hippert@hotmail.com

## Setup docker mysql-database

```sh
docker-compose -f ./docker/docker-compose.yml up --no-start
```

## Start docker database

```sh
docker-compose -f ./docker/docker-compose.yml start
```

Update the env.local file!!

## Create database migration file

```sh
php bin/console doctrine:migration:diff
```

## Apply migration to the database

```sh
php bin/console doctrine:migration:migrate
```

## Load dummy data

```sh
php bin/console doctrine:fixtures:load
```

## Start dev server

```sh
php -S 0.0.0.0:8000 -t public
```

## Get the event-chain

```sh
php bin/console debug:event-dispatcher <event>
```

## Clear the cache of the current environment

```sh
php bin/console cache:clear
```
