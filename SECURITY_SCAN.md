# Security Scan - Analyse de Sécurité des Dépendances

## 🎯 Objectif

Ce composant analyse automatiquement le projet pour détecter :
- 🔒 **Vulnérabilités de sécurité** (CVE connus dans les dépendances)
- 📦 **Packages obsolètes** nécessitant une mise à jour
- 🛡️ **Vulnérabilités système** (via Trivy)
- 🔐 **Secrets hardcodés** dans le code
- ⚙️ **Misconfigurations** de sécurité
- 🚨 **Alertes critiques** bloquant le CI/CD

## 🚀 Utilisation

### En local

```bash
# Rendre le script exécutable (première fois)
chmod +x security-scan.sh

# Lancer l'analyse
./security-scan.sh
```

Le script va :
1. ✅ Installer les dépendances Composer
2. ✅ Analyser les vulnérabilités avec `composer audit`
3. ✅ Vérifier les packages obsolètes avec `composer outdated`
4. ✅ Scanner avec Trivy (vulnérabilités, secrets, configs)
5. ✅ Générer des rapports JSON détaillés
6. ✅ Bloquer (exit 1) si vulnérabilités critiques/hautes ou secrets détectés

### Résultats

Trois fichiers JSON sont générés :
- **`composer-audit.json`** : Vulnérabilités de sécurité détectées (Composer)
- **`outdated.json`** : Liste des packages obsolètes
- **`trivy-results.json`** : Résultats complets du scan Trivy

### Dans le CI/CD (GitHub Actions)

Le workflow `.github/workflows/security-scan.yml` s'exécute :
- ✅ Sur chaque **push** (sauf `main`)
- ✅ Sur chaque **pull request** vers `main`
- ✅ **Quotidiennement** à 2h du matin (cron)
- ✅ Via `workflow_call` depuis d'autres workflows

## 📊 Jobs du workflow

### 1. `dependency-scan` - Analyse des vulnérabilités

Utilise **`composer audit`** pour détecter les CVE connus.

**Actions :**
- Analyse toutes les dépendances
- Génère un rapport Markdown détaillé
- Upload les artefacts (rapports)
- Commente automatiquement les Pull Requests
- Affiche un résumé dans GitHub Actions
- **Bloque le workflow** si vulnérabilités critiques/hautes

**Exemple de sortie :**

```
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
- Lien : https://github.com/advisories/GHSA-...
- Versions affectées : >=2.0.0,<5.4.31
- Solution : Mettre à jour vers 5.4.31
```

### 2. `outdated-dependencies` - Vérification des packages obsolètes

Utilise **`composer outdated`** pour lister les packages qui peuvent être mis à jour.

**Actions :**
- Liste tous les packages obsolètes
- Génère un rapport Markdown
- Upload les artefacts
- Affiche un résumé (top 5 packages)

**Exemple de sortie :**

```
📦 Packages Obsolètes

⚠️ 8 package(s) peuvent être mis à jour

| Package | Version actuelle | Version disponible |
|---------|------------------|-------------------|
| symfony/console | 7.0.1 | 7.0.7 |
| doctrine/orm | 3.0.0 | 3.1.2 |
| ...

💡 Commande pour mettre à jour
composer update --with-dependencies
```

### 3. `trivy-scan` - Scan de sécurité complet avec Trivy

Utilise **Trivy** (par Aqua Security) pour un scan de sécurité complet du projet.

**Actions :**
- Scanne le système de fichiers pour les vulnérabilités
- Détecte les secrets hardcodés dans le code
- Identifie les problèmes de configuration
- Génère des rapports SARIF et JSON
- Upload vers GitHub Security (Code Scanning)
- Commente automatiquement les Pull Requests
- **Bloque le workflow** si vulnérabilités critical/high ou secrets sensibles

**Ce que Trivy analyse :**

| Type | Description | Exemples |
|------|-------------|----------|
| **Vulnérabilités** | CVE dans les dépendances et OS packages | composer.lock, package-lock.json |
| **Secrets** | Credentials hardcodés | API keys, tokens, passwords |
| **Misconfigurations** | Problèmes de configuration | Dockerfile, docker-compose.yml |
| **Licenses** | Licences non conformes | GPL dans code propriétaire |

**Exemple de rapport Trivy :**

