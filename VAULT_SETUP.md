# Configuration Vault - Setup Initial

## Configuration automatique

La configuration de Vault est maintenant **intégrée dans le workflow GitHub Actions** (`.github/workflows/deploy.yml`).

Au premier déploiement, le workflow:
1. ✅ Active l'authentification Kubernetes dans Vault
2. ✅ Configure la connexion Vault ↔ Kubernetes  
3. ✅ Crée la policy `maison-epouvante-app`
4. ✅ Crée le rôle Vault pour le ServiceAccount

---

## Secrets GitHub nécessaires

Dans **Settings → Secrets and variables → Actions**, créez:

### 1. `VAULT_ROOT_TOKEN`
Token root de Vault

### 2. `KUBECONFIG_B64`  
Votre kubeconfig encodé en base64:
```bash
cat ~/.kube/config | base64 -w 0
```

---

## Configurer les secrets de l'application

Une fois Vault configuré (après le premier déploiement), configurez vos secrets:

```bash
./update-vault-secrets.sh <VAULT_ROOT_TOKEN>
```

Ce script configure interactivement:
- Variables Symfony (APP_ENV, APP_SECRET)
- Base de données (DATABASE_URL)
- Clés JWT (générées automatiquement)
- CORS, Mailer, Stripe, URLs

---

## Vérification

```bash
# Vérifier que Vault est configuré
kubectl -n maison-epouvante get secretstore vault-backend
# Devrait afficher: READY = True

# Vérifier l'ExternalSecret
kubectl -n maison-epouvante get externalsecret
# Status: SecretSynced

# Vérifier le secret créé
kubectl -n maison-epouvante get secret maison-epouvante-back-secret
```

---

## Troubleshooting

### SecretStore pas Ready

```bash
kubectl -n maison-epouvante describe secretstore vault-backend
```

Causes communes:
- Vault scellé (sealed) → `vault operator unseal`
- Rôle Vault manquant → Redéployer le workflow
- ServiceAccount manquant → `kubectl apply -f k8s/vault/serviceaccount.yaml`

### ExternalSecret pas synchronisé

```bash
kubectl -n maison-epouvante describe externalsecret maison-epouvante-back-vault
```

Causes communes:
- Secrets pas dans Vault → Exécuter `update-vault-secrets.sh`
- Chemin incorrect → Vérifier `secret/maison-epouvante/app` dans Vault
- Policy insuffisante → Vérifier avec `vault policy read maison-epouvante-app`

---

## Configuration manuelle (si nécessaire)

Si vous devez configurer Vault manuellement:

```bash
# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200 &

# Configuration
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN=<votre_root_token>

# Activer auth Kubernetes
vault auth enable kubernetes

# Configurer
vault write auth/kubernetes/config \
  token_reviewer_jwt="$(kubectl -n maison-epouvante get secret vault-auth-token -o jsonpath='{.data.token}' | base64 -d)" \
  kubernetes_host="$(kubectl config view --raw --minify --flatten -o jsonpath='{.clusters[0].cluster.server}')" \
  kubernetes_ca_cert="$(kubectl -n maison-epouvante get secret vault-auth-token -o jsonpath='{.data.ca\.crt}' | base64 -d)"

# Créer policy
vault policy write maison-epouvante-app - <<EOF
path "secret/data/maison-epouvante/*" {
  capabilities = ["read", "list"]
}
path "secret/metadata/maison-epouvante/*" {
  capabilities = ["read", "list"]
}
EOF

# Créer rôle
vault write auth/kubernetes/role/maison-epouvante-app \
  bound_service_account_names=vault-auth \
  bound_service_account_namespaces=maison-epouvante \
  policies=maison-epouvante-app \
  ttl=24h
```
