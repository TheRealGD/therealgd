FROM php:7.2-fpm-stretch

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
  libpq-dev libcurl4-openssl-dev libpng-dev zlib1g-dev && \
  pecl install apcu_bc-1.0.4 && \
  docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
  docker-php-ext-install pdo pdo_pgsql pgsql && \
  docker-php-ext-enable apcu && \
  docker-php-ext-install curl iconv gd opcache && \
  rm -r /var/lib/apt/lists/* /tmp/*
