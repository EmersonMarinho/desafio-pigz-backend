#!/bin/bash
set -e

if [ -z "$JWT_PASSPHRASE" ]; then
    if [ -f .env ]; then
        JWT_PASSPHRASE=$(grep '^JWT_PASSPHRASE=' .env | cut -d '=' -f2)
    fi
fi

JWT_PASSPHRASE=${JWT_PASSPHRASE:-your-secret-passphrase}

if [ ! -f config/jwt/private.pem ]; then
    echo "Generating JWT keys..."
    mkdir -p config/jwt
    openssl genrsa -out config/jwt/private.pem -passout pass:$JWT_PASSPHRASE -aes256 4096
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:$JWT_PASSPHRASE
    echo "JWT keys generated successfully!"
fi

echo "Waiting for database connection..."
until php bin/console doctrine:database:create --if-not-exists --no-interaction 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 2
done

echo "Database is ready!"

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Migrations completed!"

echo "Clearing cache..."
php bin/console cache:clear --no-interaction

echo "Application ready! 🚀"

exec php-fpm
