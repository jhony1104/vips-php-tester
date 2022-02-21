FROM php:7.4-apache-bullseye

RUN apt-get update &&\
    apt-get install -y --no-install-recommends libvips libvips-dev &&\
    pecl channel-update pecl.php.net &&\
    pecl install vips &&\
    apt-get autoremove --purge -y libvips-dev &&\
    echo "extension=vips.so" >> /usr/local/etc/php/conf.d/vips.ini &&\
    echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/filesize.ini &&\
    echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/filesize.ini &&\
    apt-get clean &&\
    rm -rf /var/cache/apt /tmp/pear &&\
    a2enmod rewrite

COPY ./apache.conf /etc/apache2/sites-enabled/000-default.conf

COPY . /var/www
 
WORKDIR /var/www

RUN bin/console cache:clear

EXPOSE 80
 
CMD ["apache2-foreground"]