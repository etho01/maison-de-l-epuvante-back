#!/bin/bash
# Configuration de l'authentification Kubernetes dans Vault

set -e

echo "🔐 Configuration de Vault pour l'authentification Kubernetes"
echo "============================================================"
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

# Vérifier que le ROOT_TOKEN est fourni
if [ -z "${1:-}" ]; then
  echo -e "${RED}❌ Usage: $0 <ROOT_TOKEN>${NC}"
  echo ""
  echo "Exemple:"
  echo "  $0 hvs.XXXXXXXXXXXXXXXXXXXXXX"
  exit 1
fi

ROOT_TOKEN="$1"
NAMESPACE="maison-epouvante"
SA_NAME="vault-auth"
VAULT_ROLE="maison-epouvante-app"

# Port-forward Vault
echo -e "${CYAN}📡 Démarrage du port-forward vers Vault...${NC}"
sudo kubectl -n vault port-forward svc/vault 8200:8200 > /dev/null 2>&1 &
PF_PID=$!
sleep 3

# Cleanup
cleanup() {
    echo ""
    echo -e "${CYAN}🧹 Nettoyage...${NC}"
    kill $PF_PID 2>/dev/null || true
}
trap cleanup EXIT

# Configuration Vault
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Vérifier la connexion
echo -e "${CYAN}🔍 Vérification de la connexion à Vault...${NC}"
if ! vault status &>/dev/null; then
    if vault status 2>&1 | grep -q "Vault is sealed"; then
        echo -e "${RED}❌ Vault est scellé. Desceller d'abord avec: vault operator unseal${NC}"
        exit 1
    fi
    echo -e "${RED}❌ Impossible de se connecter à Vault${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Connexion à Vault OK${NC}"
echo ""

# 1. Activer l'authentification Kubernetes
echo -e "${CYAN}🔧 Configuration de l'authentification Kubernetes...${NC}"

if vault auth list | grep -q "kubernetes/"; then
    echo -e "${YELLOW}⚠️  Auth Kubernetes déjà activée${NC}"
else
    echo "Activation de l'authentification Kubernetes..."
    vault auth enable kubernetes
    echo -e "${GREEN}✅ Auth Kubernetes activée${NC}"
fi

# 2. Récupérer les informations du cluster
echo ""
echo -e "${CYAN}📥 Récupération des informations du ServiceAccount...${NC}"

# Attendre que le ServiceAccount et son token soient créés
sudo kubectl -n ${NAMESPACE} apply -f - <<EOF
apiVersion: v1
kind: ServiceAccount
metadata:
  name: ${SA_NAME}
  namespace: ${NAMESPACE}
---
apiVersion: v1
kind: Secret
metadata:
  name: ${SA_NAME}-token
  namespace: ${NAMESPACE}
  annotations:
    kubernetes.io/service-account.name: ${SA_NAME}
type: kubernetes.io/service-account-token
EOF

# Attendre que le token soit généré
echo "Attente de la génération du token..."
for i in {1..30}; do
    if sudo kubectl -n ${NAMESPACE} get secret ${SA_NAME}-token &>/dev/null; then
        TOKEN_NAME="${SA_NAME}-token"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}❌ Timeout: token non généré${NC}"
        exit 1
    fi
    sleep 2
done

# Récupérer le token et le CA
SA_JWT_TOKEN=$(sudo kubectl -n ${NAMESPACE} get secret ${TOKEN_NAME} -o jsonpath='{.data.token}' | base64 -d)
SA_CA_CRT=$(sudo kubectl -n ${NAMESPACE} get secret ${TOKEN_NAME} -o jsonpath='{.data.ca\.crt}' | base64 -d)

# URL de l'API Kubernetes
K8S_HOST=$(sudo kubectl config view --raw --minify --flatten -o jsonpath='{.clusters[0].cluster.server}')

echo -e "${GREEN}✅ Informations récupérées${NC}"

# 3. Configurer Vault pour se connecter à Kubernetes
echo ""
echo -e "${CYAN}🔧 Configuration de la connexion Vault → Kubernetes...${NC}"

# Utiliser l'URL interne du cluster au lieu de localhost
K8S_INTERNAL_HOST="https://kubernetes.default.svc.cluster.local:443"

vault write auth/kubernetes/config \
    kubernetes_host="$K8S_INTERNAL_HOST" \
    kubernetes_ca_cert="$SA_CA_CRT" \
    disable_local_ca_jwt="false"

echo -e "${GREEN}✅ Configuration Kubernetes OK${NC}"

# 4. Créer une policy Vault pour l'application
echo ""
echo -e "${CYAN}📝 Création de la policy Vault...${NC}"

vault policy write ${NAMESPACE}-app - <<EOF
# Policy pour l'application ${NAMESPACE}
path "secret/data/${NAMESPACE}/*" {
  capabilities = ["read", "list"]
}

path "secret/metadata/${NAMESPACE}/*" {
  capabilities = ["read", "list"]
}
EOF

echo -e "${GREEN}✅ Policy créée${NC}"

# 5. Créer le rôle Vault
echo ""
echo -e "${CYAN}🎭 Création du rôle Vault...${NC}"

vault write auth/kubernetes/role/${VAULT_ROLE} \
    bound_service_account_names=${SA_NAME} \
    bound_service_account_namespaces=${NAMESPACE} \
    policies=${NAMESPACE}-app \
    ttl=24h

echo -e "${GREEN}✅ Rôle créé${NC}"

# 6. Vérification
echo ""
echo -e "${CYAN}🔍 Vérification de la configuration...${NC}"

vault read auth/kubernetes/role/${VAULT_ROLE}

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✅ Configuration Vault terminée avec succès !${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${YELLOW}📋 Prochaines étapes:${NC}"
echo ""
echo "1. Vérifier le SecretStore:"
echo "   kubectl -n ${NAMESPACE} get secretstore vault-backend"
echo ""
echo "2. Vérifier l'ExternalSecret:"
echo "   kubectl -n ${NAMESPACE} get externalsecret"
echo ""
echo "3. Si le SecretStore n'est pas Ready, attendre quelques secondes puis:"
echo "   kubectl -n ${NAMESPACE} describe secretstore vault-backend"
echo ""
echo "4. Une fois Ready, l'ExternalSecret devrait synchroniser les secrets automatiquement"
echo ""

# Cleanup automatique via trap
