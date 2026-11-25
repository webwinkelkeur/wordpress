<?php

$wp_cli = false;
// Debian release to fetch from archive.debian.org, for images based on
// releases that are no longer on deb.debian.org.
$debian_archive = null;

switch ($argv[1] ?? null) {
case 'latest':
    $version = '8.2.7-apache';
    break;
case 'min':
    $version = '7.4.33-apache';
    $wp_cli = true;
    $debian_archive = 'bullseye';
    break;
default:
    fwrite(STDERR, "Usage: {$argv[0]} { latest | min }\n");
    exit(1);
}

?>
FROM php:<?= $version . "\n"; ?>

<?php if ($debian_archive): ?>
RUN rm -f /etc/apt/sources.list.d/* && \
    echo "deb http://archive.debian.org/debian/ <?= $debian_archive ?> main" > /etc/apt/sources.list && \
    echo "deb http://archive.debian.org/debian-security/ <?= $debian_archive ?>-security main" >> /etc/apt/sources.list

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
