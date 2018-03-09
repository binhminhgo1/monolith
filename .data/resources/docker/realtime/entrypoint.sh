#!/bin/sh

/wait-for-it.sh queue:5672 -t 0 -- /usr/bin/supervisord -n -c /etc/supervisord.conf
