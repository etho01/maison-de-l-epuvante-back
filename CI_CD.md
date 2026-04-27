# CI/CD Documentation

## Vue d'ensemble

Le projet utilise **GitHub Actions** pour automatiser les tests et le déploiement.

## 📁 Structure des workflows

```
.github/workflows/
├── test.yml           # Tests automatiques sur PR et branches de développement
├── security-scan.yml  # Analyse des vulnérabilités et packages obsolètes
├── sonarqube.yml      # Analyse de qualité du code avec SonarQube
└── deploy.yml         # Tests + Build + Déploiement sur la branche main
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

## � Workflow de Sécurité (security-scan.yml)

### Déclencheurs
- **Push** sur toutes les branches (sauf `main`)
- **Pull Requests** vers `main`
- **Schedule** : Quotidiennement à 2h du matin (UTC)
- **workflow_call** : Peut être appelé depuis d'autres workflows

### Étapes

#### Job 1: `dependency-scan` - Analyse des vulnérabilités

1. **Checkout** du code
2. **Installation** de PHP 8.2 avec Composer
3. **Cache** des dépendances
4. **Installation** des dépendances (`--no-dev` pour production)
5. **Analyse** avec `composer audit --format=json`
   - Détecte les CVE connus dans les dépendances
   - Sauvegarde le résultat dans `composer-audit.json`
6. **Génération** d'un rapport Markdown détaillé (`security-report.md`)
   - Résumé des vulnérabilités
   - Détails par package (sévérité, CVE, versions affectées, solution)
   - Compteurs par sévérité (critical, high, medium, low)
7. **Vérification** des seuils critiques
   - **Bloque le workflow** (exit 1) si vulnérabilités critical/high détectées
   - Continue si medium/low uniquement
8. **Upload** des artefacts (rapports conservés 30 jours)
9. **Commentaire automatique** sur les PR avec le rapport de sécurité
10. **Affichage** d'un résumé dans GitHub Actions (tableau par sévérité)

#### Job 2: `outdated-dependencies` - Vérification des packages obsolètes

1. **Checkout** du code
2. **Installation** de PHP 8.2 avec Composer
3. **Installation** des dépendances (avec dev)
4. **Vérification** avec `composer outdated --direct --format=json`
   - Liste uniquement les dépendances directes obsolètes
   - Sauvegarde dans `outdated.json`
5. **Génération** d'un rapport Markdown (`outdated-report.md`)
   - Tableau avec versions actuelles vs disponibles
   - Commande pour mettre à jour
6. **Upload** des artefacts (rapports conservés 30 jours)
7. **Affichage** d'un résumé (top 5 des packages obsolètes)

### Permissions requises

```yaml
permissions:
  security-events: write  # Pour uploader vers GitHub Security (futur)
  contents: read          # Pour accéder au code
```

### Artefacts générés

#### Artefact `security-report`
- **`security-report.md`** : Rapport Markdown formaté pour les humains
- **`composer-audit.json`** : Données brutes JSON pour intégration

#### Artefact `outdated-report`
- **`outdated-report.md`** : Rapport Markdown formaté
- **`outdated.json`** : Données brutes JSON

### Exemple de rapport de vulnérabilité

```markdown
🔒 Rapport de Sécurité des Dépendances

⚠️ 3 vulnérabilité(s) détectée(s)

| Sévérité | Nombre |
|----------|--------|
| 🔴 Critical | 1 |
| 🟠 High | 1 |
| 🟡 Medium | 1 |
| 🟢 Low | 0 |

## symfony/http-kernel
- Sévérité : high
- CVE : CVE-2023-46733
- Titre : Symfony HTTP kernel vulnerable to cache poisoning
- Versions affectées : >=2.0.0,<5.4.31
- Solution : Mettre à jour vers 5.4.31
```

### Seuils de blocage

Le workflow **bloque** (exit 1) et empêche le merge si :
- ❌ Au moins 1 vulnérabilité **critical** détectée
- ❌ Au moins 1 vulnérabilité **high** détectée

Le workflow **continue** (exit 0) si :
- ✅ Uniquement des vulnérabilités **medium** ou **low**
- ✅ Packages obsolètes détectés (pas bloquant)
- ✅ Aucun problème détecté

### Intégration dans d'autres workflows

Le workflow `action-commit.yml` intègre le scan de sécurité :

```yaml
jobs:
  security-scan:
    uses: ./.github/workflows/security-scan.yml
    permissions:
      security-events: write
      contents: read
  
  test:
    needs: [security-scan]  # Bloqué si vulnérabilités critiques
    uses: ./.github/workflows/test.yml
```

### Tester localement

```bash
# Rendre le script exécutable
chmod +x security-scan.sh

# Lancer l'analyse
./security-scan.sh
```

Le script local va :
1. Installer les dépendances
2. Analyser avec `composer audit`
3. Vérifier avec `composer outdated`
4. Générer les rapports JSON
5. Afficher un résumé coloré
6. **Exit 1** si vulnérabilités critical/high

### Outils utilisés

- **`composer audit`** : Commande native Composer (depuis 2.4+)
  - Interroge la base [PHP Security Advisories Database](https://github.com/FriendsOfPHP/security-advisories)
  - Détecte les CVE connus dans les packages PHP
  
- **`composer outdated`** : Commande native Composer
  - Compare les versions installées aux versions disponibles
  - Flag `--direct` : uniquement les dépendances directes

- **jq** (local uniquement) : Parser JSON pour affichage formaté

## �🚀 Workflow de Déploiement (deploy.yml)

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
