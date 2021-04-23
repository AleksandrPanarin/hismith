FROM webdevops/php:7.4

ADD docker/app/crontab /etc/cron.d/cronfile
RUN chmod 0644 /etc/cron.d/cronfile && \
    touch /var/log/cron.log
RUN apt-get update && apt-get install cron

EXPOSE 9000