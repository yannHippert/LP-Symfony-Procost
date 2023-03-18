# Docker

## Build

```sh
docker build -f ./docker
```

## Compose

```sh
docker-compose -f ./docker/docker-compose.yml up --no-start
```

## Start

```sh
docker-compose -f ./docker/docker-compose.yml start
```

## Mysql show routines

```SQL
SELECT routine_schema, routine_name, routine_type
    FROM information_schema.routines
    WHERE routine_schema = 'dbProcost';
```
