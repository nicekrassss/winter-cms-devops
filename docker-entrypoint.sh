#!/bin/sh
set -e
echo "Current directory: $(pwd)"
php artisan winter:version
exec "$@"