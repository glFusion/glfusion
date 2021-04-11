FROM alpine:latest

LABEL maintainer="Mark Evans <mark@glfusion.org>"

VOLUME ["/var/www/app/public_html/data"]
VOLUME ["/var/www/app/private/data"]
VOLUME ["/var/www/app/private/logs"]
VOLUME ["/etc/nginx/ssl"]
VOLUME ["/var/lib/mysql"]

ENV TIMEZONE America/Chicago
ENV MYSQL_ROOT_PASSWORD root
ENV MYSQL_DATABASE glfusion
ENV MYSQL_USER glfusion
ENV MYSQL_PASSWORD password

EXPOSE 80 443

ARG VERSION

RUN apk --no-cache --update add \
    tzdata openssl unzip nginx bash mysql ca-certificates s6 curl ssmtp mailx php7 php7-phar php7-curl \
    php7-fpm php7-json php7-zlib php7-xml php7-dom php7-ctype php7-opcache php7-zip php7-iconv \
    php7-pdo php7-pdo_mysql php7-pdo_sqlite php7-pdo_pgsql php7-mbstring php7-session php7-bcmath \
    php7-gd php7-mcrypt php7-openssl php7-sockets php7-posix php7-ldap php7-simplexml && \
    rm -rf /var/www/localhost 

RUN addgroup mysql mysql

ADD . /var/www/app
ADD docker/ /

ADD docker/scripts/entrypoint.sh /scripts/entrypoint.sh
RUN mkdir /docker-entrypoint-initdb.d && \
    mkdir /scripts/pre-exec.d && \
    mkdir /scripts/pre-init.d && \
    chmod -R 755 /scripts


RUN rm -rf /var/www/app/docker && echo $VERSION > /version.txt
RUN chown -R nginx.nginx /var/www/app

ENTRYPOINT [ "/scripts/entrypoint.sh" ]
