ARG PHP_VERSION=8.0

FROM phpdockerio/php:${PHP_VERSION}-fpm
WORKDIR "/var/www/html"

ARG PHP_VERSION

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

ARG USER_ID=1000

ENV PHP=php${PHP_VERSION}

RUN usermod -u $USER_ID www-data

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install $PHP-mysql $PHP-xdebug $PHP-ldap

# Install tools
RUN apt-get -y install git cron

# Install Nodejs, Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
  && apt-get -y install nodejs \
  && npm install -g yarn

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN ln -s /usr/sbin/php-fpm${PHP_VERSION} /usr/bin/php-fpm

# Copy entrypoint
ADD docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

ENV HOME=/tmp
