# Maison de l'Épouvante - Backend API

API REST développée avec Symfony 7.4 et API Platform pour le projet "Maison de l'Épouvante".

## 🚀 Fonctionnalités

- ✅ **API REST complète** avec API Platform
- ✅ **Architecture DTO** (Data Transfer Object) pour séparation API/BDD
- ✅ **Authentification JWT** sécurisée
- ✅ **Gestion des utilisateurs** (création, modification, suppression)
- ✅ **Vérification d'email**
- ✅ **Changement de mot de passe**
- ✅ **Réinitialisation de mot de passe**
- ✅ **Documentation API interactive** (OpenAPI/Swagger)

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- PostgreSQL 16 (ou autre base de données compatible Doctrine)
- Extensions PHP : `pdo_pgsql`, `openssl`, `json`

## 🔧 Installation

### 1. Installer les dépendances

```bash
composer install
```

### 2. Configurer l'environnement

Copiez le fichier `.env` et configurez vos variables :

```bash
cp .env .env.local
```

Éditez `.env.local` et configurez votre base de données :

```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/maison_de_lepouvante?serverVersion=16&charset=utf8"
```

### 3. Créer la base de données

Utilisez le script fourni :

```bash
./setup-db.sh
```

Ou manuellement :

```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 4. Créer un utilisateur admin

```bash
php bin/console app:create-user admin@example.com admin123 --admin --verified --first-name=Admin --last-name=User
```

### 5. Lancer le serveur

```bash
symfony server:start
```

Ou avec PHP :

```bash
php -S localhost:8000 -t public
```

L'API est accessible sur : `http://localhost:8000/api`

## 📖 Documentation

### Documentation interactive

Accédez à la documentation Swagger/OpenAPI :

```
http://localhost:8000/api
```

### Endpoints disponibles

Consultez [AUTH_README.md](AUTH_README.md) pour la documentation complète de l'authentification.

#### Authentification
- `POST /api/login` - Connexion
- `POST /api/users` - Créer un utilisateur
- `PUT /api/users/{id}` - Modifier un utilisateur
- `PATCH /api/users/{id}` - Modifier partiellement un utilisateur
- `GET /api/users/{id}` - Récupérer un utilisateur
- `GET /api/users` - Lister les utilisateurs (admin)

#### Mot de passe
- `POST /api/change-password` - Changer son mot de passe
- `POST /api/reset-password-request` - Demander une réinitialisation

#### Vérification d'email
- `GET /api/verify/email` - Vérifier l'email
- `POST /api/verify/resend` - Renvoyer l'email de vérification

### Exemples d'utilisation

Consultez le fichier [api-examples.http](api-examples.http) pour des exemples de requêtes.

## 🧪 Tests

Lancer les tests :

```bash
php bin/phpunit
```

## 🔐 Sécurité

- Les mots de passe sont hashés avec bcrypt
- Les tokens JWT sont signés avec RSA (clés publique/privée)
- Les utilisateurs doivent vérifier leur email avant de se connecter
- CORS configuré pour localhost par défaut

### Configuration JWT

Les clés JWT sont générées automatiquement lors de l'installation dans `config/jwt/`.

Pour régénérer les clés :

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```

## 📦 Structure du projet

```
src/ApiResource/      # Ressources API (DTOs) - Ce que l'API expose
│   └── User.php     # DTO User pour l'API
│
├── Command/          # Commandes Symfony (création d'utilisateur, etc.)
│
├── Controller/       # Contrôleurs API
│   ├── AuthController.php
│   ├── PasswordController.php
│   └── VerifyEmailController.php
│
├── Entity/           # Entités Doctrine (persistance BDD)
│   └── User.php     # Entité User en base de données
│
├── Repository/       # Repositories Doctrine
│   └── UserRepository.php
│
└── State/            # State Providers/Processors (transformation DTO ↔ Entity)
    ├── UserProvider.php        # Lecture : Entity → DTO
    └── UserStateProcessor.php  # Écriture : DTO → Entity

config/
├── packages/         # Configuration des bundles
├── routes/           # Configuration des routes
└── services.yaml     # Configuration des services

tests/
└── Controller/       # Tests des contrôleurs
```

### Architecture DTO

Le projet utilise le **pattern DTO** (Data Transfer Object) pour séparer :
- **L'API** : Ce que les clients voient (`ApiResource/`)
- **La BDD** : Comment les données sont stockées (`Entity/`)

Pour en savoir plus : [DTO_ARCHITECTURE.md](DTO_ARCHITECTURE.md) Controller/       # Tests des contrôleurs
```

## 🛠️ Développement

### Commandes utiles

```bash
# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Créer un utilisateur
php bin/console app:create-user email@example.com password123 --verified

# Créer un admin
php bin/console app:create-user admin@example.com admin123 --admin --verified
```

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
