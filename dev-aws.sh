#!/bin/bash

export GIT_SHA=`git rev-parse --short HEAD`;
export GIT_BRANCH=`git rev-parse --abbrev-ref HEAD`;

erb -T - ./docker-compose.yaml.aws.erb > ./docker-compose.yaml.dev

sudo docker-compose -f ./docker-compose.yaml.dev stop;
sudo docker-compose -f ./docker-compose.yaml.dev rm -f -v;
sudo docker-compose -f ./docker-compose.yaml.dev build --force-rm;
sudo docker-compose -f ./docker-compose.yaml.dev up;
