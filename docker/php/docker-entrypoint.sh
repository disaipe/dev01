#!/bin/sh

if [ "$#" -eq 0 ]; then
  chmod 0644 /etc/cron.d/scheduler && crontab -u www-data /etc/cron.d/scheduler
  service cron start

  # start fpm
  /usr/bin/supervisord
fi

exec "$@"
