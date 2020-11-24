FROM php:8.0.0RC5-apache

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
RUN chmod +x /usr/local/bin/wp

RUN apt-get update && apt-get install -y \
        sudo \
        mariadb-client

RUN useradd -m docker

RUN cd /var/www/html && sudo -u docker wp core download

RUN curl \
    https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions \
    -o /usr/local/bin/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions mysqli pdo_mysql

COPY entrypoint.sh /

ENTRYPOINT []
CMD ["/entrypoint.sh"]
