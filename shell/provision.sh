#!/bin/bash
START_TIME=$(date +"%s")

# Import Config
mkdir -p /webdev
source /webdev/shell/config.sh

##################################################################################
# //
# There shouldn't be much need to edit lines below this normally.
# //
##################################################################################

# Add Google DNS
# --------------
echo -e "nameserver 8.8.8.8\nnameserver 8.8.4.4" | tee /etc/resolv.conf > /dev/null

# Ignore the post install questions
export DEBIAN_FRONTEND="noninteractive"

# Update the box
# --------------
# Downloads the package lists from the repositories
# and "updates" them to get information on the newest
# versions of packages and their dependencies
apt-get dist-upgrade
apt-get update
apt-get upgrade -y
apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys A4A9406876FCBD3C456770C88C718D3B5072E1F5
apt-get install -y pkg-config build-essential checkinstall
apt-get install -y dirmngr
apt-get install -y figlet

# PHP install setup and repos
# -------------------------------
figlet PHP Setup Install
apt-get install -y software-properties-common python-software-properties
add-apt-repository ppa:ondrej/php
add-apt-repository ppa:ondrej/php-qa

# MySQL 8 install setup and repos
# ---------------------------------
sudo apt-key adv --keyserver pool.sks-keyservers.net --recv-keys 5072E1F5
sudo echo "deb http://repo.mysql.com/apt/ubuntu $(lsb_release -sc) mysql-8.0" | sudo tee /etc/apt/sources.list.d/mysql.list

#APT cleanup
apt-get update
apt-get upgrade
apt-get autoremove
apt purge -y mysql*

# MySQL 8
# -----
figlet Install MySQL 8
sudo echo "mysql-community-server mysql-community-server/root-pass password ${MYSQL_ROOT_PASSWORD}" | sudo debconf-set-selections
sudo echo "mysql-community-server mysql-community-server/re-root-pass password ${MYSQL_ROOT_PASSWORD}" | sudo debconf-set-selections
sudo echo "mysql-community-server mysql-server/default-auth-override select Use Legacy Authentication Method (Retain MySQL 5.x Compatibility)" | sudo debconf-set-selections
sudo -E apt-get -y install mysql-server

# Change my.cnf settings
MYSQLINI=$(cat <<EOF
[mysqld]
sql_mode=NO_ENGINE_SUBSTITUTION
EOF
)
echo "${MYSQLINI}" >> /etc/mysql/my.cnf

# Restart MySQL
systemctl restart mysql

# Remove /var/www default
rm -rf /var/www
# Symlink /webdev to /var/www
ln -fs /webdev /var/www

# NGINX
figlet Install NGINX
apt-get install -y nginx
cp /webdev/shell/nginx/site.conf /etc/nginx/sites-available/"$REPO_NAME".megumidev
sed -i "s/REPO_NAME/$REPO_NAME/" /etc/nginx/sites-available/"$REPO_NAME".megumidev
sed -i "s/DATABASE_NAME/$DATABASE_NAME/" /etc/nginx/sites-available/"$REPO_NAME".megumidev
sed -i "s/DATABASE_USER/$DATABASE_USER/" /etc/nginx/sites-available/"$REPO_NAME".megumidev
sed -i "s/DATABASE_PASSWORD/$DATABASE_PASSWORD/" /etc/nginx/sites-available/"$REPO_NAME".megumidev
rm /etc/nginx/sites-available/default
rm /etc/nginx/sites-enabled/default
NGINX_CATCH_ALL=$(cat <<EOF
server {
    return 404;
}
EOF
)
echo "${NGINX_CATCH_ALL}" > /etc/nginx/sites-available/catch-all
ln -s /etc/nginx/sites-available/"$REPO_NAME".megumidev /etc/nginx/sites-enabled/"$REPO_NAME".megumidev

# OpenSSL
mkdir /etc/nginx/certs
sudo openssl genrsa -out /etc/nginx/certs/devcert.key 2048
sudo openssl req -new -key /etc/nginx/certs/devcert.key -out /etc/nginx/certs/devcert.csr -subj "/C=JP/ST=Tokyo/L=Itabashi-Ku/O=Megumi/OU=Web/CN=$REPO_NAME.megumidev"
sudo openssl x509 -req -days 365 -in /etc/nginx/certs/devcert.csr -signkey /etc/nginx/certs/devcert.key -out /etc/nginx/certs/devcert.crt

# Restart NGINX
systemctl restart nginx

