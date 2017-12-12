FROM php:5.6-apache

ENV TZ=Europe/Madrid

WORKDIR /var/www/html

EXPOSE 80 443

RUN apt-get update && apt-get install apache2 apache2-doc apache2-utils nano cron rsyslog -y
RUN a2dismod mpm_event && a2enmod mpm_prefork
RUN a2enmod rewrite deflate filter headers proxy proxy_http ssl
RUN docker-php-ext-install mysql mysqli pdo pdo_mysql
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN (crontab -u root -l; echo "*/15 * * * * /usr/local/bin/php /usr/src/app/core/cron/crontabs.include.php >/dev/null 2>&1" ) | crontab -u root -

COPY . /var/www/html

RUN cp /var/www/html/install/docker/mysite.conf /etc/apache2/sites-available/mysite.conf
RUN a2ensite mysite

CMD /etc/init.d/cron start && /etc/init.d/rsyslog start && apache2-foreground
