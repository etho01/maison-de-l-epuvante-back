# 📄 Service de Pagination

## 🎯 Vue d'ensemble

Le `PaginationService` gère la pagination de toutes les listes d'éléments dans l'API. Il retourne une structure standardisée avec les éléments paginés et les métadonnées de pagination.

## 📦 Structure de réponse

Toutes les listes retournent un JSON avec cette structure :

```json
{
  "member": [...],
  "pagination": {
    "page": 1,
    "itemsPerPage": 30,
    "totalItems": 150,
    "totalPages": 5,
    "hasNextPage": true,
    "hasPreviousPage": false
  }
}
```

### Propriétés de `pagination`

| Propriété | Type | Description |
|-----------|------|-------------|
| `page` | `number` | Numéro de la page actuelle (commence à 1) |
| `itemsPerPage` | `number` | Nombre d'éléments par page (min: 1, max: 100) |
| `totalItems` | `number` | Nombre total d'éléments dans la collection |
| `totalPages` | `number` | Nombre total de pages |
| `hasNextPage` | `boolean` | Indique s'il existe une page suivante |
| `hasPreviousPage` | `boolean` | Indique s'il existe une page précédente |

### Propriété `member`

Le tableau `member` contient les éléments de la page actuelle. Les éléments sont sérialisés selon les groupes définis dans les entités.

## 🔧 Utilisation

### Paramètres de requête

Tous les endpoints de collection acceptent ces paramètres :

- `page` (optionnel, défaut: 1) - Numéro de la page
- `itemsPerPage` (optionnel, défaut: 30) - Nombre d'éléments par page
- `pagination` (optionnel, défaut: true) - Active/désactive la pagination

**Contraintes :**
- `page` : minimum 1
- `itemsPerPage` : minimum 1, maximum 100
- `pagination` : accepte `true`, `false`, `1`, `0`

**Important :** Si `pagination=false` ou `pagination=0`, tous les éléments sont retournés dans `member` sans pagination.

### Exemples de requêtes

#### Catégories - Page 1 (défaut)
```http
GET /api/categories
```

Réponse :
```json
{
  "member": [
    {
      "id": 1,
      "name": "Électronique",
      "slug": "electronique"
    },
    ...
  ],
  "pagination": {
    "page": 1,
    "itemsPerPage": 30,
    "totalItems": 50,
    "totalPages": 2,
    "hasNextPage": true,
    "hasPreviousPage": false
  }
}
```

#### Catégories - TOUS les éléments (pagination désactivée)
```http
GET /api/categories?pagination=false
```

Réponse :
```json
{
  "member": [
    {
      "id": 1,
      "name": "Électronique",
      "slug": "electronique"
    },
    {
      "id": 2,
      "name": "Vêtements",
      "slug": "vetements"
    },
    ... // Tous les autres éléments
  ],
  "pagination": {
    "page": 1,
    "itemsPerPage": 50,
    "totalItems": 50,
    "totalPages": 1,
    "hasNextPage": false,
    "hasPreviousPage": false
  }
}
```

#### Produits - Page 2 avec 10 éléments
```http
GET /api/products?page=2&itemsPerPage=10
```

