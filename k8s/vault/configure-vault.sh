#!/bin/bash
set -euo pipefail

if [ -z "${1:-}" ]; then
  echo "Usage: $0 <ROOT_TOKEN>"
  exit 1
fi

ROOT_TOKEN="$1"
NAMESPACE="maison-epouvante"
SA_NAME="vault-auth"

# Configurer KUBECONFIG pour accéder au cluster K3s
export KUBECONFIG=/etc/rancher/k3s/k3s.yaml
KUBECTL="sudo -E kubectl"

echo "🔧 Configuration de Vault pour Maison de l'Épouvante..."

# Tuer les anciens port-forwards
pkill -f "port-forward.*vault" || true
sleep 2

# Port-forward Vault
$KUBECTL -n vault port-forward svc/vault 8200:8200 &
PF_PID=$!
sleep 3

export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN="$ROOT_TOKEN"

# Helper function to run vault commands in the pod
vault_exec() {
  $KUBECTL -n vault exec deploy/vault -- sh -c "export VAULT_ADDR=http://localhost:8200 VAULT_TOKEN=$ROOT_TOKEN && $*"
}

# Activer KV v2
echo "Activation du secret engine KV v2..."
vault_exec vault secrets enable -path=secret kv-v2 || echo "KV déjà activé"

# Créer la policy
echo "Création de la policy ${NAMESPACE}-app..."
POLICY_CONTENT="path \"secret/data/${NAMESPACE}/*\" {
  capabilities = [\"read\", \"list\"]
}
path \"secret/metadata/${NAMESPACE}/*\" {
  capabilities = [\"read\", \"list\"]
}"
echo "$POLICY_CONTENT" | $KUBECTL -n vault exec -i deploy/vault -- sh -c "export VAULT_ADDR=http://localhost:8200 VAULT_TOKEN=$ROOT_TOKEN && vault policy write ${NAMESPACE}-app -"

# Configurer auth Kubernetes
echo "Configuration de l'auth Kubernetes..."
vault_exec vault auth enable kubernetes || echo "Auth Kubernetes déjà activé"

# Créer le service account et les permissions RBAC depuis les fichiers
echo "Application des manifestes ServiceAccount et RBAC..."
$KUBECTL apply -f k8s/vault/serviceaccount.yaml
$KUBECTL apply -f k8s/vault/rbac.yaml

# Attendre que le token soit créé
echo "Attente du token..."
sleep 3

# Récupérer le token JWT et CA cert
SA_JWT_TOKEN=$($KUBECTL -n ${NAMESPACE} get secret ${SA_NAME}-token -o jsonpath='{.data.token}' | base64 -d)
K8S_CA_CERT=$($KUBECTL -n ${NAMESPACE} get secret ${SA_NAME}-token -o jsonpath='{.data.ca\.crt}' | base64 -d)

# Utiliser l'URL interne du cluster
K8S_INTERNAL_HOST="https://kubernetes.default.svc.cluster.local:443"

# Écrire le CA cert dans le pod Vault
$KUBECTL -n vault exec deploy/vault -- sh -c "echo '$K8S_CA_CERT' > /tmp/ca.crt"

echo "Configuration de l'authentification Kubernetes dans Vault..."
vault_exec vault write auth/kubernetes/config \
  token_reviewer_jwt="$SA_JWT_TOKEN" \
  kubernetes_host="$K8S_INTERNAL_HOST" \
  kubernetes_ca_cert=@/tmp/ca.crt

# Créer le role
echo "Création du role ${NAMESPACE}-app..."
vault_exec vault write auth/kubernetes/role/${NAMESPACE}-app \
  bound_service_account_names=${SA_NAME} \
  bound_service_account_namespaces=${NAMESPACE} \
  policies=${NAMESPACE}-app \
  ttl=24h

# Insérer les secrets
echo "Insertion des secrets pour ${NAMESPACE}..."
echo "⚠️  NOTE: Utilisez ./update-vault-secrets.sh pour configurer les secrets de manière interactive"
echo ""

echo ""
echo "✅ Configuration de Vault terminée !"
echo ""
echo "Prochaines étapes:"
echo "  1. Configurer les secrets: ./update-vault-secrets.sh $ROOT_TOKEN"
echo "  2. Vérifier le SecretStore: $KUBECTL -n ${NAMESPACE} get secretstore vault-backend"
echo "  3. Vérifier l'ExternalSecret: $KUBECTL -n ${NAMESPACE} get externalsecret"
echo ""

# Arrêter le port-forward
kill $PF_PID 2>/dev/null || true
