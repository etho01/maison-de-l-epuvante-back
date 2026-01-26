# Configuration des routes dans les ApiResource

## 📝 Approche adoptée

Les routes sont définies **dans l'ApiResource User** comme **opérations API Platform**. Toutes les routes (CRUD et authentification) sont centralisées dans un seul fichier.

## ✅ Avantages

1. **Centralisation** : Toutes les routes de la ressource User dans un seul endroit
2. **Cohérence API Platform** : Utilisation native des opérations personnalisées
3. **Auto-documentation OpenAPI** : Génération automatique de la documentation Swagger
4. **Séparation claire** : ApiResource = définition des routes, Controllers = logique métier
5. **Type-safety** : Vérification au niveau du code PHP
6. **Visibilité** : Vue d'ensemble de toutes les routes User en un coup d'œil

## 📁 Structure actuelle

### Routes définies dans ApiResource User

**Fichier complet** (`src/ApiResource/User.php`) :
```php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    operations: [
        // CRUD Operations standard
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"),
        new Post(security: "is_granted('PUBLIC_ACCESS')"),
        new Put(security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"),
        new Patch(security: "is_granted('ROLE_ADMIN') or object.id == user?.getId()"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        
        // Opérations personnalisées d'authentification
        new Post(
            uriTemplate: '/login',
            controller: \App\Controller\AuthController::class . '::login',
            name: 'api_login',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/change-password',
            controller: \App\Controller\PasswordController::class . '::changePassword',
            name: 'api_change_password',
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            uriTemplate: '/reset-password-request',
            controller: \App\Controller\PasswordController::class . '::requestResetPassword',
            name: 'api_reset_password_request',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Get(
            uriTemplate: '/verify/email',
            controller: \App\Controller\VerifyEmailController::class . '::verifyUserEmail',
            name: 'api_verify_email',
            security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/verify/resend',
            controller: \App\Controller\VerifyEmailController::class . '::resendVerificationEmail',
            name: 'api_resend_verify_email',
            security: "is_granted('ROLE_USER')"
        ),
    ],
    provider: UserProvider::class,
    processor: UserStateProcessor::class,
)]
class User { }
```

### Contrôleurs sans attributs Route

**Exemple** (`src/Controller/AuthController.php`) :
```php
class AuthController extends AbstractController
{
    // Pas d'attribut #[Route()] ici !
    // La route est définie dans l'ApiResource User
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        // Logique métier uniquement
        if (null === $user) {
            return $this->json(['message' => 'Non autorisé'], 401);
        }
        return $this->json(['user' => [...]]);
    }
}
```

## 📄 Routes disponibles

### Routes CRUD (opérations standard)
| Méthode | Route | Description | Sécurité |
|---------|-------|-------------|----------|
| GET | `/api/users` | Liste des utilisateurs | ROLE_ADMIN |
| GET | `/api/users/{id}` | Détails d'un utilisateur | ROLE_ADMIN ou propriétaire |
| POST | `/api/users` | Créer un utilisateur | PUBLIC_ACCESS |
| PUT | `/api/users/{id}` | Modifier un utilisateur | ROLE_ADMIN ou propriétaire |
| PATCH | `/api/users/{id}` | Modifier partiellement | ROLE_ADMIN ou propriétaire |
| DELETE | `/api/users/{id}` | Supprimer un utilisateur | ROLE_ADMIN |

### Routes d'authentification (opérations personnalisées)
| Nom | Méthode | Path | Contrôleur | Sécurité |
|-----|---------|------|------------|----------|
| `api_login` | POST | `/api/login` | AuthController::login | PUBLIC_ACCESS |
| `api_change_password` | POST | `/api/change-password` | PasswordController::changePassword | ROLE_USER |
| `api_reset_password_request` | POST | `/api/reset-password-request` | PasswordController::requestResetPassword | PUBLIC_ACCESS |
| `api_verify_email` | GET | `/api/verify/email` | VerifyEmailController::verifyUserEmail | PUBLIC_ACCESS |
| `api_resend_verify_email` | POST | `/api/verify/resend` | VerifyEmailController::resendVerificationEmail | ROLE_USER |

## ✏️ Structure des fichiers

