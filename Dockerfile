FROM avonnadozie/nginx-laravel-server

COPY ./ ${WEBROOT}

#ENV STARTUP_SCRIPT=deploy/docker/start.sh
#ENV CRON_FILE=deploy/cron
ENV RUN_SCHEDULER=1
ENV PRODUCTION=1
ENV RUN_MIGRATIONS_ON_BUILD=1
ENV COMPOSER_INSTALL_ON_BUILD=1