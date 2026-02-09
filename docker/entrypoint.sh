#!/bin/bash
set -e

echo "🚀 Pigz API - Iniciando setup automático..."

echo "⏳ Aguardando MySQL..."
until php -r '
    $url = getenv("DATABASE_URL") ?: "";
    if (empty($url)) exit(1);
    $params = parse_url($url);
    $host = $params["host"] ?? "database";
    $port = $params["port"] ?? 3306;
    $dbname = trim($params["path"] ?? "/pigz_db", "/");
    $user = $params["user"] ?? "user";
    $pass = $params["pass"] ?? "password";
    try {
        new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
' 2>/dev/null; do
    sleep 2
done
echo "✅ MySQL disponível!"

if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependências (composer install)..."
    composer install --no-interaction --prefer-dist
else
    echo "📦 Dependências já instaladas."
fi

echo "🗄️ Executando migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Gerar chaves JWT e garantir permissões para www-data (php-fpm)
if [ ! -f "config/jwt/private.pem" ] || [ ! -r "config/jwt/private.pem" ]; then
    echo "🔑 Gerando chaves JWT..."
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --overwrite
fi
chown www-data:www-data config/jwt/*.pem 2>/dev/null || true
chmod 644 config/jwt/*.pem 2>/dev/null || true

echo "👤 Criando usuário admin para testes..."
php bin/console app:create-user admin@pigz.com password123 --admin 2>/dev/null || true

echo "✅ Setup concluído! API pronta em http://localhost:8080"
echo "   Login: admin@pigz.com / password123"
echo ""

exec "$@"