Réponse :
```json
{
  "member": [
    {
      "id": 11,
      "name": "Produit 11",
      "price": 99.99,
      "category": {...}
    },
    ...
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

#### Commandes - Avec filtrage utilisateur
```http
GET /api/orders?page=1&itemsPerPage=20
```

**Note :** Les utilisateurs non-admin voient uniquement leurs propres commandes.

#### Tous les produits sans pagination
```http
GET /api/products?pagination=false
```

Retourne **TOUS** les produits dans le tableau `member` avec `totalPages: 1`.

## 📋 Endpoints paginés

Tous ces endpoints utilisent le service de pagination :

### E-commerce
- `GET /api/categories` - Liste des catégories
- `GET /api/products` - Liste des produits
- `GET /api/orders` - Liste des commandes (filtrées par utilisateur)

### Utilisateurs
- `GET /api/users` - Liste des utilisateurs (admin uniquement)

### Abonnements
- `GET /api/subscriptions` - Liste des abonnements (filtrés par utilisateur)
- `GET /api/subscription_plans` - Liste des plans d'abonnement

### Contenus numériques
- `GET /api/digital_contents` - Liste des contenus numériques

## 🎨 Groupes de sérialisation

Les éléments dans `member` sont sérialisés avec les groupes définis dans les entités :

### Catégories
- Groupes : `category:read`, `category:list`
- Propriétés : id, name, slug, description

### Produits
- Groupes : `product:read`, `product:list`
- Propriétés : id, name, slug, price, description, category, etc.

### Commandes
- Groupes : `order:read`, `order:list`
- Propriétés : id, user, items, status, totalPrice, etc.

### Utilisateurs
- Groupes : `user:read`, `user:list`
- Propriétés : id, email, firstName, lastName, roles

### Abonnements
- Groupes : `subscription:read`, `subscription:list`
- Propriétés : id, user, plan, status, startDate, endDate

### Plans d'abonnement
- Groupes : `subscription_plan:read`, `subscription_plan:list`
- Propriétés : id, name, price, durationInMonths

### Contenus numériques
- Groupes : `digital_content:read`, `digital_content:list`
- Propriétés : id, title, description, filePath, fileSize

## 💻 Implémentation dans un controller

### Exemple simple
```php
public function __invoke(Request $request): JsonResponse
{
    $page = max(1, (int) $request->query->get('page', 1));
    $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
    $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

    $queryBuilder = $this->categoryRepository->createQueryBuilder('c');
    $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);

    return $this->json($result, 200, [], ['groups' => ['category:read', 'category:list']]);
}
```

### Exemple avec filtrage
```php
public function __invoke(Request $request): JsonResponse
{
    $page = max(1, (int) $request->query->get('page', 1));
    $itemsPerPage = max(1, min(100, (int) $request->query->get('itemsPerPage', 30)));
    $enablePagination = filter_var($request->query->get('pagination', 'true'), FILTER_VALIDATE_BOOLEAN);

    $user = $this->security->getUser();
    
    $queryBuilder = $this->orderRepository->createQueryBuilder('o');
    
    // Filtrer par utilisateur si non-admin
    if (!$this->isGranted('ROLE_ADMIN')) {
        $queryBuilder->where('o.user = :user')
            ->setParameter('user', $user);
    }
    
    $result = $this->paginationService->paginate($queryBuilder, $page, $itemsPerPage, $enablePagination);
    
    return $this->json($result, 200, [], ['groups' => ['order:read', 'order:list']]);
}
```

## 🔍 Méthodes du service

### `paginate(QueryBuilder $queryBuilder, int $page, int $itemsPerPage, bool $enablePagination): array`

Pagine une requête Doctrine QueryBuilder.

**Paramètres :**
- `$queryBuilder` : Le QueryBuilder à paginer
- `$page` : Numéro de page (min: 1)
- `$itemsPerPage` : Éléments par page (min: 1, max: 100)
- `$enablePagination` : Active/désactive la pagination (défaut: true)

**Retour :**
```php
[
    'member' => [...],
    'pagination' => [
        'page' => int,
        'itemsPerPage' => int,
        'totalItems' => int,
        'totalPages' => int,
        'hasNextPage' => bool,
        'hasPreviousPage' => bool,
    ]
]
```

**Note :** Si `$enablePagination = false`, tous les éléments sont retournés et `totalPages` sera toujours `1`.

### `paginateArray(array $items, int $page, int $itemsPerPage, bool $enablePagination): array`

Pagine un tableau d'éléments.

**Paramètres :**
- `$items` : Le tableau d'éléments à paginer
- `$page` : Numéro de page (min: 1)
- `$itemsPerPage` : Éléments par page (min: 1, max: 100)
- `$enablePagination` : Active/désactive la pagination (défaut: true)

**Retour :** Même structure que `paginate()`

## ✨ Avantages

1. **Structure standardisée** - Toutes les listes ont le même format
2. **Métadonnées riches** - Informations complètes sur la pagination
3. **Performance** - Utilise Doctrine Paginator pour optimiser les requêtes
4. **Flexibilité** - Paramètres configurables par requête
5. **Sécurité** - Limites min/max pour éviter les abus
6. **Compatibilité** - Fonctionne avec QueryBuilder ou tableaux
7. **Option désactivation** - Possibilité de récupérer tous les éléments avec `pagination=false`

## 🚀 Frontend - Exemples d'utilisation

### JavaScript/TypeScript
```typescript
interface PaginatedResponse<T> {
  member: T[];
  pagination: {
    page: number;
    itemsPerPage: number;
    totalItems: number;
    totalPages: number;
    hasNextPage: boolean;
    hasPreviousPage: boolean;
  };
}

async function fetchProducts(page: number = 1, itemsPerPage: number = 30, enablePagination: boolean = true) {
  let url = `/api/products?page=${page}&itemsPerPage=${itemsPerPage}`;
  
  if (!enablePagination) {
    url = `/api/products?pagination=false`;
  }
  
  const response = await fetch(url);
  const data: PaginatedResponse<Product> = await response.json();
  
  console.log(`Page ${data.pagination.page} sur ${data.pagination.totalPages}`);
  console.log(`${data.member.length} produits sur ${data.pagination.totalItems} au total`);
  
  return data;
}

// Récupérer tous les produits sans pagination
const allProducts = await fetchProducts(1, 30, false);
console.log(`Total: ${allProducts.member.length} produits`);
```

### React Component
```tsx
const ProductList = () => {
  const [data, setData] = useState<PaginatedResponse<Product> | null>(null);
  const [page, setPage] = useState(1);

  useEffect(() => {
    fetchProducts(page, 20).then(setData);
  }, [page]);

  if (!data) return <div>Chargement...</div>;

  return (
    <div>
      <ul>
        {data.member.map(product => (
          <li key={product.id}>{product.name}</li>
        ))}
      </ul>
      
      <div>
        <button 
          disabled={!data.pagination.hasPreviousPage}
          onClick={() => setPage(page - 1)}
        >
          Précédent
        </button>
        
        <span>Page {data.pagination.page} / {data.pagination.totalPages}</span>
        
        <button 
          disabled={!data.pagination.hasNextPage}
          onClick={() => setPage(page + 1)}
        >
          Suivant
        </button>
      </div>
    </div>
  );
};
```