```
🛡️ Trivy Security Scan Report

## 📊 Résumé

| Type | Nombre |
|------|--------|
| 🐛 Vulnérabilités totales | 12 |
| 🔴 Critical | 2 |
| 🟠 High | 3 |
| 🟡 Medium | 5 |
| 🟢 Low | 2 |
| 🔐 Secrets détectés | 1 |
| ⚙️ Misconfigurations | 0 |

## 🚨 Vulnérabilités Critiques et Hautes

### symfony/http-kernel (7.0.5)
- **Sévérité:** CRITICAL
- **Vulnerability ID:** CVE-2023-46733
- **Titre:** HTTP kernel vulnerable to cache poisoning
- **Fix:** Mettre à jour vers 7.0.7

## 🔐 Secrets Détectés

⚠️ 1 secret(s) potentiel(s) trouvé(s) dans le code

### generic-api-key
- **Fichier:** `config/services.yaml`
- **Ligne:** 42
- **Sévérité:** HIGH
- **Titre:** Generic API Key
```

**Scanners utilisés :**

```yaml
scanners: 'vuln,secret,config'
```

- **vuln** : Détection de vulnérabilités dans les dépendances
- **secret** : Détection de secrets hardcodés
- **config** : Vérification des fichiers de configuration

**Formats de sortie :**

1. **SARIF** (`trivy-fs-results.sarif`) : Pour GitHub Security / Code Scanning
2. **JSON** (`trivy-results.json`) : Pour le parsing et les rapports
3. **Markdown** (`trivy-report.md`) : Pour les humains et les commentaires PR

**Intégration GitHub Security :**

Les résultats sont uploadés vers **Security** → **Code scanning** :
- Annotations sur les lignes de code concernées
- Filtrage par sévérité
- Historique des scans
- Alertes automatiques

## 🔧 Configuration

### Permissions GitHub Actions

Le workflow nécessite des permissions spécifiques :

```yaml
permissions:
  security-events: write  # Pour uploader vers GitHub Security
  contents: read         # Pour accéder au code
```

### Planification automatique

Analyse quotidienne configurée via cron :

```yaml
schedule:
  - cron: '0 2 * * *'  # Tous les jours à 2h du matin
```

### Intégration dans d'autres workflows

Le workflow est réutilisable via `workflow_call` :

```yaml
jobs:
  security-scan:
    uses: ./.github/workflows/security-scan.yml
    permissions:
      security-events: write
      contents: read
```

## 📈 Seuils de blocage

Le workflow **bloque** (exit 1) et empêche le merge si :
- ❌ Vulnérabilités **critical** détectées (Composer Audit ou Trivy)
- ❌ Vulnérabilités **high** détectées (Composer Audit ou Trivy)
- ❌ Secrets **critical** ou **high** détectés par Trivy

Le workflow **continue** si :
- ✅ Vulnérabilités **medium** ou **low** uniquement
- ✅ Packages obsolètes détectés
- ✅ Aucun problème détecté

## 🔍 Artefacts générés

Tous les rapports sont sauvegardés pendant **30 jours** :

### Artefact `security-report`
- `security-report.md` : Rapport Markdown formaté (Composer Audit)
- `composer-audit.json` : Données brutes des vulnérabilités

### Artefact `outdated-report`
- `outdated-report.md` : Rapport Markdown formaté (packages obsolètes)
- `outdated.json` : Données brutes des packages obsolètes

### Artefact `trivy-reports`
- `trivy-report.md` : Rapport Markdown formaté (Trivy)
- `trivy-results.json` : Données brutes JSON (vulns, secrets, configs)
- `trivy-fs-results.sarif` : Format SARIF pour GitHub Security

## 💬 Commentaires automatiques sur les PR

Sur chaque Pull Request, **3 commentaires automatiques** sont créés/mis à jour avec :

### Commentaire 1 : Composer Audit
- 🔒 Résumé des vulnérabilités Composer
- 📊 Tableau par sévérité
- 📄 Détails de chaque vulnérabilité
- 💡 Recommandations de mise à jour

### Commentaire 2 : Trivy Scan
- 🛡️ Résumé complet du scan Trivy
- 🐛 Vulnérabilités système et dépendances
- 🔐 Secrets détectés (avec localisation)
- ⚙️ Problèmes de configuration
- 🔗 Lien vers GitHub Security / Code Scanning

## 🛠️ Dépendances

### Outils requis (local)

