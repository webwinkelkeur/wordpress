<?php

$wp_cli = false;
$replace_sources_list = false;

switch ($argv[1] ?? null) {
case 'latest':
    $version = '8.2.7-apache';
    break;
case 'min':
    $version = '7.0.33-apache';
    $wp_cli = true;
    $replace_sources_list = true;
    break;
case 'old':
    $version = '5.6.40-apache';
    $replace_sources_list = true;
    break;
default:
    fwrite(STDERR, "Usage: {$argv[0]} { latest | min | old }\n");
    exit(1);
}

?>
FROM php:<?= $version . "\n"; ?>

<?php if ($replace_sources_list): ?>
RUN echo "deb http://archive.debian.org/debian/ stretch main" > /etc/apt/sources.list
<?php endif; ?>

<?php if ($wp_cli): ?>
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp
RUN chmod +x /usr/local/bin/wp
<?php endif; ?>

RUN apt-get update && apt-get install -y \
        sudo \
        mariadb-client

RUN curl \
    https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions \
    -o /usr/local/bin/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions mysqli pdo_mysql

COPY entrypoint.sh /

ENTRYPOINT []
CMD ["/entrypoint.sh"]
