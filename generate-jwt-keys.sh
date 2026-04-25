#!/bin/bash
set -e

# Couleurs pour les logs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🔐 Génération des clés JWT pour Maison de l'Épouvante${NC}"

# Créer le répertoire JWT s'il n'existe pas
mkdir -p /jwt-keys

# Vérifier que JWT_PASSPHRASE est définie
if [ -z "$JWT_PASSPHRASE" ]; then
    echo "❌ JWT_PASSPHRASE non définie"
    exit 1
fi

# Générer les clés JWT
cd /jwt-keys
openssl genpkey -out private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:"$JWT_PASSPHRASE"
openssl pkey -in private.pem -out public.pem -pubout -passin pass:"$JWT_PASSPHRASE"

# Définir les bonnes permissions (lisible par www-data)
chmod 644 private.pem public.pem
chown 33:33 private.pem public.pem

echo -e "${GREEN}✅ Clés JWT générées avec succès${NC}"
ls -lh /jwt-keys/
