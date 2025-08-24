#!/bin/sh

# Cron
crond -f -l 2 &

# FrankenPHP
exec frankenphp run --config /etc/frankenphp/Caddyfile
