# 📊 Exemple de réponse avec pagination

## Structure JSON retournée

Voici un exemple concret de la structure JSON retournée par les endpoints de collection avec le service de pagination :

### Exemple 1 : Liste de catégories (page 1, 5 éléments)

**Requête :**
```http
GET /api/categories?page=1&itemsPerPage=5
```

**Réponse :**
```json
{
  "member": [
    {
      "id": 1,
      "name": "Électronique",
      "slug": "electronique",
      "description": "Produits électroniques et high-tech"
    },
    {
      "id": 2,
      "name": "Vêtements",
      "slug": "vetements",
      "description": "Mode et accessoires"
    },
    {
      "id": 3,
      "name": "Maison & Jardin",
      "slug": "maison-jardin",
      "description": "Tout pour la maison et le jardin"
    },
    {
      "id": 4,
      "name": "Sports & Loisirs",
      "slug": "sports-loisirs",
      "description": "Équipements sportifs et loisirs"
    },
    {
      "id": 5,
      "name": "Livres",
      "slug": "livres",
      "description": "Livres et magazines"
    }
  ],
  "pagination": {
    "page": 1,
    "itemsPerPage": 5,
    "totalItems": 12,
    "totalPages": 3,
    "hasNextPage": true,
    "hasPreviousPage": false
  }
}
```

### Exemple 2 : Liste de produits (page 2, 10 éléments)

**Requête :**
```http
GET /api/products?page=2&itemsPerPage=10
```

**Réponse :**
```json
{
  "member": [
    {
      "id": 11,
      "name": "Smartphone XYZ",
      "slug": "smartphone-xyz",
      "price": 599.99,
      "description": "Dernier modèle avec écran OLED",
      "stock": 50,
      "category": {
        "id": 1,
        "name": "Électronique",
        "slug": "electronique"
      }
    },
    {
      "id": 12,
      "name": "Laptop Pro 2024",
      "slug": "laptop-pro-2024",
      "price": 1299.99,
      "description": "Ordinateur portable haute performance",
      "stock": 25,
      "category": {
        "id": 1,
        "name": "Électronique",
        "slug": "electronique"
      }
    }
    // ... 8 autres produits
  ],
  "pagination": {
    "page": 2,
    "itemsPerPage": 10,
    "totalItems": 45,
    "totalPages": 5,
    "hasNextPage": true,
    "hasPreviousPage": true
  }
}
```

### Exemple 3 : Liste vide (aucun résultat)

**Requête :**
```http
GET /api/products?page=100&itemsPerPage=10
```

**Réponse :**
```json
{
  "member": [],
  "pagination": {
    "page": 100,
    "itemsPerPage": 10,
    "totalItems": 45,
    "totalPages": 5,
    "hasNextPage": false,
    "hasPreviousPage": true
  }
}
```

### Exemple 4 : Commandes filtrées par utilisateur

**Requête :**
```http
GET /api/orders?page=1&itemsPerPage=10
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Réponse (utilisateur normal) :**
```json
{
  "member": [
    {
      "id": 42,
      "user": {
        "id": 5,
        "email": "user@example.com",
        "firstName": "John",
        "lastName": "Doe"
      },
      "status": "completed",
      "totalPrice": 199.98,
      "createdAt": "2026-02-01T10:30:00+00:00",
      "items": [
        {
          "product": {
            "id": 10,
            "name": "Produit A",
            "price": 99.99
          },
          "quantity": 2,
          "price": 99.99
        }
      ]
    }
  ],
  "pagination": {
    "page": 1,
    "itemsPerPage": 10,
    "totalItems": 3,
    "totalPages": 1,
    "hasNextPage": false,
    "hasPreviousPage": false
  }
}
```

**Réponse (admin) :**
L'admin voit TOUTES les commandes de tous les utilisateurs avec la même structure.

### Exemple 5 : Plans d'abonnement

**Requête :**
```http
GET /api/subscription_plans
```

**Réponse :**
```json
{
  "member": [
    {
      "id": 1,
      "name": "Basic",
      "price": 9.99,
      "durationInMonths": 1,
      "features": [
        "Accès au contenu de base",
        "Support par email"
      ],
      "active": true
    },
    {
      "id": 2,
      "name": "Premium",
      "price": 19.99,
      "durationInMonths": 1,
      "features": [
        "Accès au contenu premium",
        "Support prioritaire",
        "Téléchargements illimités"
      ],
      "active": true
    },
    {
      "id": 3,
      "name": "Enterprise",
      "price": 99.99,
      "durationInMonths": 12,
      "features": [
        "Accès complet",
        "Support 24/7",
        "API access",
        "Custom features"
      ],
      "active": true
    }
  ],
  "pagination": {
    "page": 1,
    "itemsPerPage": 30,
    "totalItems": 3,
    "totalPages": 1,
    "hasNextPage": false,
    "hasPreviousPage": false
  }
}
```

## 📝 Notes importantes

### Navigation entre pages

Pour naviguer entre les pages, utilisez les propriétés de pagination :

```typescript
// Page suivante
if (response.pagination.hasNextPage) {
  const nextPage = response.pagination.page + 1;
  fetch(`/api/products?page=${nextPage}&itemsPerPage=${response.pagination.itemsPerPage}`);
}

// Page précédente
if (response.pagination.hasPreviousPage) {
  const previousPage = response.pagination.page - 1;
  fetch(`/api/products?page=${previousPage}&itemsPerPage=${response.pagination.itemsPerPage}`);
}

// Dernière page
const lastPage = response.pagination.totalPages;
fetch(`/api/products?page=${lastPage}&itemsPerPage=${response.pagination.itemsPerPage}`);
```

### Calcul des indices

Pour afficher "Affichage de X à Y sur Z éléments" :

```typescript
const startIndex = (pagination.page - 1) * pagination.itemsPerPage + 1;
const endIndex = Math.min(
  pagination.page * pagination.itemsPerPage,
  pagination.totalItems
);

console.log(`Affichage de ${startIndex} à ${endIndex} sur ${pagination.totalItems} éléments`);
// Exemple : "Affichage de 11 à 20 sur 45 éléments"
```

### Groupes de sérialisation utilisés

Chaque endpoint utilise des groupes spécifiques pour contrôler les données retournées :

- **Catégories** : `category:read`, `category:list`
- **Produits** : `product:read`, `product:list`
- **Commandes** : `order:read`, `order:list`
- **Utilisateurs** : `user:read`, `user:list`
- **Abonnements** : `subscription:read`, `subscription:list`
- **Plans** : `subscription_plan:read`, `subscription_plan:list`
- **Contenus** : `digital_content:read`, `digital_content:list`

Ces groupes sont définis dans les entités correspondantes avec l'attribut `#[Groups]`.
