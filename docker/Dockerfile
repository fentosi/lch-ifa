FROM php:7.3.8-apache

RUN apt-get update -yqq && \
    apt-get install -y apt-utils zip unzip && \
    apt-get install -y nano && \
    apt-get install -y libzip-dev libpq-dev && \
    a2enmod rewrite && \
    docker-php-ext-configure zip --with-libzip && \
    docker-php-ext-install zip && \
    rm -rf /var/lib/apt/lists/*

RUN curl -Ss https://getcomposer.org/installer | php && \
    mv composer.phar /usr/bin/composer

COPY app.conf /etc/apache2/sites-available/

RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/php.ini

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
