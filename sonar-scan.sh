#!/bin/bash
# Script pour analyser le code avec SonarQube
# Usage: ./sonar-scan.sh [sonar-host-url]

set -e

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 SonarQube Code Analysis${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ Error: composer.json not found. Are you in the project root?${NC}"
    exit 1
fi

# Vérifier que sonar-project.properties existe
if [ ! -f "sonar-project.properties" ]; then
    echo -e "${RED}❌ Error: sonar-project.properties not found${NC}"
    exit 1
fi

# Configuration SonarQube
SONAR_HOST_URL="${1:-${SONAR_HOST_URL:-http://localhost:9000}}"
SONAR_TOKEN="${SONAR_TOKEN:-}"

if [ -z "$SONAR_TOKEN" ]; then
    echo -e "${YELLOW}⚠️  SONAR_TOKEN not set. You may need to authenticate.${NC}"
    echo -e "${YELLOW}   Set it with: export SONAR_TOKEN=your_token${NC}"
    echo ""
fi

# Vérifier si sonar-scanner est installé
USE_DOCKER=false
if ! command -v sonar-scanner &> /dev/null; then
    if command -v docker &> /dev/null; then
        echo -e "${YELLOW}⚠️  sonar-scanner not found, will use Docker instead${NC}"
        USE_DOCKER=true
    else
        echo -e "${RED}❌ Neither sonar-scanner nor Docker found${NC}"
        echo ""
        echo "Install sonar-scanner with:"
        echo ""
        echo "  # macOS"
        echo "  brew install sonar-scanner"
        echo ""
        echo "  # Linux"
        echo "  wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-linux.zip"
        echo "  unzip sonar-scanner-cli-5.0.1.3006-linux.zip"
        echo "  export PATH=\$PATH:\$PWD/sonar-scanner-5.0.1.3006-linux/bin"
        echo ""
        echo "Or install Docker to run the scanner in a container"
        echo ""
        exit 1
    fi
fi

# Installer les dépendances si nécessaire
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}⚠️  Installing dependencies...${NC}"
    composer install --no-interaction --prefer-dist
fi

# Exécuter les tests avec couverture de code
echo -e "${BLUE}🧪 Running tests with coverage...${NC}"

# Créer le fichier .env.test si nécessaire
if [ ! -f ".env.test" ]; then
    echo -e "${YELLOW}⚠️  Creating .env.test file...${NC}"
    cat > .env.test << 'EOF'
KERNEL_CLASS='App\Kernel'
APP_SECRET='test-secret-for-sonar'
SYMFONY_DEPRECATIONS_HELPER=999999
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
DEFAULT_URI=http://localhost:8000
FRONTEND_URL=http://localhost:3000
JWT_PASSPHRASE=test-passphrase-for-sonar
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
MAILER_DSN=null://null
STRIPE_SECRET_KEY=sk_test_mock_key_for_sonar
EOF
fi

# Générer les clés JWT si nécessaire
mkdir -p config/jwt
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    echo -e "${YELLOW}⚠️  Generating JWT keys...${NC}"
    openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:test-passphrase-for-sonar 4096 > /dev/null 2>&1
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:test-passphrase-for-sonar > /dev/null 2>&1
fi

# Clear cache
php bin/console cache:clear --env=test > /dev/null 2>&1

# Exécuter les tests avec couverture
echo "Running PHPUnit with coverage..."
php bin/phpunit \
    --coverage-clover=coverage.xml \
    --log-junit=junit.xml \
    --colors=never

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Tests failed. Fix them before running SonarQube analysis.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Tests passed${NC}"
echo ""

# Exécuter l'analyse SonarQube
echo -e "${BLUE}📊 Running SonarQube analysis...${NC}"
echo "Host: $SONAR_HOST_URL"
if [ "$USE_DOCKER" = true ]; then
    echo "Method: Docker"
fi
echo ""

if [ "$USE_DOCKER" = true ]; then
    # Utiliser Docker
    DOCKER_ARGS="run --rm -v $(pwd):/usr/src -w /usr/src sonarsource/sonar-scanner-cli:latest"
    
    if [ -n "$SONAR_TOKEN" ]; then
        docker $DOCKER_ARGS -Dsonar.host.url="$SONAR_HOST_URL" -Dsonar.token="$SONAR_TOKEN"
    else
        docker $DOCKER_ARGS -Dsonar.host.url="$SONAR_HOST_URL"
    fi
else
    # Utiliser sonar-scanner local
    SONAR_SCANNER_OPTS=""
    if [ -n "$SONAR_TOKEN" ]; then
        SONAR_SCANNER_OPTS="-Dsonar.token=$SONAR_TOKEN"
    fi

    sonar-scanner \
        -Dsonar.host.url="$SONAR_HOST_URL" \
        $SONAR_SCANNER_OPTS
fi

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✅ SonarQube analysis completed successfully!${NC}"
    echo ""
    echo -e "View results at: ${BLUE}$SONAR_HOST_URL/dashboard?id=maison-de-lepouvante-back${NC}"
else
    echo ""
    echo -e "${RED}❌ SonarQube analysis failed${NC}"
    exit 1
fi

# Nettoyage
echo ""
echo -e "${BLUE}🧹 Cleaning up...${NC}"
rm -f coverage.xml junit.xml

echo -e "${GREEN}✅ Done!${NC}"
