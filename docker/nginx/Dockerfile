FROM nginx

RUN rm -f /etc/nginx/conf.d/default.conf
ADD nginx.conf /etc/nginx/
ADD gundeals.local /etc/nginx/sites-available/gundeals.conf
RUN ln -s /etc/nginx/sites-available/gundeals.conf /etc/nginx/conf.d/default.conf
RUN usermod -u 1000 www-data

CMD ["nginx"]
