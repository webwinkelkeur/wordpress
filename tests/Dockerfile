ARG PHP_VERSION
FROM wordpress:cli-php${PHP_VERSION}
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# Cache directory for downloads; a new volume mounted here inherits the owner.
USER root
RUN mkdir /cache && chown www-data:www-data /cache
USER www-data
