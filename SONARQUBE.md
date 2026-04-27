# SonarQube Configuration

## � Guide de démarrage rapide (GitHub Actions)

Si le workflow GitHub Actions échoue avec l'erreur **"You're not authorized to analyze this project"**, suivez ces étapes :

### ✅ Checklist rapide

1. **Le projet existe-t-il dans SonarQube ?**
   - ❌ Non → Créez-le manuellement (voir étape 2 ci-dessous)
   - ✅ Oui → Vérifiez les secrets GitHub (étape 3)

2. **Créer le projet dans SonarQube**
   ```
   1. Connexion à SonarQube (https://votre-sonarqube.com)
   2. "Create Project" → "Manually"
   3. Project key: maison-de-lepouvante-back (EXACT)
   4. Display name: Maison de l'Épouvante - Backend API
   5. "Set Up"
   ```

3. **Vérifier les secrets GitHub**
   ```
   Repository Settings → Secrets and variables → Actions
   
   ✅ SONAR_TOKEN existe ? (commence par sqp_)
   ✅ SONAR_HOST_URL existe ? (URL complète du serveur)
   ```

4. **Re-déclencher le workflow**
   - Faire un nouveau commit
   - Ou "Re-run all jobs" dans GitHub Actions

---

## �📋 Prérequis

### Option 1 : Utiliser Docker (recommandé)
- Docker installé et fonctionnel

### Option 2 : Installation locale
- SonarQube Server installé et démarré
- SonarScanner CLI installé

## 🚀 Démarrage rapide

### 1. Démarrer SonarQube avec Docker

```bash
docker run -d --name sonarqube \
  -p 9000:9000 \
  sonarqube:latest
```

Attendez que SonarQube démarre (environ 2-3 minutes), puis accédez à http://localhost:9000

**Login par défaut** :
- Username: `admin`
- Password: `admin` (vous serez invité à le changer)

### 2. Créer le projet dans SonarQube

**⚠️ IMPORTANT** : Vous devez créer le projet avant la première analyse, sinon vous obtiendrez l'erreur :
```
ERROR You're not authorized to analyze this project or the project doesn't exist
```

