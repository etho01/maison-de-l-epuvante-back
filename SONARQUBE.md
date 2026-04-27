# SonarQube Configuration

## 📋 Prérequis

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

### 2. Créer un token d'authentification

1. Allez dans **My Account** → **Security** → **Generate Tokens**
2. Créez un token nommé `maison-epouvante-back`
3. Copiez le token généré

### 3. Configurer les secrets

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

### 4. Lancer l'analyse locale

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
- Push vers `main` ou `develop`
- Pull Requests vers `main`

### Configuration des secrets GitHub

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
