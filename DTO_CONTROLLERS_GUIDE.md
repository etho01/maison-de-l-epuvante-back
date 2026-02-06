# Architecture DTO et Controllers

## Vue d'ensemble

Cette architecture utilise des DTOs (Data Transfer Objects) pour la validation des données et des controllers personnalisés pour gérer la logique métier, séparés des entités Doctrine.

## Structure

```
src/
├── Dto/                           # Classes de validation
│   ├── CategoryDto.php
│   ├── ProductDto.php
│   ├── UserDto.php
│   ├── OrderDto.php
│   ├── SubscriptionDto.php
│   ├── SubscriptionPlanDto.php
│   └── DigitalContentDto.php
│
├── Controller/                    # Controllers avec logique métier
│   ├── CreateUserController.php
│   ├── UpdateUserController.php
│   ├── CreateSubscriptionController.php
│   ├── CreateSubscriptionPlanController.php
│   └── CreateDigitalContentController.php
│
├── Ecommerce/Controller/         # Controllers e-commerce
│   ├── CreateCategoryController.php
│   ├── UpdateCategoryController.php
│   ├── CreateProductController.php
│   ├── UpdateProductController.php
│   └── CreateOrderController.php
│
└── ApiResource/                   # API Resources avec validation
    ├── UserManagement.php
    ├── OrderManagement.php
    ├── SubscriptionManagement.php
    └── DigitalContentManagement.php
```

## DTOs avec Validation

Les DTOs contiennent les contraintes de validation Symfony :

### Exemple : CategoryDto

```php
#[Assert\NotBlank(message: 'Le nom de la catégorie est requis')]
#[Assert\Length(min: 2, max: 255)]
public ?string $name = null;

#[Assert\Regex(
    pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    message: 'Le slug doit être en minuscules avec des tirets uniquement'
)]
public ?string $slug = null;
```

### Contraintes disponibles

- `NotBlank` : Champ requis
- `Length` : Longueur min/max
- `Email` : Format email valide
- `Regex` : Expression régulière
- `Positive` / `PositiveOrZero` : Nombres positifs
- `Range` : Plage de valeurs
- `Choice` : Liste de valeurs autorisées

## Controllers

Les controllers reçoivent automatiquement les DTOs validés et gèrent la logique métier.

### Exemple : CreateCategoryController

```php
#[AsController]
class CreateCategoryController extends AbstractController
{
    public function __invoke(CategoryDto $data): JsonResponse
    {
        // $data est déjà validé automatiquement
        
        $category = new Category();
        $category->setName($data->name);
        $category->setSlug($data->slug);
        
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        return $this->json(['message' => 'Créé'], 201);
    }
}
```

## API Resources

Les API Resources lient les endpoints aux controllers et aux DTOs.

### Exemple : Category

```php
#[ApiResource(
    operations: [
        new Post(
            controller: CreateCategoryController::class,
            input: CategoryDto::class,          // DTO utilisé
            security: "is_granted('ROLE_ADMIN')",
            read: false                          // Pas besoin de lire l'entité
        ),
    ]
)]
```

## Utilisation

### Créer une catégorie

**Endpoint :** `POST /api/ecommerce/categories`

**Headers :**
```
Content-Type: application/json
Authorization: Bearer {token}
```

**Body :**
```json
{
  "name": "Vêtements",
  "slug": "vetements",
  "description": "Tous les vêtements"
}
```

**Réponse (201) :**
```json
{
  "message": "Catégorie créée avec succès",
  "id": 1,
  "category": {
    "id": 1,
    "name": "Vêtements",
    "slug": "vetements"
  }
}
```

**Erreur de validation (400) :**
```json
{
  "type": "https://tools.ietf.org/html/rfc2616#section-10",
  "title": "An error occurred",
  "detail": "name: Le nom de la catégorie est requis",
  "violations": [
    {
      "propertyPath": "name",
      "message": "Le nom de la catégorie est requis"
    }
  ]
}
```

### Créer un produit

**Endpoint :** `POST /api/ecommerce/products`

**Body :**
```json
{
  "name": "T-shirt noir",
  "slug": "t-shirt-noir",
  "description": "T-shirt en coton",
  "price": 29.99,
  "stock": 100,
  "type": "physical",
  "categoryId": 1,
  "active": true
}
```

### Créer un utilisateur

**Endpoint :** `POST /api/users/register`

**Body :**
```json
{
  "email": "user@example.com",
  "password": "Password123",
  "firstName": "Jean",
  "lastName": "Dupont"
}
```

### Créer une commande

**Endpoint :** `POST /api/orders/create`

**Body :**
```json
{
  "status": "pending",
  "shippingAddress": "123 Rue de Paris, 75001 Paris",
  "billingAddress": "123 Rue de Paris, 75001 Paris",
  "paymentMethod": "credit_card",
  "items": []
}
```

### Créer un abonnement

**Endpoint :** `POST /api/subscriptions/create`

**Body :**
```json
{
  "planId": 1,
  "status": "active",
  "autoRenew": true
}
```

## Groupes de validation

Pour appliquer différentes règles selon l'opération :

```php
#[Assert\NotBlank(groups: ['create'])]
#[Assert\Length(min: 8, groups: ['create', 'password'])]
public ?string $password = null;
```

Puis dans l'API Resource :

```php
new Post(
    validationContext: ['groups' => ['create']],
)
```

## Avantages

✅ **Validation automatique** : Les données sont validées avant d'atteindre le controller  
✅ **Séparation des préoccupations** : DTO pour validation, Entity pour persistance  
✅ **Messages d'erreur clairs** : Messages personnalisés en français  
✅ **Réutilisabilité** : Les DTOs peuvent être utilisés dans plusieurs contexts  
✅ **Sécurité** : Seules les données définies dans le DTO sont acceptées  
✅ **Documentation automatique** : API Platform génère la doc à partir des DTOs  

## Validation personnalisée

Pour des règles complexes, créer un validateur personnalisé :

```php
// src/Validator/UniqueEmailValidator.php
#[AsConstraint]
class UniqueEmail extends Constraint
{
    public string $message = 'L\'email {{ email }} est déjà utilisé';
}

// Utilisation
#[UniqueEmail]
public ?string $email = null;
```

## Tests

Exemple de test avec validation :

```php
public function testCreateCategoryWithInvalidData(): void
{
    $client = static::createClient();
    
    $client->request('POST', '/api/ecommerce/categories', [
        'json' => [
            'name' => 'A',  // Trop court
            'slug' => 'INVALID-SLUG'  // Majuscules interdites
        ]
    ]);
    
    $this->assertResponseStatusCodeSame(400);
    $this->assertJsonContains([
        '@type' => 'ConstraintViolationList'
    ]);
}
```
