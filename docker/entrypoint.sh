#!/usr/bin/env bash

set -euo pipefail

cd /var/www/html

while ! mysql -h db -u root -e 'SELECT 1'; do
	sleep 0.5
done

if [[ ! -f .configured ]]; then
	sudo -u docker wp core config --dbuser=root --dbname=wordpress --dbhost=db
	touch .configured
fi

if [[ ! -f .installed ]]; then
	mysqladmin -h db -u root drop -f wordpress || true
	mysqladmin -h db -u root create wordpress
	sudo -u docker wp core install \
		--url=http://localhost:13779 \
		--admin_user=admin \
		--admin_email=info@webwinkelkeur.nl \
		--admin_password=password \
		--title='Valued WordPress'
	touch .installed
fi

exec apache2-foreground
