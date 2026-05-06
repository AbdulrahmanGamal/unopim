FROM webkul/unopim:1.0.1

COPY dockerfiles/q-entrypoint.sh /usr/local/bin/q-entrypoint.sh
RUN chmod +x /usr/local/bin/q-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/q-entrypoint.sh"]
