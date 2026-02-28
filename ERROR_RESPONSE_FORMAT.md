# Format des Réponses d'Erreur API

## Structure Standard

Toutes les réponses d'erreur de l'API suivent ce format :

```json
{
  "code": 404,
  "errors": ["PRODUCT_NOT_FOUND"]
}
```

## Mode Développement

En mode développement (`APP_ENV=dev`), les réponses d'erreur incluent des informations de debug :

```json
{
  "code": 500,
  "errors": ["INTERNAL_SERVER_ERROR"],
  "debug": {
    "message": "Call to undefined method App\\Entity\\Product::getNonExistentMethod()",
    "file": "/app/src/Controller/Product/GetProductController.php",
    "line": 42,
    "trace": [
      {
        "file": "/app/src/Controller/Product/GetProductController.php",
        "line": 42,
        "function": "getNonExistentMethod",
        "class": "App\\Entity\\Product"
      },
      {
        "file": "/app/vendor/symfony/http-kernel/HttpKernel.php",
        "line": 163,
        "function": "__invoke",
        "class": "App\\Controller\\Product\\GetProductController"
      }
      // ... jusqu'à 10 frames de stack trace
    ]
  }
}
```

## Mode Production

En mode production (`APP_ENV=prod`), seules les erreurs 500 incluent le message d'erreur (sans traceback) :

```json
{
  "code": 500,
  "errors": ["INTERNAL_SERVER_ERROR"],
  "debug": {
    "message": "An error occurred"
  }
}
```

Pour les autres codes d'erreur (400, 404, etc.), le format reste simple :

```json
{
  "code": 404,
  "errors": ["PRODUCT_NOT_FOUND"]
}
```

## Erreurs avec Données Supplémentaires

Certaines erreurs peuvent inclure des données contextuelles :

```json
{
  "code": 409,
  "errors": ["CATEGORY_HAS_PRODUCTS"],
  "data": {
    "productsCount": 5
  }
}
```

## Liste des Codes d'Erreur

Voir le fichier `src/Enum/ApiError.php` pour la liste complète des codes d'erreur disponibles.

### Erreurs d'Authentification (4xx)
- `USER_NOT_AUTHENTICATED` - L'utilisateur n'est pas authentifié
- `ACCESS_DENIED` - Accès refusé
- `INVALID_CREDENTIALS` - Identifiants invalides
- `EMAIL_NOT_VERIFIED` - Email non vérifié

### Erreurs de Validation (400)
- `INVALID_DATA` - Données invalides
- `VALIDATION_FAILED` - Échec de validation
- `MISSING_PARAMETERS` - Paramètres manquants

### Erreurs de Resources (404)
- `USER_NOT_FOUND` - Utilisateur non trouvé
- `PRODUCT_NOT_FOUND` - Produit non trouvé
- `CATEGORY_NOT_FOUND` - Catégorie non trouvée
- `ORDER_NOT_FOUND` - Commande non trouvée

### Erreurs de Conflit (409)
- `CATEGORY_HAS_PRODUCTS` - Impossible de supprimer, la catégorie contient des produits
- `CATEGORY_HAS_CHILDREN` - Impossible de supprimer, la catégorie a des sous-catégories
- `PRODUCT_HAS_ORDERS` - Impossible de supprimer, le produit est dans des commandes
- `USER_HAS_ORDERS` - Impossible de supprimer, l'utilisateur a des commandes
- `USER_HAS_SUBSCRIPTIONS` - Impossible de supprimer, l'utilisateur a des abonnements
- `SUBSCRIPTION_PLAN_HAS_SUBSCRIPTIONS` - Impossible de supprimer, le plan a des abonnements actifs

### Erreurs Serveur (500)
- `INTERNAL_SERVER_ERROR` - Erreur interne du serveur
- `PAYMENT_ERROR` - Erreur de paiement
