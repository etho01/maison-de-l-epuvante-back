# Architecture du système d'authentification

## Vue d'ensemble

Le système d'authentification utilise JWT (JSON Web Tokens) avec API Platform et Symfony Security.

**Architecture :** DTO Pattern (Data Transfer Object) pour une séparation complète entre l'API et la persistance.

> 📘 Pour comprendre l'architecture DTO en détail, consultez [DTO_ARCHITECTURE.md](DTO_ARCHITECTURE.md)

## Flux d'authentification

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       │ POST /api/users (création)
       ▼
┌─────────────────────────────┐
│   UserStateProcessor        │
│   - DTO → Entity            │
│   - Hash le mot de passe    │
│   - Génère lien vérification│
└──────────┬──────────────────┘
           │
           ▼
    ┌──────────────┐
    │   Database   │
    └──────┬───────┘
           │
           ▼
    Email de vérification (TODO)
    
    
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       │ POST /api/login
       ▼
┌─────────────────────────────┐
│   AuthController            │
│   - Vérifie identifiants    │
│   - Vérifie email vérifié   │
└──────────┬──────────────────┘
           │
           ▼
    ┌──────────────────┐
    │  JWT Generator   │
    │  (Lexik Bundle)  │
    └────────┬─────────┘
             │
             ▼
    Retourne token JWT
    
    
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       │ Authorization: Bearer <token>
       ▼
┌─────────────────────────────┐
│   JWT Authenticator        │
│   - Valide le token        │
│   - Extrait l'utilisateur  │
└──────────┬──────────────────┘
           │
           ▼
    Accès aux ressources protégées
