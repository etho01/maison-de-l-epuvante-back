# CI/CD Documentation

## Vue d'ensemble

Le projet utilise **GitHub Actions** pour automatiser les tests et le déploiement.

## 📁 Structure des workflows

```
.github/workflows/
├── test.yml      # Tests automatiques sur PR et branches de développement
└── deploy.yml    # Tests + Build + Déploiement sur la branche main
```

## 🧪 Workflow de Tests (test.yml)

### Déclencheurs
- **Pull Requests** vers `main`
- **Push** sur `develop`
- **Push** sur les branches `feature/*`

### Étapes

#### Job: `test`
1. Checkout du code
2. Installation de PHP 8.2 avec extensions (pdo_sqlite, etc.)
3. Cache des dépendances Composer
4. Installation des dépendances
5. Création du fichier `.env.test` avec configuration SQLite
6. Génération des clés JWT
7. Clear du cache Symfony
8. Exécution de PHPUnit
9. Génération de la couverture de code (uniquement sur PR)
10. Upload vers Codecov (uniquement sur PR)
11. Upload des logs en cas d'échec

#### Job: `code-quality`
1. Vérification de la syntaxe PHP
2. Analyse statique avec PHPStan (si configuré)

### Configuration de l'environnement de test

L'environnement de test CI utilise :
- **Base de données** : SQLite (fichier)
- **JWT** : Clés générées à la volée avec passphrase `test-passphrase-for-ci`
- **Mailer** : Mode null (pas d'envoi réel)
- **Stripe** : Clé mock pour les tests

## 🚀 Workflow de Déploiement (deploy.yml)

### Déclencheur
- **Push** sur la branche `main`

### Étapes

#### Job 1: `test`
Identique au workflow de tests - **bloque le déploiement si les tests échouent**.

#### Job 2: `build-and-push`
Dépend de : `test`

1. Checkout du code
2. Génération du tag d'image (basé sur le commit SHA)
3. Connexion à GitHub Container Registry (GHCR)
4. Build de l'image Docker avec cache
5. Push vers `ghcr.io/etho01/maison-de-l-epuvante-back:SHA` et `:latest`

#### Job 3: `deploy`
Dépend de : `build-and-push`

1. Installation de kubectl et Helm
2. Configuration du kubeconfig (depuis secret)
3. Installation/vérification d'External Secrets Operator
4. Déploiement de l'intégration Vault :
   - ServiceAccount pour l'authentification Vault
   - SecretStore pour la connexion
   - ExternalSecret pour la synchronisation
5. Attente de la synchronisation des secrets Vault
6. Application des manifests Kubernetes :
   - Deployment
   - Service
   - Ingress
   - HPA (autoscaling)
7. Mise à jour de l'image du déploiement
8. Exécution des migrations de base de données (Job Kubernetes)
9. Attente du rollout complet
10. **Rollback automatique** en cas d'échec

## 🔧 Configuration requise

### Secrets GitHub

Les secrets suivants doivent être configurés dans le dépôt GitHub :

| Secret | Description | Requis pour |
|--------|-------------|-------------|
| `GITHUB_TOKEN` | Token GitHub (automatique) | build-and-push |
| `KUBECONFIG_B64` | Config kubectl encodée en base64 | deploy |

### Créer KUBECONFIG_B64

```bash
# Sur votre machine avec kubectl configuré
cat ~/.kube/config | base64 -w 0
# Copier la sortie dans le secret GitHub
```

## 🧪 Tester localement

### Avec le script fourni

```bash
./run-tests.sh
```

### Manuellement

```bash
# 1. Créer l'environnement de test
cat > .env.test << 'EOF'
KERNEL_CLASS='App\Kernel'
APP_SECRET='test-secret-for-ci'
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
DEFAULT_URI=http://localhost:8000
FRONTEND_URL=http://localhost:3000
JWT_PASSPHRASE=test-passphrase
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
MAILER_DSN=null://null
STRIPE_SECRET_KEY=sk_test_mock_key
EOF

# 2. Générer les clés JWT
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 -passout pass:test-passphrase 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:test-passphrase

# 3. Installer et tester
composer install
php bin/console cache:clear --env=test
php bin/phpunit
```

## 📊 Statistiques de tests

Actuellement :
- **184 tests** au total
- **359 assertions**
- **7 tests incomplets** (fonctionnalités à venir)
- **0 erreurs, 0 échecs** ✅

Suites de tests :
- Tests de contrôleurs (Auth, User, Admin, Ecommerce)
- Tests d'entités (validation, relations)
- Tests de services (pagination)
- Tests e-commerce (produits, catégories, commandes)

## 🐛 Dépannage

### Les tests échouent en CI mais pas localement

1. Vérifiez que votre `.env.test` local correspond à celui du CI
2. Assurez-vous d'utiliser SQLite comme en CI : `DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"`
3. Nettoyez complètement le cache : `rm -rf var/cache/test var/test.db`

### Le déploiement échoue lors de la synchronisation Vault

1. Vérifiez que l'External Secrets Operator est installé sur le cluster
2. Vérifiez la configuration du SecretStore dans `k8s/secretstore.yaml`
3. Consultez les logs : `kubectl -n maison-epouvante describe externalsecret maison-epouvante-back-vault`

### Rollback automatique

Si le déploiement échoue, le workflow effectue automatiquement un rollback vers la version précédente. Vérifiez les logs du workflow pour comprendre la cause de l'échec.

## 📈 Amélioration continue

### Prochaines étapes suggérées

1. ✅ Tests intégrés au CI/CD
2. ⏳ Ajout de tests E2E (end-to-end)
3. ⏳ Analyse de sécurité automatisée (Snyk, Dependabot)
4. ⏳ Déploiement en staging avant production
5. ⏳ Tests de performance automatisés
6. ⏳ Notifications Slack/Discord sur échec

### Badges à ajouter au README

```markdown
![Tests](https://github.com/USERNAME/REPO/workflows/Tests/badge.svg)
![Deploy](https://github.com/USERNAME/REPO/workflows/Deploy%20maison-epouvante-back%20to%20K3s/badge.svg)
![Coverage](https://codecov.io/gh/USERNAME/REPO/branch/main/graph/badge.svg)
```

## 📚 Ressources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [External Secrets Operator](https://external-secrets.io/)
