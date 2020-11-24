#!/usr/bin/env bash

set -euo pipefail

while ! mysql -h db -u root </dev/null &>/dev/null; do
	sleep 0.5
done

if [[ ${WP_INSTALL:-} = yes ]]; then
	cd /var/www/html

	if [[ ! -f .downloaded ]]; then
		sudo -u www-data wp core download
		touch .downloaded
	fi

	if [[ ! -f .configured ]]; then
		sudo -u www-data wp config create --dbuser=root --dbname=wordpress --dbhost=db --extra-php <<-EOF
			if (isset(\$_SERVER['SERVER_PORT'])) {
				define('WP_SITEURL', sprintf('http://localhost:%d', \$_SERVER['SERVER_PORT']));
				define('WP_HOME', WP_SITEURL);
			}
		EOF
		touch .configured
	fi

	if [[ ! -f .installed ]]; then
		mysqladmin -h db -u root drop -f wordpress || true
		mysqladmin -h db -u root create wordpress
		sudo -u www-data wp core install \
			--url=http://localhost:13779 \
			--admin_user=admin \
			--admin_email=info@webwinkelkeur.nl \
			--admin_password=password \
			--title='Valued WordPress'
		touch .installed
	fi

	cd wp-content/plugins
	sudo -u www-data ln -sn /data/webwinkelkeur || true
	sudo -u www-data ln -sn /data/trustprofile || true
fi

cd /
exec apache2-foreground
