#!/bin/bash

erb -T - ./docker-compose.yaml.aws.erb > ./docker-compose.yaml.dev

sudo docker-compose -f ./docker-compose.yaml.dev stop;
sudo docker-compose -f ./docker-compose.yaml.dev rm -f;
sudo docker-compose -f ./docker-compose.yaml.dev build --force-rm;
sudo docker-compose -f ./docker-compose.yaml.dev up;
