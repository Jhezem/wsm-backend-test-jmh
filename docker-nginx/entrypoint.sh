#!/bin/bash


envsubst '$NGINX_ROOT $NGINX_FPM_HOST $NGINX_FPM_PORT $NGINX_FRONTEND' < /etc/nginx/dev.tmpl > /etc/nginx/conf.d/default.conf
exec nginx -g "daemon off;"