- **Composer** : Pour `composer audit` et `composer outdated`
- **Trivy** (optionnel) : Pour le scan de sécurité complet
  - Si non installé, le script utilisera Docker automatiquement
- **Docker** (optionnel) : Pour exécuter Trivy via conteneur si non installé
- **jq** (optionnel) : Pour un affichage formaté (sinon affichage JSON brut)

Installation de jq :
```bash
# macOS
brew install jq

# Ubuntu/Debian
sudo apt-get install jq

# Alpine
apk add jq
```

Installation de Trivy :
```bash
# macOS
brew install trivy

# Ubuntu/Debian
sudo apt-get install wget apt-transport-https gnupg lsb-release
wget -qO - https://aquasecurity.github.io/trivy-repo/deb/public.key | sudo apt-key add -
echo "deb https://aquasecurity.github.io/trivy-repo/deb $(lsb_release -sc) main" | sudo tee -a /etc/apt/sources.list.d/trivy.list
sudo apt-get update
sudo apt-get install trivy

# Via binaire (Linux/macOS)
wget https://github.com/aquasecurity/trivy/releases/download/v0.48.0/trivy_0.48.0_Linux-64bit.tar.gz
tar zxvf trivy_0.48.0_Linux-64bit.tar.gz
sudo mv trivy /usr/local/bin/

# Via Docker (pas d'installation nécessaire)
docker pull aquasec/trivy:latest
```

**Note :** Le script `security-scan.sh` gère automatiquement Trivy :
- Si Trivy est installé : utilisation directe
- Si Docker est disponible : utilisation via conteneur
- Sinon : skip Trivy avec avertissement

### Outils utilisés (CI/CD)

- `shivammathur/setup-php@v2` : Setup PHP 8.2
- `actions/cache@v3` : Cache Composer
- `actions/upload-artifact@v4` : Upload des rapports
- `actions/github-script@v7` : Commentaires automatiques
- `aquasecurity/trivy-action@master` : Scan Trivy
- `github/codeql-action/upload-sarif@v3` : Upload SARIF vers GitHub Security

## 🐛 Dépannage

### "composer audit command not found"

`composer audit` est disponible depuis Composer 2.4+. Mettez à jour Composer :

```bash
composer self-update
```

### Faux positifs

Si une vulnérabilité est un faux positif, vous pouvez :

1. **Documenter** dans le code/PR pourquoi c'est un faux positif
2. **Mettre à jour** le package concerné
3. **Modifier le seuil** : Changer `continue-on-error: true` dans le workflow

### Workflow toujours en échec

Vérifiez les logs du job `dependency-scan` :
- Consulter `composer-audit.json` dans les artefacts
- Identifier les packages vulnérables
- Mettre à jour avec `composer update package/name`

### "Trivy not found"

Le script utilise automatiquement Docker si Trivy n'est pas installé.

Pour installer Trivy localement, voir la section **Dépendances** ci-dessus.

### "Trivy détecte des secrets qui sont des faux positifs"

Trivy peut détecter des patterns qui ressemblent à des secrets. Il existe deux méthodes pour gérer les faux positifs :

#### Méthode 1 : Ignorer des fichiers entiers (recommandé pour secrets)

Utilisez `--skip-files` pour ignorer des fichiers contenant des placeholders ou mock credentials :

**Dans le workflow GitHub Actions** (`.github/workflows/security-scan.yml`) :
```yaml
- name: Run Trivy vulnerability scanner
  uses: aquasecurity/trivy-action@master
  with:
    skip-files: '.env,.env.test,docs/examples.md'
```

**Dans le script local** (`security-scan.sh`) :
```bash
trivy fs --skip-files '.env,.env.test,docs/examples.md' .
```

**Fichiers actuellement exclus :**
- `.env` - Placeholders Docker/Kubernetes
- `.env.test` - Mock credentials de test
- `CI_CD.md`, `README.md`, `QUICKSTART.md` - Documentation avec exemples

#### Méthode 2 : Ignorer des vulnérabilités spécifiques

Le fichier `.trivyignore` est utilisé pour ignorer des **CVE spécifiques**, pas des fichiers :

```
# Ignorer une CVE spécifique
CVE-2023-12345

# Ignorer toutes les vulnérabilités d'un package
pkg:composer/vendor/package@1.2.3
```

### "Trop de vulnérabilités détectées"

Si Trivy détecte beaucoup de vulnérabilités :

