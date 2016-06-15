#!/usr/bin/env bash

apt update

# Defaults for MySQL and phpMyAdmin:
debconf-set-selections <<< 'mysql-server mysql-server/root_password password password'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password password'
echo 'phpmyadmin phpmyadmin/dbconfig-install boolean true' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/app-password-confirm password password' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/admin-pass password password' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/mysql/app-pass password password' | debconf-set-selections
echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections

apt full-upgrade -y

apt install -y apache2 php7.0 php7.0-intl php7.0-mysql php-rrd php7.0-cgi php7.0-cli php7.0-curl php7.0-mcrypt \
    php-memcached libapache2-mod-php7.0 mysql-server mysql-client php-mysql joe memcached      \
    php7.0-mbstring php7.0-xml phpmyadmin php-gettext screen joe php-memcache

if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant/public /var/www
fi

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

export MYSQL_PWD=password

mysql -u root <<END_SQL
DROP DATABASE IF EXISTS \`vimbadmin\`;
CREATE DATABASE \`vimbadmin\` CHARACTER SET = 'utf8mb4' COLLATE = 'utf8mb4_unicode_ci';
GRANT ALL ON \`vimbadmin\`.* TO \`vimbadmin\`@\`127.0.0.1\` IDENTIFIED BY 'password';
GRANT ALL ON \`vimbadmin\`.* TO \`vimbadmin\`@\`localhost\` IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
END_SQL

if [[ -f /vagrant/vimbadmin-preferred.sql.bz2 ]]; then
    bzcat /vagrant/vimbadmin-preferred.sql.bz2 | mysql -u root vimbadmin
elif [[ -f /vagrant/data/vagrant-base.sql ]]; then
    cat /vagrant/data/vagrant-base.sql | mysql -u root vimbadmin
fi

cat >/vagrant/public/.htaccess <<END_HTACCESS
SetEnv APPLICATION_ENV vagrant
END_HTACCESS


cd /vagrant
su - vagrant -c "cd /vagrant && composer install"

cat >/etc/apache2/sites-available/000-default.conf <<END_APACHE
<VirtualHost *:80>
    DocumentRoot /vagrant/public

    <Directory /vagrant/public>
        Options FollowSymLinks
        AllowOverride None
        Require all granted

        SetEnv APPLICATION_ENV vagrant

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} -s [OR]
        RewriteCond %{REQUEST_FILENAME} -l [OR]
        RewriteCond %{REQUEST_FILENAME} -d
        RewriteRule ^.*$ - [NC,L]
        RewriteRule ^.*$ /index.php [NC,L]
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
END_APACHE

cp /vagrant/application/configs/application.ini.vagrant /vagrant/application/configs/application.ini
a2enmod rewrite
chmod -R a+rwX /vagrant/var
service apache2 restart

# Useful screen settings for barryo:
cat >/home/vagrant/.screenrc <<END_SCREEN
termcapinfo xterm* ti@:te@
vbell off
startup_message off
defutf8 on
defscrollback 2048
nonblock on
hardstatus on
hardstatus alwayslastline
hardstatus string '%{= kG}%-Lw%{= kW}%50> %n%f* %t%{= kG}%+Lw%<'
screen -t bash     0
altscreen on
END_SCREEN


cd /vagrant
