#!/bin/bash
# Script de mise à jour des secrets Vault - Maison de l'Épouvante Backend

set -e

echo "🔐 Mise à jour des secrets Vault - Maison de l'Épouvante"
echo "========================================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Vérifier que le ROOT_TOKEN est fourni
if [ -z "${1:-}" ]; then
  echo -e "${RED}❌ Usage: $0 <ROOT_TOKEN>${NC}"
  echo ""
  echo "Exemple:"
  echo "  $0 hvs.XXXXXXXXXXXXXXXXXXXXXX"
  exit 1
fi

ROOT_TOKEN="$1"

# Vérifier kubectl
if ! command -v kubectl &> /dev/null; then
    echo -e "${RED}❌ kubectl non trouvé${NC}"
    exit 1
fi

# Vérifier vault CLI
if ! command -v vault &> /dev/null; then
    echo -e "${RED}❌ vault CLI non trouvé${NC}"
    exit 1
fi

# Port-forward Vault en arrière-plan
echo -e "${CYAN}📡 Démarrage du port-forward vers Vault...${NC}"
kubectl -n vault port-forward svc/vault 8200:8200 > /dev/null 2>&1 &
PF_PID=$!
sleep 3

# Créer un fichier temporaire sécurisé
TEMP_FILE=$(mktemp)

# Trap pour arrêter le port-forward à la fin
cleanup() {
    echo ""
    echo -e "${CYAN}🧹 Nettoyage...${NC}"
    kill $PF_PID 2>/dev/null || true
    rm -f "$TEMP_FILE" 2>/dev/null || true
}
trap cleanup EXIT

# Configuration Vault
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Vérifier la connexion
echo -e "${CYAN}🔍 Vérification de la connexion à Vault...${NC}"
if ! vault status &>/dev/null; then
    # Vérifier si Vault est scellé
    if vault status 2>&1 | grep -q "Vault is sealed"; then
        echo -e "${RED}❌ Vault est scellé (sealed)${NC}"
        echo -e "${YELLOW}📋 Pour desceller Vault:${NC}"
        echo ""
        echo "  1. Dans un autre terminal, exécutez:"
        echo "     vault operator unseal <VOTRE_UNSEAL_KEY>"
        echo ""
        echo "  2. Ou si vous avez la clé maintenant:"
        read -sp "     Entrez la clé de descellement (ENTRÉE pour annuler): " UNSEAL_KEY
        echo ""
        
        if [ -n "$UNSEAL_KEY" ]; then
            echo -e "${CYAN}🔓 Tentative de descellement...${NC}"
            if vault operator unseal "$UNSEAL_KEY" &>/dev/null; then
                echo -e "${GREEN}✅ Vault descellé avec succès${NC}"
            else
                echo -e "${RED}❌ Échec du descellement - clé invalide${NC}"
                exit 1
            fi
        else
            echo -e "${RED}❌ Script annulé${NC}"
            exit 1
        fi
    else
        echo -e "${RED}❌ Impossible de se connecter à Vault${NC}"
        exit 1
    fi
fi

# Vérifier à nouveau après descellement potentiel
if ! vault status &>/dev/null; then
    echo -e "${RED}❌ Vault toujours inaccessible${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Connexion à Vault OK${NC}"
echo ""

# Récupérer les secrets actuels
echo -e "${CYAN}📥 Récupération des secrets actuels...${NC}"
if ! vault kv get -format=json secret/maison-epouvante/app > "$TEMP_FILE" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  Aucun secret existant, création d'une nouvelle configuration${NC}"
    echo '{"data":{"data":{}}}' > "$TEMP_FILE"
fi

# Fonction pour récupérer une valeur actuelle
get_current_value() {
    local key=$1
    jq -r ".data.data.${key} // \"\"" "$TEMP_FILE"
}

# Fonction pour demander une valeur
ask_value() {
    local key=$1
    local description=$2
    local current_value=$(get_current_value "$key")
    local value
    
    echo "" >&2
    if [ -n "$current_value" ] && [ "$current_value" != "null" ]; then
        echo -e "${YELLOW}🔑 ${key}${NC} - ${description}" >&2
        echo -e "${CYAN}   Actuelle: ${current_value}${NC}" >&2
        read -p "   Nouvelle (ENTRÉE pour garder): " value >&2
        echo "${value:-$current_value}"
    else
        echo -e "${YELLOW}🔑 ${key}${NC} - ${description}" >&2
        read -p "   Valeur: " value >&2
        echo "${value}"
    fi
}

