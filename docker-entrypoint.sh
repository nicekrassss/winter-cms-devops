#!/bin/sh
set -e

php artisan winter:version

exec "$@"