### ApiResource (définition de TOUTES les routes)
- ✅ `src/ApiResource/User.php` - **Routes CRUD + routes d'authentification**
  - Operations CRUD standard (GetCollection, Get, Post, Put, Patch, Delete)
  - Operations personnalisées (login, change-password, reset-password, verify-email)

### Contrôleurs (logique métier uniquement, sans #[Route()])
- ✅ `src/Controller/AuthController.php` - Logique login
- ✅ `src/Controller/PasswordController.php` - Logique mot de passe
- ✅ `src/Controller/VerifyEmailController.php` - Logique vérification email

### State (transformation DTO ↔ Entity)
- ✅ `src/State/UserProvider.php` - Entity → DTO (lecture)
- ✅ `src/State/UserStateProcessor.php` - DTO → Entity (écriture)

## 💡 Pour ajouter une nouvelle route

### Option 1 : Opération personnalisée dans ApiResource User (pour routes liées aux users)

1. Ajoutez l'opération dans `src/ApiResource/User.php` :

```php
#[ApiResource(
    operations: [
        // ... opérations existantes
        
        // Nouvelle route personnalisée
        new Post(
            uriTemplate: '/mon-action',
            controller: \App\Controller\MonController::class . '::maMethode',
            name: 'api_mon_action',
            security: "is_granted('ROLE_USER')"
        ),
    ]
)]
class User { }
```

2. Créez le contrôleur avec la logique métier (sans attribut Route) :

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class MonController extends AbstractController
{
    // Pas d'attribut #[Route()] !
    public function maMethode(): JsonResponse
    {
        return $this->json(['message' => 'Nouvelle route']);
    }
}
```

### Option 2 : Nouvelle ApiResource pour une autre ressource (ex: Article, Comment)

1. Créez un DTO ApiResource :

```php
// src/ApiResource/Article.php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
    ],
    provider: ArticleProvider::class,
    processor: ArticleStateProcessor::class,
)]
class Article
{
    public ?int $id = null;
    public ?string $title = null;
}
```

2. Créez le Provider et Processor correspondants

## 📚 Options des opérations personnalisées

### Opération personnalisée basique
```php
new Post(
    uriTemplate: '/login',
    controller: \App\Controller\AuthController::class . '::login',
    name: 'api_login'
)
```

### Opération avec sécurité
```php
new Post(
    uriTemplate: '/change-password',
    controller: \App\Controller\PasswordController::class . '::changePassword',
    name: 'api_change_password',
    security: "is_granted('ROLE_USER')"
)
```

### Opération avec paramètres d'URL
```php
new Post(
    uriTemplate: '/users/{id}/activate',
    controller: \App\Controller\UserController::class . '::activate',
    name: 'api_user_activate',
    security: "is_granted('ROLE_ADMIN')"
)
```

### Différentes méthodes HTTP
```php
// GET pour lecture
new Get(
    uriTemplate: '/verify/email',
    controller: \App\Controller\VerifyEmailController::class . '::verifyUserEmail',
    name: 'api_verify_email'
)

// POST pour action/création
new Post(
    uriTemplate: '/verify/resend',
    controller: \App\Controller\VerifyEmailController::class . '::resendVerificationEmail',
    name: 'api_resend_verify_email'
)

// PUT pour remplacement complet
new Put(
    uriTemplate: '/users/{id}',
    security: "is_granted('ROLE_ADMIN')"
)

// PATCH pour modification partielle
new Patch(
    uriTemplate: '/users/{id}',
    security: "is_granted('ROLE_USER')"
)

// DELETE pour suppression
new Delete(
    uriTemplate: '/users/{id}',
    security: "is_granted('ROLE_ADMIN')"
)
```

### Operations CRUD standard
```php
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_ADMIN')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
class Article { }
```

## 🎯 Bonnes pratiques

1. **✅ Centralisez dans ApiResource** : Toutes les routes d'une ressource dans son ApiResource
2. **✅ Opérations personnalisées** : Utilisez `new Post(uriTemplate: ...)` pour les actions custom
3. **✅ Noms explicites** : `api_login` plutôt que `route1`
4. **✅ Cohérence** : Préfixe `api_` pour toutes les routes API
5. **✅ Contrôleurs purs** : Pas d'attribut `#[Route()]`, uniquement la logique métier
6. **✅ Sécurité explicite** : Définissez toujours `security` dans les opérations
7. **✅ Méthodes HTTP appropriées** : GET (lecture), POST (action/création), PUT (remplacement), PATCH (modification), DELETE (suppression)

