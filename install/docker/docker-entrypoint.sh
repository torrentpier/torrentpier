#!/bin/sh

crond -f -l 2 &

exec frankenphp run --config /etc/frankenphp/Caddyfile