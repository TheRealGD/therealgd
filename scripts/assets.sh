#!/bin/bash
docker run --rm -it \
  -v $PWD:/home/node/app -w /home/node/app \
  node:8 bash -c 'npm install && npm run build-dev'