1. **Filtrez par sévérité** : Focus sur CRITICAL et HIGH
2. **Vérifiez** si des fixes sont disponibles
3. **Mettez à jour** les dépendances :
   ```bash
   composer update --with-dependencies
   ```
4. **Ignorez temporairement** les vulnérabilités sans fix disponible (avec documentation)

### "GitHub Security / Code Scanning ne montre pas les résultats"

1. Vérifiez que le workflow a les bonnes permissions :
   ```yaml
   permissions:
     security-events: write
   ```
2. Consultez l'onglet **Actions** pour voir les erreurs
3. Les résultats apparaissent dans **Security** → **Code scanning**

## 📖 Ressources

### Documentation officielle

- [Composer Audit](https://getcomposer.org/doc/03-cli.md#audit)
- [PHP Security Advisories Database](https://github.com/FriendsOfPHP/security-advisories)
- [Trivy Documentation](https://aquasecurity.github.io/trivy/)
- [Trivy GitHub Action](https://github.com/aquasecurity/trivy-action)
- [GitHub Security Features](https://docs.github.com/en/code-security)
- [GitHub Code Scanning](https://docs.github.com/en/code-security/code-scanning)
- [SARIF Format](https://docs.github.com/en/code-security/code-scanning/integrating-with-code-scanning/sarif-support-for-code-scanning)
- [CVE Database](https://cve.mitre.org/)

### Guides Trivy

- [Scanning for Vulnerabilities](https://aquasecurity.github.io/trivy/latest/docs/scanner/vulnerability/)
- [Secret Scanning](https://aquasecurity.github.io/trivy/latest/docs/scanner/secret/)
- [Configuration Scanning](https://aquasecurity.github.io/trivy/latest/docs/scanner/misconfiguration/)
- [Trivy Ignore](https://aquasecurity.github.io/trivy/latest/docs/configuration/filtering/#by-finding-ids)

## 💡 Bonnes pratiques

1. **Exécutez localement** avant de pousser :
   ```bash
   ./security-scan.sh
   ```

2. **Mettez à jour régulièrement** :
   ```bash
   composer update --with-dependencies
   composer audit
   trivy fs . --scanners vuln
   ```

3. **Consultez les CVE** : Lisez les détails de chaque vulnérabilité

4. **Priorisez** : Corrigez d'abord les critical/high

5. **Testez après mise à jour** :
   ```bash
   ./run-tests.sh
   ```

6. **Surveillez les notifications** : GitHub peut envoyer des alertes automatiques

7. **Vérifiez GitHub Security** : Consultez régulièrement **Security** → **Code scanning**

8. **Documentez les faux positifs** : Créez `.trivyignore` avec des commentaires

9. **Ne commitez JAMAIS de secrets** : Utilisez des variables d'environnement

10. **Scannez régulièrement** : Le workflow quotidien détecte les nouvelles vulnérabilités

## 🔗 Intégration avec d'autres outils

### Avec SonarQube

SonarQube et Trivy se complètent :
- **composer audit** : Détection rapide des CVE dans dépendances PHP
- **Trivy** : Scan complet (vulns, secrets, configs) multi-langages
- **SonarQube** : Analyse statique du code (bugs, code smells, complexité)

### Avec Dependabot

GitHub Dependabot peut créer automatiquement des PR pour les mises à jour de sécurité :

1. Activez Dependabot dans **Settings** → **Security** → **Dependabot**
2. Créez `.github/dependabot.yml` :
   ```yaml
   version: 2
   updates:
     - package-ecosystem: "composer"
       directory: "/"
       schedule:
         interval: "weekly"
   ```

## 📊 Métriques

Le workflow affiche dans le **Summary** :
- Nombre total de vulnérabilités
- Répartition par sévérité
- Top 5 des packages obsolètes
- Recommandations d'actions

## ✅ Checklist de mise en place

- [x] Script `security-scan.sh` créé et exécutable
- [x] Workflow `.github/workflows/security-scan.yml` créé
- [x] Intégré dans `action-commit.yml`
- [x] Permissions GitHub Actions configurées
- [x] Cron quotidien activé
- [ ] Tester localement avec `./security-scan.sh`
- [ ] Vérifier le premier run dans GitHub Actions
- [ ] Configurer Dependabot (optionnel)
- [ ] Former l'équipe sur l'utilisation des rapports

---

**Créé le :** 27 avril 2026  
**Dernière mise à jour :** 27 avril 2026
