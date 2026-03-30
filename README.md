# 🎃 Maison de l'Épouvante - Backend API

<div align="center">

![Status](https://img.shields.io/badge/Status-En%20D%C3%A9veloppement-orange?style=flat-square)
![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Symfony Version](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony&logoColor=white)
![API Platform](https://img.shields.io/badge/API%20Platform-4.2-38A3A5?logo=api&logoColor=white)
![License](https://img.shields.io/badge/License-Proprietary-red)
![Tests](https://img.shields.io/badge/Tests-PHPUnit-3C9CD7?logo=testing-library&logoColor=white)

**API REST moderne et complète développée avec Symfony 7.4 et API Platform**

[Fonctionnalités](#-fonctionnalités-clés) • [Installation](#-installation) • [Documentation](#-documentation) • [Tests](#-tests) • [Architecture](#-architecture)

</div>

> **⚠️ Note** : Ce projet est actuellement **en cours de développement**. Certaines fonctionnalités peuvent être incomplètes ou sujettes à modifications.

---

## 🚀 Fonctionnalités Clés

### 🔐 Authentification & Sécurité
- ✅ **Authentification JWT** avec clés RSA
- ✅ **Vérification d'email** pour les nouveaux utilisateurs
- ✅ **Réinitialisation de mot de passe** sécurisée
- ✅ **Changement de mot de passe** pour utilisateurs authentifiés
- ✅ **Gestion des rôles** (USER, ADMIN)

### 🛒 E-Commerce
- ✅ **Gestion des produits** (physiques, numériques, abonnements)
- ✅ **Catégories** hiérarchiques
- ✅ **Panier et commandes** avec suivi de statut
- ✅ **Intégration Stripe** pour les paiements
- ✅ **Gestion des livraisons**

### 📚 Contenu Numérique
- ✅ **Fanzines digitalisés** accessibles après achat
- ✅ **Abonnements** avec renouvellement automatique
- ✅ **Liseuse intégrée** (frontend)

### 🏗️ Technique
- ✅ **Architecture DTO** (Data Transfer Object) pour séparation API/BDD
- ✅ **Documentation API interactive** (OpenAPI/Swagger)
- ✅ **Pagination intelligente** avec métadonnées
- ✅ **Gestion d'erreurs standardisée**
- ✅ **Tests unitaires et d'intégration**
- ✅ **Docker** prêt pour le développement

## 📋 Prérequis

- **PHP** 8.2 ou supérieur
- **Composer** 2.x
- **MySQL/MariaDB** 11.3+ ou **PostgreSQL** 16+
- **Extensions PHP requises** : `pdo_mysql` (ou `pdo_pgsql`), `openssl`, `json`, `ctype`, `iconv`
- **Docker** (optionnel, recommandé pour le développement)

## 🔧 Installation

### Option 1 : Installation rapide avec Docker 🐳

```bash
# 1. Cloner le dépôt
git clone <repository-url>
cd back

# 2. Copier et configurer les variables d'environnement
cp .env.example .env.local

# 3. Démarrer les services Docker
docker-compose up -d

# 4. Installer les dépendances
docker-compose exec api composer install

# 5. Initialiser la base de données
./setup-db.sh

# 6. Créer un utilisateur admin
docker-compose exec api php bin/console app:create-user admin@example.com admin123 --admin --verified
```

L'API sera accessible sur : `http://localhost:8902`

### Option 2 : Installation manuelle

#### 1. Installer les dépendances

```bash
composer install
```

#### 2. Configurer l'environnement

Copiez le fichier `.env.example` en `.env.local` et configurez vos variables :

```bash
cp .env.example .env.local
```

**Variables essentielles à configurer dans `.env.local` :**

```env
# Secret Symfony (générer avec: openssl rand -base64 32)
APP_SECRET=your_random_secret_key_here

# Base de données
DATABASE_URL="mysql://root@127.0.0.1:3307/maison_epouvante?serverVersion=mariadb-11.3.0"

# JWT Passphrase (générer avec: openssl rand -base64 32)
JWT_PASSPHRASE=your_jwt_passphrase_here

# Stripe (clé de test depuis https://dashboard.stripe.com/apikeys)
STRIPE_SECRET_KEY=sk_test_your_stripe_key_here

# CORS (URL du frontend)
CORS_ALLOW_ORIGIN=http://localhost:3000
```

#### 3. Générer les clés JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

#### 4. Créer la base de données

Utilisez le script fourni :

```bash
./setup-db.sh
```

Ou manuellement :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

#### 5. Créer un utilisateur admin

```bash
php bin/console app:create-user admin@example.com admin123 --admin --verified --first-name=Admin --last-name=User
```

#### 6. Lancer le serveur

```bash
symfony server:start
```
# ou
php -S localhost:8000 -t public
```

✅ L'API est maintenant accessible sur : **`http://localhost:8000/api`**

---

## 📖 Documentation

### 📚 Documentation Interactive (Swagger/OpenAPI)

La documentation complète de l'API est accessible via l'interface Swagger :

```
http://localhost:8000/api
```

Cette interface permet de :
- 🔍 Explorer tous les endpoints disponibles
- 📝 Tester les requêtes directement depuis le navigateur
- 📄 Voir les schémas de données (DTOs)
- 🔐 S'authentifier avec un token JWT

### 🚀 Démarrage Rapide

Consultez le [QUICKSTART.md](QUICKSTART.md) pour un guide de démarrage en 3 étapes.

### 📁 Fichiers de Documentation

- **[PROJECT_CONTEXT.md](PROJECT_CONTEXT.md)** - Contexte métier du projet
- **[ROUTES_CONFIG.md](ROUTES_CONFIG.md)** - Architecture des routes API Platform
- **[QUICKSTART.md](QUICKSTART.md)** - Guide de démarrage rapide
- **[api-examples.http](api-examples.http)** - Exemples de requêtes HTTP
### 🔌 Endpoints Principaux

<details>
<summary><b>🔐 Authentification</b></summary>

- `POST /api/login` - Connexion utilisateur
- `POST /api/users` - Créer un compte
- `POST /api/change-password` - Changer son mot de passe
- `POST /api/reset-password-request` - Demander une réinitialisation de mot de passe
- `GET /api/verify/email` - Vérifier l'email
- `POST /api/verify/resend` - Renvoyer l'email de vérification

</details>

<details>
<summary><b>👥 Utilisateurs</b></summary>

- `GET /api/users` - Lister les utilisateurs (admin uniquement)
- `GET /api/users/{id}` - Récupérer un utilisateur
- `PUT /api/users/{id}` - Modifier un utilisateur
- `PATCH /api/users/{id}` - Modifier partiellement un utilisateur
- `DELETE /api/users/{id}` - Supprimer un utilisateur (admin)

</details>

<details>
<summary><b>🛒 Produits & Commandes</b></summary>

- `GET /api/products` - Lister les produits
- `GET /api/products/{id}` - Détails d'un produit
- `GET /api/products/by-slug/{slug}` - Produit par slug
- `POST /api/products` - Créer un produit (admin)
- `GET /api/categories` - Lister les catégories
- `GET /api/orders` - Mes commandes
- `POST /api/orders` - Créer une commande
- `GET /api/orders/{id}` - Détails d'une commande

</details>

<details>
<summary><b>📚 Contenu Numérique</b></summary>

- `GET /api/digital_contents` - Lister le contenu numérique
- `GET /api/digital_contents/{id}` - Accéder à un contenu
- `GET /api/subscriptions` - Mes abonnements
- `GET /api/subscription_plans` - Plans d'abonnement disponibles

</details>

### 📄 Exemples de Requêtes

Consultez les fichiers d'exemples pour tester l'API :
- [api-examples.http](api-examples.http) - Exemples généraux
- [api-examples-administrators.http](api-examples-administrators.http) - Endpoints administrateurs
- [api-examples-dto.http](api-examples-dto.http) - Exemples avec DTOs
- [pagination-examples.http](pagination-examples.http) - Exemples de pagination

---

## 🧪 Tests

### Exécuter les tests

```bash
# Tous les tests
php bin/phpunit

# Tests d'un fichier spécifique
php bin/phpunit tests/Controller/UserControllerTest.php

# Tests avec couverture de code
php bin/phpunit --coverage-html coverage
```

### Tests disponibles

Le projet inclut **16 suites de tests** :
- ✅ Tests de contrôleurs (authentification, utilisateurs, produits, commandes)
- ✅ Tests d'entités (validation, relations)
- ✅ Tests de services (pagination)
- ✅ Tests e-commerce (produits, catégories, commandes)

---

## 🔐 Sécurité

### Authentification
- 🔒 **Mots de passe hashés** avec l'algorithme `bcrypt` (auto)
- 🔑 **Tokens JWT** signés avec RSA (clés publique/privée)
- ✉️ **Vérification d'email obligatoire** avant connexion
- 🛡️ **Protection CSRF** via tokens JWT

### Autorisation
- 👤 **Rôles utilisateur** : `ROLE_USER`, `ROLE_ADMIN`
- 🔓 **Accès granulaire** : les utilisateurs peuvent modifier uniquement leurs propres données
- 🚫 **Endpoints admin protégés** pour la gestion des ressources

### Configuration

#### CORS
CORS configuré pour autoriser les requêtes cross-origin. Modifiez `CORS_ALLOW_ORIGIN` dans `.env.local` :

```env
# Développement - Autoriser localhost sur tous les ports
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

# Production - Domaines spécifiques uniquement
CORS_ALLOW_ORIGIN='^https?://(www\.)?votredomaine\.com$'
```

#### JWT

Les clés JWT sont générées automatiquement lors de l'installation dans `config/jwt/`.

Pour régénérer les clés :

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

**⚠️ Important** : Ne committez jamais les clés JWT ! Elles sont dans `.gitignore`.

---

## 🏗️ Architecture

### Structure du Projet

```
src/
├── ApiResource/              # 🔷 Ressources API (DTOs exposés)
│   ├── User.php
│   ├── DigitalContent.php
│   ├── Subscription.php
│   └── SubscriptionPlan.php
│
├── Command/                  # 🔧 Commandes Symfony CLI
│   └── CreateUserCommand.php
│
├── Controller/               # 🎮 Contrôleurs API
│   ├── Administrator/        # - Gestion administrateurs
│   ├── Auth/                 # - Authentification (login, mot de passe)
│   ├── DigitalContent/       # - Contenu numérique
│   ├── Subscription/         # - Abonnements
│   └── User/                 # - Utilisateurs
│
├── Ecommerce/                # 🛒 Module E-Commerce
│   ├── ApiResource/          # - DTOs e-commerce
│   ├── Controller/           # - Contrôleurs (produits, commandes, catégories)
│   ├── Dto/                  # - Request/Response DTOs
│   ├── Entity/               # - Entités (Product, Order, Category, Delivery)
│   ├── Enum/                 # - Énumérations (OrderStatus, ProductType)
│   ├── Repository/           # - Repositories Doctrine
│   └── State/                # - State Providers & Processors
│
├── Entity/                   # 💾 Entités Doctrine (modèle BDD)
│   ├── User.php
│   ├── DigitalContent.php
│   ├── Subscription.php
│   ├── SubscriptionPlan.php
│   └── ResetPasswordRequest.php
│
├── Enum/                     # 📋 Énumérations
│   └── ApiError.php          # - Codes d'erreur standardisés
│
├── EventListener/            # 👂 Event Listeners
│   └── ApiExceptionListener.php  # - Gestion des exceptions API
│
├── EventSubscriber/          # 📡 Event Subscribers
│   └── AddPaginationMetadataSubscriber.php
│
├── Repository/               # 📚 Repositories Doctrine
│   ├── UserRepository.php
│   ├── DigitalContentRepository.php
│   └── SubscriptionRepository.php
│
├── Serializer/               # 🔄 Normaliseurs/Dénormaliseurs
│
├── Service/                  # ⚙️ Services métier
│   └── PaginationService.php
│
├── State/                    # 🔄 State Providers & Processors
│   └── UserStateProcessor.php
│
└── Trait/                    # 🧩 Traits réutilisables
    └── ApiResponseTrait.php  # - Responses API standardisées

config/
├── packages/                 # Configuration des bundles
├── routes/                   # Configuration des routes
└── services.yaml            # Configuration des services

tests/
├── Controller/               # Tests des contrôleurs
├── Ecommerce/               # Tests e-commerce
├── Entity/                  # Tests des entités
├── Repository/              # Tests des repositories
└── Service/                 # Tests des services
```

### Pattern DTO (Data Transfer Object)

Ce projet utilise une **architecture DTO** pour séparer :

| Couche | Dossier | Rôle |
|--------|---------|------|
| **API** | `ApiResource/` | Ce que les clients API voient et manipulent |
| **Base de données** | `Entity/` | Comment les données sont stockées en BDD |
| **Transformation** | `State/` | Providers (lecture) et Processors (écriture) |

**Avantages** :
- ✅ Séparation des responsabilités (API ≠ BDD)
- ✅ Évolution indépendante de l'API et du modèle de données
- ✅ Sécurité renforcée (pas d'exposition directe du modèle)
- ✅ Validation ciblée selon le contexte (création, modification)

**Exemple de flux** :
```
Client → ApiResource (DTO) → StateProcessor → Entity → BDD
BDD → Entity → StateProvider → ApiResource (DTO) → Client
```

### Technologies Utilisées

| Catégorie | Technologie | Version |
|-----------|------------|---------|
| **Framework** | Symfony | 7.4 |
| **API** | API Platform | 4.2 |
| **ORM** | Doctrine | 3.6 |
| **Authentification** | Lexik JWT Bundle | 3.2 |
| **Paiement** | Stripe PHP | 19.3 |
| **Tests** | PHPUnit | 11.5 |
| **Database** | MariaDB | 11.3 |

---

## 🛠️ Développement

### Commandes Utiles

```bash
# Base de données
php bin/console doctrine:database:create          # Créer la BDD
php bin/console doctrine:migrations:migrate       # Exécuter les migrations
php bin/console doctrine:schema:validate          # Valider le schéma
php bin/console doctrine:fixtures:load            # Charger des fixtures (si installées)

# Utilisateurs
php bin/console app:create-user email@example.com password123 --verified
php bin/console app:create-user admin@example.com admin123 --admin --verified

# JWT
php bin/console lexik:jwt:generate-keypair        # Générer les clés JWT
php bin/console lexik:jwt:generate-keypair --overwrite  # Régénérer

# Cache
php bin/console cache:clear                       # Vider le cache
php bin/console cache:warmup                      # Préchauffer le cache

# Développement
php bin/console debug:router                      # Lister les routes
php bin/console debug:container                   # Lister les services
php bin/console debug:config                      # Afficher la configuration

# Tests
php bin/phpunit                                   # Lancer tous les tests
php bin/phpunit --testdox                         # Tests avec format lisible
php bin/phpunit --coverage-html coverage          # Avec couverture de code
```

### Docker

```bash
# Démarrer les services
docker-compose up -d

# Arrêter les services
docker-compose down

# Voir les logs
docker-compose logs -f api

# Accéder au conteneur PHP
docker-compose exec api bash

# Redémarrer un service
docker-compose restart api
```

### Scripts Utiles

| Script | Description |
|--------|-------------|
| `./setup-db.sh` | Initialise la base de données (création + migrations) |
| `./start.sh` | Démarre Docker et initialise le projet |

### Variables d'Environnement

Toutes les variables sont documentées dans [.env.example](.env.example). Les principales :

| Variable | Description | Exemple |
|----------|-------------|---------|
| `APP_ENV` | Environnement (dev/prod/test) | `dev` |
| `APP_SECRET` | Secret Symfony | `openssl rand -base64 32` |
| `DATABASE_URL` | URL de connexion BDD | `mysql://user:pass@host:3306/db` |
| `JWT_PASSPHRASE` | Passphrase pour les clés JWT | `openssl rand -base64 32` |
| `STRIPE_SECRET_KEY` | Clé secrète Stripe | `sk_test_xxx` |
| `CORS_ALLOW_ORIGIN` | Origines autorisées pour CORS | `http://localhost:3000` |

---

## 📊 Standards de Code

- ✅ **PSR-12** : Standard de codage PHP
- ✅ **Typage strict** : Types de retour et paramètres définis
- ✅ **DTOs** : Séparation API/BDD
- ✅ **Validation** : Attributs Symfony Validator
- ✅ **Documentation** : Commentaires PHPDoc
- ✅ **Tests** : Couverture tests unitaires et d'intégration

---

## 🚀 Déploiement

### Prérequis Production

- PHP 8.2+ avec OPcache activé
- Base de données (MySQL/MariaDB ou PostgreSQL)
- HTTPS obligatoire (Let's Encrypt recommandé)
- Clés JWT sécurisées

### Checklist de Déploiement

- [ ] Configurer `APP_ENV=prod` dans `.env.local`
- [ ] Générer un `APP_SECRET` fort et unique
- [ ] Configurer la vraie base de données
- [ ] Générer des clés JWT de production sécurisées
- [ ] Configurer les vraies clés Stripe (production)
- [ ] Configurer le CORS pour les domaines de production uniquement
- [ ] Activer OPcache pour les performances
- [ ] Désactiver le mode debug
- [ ] Configurer le serveur web (Apache/Nginx)
- [ ] Mettre en place des sauvegardes automatiques
- [ ] Configurer les logs (rotation, monitoring)

---

## 📝 Licence

Ce projet est sous licence propriétaire. Tous droits réservés.

---

## 👨‍💻 Contributeurs

Développé par Nicolas pour **La Petite Maison de l'Épouvante**.

---

## 📞 Support

Pour toute question ou problème :
- 📖 Consultez la [documentation interactive](http://localhost:8000/api)
- 📄 Lisez les fichiers de documentation du projet
- 🐛 Ouvrez une issue sur le dépôt

---

<div align="center">

**⭐ Si vous aimez ce projet, n'hésitez pas à lui donner une étoile ! ⭐**

Made with ❤️ using Symfony & API Platform

</div>

### Ajouter une nouvelle entité API

```bash
php bin/console make:entity --api-resource
```

## 📝 TODO

- [ ] Implémenter l'envoi d'emails (vérification, réinitialisation)
- [ ] Ajouter un système de rate limiting
- [ ] Implémenter la réinitialisation de mot de passe avec token
- [ ] Ajouter des tests unitaires et fonctionnels complets
- [ ] Configurer CI/CD

## 📄 Licence

Ce projet est privé.

## 👥 Auteur

Nicolas
