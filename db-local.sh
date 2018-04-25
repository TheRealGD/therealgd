#!/bin/bash

export GIT_SHA=`git rev-parse --short HEAD`;
export GIT_BRANCH=`git rev-parse --abbrev-ref HEAD`;
docker-compose exec db-dev-local psql -U developer -d devdb;
