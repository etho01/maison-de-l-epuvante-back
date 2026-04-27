#!/bin/bash
# Script pour exécuter les tests dans un environnement similaire au CI

set -e

echo "🧪 Preparing test environment (CI-like)..."

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Error: composer.json not found. Are you in the project root?${NC}"
    exit 1
fi

# Créer le fichier .env.test s'il n'existe pas
if [ ! -f ".env.test" ]; then
    echo -e "${YELLOW}⚠️  Creating .env.test file...${NC}"
    cat > .env.test << 'EOF'
KERNEL_CLASS='App\Kernel'
APP_SECRET='test-secret-for-ci'
SYMFONY_DEPRECATIONS_HELPER=999999
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
DEFAULT_URI=http://localhost:8000
FRONTEND_URL=http://localhost:3000
JWT_PASSPHRASE=test-passphrase-for-ci
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
MAILER_DSN=null://null
STRIPE_SECRET_KEY=sk_test_mock_key_for_ci
EOF
    echo -e "${GREEN}✅ .env.test created${NC}"
fi

# Créer le répertoire JWT s'il n'existe pas
mkdir -p config/jwt

# Générer les clés JWT si elles n'existent pas
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    echo -e "${YELLOW}⚠️  Generating JWT keys...${NC}"
    openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:test-passphrase-for-ci 4096 > /dev/null 2>&1
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:test-passphrase-for-ci > /dev/null 2>&1
    echo -e "${GREEN}✅ JWT keys generated${NC}"
fi

# Installer les dépendances si nécessaire
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}⚠️  Installing dependencies...${NC}"
    composer install --prefer-dist --no-progress --no-interaction
    echo -e "${GREEN}✅ Dependencies installed${NC}"
fi

# Nettoyer le cache de test
echo "🧹 Clearing test cache..."
rm -rf var/cache/test var/test.db
php bin/console cache:clear --env=test > /dev/null 2>&1
echo -e "${GREEN}✅ Cache cleared${NC}"

# Exécuter les tests
echo ""
echo "🚀 Running PHPUnit tests..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
php bin/phpunit "$@"
TEST_EXIT_CODE=$?

echo ""
if [ $TEST_EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ All tests passed!${NC}"
else
    echo -e "${RED}❌ Some tests failed (exit code: $TEST_EXIT_CODE)${NC}"
fi

exit $TEST_EXIT_CODE