## 🔧 Commandes utiles

### Lister toutes les routes
```bash
php bin/console debug:router
```

### Lister uniquement les routes d'authentification
```bash
php bin/console debug:router | grep api_
```

### Vérifier une route spécifique
```bash
php bin/console debug:router api_login
```

### Tester la configuration
```bash
php bin/console lint:container
```

## 📊 Quand utiliser quoi ?

| Type de route | Outil | Exemple | Fichier |
|---------------|-------|---------|---------|
| CRUD standard | `new Get()`, `new Post()` | GET/POST /api/users | ApiResource/User.php |
| Action liée à User | `new Post(uriTemplate: ...)` | POST /api/login | ApiResource/User.php |
| Nouvelle ressource | Nouvel ApiResource | Article, Comment | ApiResource/Article.php |
| Webhook externe | Contrôleur avec `#[Route()]` | POST /webhooks/stripe | Controller/WebhookController.php |

## 🆚 Comparaison des approches

| Aspect | ApiResource Operations | Attributs #[Route()] | YAML |
|--------|----------------------|---------------------|------|
| **Localisation** | Dans l'ApiResource | Dans le contrôleur | Fichier séparé |
| **Centralisation** | ⭐⭐⭐ Excellent | ⭐ Dispersé | ⭐⭐ Par fichier |
| **IDE Support** | ⭐⭐⭐ Excellent | ⭐⭐⭐ Excellent | ⭐ Limité |
| **Auto-doc OpenAPI** | ⭐⭐⭐ Automatique | ⭐ Manuel | ⭐ Manuel |
| **Refactoring** | ⭐⭐⭐ Auto | ⭐⭐⭐ Auto | ⭐ Manuel |
| **Visibilité** | ⭐⭐⭐ Un fichier | ⭐ Plusieurs fichiers | ⭐⭐ Un fichier |
| **Type-safety** | ⭐⭐⭐ Oui | ⭐⭐⭐ Oui | ⭐ Non |
| **Recommandé pour** | Routes API REST | Routes hors API Platform | Legacy/migration |

**Notre choix** : ✅ **ApiResource Operations** pour TOUT ce qui concerne User (CRUD + authentification)

## ✅ Résultat final

Toutes les routes User sont centralisées dans `src/ApiResource/User.php` :

- ✅ **ApiResource User** : Définit TOUTES les routes (CRUD + authentification)
- ✅ **Contrôleurs** : Contiennent UNIQUEMENT la logique métier (pas d'attribut `#[Route()]`)
- ✅ **Pas de YAML** : Tout est défini en PHP dans l'ApiResource
- ✅ **Documentation auto** : Swagger/OpenAPI généré automatiquement à `/api`
- ✅ **Centralisation** : Vue d'ensemble complète en un seul fichier
- ✅ **Séparation claire** : Routes dans ApiResource, logique dans Controllers

## 🔍 Exemple complet actuel

**Routes définies** (toutes dans `src/ApiResource/User.php`) :
```
CRUD:
  GET    /api/users             → UserProvider (GetCollection)
  GET    /api/users/{id}        → UserProvider (Get)
  POST   /api/users             → UserStateProcessor (Post)
  PUT    /api/users/{id}        → UserStateProcessor (Put)
  PATCH  /api/users/{id}        → UserStateProcessor (Patch)
  DELETE /api/users/{id}        → UserStateProcessor (Delete)

Authentification:
  POST   /api/login             → AuthController::login
  POST   /api/change-password   → PasswordController::changePassword
  POST   /api/reset-password-request → PasswordController::requestResetPassword
  GET    /api/verify/email      → VerifyEmailController::verifyUserEmail
  POST   /api/verify/resend     → VerifyEmailController::resendVerificationEmail
```

**Contrôleurs** (logique métier uniquement) :
```php
// src/Controller/AuthController.php
class AuthController extends AbstractController
{
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        // Pas de #[Route()] ici !
        // La route est définie dans ApiResource/User.php
    }
}
```

## 🚀 Pour les futures routes

**Règle à suivre** : Toujours définir les routes dans l'ApiResource, jamais dans les contrôleurs (sauf cas très particuliers hors API Platform comme webhooks externes).
