# Architecture API Resources Autonomes - Récapitulatif

## ✅ Changements effectués

### 1. API Resources autonomes (plus d'héritage d'entités)

Toutes les API Resources ne dépendent plus des entités. Elles sont maintenant des classes autonomes avec leur propre validation.

**Avant :**
```php
class Category extends CategoryEntity { }
```

**Après :**
```php
class Category {
    #[Assert\NotBlank]
    #[Groups(['category:read', 'category:write'])]
    public ?string $name = null;
    // ... toutes les propriétés avec validation intégrée
}
```

### 2. Validation intégrée dans les API Resources

Les contraintes de validation sont directement dans les API Resources au lieu d'être dans des DTOs séparés.

**Exemple - Category :**
```php
#[Assert\NotBlank(message: 'Le nom de la catégorie est requis', groups: ['category:write'])]
#[Assert\Length(min: 2, max: 255, groups: ['category:write'])]
#[Groups(['category:read', 'category:list', 'category:detail', 'category:write'])]
public ?string $name = null;
```

### 3. Toutes les routes définies dans les API Resources

Plus besoin de fichiers séparés comme UserManagement.php. Tout est centralisé.

## 📂 Fichiers modifiés

### API Resources Ecommerce (src/Ecommerce/ApiResource/)

#### ✅ Category.php
- Classe autonome sans héritage
- Validation intégrée (nom, slug, description)
- Routes : GET, POST, PUT, PATCH, DELETE
- Controllers : CreateCategoryController, UpdateCategoryController
- Formats : JSON + JSON-LD

#### ✅ Product.php
- Classe autonome sans héritage
- Validation intégrée (nom, prix, stock, type, etc.)
- Routes : GET, POST, PUT, PATCH, DELETE + slug lookup
- Controllers : CreateProductController, UpdateProductController
- Filtres : SearchFilter, RangeFilter, BooleanFilter
- Formats : JSON + JSON-LD

#### ✅ Order.php
- Classe autonome sans héritage
- Validation intégrée (status, addresses, payment)
- Routes : GET, POST /orders/checkout, PATCH
- Controller : CreateOrderController
- Formats : JSON + JSON-LD

### API Resources Principal (src/ApiResource/)

#### ✅ User.php
- Classe autonome sans héritage
- Validation intégrée (email, password, firstName, lastName)
- Routes complètes :
  - CRUD : GET, POST, PUT, PATCH, DELETE
  - Auth : /login, /me
  - Password : /change-password, /reset-password-request, /reset-password-confirm
  - Email : /verify/email, /verify/resend
- Controllers : CreateUserController, UpdateUserController
- Formats : JSON + JSON-LD

#### ✅ Subscription.php
- Classe autonome sans héritage
- Validation intégrée (planId, status)
- Routes : GET, POST /subscriptions/subscribe, PATCH /cancel, PATCH /renew
- Controller : CreateSubscriptionController
- Formats : JSON + JSON-LD

#### ✅ SubscriptionPlan.php
- Classe autonome sans héritage
- Validation intégrée (name, price, duration, durationUnit)
- Routes : GET, POST, PUT, PATCH, DELETE
- Controller : CreateSubscriptionPlanController
- State Provider/Processor
- Formats : JSON + JSON-LD

#### ✅ DigitalContent.php
- Classe autonome sans héritage
- Validation intégrée (name, filePath, contentType)
- Routes : GET, GET /download, POST
- Controller : CreateDigitalContentController
- Formats : JSON + JSON-LD

### Controllers modifiés (src/Controller/ et src/Ecommerce/Controller/)

Tous les controllers acceptent maintenant les API Resources au lieu des DTOs :

- ✅ CreateCategoryController
- ✅ UpdateCategoryController
- ✅ CreateProductController
- ✅ UpdateProductController
- ✅ CreateUserController
- ✅ UpdateUserController
- ✅ CreateOrderController
- ✅ CreateSubscriptionController
- ✅ CreateSubscriptionPlanController
- ✅ CreateDigitalContentController

## 🗑️ Fichiers supprimés

- ❌ src/ApiResource/UserManagement.php (routes intégrées dans User.php)
- ❌ src/ApiResource/OrderManagement.php (routes intégrées dans Order.php)
- ❌ src/ApiResource/SubscriptionManagement.php (routes intégrées dans Subscription.php)
- ❌ src/ApiResource/DigitalContentManagement.php (routes intégrées dans DigitalContent.php)

## 🎯 Structure finale

```
src/
├── ApiResource/
│   ├── User.php                    ← API Resource autonome avec validation
│   ├── Subscription.php            ← API Resource autonome avec validation
│   ├── SubscriptionPlan.php        ← API Resource autonome avec validation
│   └── DigitalContent.php          ← API Resource autonome avec validation
│
├── Ecommerce/ApiResource/
│   ├── Category.php                ← API Resource autonome avec validation
│   ├── Product.php                 ← API Resource autonome avec validation
│   └── Order.php                   ← API Resource autonome avec validation
│
├── Controller/
│   ├── CreateUserController.php
│   ├── UpdateUserController.php
│   ├── CreateSubscriptionController.php
│   ├── CreateSubscriptionPlanController.php
│   └── CreateDigitalContentController.php
│
├── Ecommerce/Controller/
│   ├── CreateCategoryController.php
│   ├── UpdateCategoryController.php
│   ├── CreateProductController.php
│   ├── UpdateProductController.php
│   └── CreateOrderController.php
│
├── Entity/                         ← Entités Doctrine (inchangées)
│   ├── User.php
│   ├── Subscription.php
│   ├── SubscriptionPlan.php
│   └── DigitalContent.php
│
└── Ecommerce/Entity/              ← Entités Ecommerce (inchangées)
    ├── Category.php
    ├── Product.php
    └── Order.php
```

