ARG PHP_VERSION=8.0

FROM phpdockerio/php:${PHP_VERSION}-fpm
WORKDIR "/var/www/html"

ARG PHP_VERSION

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive
ARG USER_ID=1000
ARG NODE_VERSION=18

# https://learn.microsoft.com/en-us/sql/connect/php/microsoft-php-drivers-for-sql-server-support-matrix?view=sql-server-ver16#sql-server-version-certified-compatibility
ARG SQLSRV_VERSION=5.10.1

ENV PHP=php${PHP_VERSION}

RUN usermod -u $USER_ID www-data

# Install selected extensions and other stuff
RUN apt update
RUN apt install php-pear
RUN apt -y --no-install-recommends install \
  $PHP-dev \
  $PHP-intl \
  $PHP-mysql \
  $PHP-pgsql \
  $PHP-xdebug \
  $PHP-ldap \
  $PHP-gd

# Instal SQL Server driver
RUN apt -y --no-install-recommends install unixodbc unixodbc-dev g++ make
RUN pecl install sqlsrv-${SQLSRV_VERSION} \
  && pecl install pdo_sqlsrv-${SQLSRV_VERSION} \
  && echo -e "; priority=20\nextension=sqlsrv.so\n" > /etc/php/${PHP_VERSION}/mods-available/sqlsrv.ini \
  && echo -e "; priority=20\nextension=pdo_sqlsrv.so\n" > /etc/php/${PHP_VERSION}/mods-available/pdo_sqlsrv.ini \
  && phpenmod sqlsrv pdo_sqlsrv

# Install Microsoft ODBC driver for sqlsrv driver
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -
RUN curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list > /etc/apt/sources.list.d/mssql-release.list \
  && apt update \
  && ACCEPT_EULA=Y apt install -y msodbcsql18

# Install tools
RUN apt -y install git cron supervisor mysql-client procps ca-certificates gnupg

RUN mkdir -p /etc/apt/keyrings

# Install Nodejs, Yarn
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update
RUN apt-get -y install nodejs && npm install -g npm yarn

RUN apt clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Additional actions
RUN ln -s /usr/sbin/php-fpm${PHP_VERSION} /usr/bin/php-fpm \
  && sed -i 's/DEFAULT:@SECLEVEL=2/DEFAULT:@SECLEVEL=0/g' /etc/ssl/openssl.cnf

# Copy entrypoint
ADD docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

ENV HOME=/tmp
