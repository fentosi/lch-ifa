FROM php:7.1-apache-stretch

RUN apt-get update -yqq && \
    apt-get install -y apt-utils zip unzip && \
    apt-get install -y nano && \
    apt-get install -y libzip-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev && \
    a2enmod rewrite && \
    docker-php-ext-configure zip --with-libzip && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install zip && \
    docker-php-ext-install mysqli && docker-php-ext-enable mysqli && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-jpeg-dir=/usr/lib \
        --with-freetype-dir=/usr/include/freetype2 && \
    docker-php-ext-install gd

RUN curl -Ss https://getcomposer.org/installer | php && \
    mv composer.phar /usr/bin/composer

COPY app.conf /etc/apache2/sites-available/

RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/php.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/php.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/php.ini

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
