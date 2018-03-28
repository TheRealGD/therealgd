#!/bin/bash

add-apt-repository -y ppa:ondrej/php
add-apt-repository -y "deb https://apt.postgresql.org/pub/repos/apt/ xenial-pgdg main"
curl -L https://www.postgresql.org/media/keys/ACCC4CF8.asc 2>/dev/null | apt-key add -

apt-get update && apt-get install -y \
  php7.2-cli \
  php7.2-curl \
  php7.2-fpm \
  php7.2-intl \
  php7.2-pgsql \
  composer \
  nginx \
  nodejs \
  postgresql-client-9.6 \
  postgresql-contrib-9.6 \
  postgresql-common \
  libpq-dev # TODO I don't think this is the right version
