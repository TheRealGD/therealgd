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
ARG aws_ssm_name_db_url
ARG aws_ssm_region

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update
RUN apt-get install -y libpq-dev libcurl4-openssl-dev libpng-dev libjpeg-dev zlib1g-dev ruby gnupg libfreetype6-dev
RUN pecl install apcu_bc-1.0.4
RUN docker-php-ext-install mbstring curl iconv opcache pdo pdo_pgsql pgsql
RUN docker-php-ext-configure gd \
          --with-freetype-dir=/usr/include/ \
          --with-png-dir=/usr/include \
          --with-jpeg-dir=/usr/include
RUN docker-php-ext-install gd
RUN docker-php-ext-enable apcu opcache gd

# install node
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get install -y nodejs

# install composer
RUN apt-get install -y zip git
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

RUN rm -r /var/lib/apt/lists/* /tmp/*

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
    /tmp/.env.erb > /var/www/.env

RUN mkdir -p /etc/php/7.2/fpm/conf.d/
RUN echo "\n opcache.max_accelerated_files = 20000         \
          \n realpath_cache_size=4096K                     \
          \n realpath_cache_ttl=600                        \
          \n php_admin_flag[log_errors] = ${log_errors}    \
          \n php_flag[display_errors] = ${display_errors}" >> /etc/php/7.2/fpm/conf.d/99-overrides.ini

# uncomment me for lighter container and slower build
# RUN apt-get purge   -y ruby

CMD ["sh", "-c", "cd /var/www; npm install; npm run build-dev; composer install; \
                 bin/console doctrine:migrations:migrate --no-interaction; \
                 cd /var/www/public; php-fpm "]
