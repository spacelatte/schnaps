#!/usr/bin/env docker build --compress -t pvtmert/schnaps -f

FROM debian:stable

EXPOSE 80
VOLUME /app/data
WORKDIR /app

RUN apt update && apt install -y \
	nginx php-fpm ca-certificates \
	&& apt clean

COPY --chown=www-data:www-data ./ ./

RUN ln -sf /app/nginx.conf /etc/nginx/sites-enabled/default

CMD service php7.0-fpm start; \
	service nginx start; \
	tail -f \
		/var/log/nginx/access.log \
		/var/log/nginx/error.log \
		/var/log/php7.0-fpm.log
