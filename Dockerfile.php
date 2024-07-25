# composer dependencies
FROM composer:1 as vendor
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --ignore-platform-reqs --no-interaction --prefer-dist

FROM craftcms/nginx:8.2 as cms

# switch to the root user to install mysql tools
USER root
RUN apk add --no-cache mysql-client htop
USER www-data

# the user is `www-data`, so we copy the files using the user and group
COPY --chown=www-data:www-data --from=vendor /app/vendor/ /app/vendor/
COPY --chown=www-data:www-data . .

# Duplicated only to run queue listen comand on
FROM craftcms/cli:8.2 as cli

# switch to the root user to install mysql tools
USER root
RUN apk add --no-cache mysql-client htop
USER www-data

# the user is `www-data`, so we copy the files using the user and group
COPY --chown=www-data:www-data --from=vendor /app/vendor/ /app/vendor/
COPY --chown=www-data:www-data . .
CMD ["php", "craft", "queue/listen"]