FROM webkul/unopim:1.0.1

COPY dockerfiles/web-entrypoint.sh /usr/local/bin/web-entrypoint.sh
RUN chmod +x /usr/local/bin/web-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/web-entrypoint.sh"]
