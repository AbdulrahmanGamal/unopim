FROM webkul/unopim:1.0.1

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html/

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

COPY dockerfiles/q-entrypoint.sh /usr/local/bin/q-entrypoint.sh
RUN chmod +x /usr/local/bin/q-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/q-entrypoint.sh"]
