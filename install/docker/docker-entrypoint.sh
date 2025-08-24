#!/bin/sh

# Cron
crond -f -l 2 &

# FrankePHP
exec frankenphp run --config /etc/frankenphp/Caddyfile
