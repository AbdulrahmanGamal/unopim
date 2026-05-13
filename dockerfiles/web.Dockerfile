FROM webkul/unopim:1.0.1

WORKDIR /var/www/html

# Copy the application source from the build context (the cloned repo)
COPY --chown=www-data:www-data . /var/www/html/

# Install PHP dependencies (skip if no composer.json)
RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --no-progress; \
    else \
        echo "No composer.json found, skipping composer install"; \
    fi

# Build front-end assets (skip if no package.json)
RUN if [ -f package.json ]; then \
        npm install && npm run build && rm -rf node_modules; \
    else \
        echo "No package.json found, skipping front-end build"; \
    fi

# Ensure storage and cache directories are writable by Apache
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Move entrypoint outside the app dir so nothing can shadow it
COPY dockerfiles/web-entrypoint.sh /usr/local/bin/web-entrypoint.sh
RUN chmod +x /usr/local/bin/web-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/web-entrypoint.sh"]