# PHP 7.4
# -------
figlet Install PHP 7.4
apt-get install -y php7.4-fpm php7.4-common php7.4-cli php-pear php7.4-curl php7.4-gd php7.4-gmp php7.4-intl php7.4-imap php7.4-json php7.4-ldap php7.4-mbstring php7.4-mysql php7.4-ps php7.4-readline php7.4-tidy php7.4-xmlrpc php7.4-xsl php7.4-dev php7.4-zip php7.4-pgsql

# Update
apt-get update

# Change php.ini settings
sed -i "s/; max_input_vars = .*/max_input_vars = 5000/" /etc/php/7.4/fpm/php.ini
sed -i "s/post_max_size = .*/post_max_size = 30M/" /etc/php/7.4/fpm/php.ini
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 30M/" /etc/php/7.4/fpm/php.ini
sed -i "s/max_execution_time = .*/max_execution_time = 1200/" /etc/php/7.4/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php/7.4/fpm/php.ini

# Install ImageMagick
figlet Install ImageMagick
apt-get install -y php-imagick

# Restart NGINX
systemctl restart nginx

# Restart PHPFPM
systemctl reload php7.4-fpm
systemctl restart php7.4-fpm

# cURL
# ----
figlet Install cURL
apt-get install -y curl

# Xdebug
figlet Install Xdebug
wget -O xdebug.tgz https://xdebug.org/files/xdebug-2.9.6.tgz
tar -xvzf xdebug.tgz
ls
cd xdebug-2.9.6
ls
phpize
./configure
make
cp modules/xdebug.so /usr/lib/php/20190902
XDEBUGINI=$(cat <<EOF
[xdebug]
zend_extension = /usr/lib/php/20190902/xdebug.so
xdebug.remote_enable = 1
xdebug.remote_connect_back = 1
xdebug.remote_port = 9000
xdebug.scream = 0
xdebug.show_local_vars = 1
xdebug.idekey = PHPSTORM
xdebug.max_nesting_level = 1000
EOF
)
echo "${XDEBUGINI}" >> /etc/php/7.4/fpm/php.ini

# Redis
figlet Install Redis
apt-get install -y redis-server
apt-get install -y php-redis
REDISCONF=$(cat <<EOF
maxmemory 1gb
maxmemory-policy allkeys-lru
EOF
)
echo "${REDISCONF}" >> /etc/redis/redis.conf
systemctl restart redis-server.service
systemctl enable redis-server.service

# PHP Redis
figlet Install PHP Redis
sudo pecl install redis
echo "extension=redis.so" >> /etc/php/7.4/fpm/php.ini

# Restart PHPFPM
systemctl reload php7.4-fpm
systemctl restart php7.4-fpm

# Git
# ---
figlet Install GIT
apt-get install git-core

# NPM
# ---
figlet Install NPM
curl -sL https://deb.nodesource.com/setup_10.x | bash
apt-get install nodejs

# Composer
# ---
figlet Install Composer
curl -s https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Set permissions
# ---------------
figlet Setting permissions
chmod -R 777 /webdev/storage/
chmod -R 777 /webdev/bootstrap/cache/

# Set up the database
figlet Database Setup
echo "Creating database";
echo "CREATE DATABASE IF NOT EXISTS $DATABASE_NAME" | mysql -uroot -p"$MYSQL_ROOT_PASSWORD";
echo "Creating user";
echo "CREATE USER '$DATABASE_USER'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DATABASE_PASSWORD'" | mysql -uroot -p"$MYSQL_ROOT_PASSWORD"
echo "Granting privaleges to user";
echo "GRANT ALL PRIVILEGES ON $DATABASE_NAME.* TO '$DATABASE_USER'@'localhost'" | mysql -uroot -p"$MYSQL_ROOT_PASSWORD"

# Import SQL from project into DB
echo "Importing $DATABASE_NAME.sql";
mysql -u"$DATABASE_USER" -p"$DATABASE_PASSWORD" "$DATABASE_NAME" < /webdev/"$DATABASE_NAME".sql

# Set up crontask for backup of MySQL database every 5 minutes
echo "Setting CRON task";
echo "0,5,10,15,20,25,30,35,40,45,50,55 * * * * root mysqldump --user=$DATABASE_USER --password=$DATABASE_PASSWORD $DATABASE_NAME > /webdev/$DATABASE_NAME.sql &> /dev/null" >> /etc/cron.d/"$DATABASE_USER"db

figlet Provision Complete

END_TIME=$(date +"%s")
RUNTIME=$(($END_TIME-$START_TIME))

echo "$(($RUNTIME / 60)) minutes and $(($RUNTIME % 60)) seconds elapsed for provision script.";