## 📋 Routes disponibles

### Catégories
- `GET /api/categories` - Lister toutes les catégories
- `GET /api/categories/{id}` - Obtenir une catégorie
- `POST /api/categories` - Créer une catégorie (ADMIN, validation)
- `PUT /api/categories/{id}` - Mettre à jour complète (ADMIN, validation)
- `PATCH /api/categories/{id}` - Mise à jour partielle (ADMIN, validation)
- `DELETE /api/categories/{id}` - Supprimer (ADMIN)

### Produits
- `GET /api/products` - Lister tous les produits
- `GET /api/products/{id}` - Obtenir un produit
- `GET /api/products/slug/{slug}` - Obtenir par slug
- `POST /api/products` - Créer un produit (ADMIN, validation)
- `PUT /api/products/{id}` - Mettre à jour complète (ADMIN, validation)
- `PATCH /api/products/{id}` - Mise à jour partielle (ADMIN, validation)
- `DELETE /api/products/{id}` - Supprimer (ADMIN)

### Commandes
- `GET /api/orders` - Mes commandes (USER)
- `GET /api/orders/{id}` - Détails commande (USER/ADMIN)
- `POST /api/orders/checkout` - Créer commande (USER, validation)
- `PATCH /api/orders/{id}` - Modifier statut (ADMIN)

### Utilisateurs
- `GET /api/users` - Lister utilisateurs (ADMIN)
- `GET /api/users/{id}` - Détails utilisateur (ADMIN/OWNER)
- `POST /api/users` - Créer utilisateur (PUBLIC, validation)
- `PUT /api/users/{id}` - Mettre à jour (ADMIN/OWNER, validation)
- `PATCH /api/users/{id}` - Mise à jour partielle (ADMIN/OWNER, validation)
- `DELETE /api/users/{id}` - Supprimer (ADMIN)
- `POST /api/login` - Connexion (PUBLIC)
- `GET /api/me` - Utilisateur actuel (USER)
- `PATCH /api/me` - Modifier profil (USER)
- `POST /api/change-password` - Changer mot de passe (USER)
- `POST /api/reset-password-request` - Demande reset (PUBLIC)
- `POST /api/reset-password-confirm` - Confirmer reset (PUBLIC)
- `GET /api/verify/email` - Vérifier email (PUBLIC)
- `POST /api/verify/resend` - Renvoyer vérification (USER)

### Abonnements
- `GET /api/subscriptions` - Mes abonnements (USER)
- `GET /api/subscriptions/{id}` - Détails abonnement (USER/ADMIN)
- `POST /api/subscriptions/subscribe` - S'abonner (USER, validation)
- `PATCH /api/subscriptions/{id}/cancel` - Annuler (USER)
- `PATCH /api/subscriptions/{id}/renew` - Renouveler (USER)

### Plans d'abonnement
- `GET /api/subscription_plans` - Tous les plans
- `GET /api/subscription_plans/{id}` - Détails plan
- `POST /api/subscription_plans` - Créer plan (ADMIN, validation)
- `PUT /api/subscription_plans/{id}` - Mettre à jour (ADMIN, validation)
- `PATCH /api/subscription_plans/{id}` - Mise à jour partielle (ADMIN, validation)
- `DELETE /api/subscription_plans/{id}` - Supprimer (ADMIN)

### Contenus numériques
- `GET /api/digital_contents` - Lister contenus (USER)
- `GET /api/digital_contents/{id}` - Détails contenu (USER)
- `GET /api/digital_contents/{id}/download` - Télécharger (USER)
- `POST /api/digital_contents` - Créer contenu (ADMIN, validation)

## ✨ Avantages de cette architecture

1. **Centralisation** - Toutes les routes sont dans les API Resources
2. **Validation intégrée** - Plus besoin de DTOs séparés
3. **Indépendance** - API Resources ne dépendent pas des entités
4. **Clarté** - Un seul fichier par ressource avec tout dedans
5. **Maintenance** - Plus facile de trouver et modifier une route
6. **Groupes de serialization** - Contrôle précis de ce qui est exposé
7. **Formats multiples** - JSON + JSON-LD partout
8. **Sécurité** - Contrôle d'accès sur chaque route

## 🚀 Utilisation

### Créer une catégorie (avec validation)
```bash
POST /api/categories
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "name": "Vêtements gothiques",
  "slug": "vetements-gothiques",
  "description": "Notre collection de vêtements"
}
```

### Créer un produit (avec validation)
```bash
POST /api/products
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "name": "T-shirt noir",
  "slug": "t-shirt-noir",
  "price": 29.99,
  "stock": 100,
  "type": "physical",
  "categoryId": 1
}
```

### Créer un utilisateur (avec validation)
```bash
POST /api/users
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "Password123",
  "firstName": "Jean",
  "lastName": "Dupont"
}
```

## 🔍 Validation automatique

Toutes les requêtes sont validées automatiquement :
- ✅ Nom de catégorie min 2 caractères
- ✅ Slug en minuscules avec tirets
- ✅ Prix positif
- ✅ Stock >= 0
- ✅ Email valide
- ✅ Mot de passe fort (8+ caractères, majuscule, minuscule, chiffre)
- ✅ Type de produit : physical/digital/subscription
- ✅ Statut de commande valide
- ✅ Et bien plus...

## ✅ Tests

Cache vidé avec succès ✓
Routes enregistrées ✓
Validation fonctionnelle ✓
Controllers mis à jour ✓
