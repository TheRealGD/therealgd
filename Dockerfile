FROM php:7.2-fpm-stretch

ARG log_errors
ARG display_errors
ARG site_name
ARG app_env
ARG app_secret
ARG trusted_proxies
ARG trusted_hosts
ARG no_reply_address
ARG mailer_url
ARG database_url

ENV DEBIAN_FRONTEND=noninteractive
RUN  apt-get update \
  && apt-get install -y libpq-dev libcurl4-openssl-dev libpng-dev libjpeg-dev zlib1g-dev ruby gnupg libfreetype6-dev \
  && pecl install apcu_bc-1.0.4 \
  && apt-get install -y libpq-dev \
  && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
  && docker-php-ext-install pdo pdo_pgsql pgsql \
  && docker-php-ext-install mbstring curl iconv opcache \
  && docker-php-ext-configure gd \
            --with-freetype-dir=/usr/include/ \
            --with-png-dir=/usr/include \
            --with-jpeg-dir=/usr/include \
  && docker-php-ext-install gd \
  && docker-php-ext-enable apcu opcache gd pdo pdo_pgsql pgsql \
  && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
  && apt-get install -y nodejs \
  && apt-get install -y zip git \
  && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
  && php composer-setup.php --install-dir=/usr/bin --filename=composer \
  && php -r "unlink('composer-setup.php');" \
  && rm -r /var/lib/apt/lists/* /tmp/*

# PHP-FPM Config
RUN mkdir -p /etc/php/7.2/fpm/conf.d/
RUN echo "\n opcache.max_accelerated_files = 20000         \
          \n realpath_cache_size=4096K                     \
          \n realpath_cache_ttl=600                        \
          \n php_admin_flag[log_errors] = ${log_errors}    \
          \n php_flag[display_errors] = ${display_errors}" >> /etc/php/7.2/fpm/conf.d/99-overrides.ini

# Generate ENV
ADD ./.env.erb /tmp
RUN erb -T - site_name=$site_name                           \
    app_env=$app_env                                        \
    app_secret=$app_secret                                  \
    trusted_proxies=$trusted_proxies                        \
    trusted_hosts=$trusted_hosts                            \
    database_url=$database_url                              \
    aws_ssm_name_db_url=$aws_ssm_name_db_url                \
    aws_ssm_region=$aws_ssm_region                          \
    no_reply_address=$no_reply_address                      \
    mailer_url=$mailer_url                                  \
    /tmp/.env.erb > /tmp/.env

# build prod-like stuff
ADD assets/           /var/www/assets/
ADD config/           /var/www/config/
ADD public/           /var/www/public/
ADD src/              /var/www/src/
ADD templates/        /var/www/templates/
ADD translations/     /var/www/translations/
ADD composer.json     /var/www/
ADD fontello.json     /var/www/
ADD package.json      /var/www/
ADD phpunit.xml.dist  /var/www/
ADD webpack.config.js /var/www

# uncomment me for lighter container and slower build
# RUN apt-get purge   -y ruby

RUN cd /var/www && npm install

WORKDIR /var/www/public
CMD ["sh", "-c", "cd /var/www; \
                 cp /tmp/.env /var/www/.env; \
                 chown www-data:www-data /var/www/.env; \
                 composer install; npm run build-dev; \
                 cp /tmp/.env /var/www/.env && rm /tmp/.env && rm /tmp/env.erb;\
                 chown www-data:www-data /var/www/.env; \
                 mkdir -p ./public/media/; chown www-data:www-data public/media -R; \
                 mkdir -p ./public/submission_images/; chown www-data:www-data public/submission_images -R; \
                 mkdir -p ./var/cache/; chown www-data:www-data ./var/cache/ -R; \
                 bin/console doctrine:migrations:migrate --no-interaction; \
                 bin/console app:user:add -a -p devdevdev dev; \
                 cd /var/www/public; php-fpm"]

