FROM webkul/unopim:1.0.1

WORKDIR /var/www/html

# Copy the application source from build context (the cloned repo)
COPY --chown=www-data:www-data . /var/www/html/

# Install PHP deps for production
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Build front-end assets
RUN npm ci && npm run build && rm -rf node_modules

# Make sure storage/cache are writable
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Move entrypoint outside the app dir so nothing can shadow it
COPY dockerfiles/web-entrypoint.sh /usr/local/bin/web-entrypoint.sh
RUN chmod +x /usr/local/bin/web-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/web-entrypoint.sh"]