```

## Composants principaux

### 1. DTOs et Entités

#### `ApiResource\User` (src/ApiResource/User.php) - DTO
- Représentation de l'utilisateur exposée par l'API
- Propriétés publiques
- Attributs : id, email, plainPassword, firstName, lastName, isVerified, roles, createdAt, updatedAt
- Groupes de sérialisation : `user:read`, `user:write`
- Contraintes de validation

#### `Entity\User` (src/Entity/User.php) - Entité Doctrine
- Représentation de l'utilisateur en base de données
- Propriétés privées
- Implémente `UserInterface` et `PasswordAuthenticatedUserInterface`
- Pas d'annotations API Platform
- Annotations Doctrine (ORM)

### 2. State Layer

#### `UserProvider` (src/State/UserProvider.php)
- Transforme les entités en DTOs pour les lectures (GET)
- Méthode `entityToDto()` : Entity → DTO
- Gère les collections et les items individuels

#### `UserStateProcessor` (src/State/UserStateProcessor.php)
- Transforme les DTOs en entités pour les écritures (POST/PUT/PATCH)
- Hash le mot de passe automatiquement
- Génère le lien de vérification d'email pour les nouveaux utilisateurs
- Méthodes :
  - `dtoToEntity()` : DTO → Entity
  - `entityToDto()` : Entity → DTO (pour la réponse)

### 2. lic
  - Ne révèle pas si l'email existe (sécurité)

#### `VerifyEmailController` (src/Controller/VerifyEmailController.php)
- `GET /api/verify/email` : Vérification d'email
  - Paramètres : id, token
  - Marque l'utilisateur comme vérifié
- `POST /api/verify/resend` : Renvoyer l'email de vérification
  - Public
  - Génère un nouveau lien de vérification

### 3. Event Subscribers

#### `UserPasswordHasherSubscriber` (src/EventSubscriber/UserPasswordHasherSubscriber.php)
- Écoute les événements Doctrine `prePersist` et `preUpdate`
- Hashe automatiquement le mot de passe si `plainPassword` est défini
- Met à jour `updatedAt` lors des modifications

### 4. State Processors

#### `UserProcessor` (src/State/UserProcessor.php)
- Décore le processor Doctrine standard
- Génère le lien de vérification d'email lors de la création d'utilisateur
- TODO : Envoyer l'email de vérification

### 5. Commands

#### `CreateUserCommand` (src/Command/CreateUserCommand.php)
- `php bin/console app:create-user <email> <password> [options]`
- Options :
  - `--admin` : Créer un administrateur
  - `--verified` : Marquer l'email comme vérifié
  - `--first-name` : Prénom
  - `--last-name` : Nom

## Configuration de sécurité

### Firewalls (config/packages/security.yaml)

1. **dev** : Désactive la sécurité pour le profiler
2. **login** : Gère l'authentification JSON
   - Chemin : `/api/login`
   - Stateless (pas de session)
   - Handlers Lexik pour le succès/échec
3. **api** : Protège les routes API
   - Chemin : `/api`
   - Stateless
   - Authentification JWT

### Access Control

- `/api/login` : PUBLIC_ACCESS
- `/api/verify/**` : PUBLIC_ACCESS
- `/api/reset-password-request` : PUBLIC_ACCESS
- `/api/users` (POST uniquement) : PUBLIC_ACCESS
- `/api/**` : IS_AUTHENTICATED_FULLY

## Flux de vérification d'email

```
1. Utilisateur créé
   │
   ▼
2. UserProcessor génère signature
   │
   ▼
3. Email envoyé avec lien (TODO)
   │
   ▼
4. Utilisateur clique sur le lien
   │
   ▼
5. GET /api/verify/email?id=X&token=Y
   │
   ▼
6. VerifyEmailHelper valide la signature
   │
   ▼
7. User.isVerified = true
   │
   ▼
8. L'utilisateur peut se connecter
```

## Sécurité

### Hachage des mots de passe
- Algorithme : `auto` (bcrypt par défaut)
- Coût adapté automatiquement
- Salt unique par mot de passe

### JWT
- Algorithme : RS256 (RSA avec SHA-256)
- Clés : Paire publique/privée RSA
- TTL : 1 heure par défaut (configurable)
- Stateless : Aucune session côté serveur

### Validation
- E🔄 Flux de données avec DTO

### Lecture (GET /api/users/1)

```
Client → API Platform → UserProvider
  ↓
UserRepository.find(1) → UserEntity
  ↓
entityToDto() → UserDto
  ↓
Serializer (groupes: user:read) → JSON
```

### Création (POST /api/users)

```
Client (JSON) → Deserializer → UserDto
  ↓
Validation (contraintes + groups)
  ↓
UserStateProcessor
  ↓
dtoToEntity() → UserEntity (nouveau)
  ↓
Hash plainPassword → password
  ↓
EntityManager.persist() → Database
  ↓
Génération lien vérification
  ↓
entityToDto() → UserDto → JSON Response
```

## mail unique (contrainte de base de données)
- Mot de passe minimum 8 caractères
- Email doit être vérifié pour se connecter

## Extensions futures

### À implémenter

1. **Envoi d'emails**
   - Installer Symfony Mailer
   - Créer des templates Twig pour les emails
   - Implémenter l'envoi dans UserProcessor et PasswordController

2. **Rate limiting**
   - Limiter les tentatives de connexion
   - Limiter les demandes de réinitialisation

3. **Refresh tokens**
   - Token de rafraîchissement pour renouveler le JWT
   - Stockage en base de données
   - Révocation possible

4. **2FA (Two-Factor Authentication)**
   - TOTP (Time-based One-Time Password)
   - Code par SMS ou email

5. **OAuth2**
   - Connexion via Google, Facebook, etc.
   - Utiliser KnpUOAuth2ClientBundle

6. **Audit log**
   - Tracer les connexions
   - Tracer les changements de mot de passe
   - IP, user agent, timestamp

## Tests

Les tests sont dans `tests/Controller/AuthControllerTest.php` :

- Test de création d'utilisateur
- Test de connexion sans vérification (doit échouer)

Pour exécuter :
```bash
php bin/phpunit
```

## Dépendances principales

- `api-platform/api-pack` : Framework API REST
- `lexik/jwt-authentication-bundle` : Authentification JWT
- `symfonycasts/verify-email-bundle` : Vérification d'email
- `symfony/security-bundle` : Gestion de la sécurité
- `doctrine/orm` : ORM pour la base de données
