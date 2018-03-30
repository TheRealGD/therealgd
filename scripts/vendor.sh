#!/bin/bash
docker run --rm -it \
   -v $PWD:/app \
   composer install --ignore-platform-reqs --no-scripts
