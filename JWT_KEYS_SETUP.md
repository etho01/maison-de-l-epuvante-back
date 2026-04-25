# Configuration des clés JWT pour la production

## Architecture

Les clés JWT sont **stockées dans Vault** et injectées automatiquement dans les pods Kubernetes via External Secrets Operator.

### Avantages
✅ **Persistantes**: Clés conservées entre les déploiements  
✅ **Sécurisées**: Stockées chiffrées dans Vault  
✅ **Centralisées**: Gestion via le script `update-vault-secrets.sh`  
✅ **Rotation simple**: Régénération via le script avec confirmation

---

## Configuration initiale

### 1. Générer et stocker les clés dans Vault

```bash
# Lancer le script de configuration
./update-vault-secrets.sh <VAULT_ROOT_TOKEN>
```

Le script va:
1. Détecter s'il existe déjà des clés JWT dans Vault
2. Si oui: demander si vous voulez les conserver ou les régénérer
3. Si non: générer automatiquement de nouvelles clés RSA 4096 bits
4. Encoder les clés en base64 et les stocker dans Vault

### 2. Variables Vault stockées

```
JWT_PASSPHRASE      # Passphrase pour chiffrer la clé privée
JWT_PRIVATE_KEY     # Clé privée RSA (base64)
JWT_PUBLIC_KEY      # Clé publique RSA (base64)
```

### 3. Synchronisation Kubernetes

L'ExternalSecret récupère automatiquement les clés depuis Vault et les décode:

```yaml
# Les clés sont montées comme fichiers dans le pod:
/var/www/project/config/jwt/private.pem
/var/www/project/config/jwt/public.pem
```

---

## Développement local

En local, générez les clés avec Symfony CLI:

```bash
# Copier le fichier d'exemple
cp .env.example .env.local

# Modifier JWT_PASSPHRASE dans .env.local
# Puis générer les clés
php bin/console lexik:jwt:generate-keypair

# Vérifier
ls -l config/jwt/
```

Les fichiers `config/jwt/*.pem` sont ignorés par Git (`.gitignore`).

---

## Rotation des clés JWT

Pour régénérer les clés en production:

```bash
# Re-lancer le script
./update-vault-secrets.sh <VAULT_ROOT_TOKEN>

# Répondre "oui" quand demandé si vous voulez régénérer les clés

# Forcer le redémarrage des pods pour recharger les nouvelles clés
kubectl -n maison-epouvante rollout restart deployment/maison-epouvante-back
```

⚠️ **Attention**: La rotation des clés JWT **invalidera tous les tokens existants**. Les utilisateurs devront se reconnecter.

---

## Vérification

### Vérifier que les clés sont dans Vault

```bash
# Port-forward Vault
kubectl -n vault port-forward svc/vault 8200:8200

# Connexion
export VAULT_ADDR=http://127.0.0.1:8200
export VAULT_TOKEN=<votre_root_token>

# Lister les secrets
vault kv get secret/maison-epouvante/app
```

### Vérifier dans Kubernetes

```bash
# Voir l'ExternalSecret
kubectl -n maison-epouvante get externalsecret

# Vérifier que le secret existe
kubectl -n maison-epouvante get secret maison-epouvante-back-secret

# Voir les clés montées dans un pod
kubectl -n maison-epouvante exec -it deployment/maison-epouvante-back -- ls -l /var/www/project/config/jwt/
```

---

## Troubleshooting

### Erreur "JWT keys not found"

```bash
# Vérifier que l'ExternalSecret est synchronisé
kubectl -n maison-epouvante describe externalsecret maison-epouvante-back-vault

# Si status != Ready, vérifier la connexion Vault
kubectl -n maison-epouvante logs -l app=maison-epouvante-back
```

### Tokens JWT invalides après déploiement

Si les clés ont été régénérées, tous les tokens existants sont invalides. C'est normal - les utilisateurs doivent se reconnecter.

### Clés corrompues dans Vault

Si les clés sont corrompues (base64 invalide):

```bash
# Régénérer avec le script
./update-vault-secrets.sh <VAULT_ROOT_TOKEN>

# Choisir "oui" pour régénérer
# Puis redémarrer les pods
kubectl -n maison-epouvante rollout restart deployment/maison-epouvante-back
```