# Fonction pour demander une valeur sensible (masquée)
ask_secret() {
    local key=$1
    local description=$2
    local current_value=$(get_current_value "$key")
    local value
    
    echo "" >&2
    if [ -n "$current_value" ] && [ "$current_value" != "null" ]; then
        echo -e "${YELLOW}🔐 ${key}${NC} - ${description}" >&2
        echo -e "${CYAN}   Actuelle: ${current_value:0:10}...${NC} (masquée)" >&2
        read -sp "   Nouvelle (ENTRÉE pour garder): " value
        echo "" >&2
        echo "${value:-$current_value}"
    else
        echo -e "${YELLOW}🔐 ${key}${NC} - ${description}" >&2
        read -sp "   Valeur: " value
        echo "" >&2
        echo "${value}"
    fi
}

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}📝 CONFIGURATION DES SECRETS${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Appuyez sur ENTRÉE sans saisir de valeur pour conserver la valeur actuelle."

# Application
echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Application Symfony${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
APP_ENV=$(ask_value "APP_ENV" "Environnement (prod/dev)")
APP_SECRET=$(ask_secret "APP_SECRET" "Secret Symfony (générez avec: openssl rand -base64 32)")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Base de données${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
DB_HOST=$(ask_value "DB_HOST" "Hôte MySQL/MariaDB")
DB_PORT=$(ask_value "DB_PORT" "Port MySQL")
DB_NAME=$(ask_value "DB_NAME" "Nom de la base de données")
DB_USER=$(ask_value "DB_USER" "Utilisateur MySQL")
DB_PASSWORD=$(ask_secret "DB_PASSWORD" "Mot de passe MySQL")
DB_VERSION=$(ask_value "DB_VERSION" "Version serveur (ex: mariadb-11.3.0)")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     JWT Authentication${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"

# Vérifier si des clés JWT existent déjà dans Vault
EXISTING_JWT_PRIVATE=$(get_current_value "JWT_PRIVATE_KEY")
EXISTING_JWT_PUBLIC=$(get_current_value "JWT_PUBLIC_KEY")

if [ -n "$EXISTING_JWT_PRIVATE" ] && [ "$EXISTING_JWT_PRIVATE" != "null" ]; then
    echo -e "${GREEN}✅ Clés JWT existantes trouvées dans Vault${NC}"
    read -p "   Voulez-vous les régénérer ? (o/N): " regenerate
    
    if [[ "$regenerate" =~ ^([oO][uU][iI]|[oO])$ ]]; then
        echo -e "${YELLOW}🔐 Génération de nouvelles clés JWT...${NC}"
        
        # Demander la passphrase
        read -sp "   JWT Passphrase (générez avec: openssl rand -base64 32): " JWT_PASSPHRASE
        echo ""
        
        # Générer les clés dans un répertoire temporaire
        TEMP_JWT_DIR=$(mktemp -d)
        cd "$TEMP_JWT_DIR"
        
        openssl genpkey -out private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:"$JWT_PASSPHRASE"
        openssl pkey -in private.pem -out public.pem -pubout -passin pass:"$JWT_PASSPHRASE"
        
        # Encoder en base64 pour Vault
        JWT_PRIVATE_KEY=$(cat private.pem | base64 -w 0)
        JWT_PUBLIC_KEY=$(cat public.pem | base64 -w 0)
        
        # Nettoyer
        cd - > /dev/null
        rm -rf "$TEMP_JWT_DIR"
        
        echo -e "${GREEN}✅ Nouvelles clés JWT générées${NC}"
    else
        echo -e "${CYAN}Conservation des clés JWT existantes${NC}"
        JWT_PASSPHRASE=$(ask_secret "JWT_PASSPHRASE" "Passphrase JWT")
        JWT_PRIVATE_KEY="$EXISTING_JWT_PRIVATE"
        JWT_PUBLIC_KEY="$EXISTING_JWT_PUBLIC"
    fi
else
    echo -e "${YELLOW}🔐 Aucune clé JWT trouvée, génération de nouvelles clés...${NC}"
    
    # Demander la passphrase
    read -sp "   JWT Passphrase (générez avec: openssl rand -base64 32): " JWT_PASSPHRASE
    echo ""
    
    # Générer les clés dans un répertoire temporaire
    TEMP_JWT_DIR=$(mktemp -d)
    cd "$TEMP_JWT_DIR"
    
    openssl genpkey -out private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:"$JWT_PASSPHRASE"
    openssl pkey -in private.pem -out public.pem -pubout -passin pass:"$JWT_PASSPHRASE"
    
    # Encoder en base64 pour Vault
    JWT_PRIVATE_KEY=$(cat private.pem | base64 -w 0)
    JWT_PUBLIC_KEY=$(cat public.pem | base64 -w 0)
    
    # Nettoyer
    cd - > /dev/null
    rm -rf "$TEMP_JWT_DIR"
    
    echo -e "${GREEN}✅ Clés JWT générées${NC}"
fi

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     CORS Configuration${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
CORS_ALLOW_ORIGIN=$(ask_value "CORS_ALLOW_ORIGIN" "Origine CORS autorisée (regex)")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Mailer (Symfony)${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
MAILER_DSN=$(ask_secret "MAILER_DSN" "DSN Mailer (ex: smtp://user:pass@smtp.example.com:587 ou null://null)")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     Stripe${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
STRIPE_SECRET_KEY=$(ask_secret "STRIPE_SECRET_KEY" "Stripe Secret Key (sk_test_... ou sk_live_...)")

echo ""
echo -e "${CYAN}══════════════════════════════════${NC}"
echo -e "${CYAN}     URLs${NC}"
echo -e "${CYAN}══════════════════════════════════${NC}"
DEFAULT_URI=$(ask_value "DEFAULT_URI" "URL de base de l'API")
FRONTEND_URL=$(ask_value "FRONTEND_URL" "URL du frontend")

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}📋 RÉSUMÉ DE LA CONFIGURATION${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "Environnement: $APP_ENV"
echo "API URL: $DEFAULT_URI"
echo "Frontend URL: $FRONTEND_URL"
echo ""
echo "Base de données: $DB_USER@$DB_HOST:$DB_PORT/$DB_NAME ($DB_VERSION)"
echo "Mailer: $MAILER_DSN"
echo ""

read -p "Voulez-vous sauvegarder cette configuration dans Vault ? (o/N): " confirm
if [[ ! "$confirm" =~ ^([oO][uU][iI]|[oO])$ ]]; then
    echo -e "${RED}❌ Annulé${NC}"
    exit 0
fi

echo ""
echo -e "${CYAN}💾 Sauvegarde des secrets dans Vault...${NC}"

# Construction de DATABASE_URL pour Symfony/Doctrine
DATABASE_URL="mysql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?serverVersion=${DB_VERSION}"

vault kv put secret/maison-epouvante/app \
  APP_ENV="$APP_ENV" \
  APP_SECRET="$APP_SECRET" \
  DATABASE_URL="$DATABASE_URL" \
  DATABASE_HOST="$DB_HOST" \
  DATABASE_PORT="$DB_PORT" \
  DATABASE_NAME="$DB_NAME" \
  DATABASE_USER="$DB_USER" \
  DATABASE_PASSWORD="$DB_PASSWORD" \
  JWT_PASSPHRASE="$JWT_PASSPHRASE" \
  JWT_PRIVATE_KEY="$JWT_PRIVATE_KEY" \
  JWT_PUBLIC_KEY="$JWT_PUBLIC_KEY" \
  CORS_ALLOW_ORIGIN="$CORS_ALLOW_ORIGIN" \
  MAILER_DSN="$MAILER_DSN" \
  STRIPE_SECRET_KEY="$STRIPE_SECRET_KEY" \
  DEFAULT_URI="$DEFAULT_URI" \
  FRONTEND_URL="$FRONTEND_URL"

echo ""
echo -e "${GREEN}✅ Secrets sauvegardés dans Vault${NC}"
echo ""

echo -e "${CYAN}🔍 Vérification des secrets...${NC}"
vault kv get secret/maison-epouvante/app

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Configuration mise à jour avec succès !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📋 Prochaines étapes:${NC}"
echo ""
echo "1. Les secrets seront automatiquement synchronisés vers Kubernetes"
echo "   (délai: jusqu'à 15 minutes ou au prochain redémarrage des pods)"
echo ""
echo "2. Pour forcer la resynchronisation immédiate:"
echo "   kubectl -n maison-epouvante rollout restart deployment/maison-epouvante-back"
echo ""
echo "3. Vérifier la synchronisation:"
echo "   kubectl -n maison-epouvante get externalsecret"
echo "   kubectl -n maison-epouvante describe externalsecret maison-epouvante-back-vault"
echo ""

# Cleanup automatique via trap