1. Connectez-vous à SonarQube (http://localhost:9000)
2. Cliquez sur **"Create Project"** (bouton en haut à droite)
3. Choisissez **"Manually"**
4. Remplissez les informations :
   - **Project key** : `maison-de-lepouvante-back` (doit correspondre à sonar-project.properties)
   - **Display name** : `Maison de l'Épouvante - Backend API`
   - **Main branch name** : `main`
5. Cliquez sur **"Set Up"**
6. Choisissez **"Locally"** → **"Generate a token"**
7. Copiez le token généré

### 3. Créer un token d'authentification (si déjà fait lors de la création du projet)

Si vous avez déjà créé le projet, vous pouvez créer un nouveau token :

1. Allez dans **My Account** → **Security** → **Generate Tokens**
2. Créez un token nommé `maison-epouvante-back-ci`
3. **Type** : Choisissez **"Global Analysis Token"** (permet de scanner tous les projets)
4. **Expires in** : Choisissez la durée (30 jours, 90 jours, ou "No expiration")
5. Cliquez sur **"Generate"**
6. Copiez le token généré (commence par `sqp_`)

### 4. Configurer les variables d'environnement

```bash
# Définir le token (à faire dans chaque session)
export SONAR_TOKEN=votre_token_ici
export SONAR_HOST_URL=http://localhost:9000
```

Pour rendre permanent :

```bash
# Ajouter au ~/.bashrc ou ~/.zshrc
echo 'export SONAR_TOKEN=votre_token_ici' >> ~/.bashrc
echo 'export SONAR_HOST_URL=http://localhost:9000' >> ~/.bashrc
source ~/.bashrc
```

### 5. Lancer l'analyse locale

```bash
# Rendre le script exécutable (première fois seulement)
chmod +x sonar-scan.sh

# Lancer l'analyse
./sonar-scan.sh
```

Le script va :
1. ✅ Installer les dépendances si nécessaire
2. ✅ Générer les clés JWT
3. ✅ Exécuter les tests avec couverture
4. ✅ Envoyer les résultats à SonarQube
5. ✅ Afficher l'URL des résultats

## 📊 Voir les résultats

Une fois l'analyse terminée, ouvrez :
- **Local** : http://localhost:9000/dashboard?id=maison-de-lepouvante-back
- **Serveur distant** : Vérifiez l'URL affichée par le script

## 🔄 CI/CD avec GitHub Actions

Le workflow `.github/workflows/sonarqube.yml` s'exécute automatiquement sur :
- Push vers toutes les branches **sauf `main`**
- Idéal pour vérifier la qualité avant de merger

### ⚠️ Configuration OBLIGATOIRE avant la première exécution

**Étape 1 : Créer le projet dans SonarQube**

Le projet **DOIT exister** dans SonarQube avant d'exécuter le workflow, sinon vous aurez l'erreur :
```
ERROR You're not authorized to analyze this project or the project doesn't exist
```

1. Connectez-vous à votre serveur SonarQube
2. **Create Project** → **Manually**
3. **Project key** : `maison-de-lepouvante-back` ⚠️ **Doit être exactement ce nom**
4. **Display name** : `Maison de l'Épouvante - Backend API`
5. Cliquez sur **"Set Up"**

**Étape 2 : Générer un token**

1. Dans SonarQube, allez dans **Administration** → **Security** → **Users**
2. Cliquez sur le token icon pour votre utilisateur
3. **Generate Token** :
   - **Name** : `github-actions-maison-epouvante`
   - **Type** : **Global Analysis Token**
   - **Expires in** : 90 days (ou "No expiration" pour prod)
4. Copiez le token (commence par `sqp_`)

**Étape 3 : Configurer les secrets GitHub**

Allez dans **Settings** → **Secrets and variables** → **Actions** et ajoutez :

| Secret | Description | Exemple |
|--------|-------------|---------|
| `SONAR_TOKEN` | Token d'authentification SonarQube | `sqp_xxxxxxxxxxxxx` |
| `SONAR_HOST_URL` | URL du serveur SonarQube | `https://sonarqube.votredomaine.com` |

## 🎯 Quality Gate

Le Quality Gate par défaut vérifie :
- ✅ Couverture de code > 80%
- ✅ Pas de bugs critiques
- ✅ Pas de vulnérabilités
- ✅ Maintainability rating A ou B
- ✅ Duplication de code < 3%

## 📁 Configuration du projet

Le fichier `sonar-project.properties` contient la configuration :

```properties
sonar.projectKey=maison-de-lepouvante-back
sonar.sources=src
sonar.tests=tests
sonar.php.coverage.reportPaths=coverage.xml
```

### Exclusions

Les dossiers suivants sont exclus de l'analyse :
- `vendor/` - Dépendances Composer
- `var/` - Cache et logs Symfony
- `public/` - Assets publics
- `config/` - Fichiers de configuration
- `migrations/` - Migrations de base de données

## 🐛 Dépannage

### "You're not authorized to analyze this project" ⚠️ ERREUR FRÉQUENTE

Cette erreur signifie que le projet n'existe pas dans SonarQube ou que votre token n'a pas les bonnes permissions.

**Solution 1 : Créer le projet manuellement (RECOMMANDÉ)**

1. Connectez-vous à SonarQube (http://localhost:9000 ou votre serveur)
2. Cliquez sur **"Create Project"** → **"Manually"**
3. **Project key** : `maison-de-lepouvante-back` (doit correspondre exactement)
4. **Display name** : `Maison de l'Épouvante - Backend API`
5. Cliquez sur **"Set Up"** → **"Locally"**
6. Générez un nouveau token si nécessaire

**Solution 2 : Vérifier les permissions du token**

Si le projet existe déjà, vérifiez que votre token a les bonnes permissions :

1. Allez dans **My Account** → **Security** → **Tokens**
2. Vérifiez que votre token est de type **"Global Analysis Token"**
3. Si c'est un **"Project Analysis Token"**, créez-en un nouveau de type "Global"

**Solution 3 : Pour GitHub Actions**

1. Créez le projet manuellement dans SonarQube (voir Solution 1)
2. Vérifiez que les secrets GitHub sont bien configurés :
   - `SONAR_TOKEN` : Token valide commençant par `sqp_`
   - `SONAR_HOST_URL` : URL complète (ex: `https://sonarqube.example.com`)
3. Re-déclenchez le workflow

### "sonar-scanner not found"

Le script utilisera automatiquement Docker si sonar-scanner n'est pas installé.

Pour installer manuellement :

**macOS** :
```bash
brew install sonar-scanner
```

**Linux** :
```bash
wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-linux.zip
unzip sonar-scanner-cli-5.0.1.3006-linux.zip
export PATH=$PATH:$PWD/sonar-scanner-5.0.1.3006-linux/bin
```

### "Cannot connect to SonarQube server"

1. Vérifiez que SonarQube est démarré : `docker ps | grep sonarqube`
2. Vérifiez l'URL : `curl http://localhost:9000/api/system/status`
3. Vérifiez le token dans les logs SonarQube

### "Tests failed"

L'analyse ne peut pas continuer si les tests échouent. Corrigez d'abord les tests :

```bash
./run-tests.sh
```

### Problèmes de coverage

Si la couverture n'apparaît pas dans SonarQube :

1. Vérifiez que `coverage.xml` est généré localement
2. Vérifiez les chemins dans `sonar-project.properties`
3. Assurez-vous que Xdebug est installé : `php -m | grep xdebug`

## 🔗 Ressources

- [Documentation SonarQube](https://docs.sonarqube.org/latest/)
- [SonarQube for PHP](https://docs.sonarqube.org/latest/analyzing-source-code/languages/php/)
- [Quality Profiles](https://docs.sonarqube.org/latest/instance-administration/quality-profiles/)
- [Quality Gates](https://docs.sonarqube.org/latest/user-guide/quality-gates/)

## 💡 Conseils

1. **Exécutez SonarQube localement** avant de pousher pour éviter les surprises
2. **Corrigez les bugs critiques** immédiatement
3. **Visez au moins 80% de couverture** pour le nouveau code
4. **Utilisez les issues SonarQube** comme guide pour améliorer le code
5. **Ne désactivez pas les règles** sans justification documentée

## 📈 Métriques importantes

- **Bugs** : Problèmes de logique qui peuvent causer des erreurs
- **Vulnerabilities** : Failles de sécurité potentielles
- **Code Smells** : Problèmes de maintenabilité
- **Coverage** : Pourcentage de code testé
- **Duplications** : Code dupliqué (refactoring recommandé)
- **Technical Debt** : Temps estimé pour corriger les problèmes

## 🎯 Objectifs du projet

| Métrique | Objectif | Actuel |
|----------|----------|--------|
| Coverage | > 80% | À mesurer |
| Bugs | 0 | À mesurer |
| Vulnerabilities | 0 | À mesurer |
| Code Smells | < 50 | À mesurer |
| Duplication | < 3% | À mesurer |
| Maintainability | A | À mesurer |
| Reliability | A | À mesurer |
| Security | A | À mesurer |
