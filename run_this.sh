#!/bin/bash

# make dirs
mkdir /var/www/$1
mkdir /var/www/$1/public_html
mkdir /var/www/$1/public_html/system
mkdir /var/www/$1/public_html/media
mkdir /var/www/$1/public_html/style
mkdir /var/www/$1/public_html/pages
mkdir /var/www/$1/public_html/pages/forbidden

cp -r /home/calhax/create_site/assets/* /var/www/$1/public_html/style/

# make useable by slaves
chmod 777 /var/www/$1
chmod 777 /var/www/$1/*

# compile apache context
v1='<VirtualHost *:80>\n'
v2='    ServerAdmin callumfleming@tutanota.com\n'
v3='    ServerName '$1'\n'
v4='    ServerAlias www.'$1'\n'
v5='    DocumentRoot /var/www/'$1'/public_html\n'
v6='    ErrorLog /var/www/'$1'/error.log\n'
v7='    CustomLog /var/www/'$1'/access.log combined\n'
v8='</VirtualHost>'
config_file=$v1$v2$v3$v4$v5$v6$v7$v8

# name of file to store
apache_name=$1'.conf'

# goto dir
cd /etc/apache2/sites-available/

# put the file + data in there
echo $config_file>$apache_name

# enable site
a2ensite $apache_name

# reboot apache
systemctl restart apache2.service

# php call
/usr/bin/php /home/calhax/create_site/setup_site.php $1
