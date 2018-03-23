FROM nginx:stable

RUN apt-get update && \
    apt-get -y install software-properties-common

RUN apt-get install -y gnupg && \
    apt-get install -y cron && \
    apt-get install -y inetutils-ping && \
    apt-get install -y inetutils-telnet && \
    apt-get install -y rsyslog && \
    apt-get install -y sudo && \
    apt-get install -y curl

RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 8C47BE8E75BCA694
RUN add-apt-repository ppa:certbot/certbot
RUN apt-get update
RUN apt-get install -y certbot
RUN apt-get install -y python-certbot-nginx 

COPY ./letsencrypt/ /etc/letsencrypt/

ADD ./crontab /certcrontab
ADD ./updatecerts.sh /
ADD ./entrypoint.sh /

RUN chmod +x /updatecerts.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT /entrypoint.sh

