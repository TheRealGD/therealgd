FROM nginx:stable

VOLUME /etc/letsencrypt

EXPOSE 80
EXPOSE 443

ARG SERVER_NAME
ARG REDIRECT_DOMAINS
ARG HTTPS_CERT_NAME
ARG FAKE_HOSTS

RUN apt-get update && \
    apt-get -y install software-properties-common

RUN apt-get install -y gnupg && \
    apt-get install -y cron && \
    apt-get install -y inetutils-ping && \
    apt-get install -y inetutils-telnet && \
    apt-get install -y rsyslog && \
    apt-get install -y sudo && \
    apt-get install -y curl
RUN apt-get install -y ruby

# Instructions from https://github.com/staticfloat/docker-nginx-certbot/blob/master/Dockerfile
RUN apt update && \
      apt install -y cron python python-dev libffi6 libffi-dev libssl-dev curl build-essential && \
      curl -L 'https://bootstrap.pypa.io/get-pip.py' | python && \
      pip install -U cffi certbot && \
      apt remove --purge -y python-dev build-essential libffi-dev libssl-dev curl && \
      apt-get autoremove -y && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/*

ADD ./config/crontab /certcrontab
ADD ./scripts/updatecerts.sh /
ADD ./scripts/entrypoint.sh /

ADD ./config/nginx.conf.erb /tmp
RUN SERVER_NAME=$SERVER_NAME \
    REDIRECT_SERVER_NAMES=$REDIRECT_DOMAINS \
    HTTPS_CERT_NAME=$HTTPS_CERT_NAME \
    FAKE_HOSTS=$FAKE_HOSTS \
    erb -T - /tmp/nginx.conf.erb > /etc/nginx/nginx.conf

RUN chmod +x /updatecerts.sh
RUN chmod +x /entrypoint.sh
RUN usermod -u 1000 www-data
RUN mkdir -p /var/wwwnossl

# uncomment me for lighter container and slower build
# RUN apt-get purge   -y ruby
RUN rm /tmp/nginx.conf.erb

ENTRYPOINT /entrypoint.sh

