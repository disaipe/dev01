#!/bin/sh

if [ "$#" -eq 0 ]; then
  chmod 0644 /etc/cron.d/scheduler && crontab /etc/cron.d/scheduler
  service cron start

  /bin/sh -c /usr/bin/php-fpm -o
fi

exec "$@"
