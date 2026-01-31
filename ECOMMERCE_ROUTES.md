# Routes E-Commerce - La Petite Maison de l'Épouvante

Documentation complète des routes API pour le système e-commerce.

---

## 🏷️ Catégories

### Lister toutes les catégories
```
GET /api/categories
```
- **Accès:** Public
- **Pagination:** Oui
- **Filtres:** Aucun

### Obtenir une catégorie
```
GET /api/categories/{id}
```
- **Accès:** Public

### Créer une catégorie
```
POST /api/categories
```
- **Accès:** ROLE_ADMIN
- **Payload:**
```json
{
  "name": "Figurines",
  "description": "Figurines de collection",
  "slug": "figurines",
  "parent": "/api/categories/1"
}
```

### Modifier une catégorie
```
PUT /api/categories/{id}
PATCH /api/categories/{id}
```
- **Accès:** ROLE_ADMIN

### Supprimer une catégorie
```
DELETE /api/categories/{id}
```
- **Accès:** ROLE_ADMIN

---

## 🛍️ Produits

### Lister tous les produits
```
GET /api/products
```
- **Accès:** Public
- **Pagination:** Oui
- **Filtres:**
  - `name` (partiel)
  - `type` (exact: physical, digital, subscription)
  - `category.id` (exact)
  - `price[gte]`, `price[lte]` (range)
  - `active` (boolean)
  - `exclusiveOnline` (boolean)

**Exemples:**
```
GET /api/products?type=physical
GET /api/products?category.id=5
GET /api/products?price[gte]=10&price[lte]=50
GET /api/products?active=true&exclusiveOnline=true
```

### Obtenir un produit
```
GET /api/products/{id}
```
- **Accès:** Public

### Créer un produit
```
POST /api/products
```
- **Accès:** ROLE_ADMIN
- **Payload:**
```json
{
  "name": "Figurine Evil Ed",
  "description": "Figurine exclusive...",
  "slug": "figurine-evil-ed",
  "price": "29.99",
  "stock": 100,
  "type": "physical",
  "sku": "FIG-EE-001",
  "category": "/api/categories/1",
  "active": true,
  "exclusiveOnline": true,
  "images": ["url1.jpg", "url2.jpg"],
  "weight": "0.5",
  "metadata": {}
}
```

### Modifier un produit
```
PUT /api/products/{id}
PATCH /api/products/{id}
```
- **Accès:** ROLE_ADMIN

### Supprimer un produit
```
DELETE /api/products/{id}
```
- **Accès:** ROLE_ADMIN

---

## 📦 Commandes

### Lister mes commandes
```
GET /api/orders
```
- **Accès:** ROLE_USER
- **Pagination:** Oui
- **Retourne:** Commandes de l'utilisateur connecté (ou toutes pour ADMIN)

### Obtenir une commande
```
GET /api/orders/{id}
```
- **Accès:** ROLE_USER (propriétaire) ou ROLE_ADMIN

### Passer commande (checkout)
```
POST /api/orders/checkout
```
- **Accès:** ROLE_USER
- **Payload:**
```json
{
  "shippingAddress": {
    "firstName": "John",
    "lastName": "Doe",
    "address": "123 rue Example",
    "city": "Paris",
    "postalCode": "75001",
    "country": "FR"
  },
  "billingAddress": {
    "firstName": "John",
    "lastName": "Doe",
    "address": "123 rue Example",
    "city": "Paris",
    "postalCode": "75001",
    "country": "FR"
  },
  "paymentMethod": "card",
  "customerNotes": "Livraison après 18h svp"
}
```
- **Retourne:** Commande créée avec orderNumber

### Mettre à jour une commande (admin)
```
PATCH /api/orders/{id}
```
- **Accès:** ROLE_ADMIN
- **Payload:**
```json
{
  "status": "shipped",
  "adminNotes": "Envoyé par Colissimo"
}
```

**Statuts disponibles:**
- `pending` - En attente
- `processing` - En cours de traitement
- `paid` - Payée
- `shipped` - Expédiée
- `delivered` - Livrée
- `cancelled` - Annulée
- `refunded` - Remboursée

---

## 📚 Contenu Numérique (Fanzines)

### Lister les contenus numériques
```
GET /api/digital-contents
```
- **Accès:** ROLE_USER
- **Pagination:** Oui
- **Retourne:** Contenus accessibles par l'utilisateur

### Obtenir un contenu numérique
```
GET /api/digital-contents/{id}
```
- **Accès:** ROLE_USER

### Télécharger un contenu numérique
```
GET /api/digital-contents/{id}/download
```
- **Accès:** ROLE_USER
- **Vérifications:**
  - Utilisateur a acheté le contenu OU
  - Utilisateur a un abonnement actif (si requiresSubscription = true)
- **Retourne:** Fichier à télécharger

---

## 💳 Plans d'Abonnement

### Lister les plans d'abonnement
```
GET /api/subscription-plans
```
- **Accès:** Public
- **Pagination:** Non
- **Retourne:** Plans actifs disponibles

### Obtenir un plan
```
GET /api/subscription-plans/{id}
```
- **Accès:** Public

### Créer un plan (admin)
```
POST /api/subscription-plans
```
- **Accès:** ROLE_ADMIN
- **Payload:**
```json
{
  "name": "Abonnement Annuel Digital",
  "description": "Accès à tous les numéros digitaux",
  "price": "49.99",
  "billingInterval": "yearly",
  "durationInMonths": 12,
  "format": "digital",
  "active": true
}
```

**Formats disponibles:**
- `paper` - Papier uniquement
- `digital` - Numérique uniquement
- `both` - Papier + Numérique

**Intervalles de facturation:**
- `monthly` - Mensuel
- `quarterly` - Trimestriel
- `yearly` - Annuel

### Modifier un plan (admin)
```
PUT /api/subscription-plans/{id}
PATCH /api/subscription-plans/{id}
```
- **Accès:** ROLE_ADMIN

### Supprimer un plan (admin)
```
DELETE /api/subscription-plans/{id}
```
- **Accès:** ROLE_ADMIN

---

## 🎫 Abonnements

### Lister mes abonnements
```
GET /api/subscriptions
```
- **Accès:** ROLE_USER
- **Pagination:** Oui
- **Retourne:** Abonnements de l'utilisateur (ou tous pour ADMIN)

### Obtenir un abonnement
```
GET /api/subscriptions/{id}
```
- **Accès:** ROLE_USER (propriétaire) ou ROLE_ADMIN

### S'abonner
```
POST /api/subscriptions/subscribe
```
- **Accès:** ROLE_USER
- **Payload:**
```json
{
  "plan": "/api/subscription-plans/2",
  "paymentMethod": "card",
  "autoRenew": true
}
```
- **Retourne:** Abonnement créé

### Annuler un abonnement
```
PATCH /api/subscriptions/{id}/cancel
```
- **Accès:** ROLE_USER (propriétaire)
- **Effet:** Met le statut à `cancelled`, désactive `autoRenew`

### Renouveler un abonnement
```
PATCH /api/subscriptions/{id}/renew
```
- **Accès:** ROLE_USER (propriétaire)
- **Payload:**
```json
{
  "autoRenew": true
}
```

**Statuts d'abonnement:**
- `pending` - En attente de paiement
- `active` - Actif
- `cancelled` - Annulé
- `expired` - Expiré

---

## 🔐 Sécurité & Autorisations

### Niveaux d'accès

- **Public:** Accessible sans authentification
  - Liste des produits
  - Liste des catégories
  - Liste des plans d'abonnement

- **ROLE_USER:** Utilisateur authentifié
  - Gestion du panier
  - Passage de commande
  - Consultation des commandes personnelles
  - Gestion des abonnements personnels
  - Accès aux contenus numériques achetés

- **ROLE_ADMIN:** Administrateur
  - Gestion complète des produits
  - Gestion des catégories
  - Gestion des plans d'abonnement
  - Modification des statuts de commande
  - Accès à toutes les commandes et abonnements

### Headers requis

Pour les routes protégées:
```
Authorization: Bearer {jwt_token}
```

## 🛒 Panier

### Obtenir mon panier
```
GET /api/cart/me
```
- **Accès:** ROLE_USER
- **Retourne:** Panier de l'utilisateur connecté avec tous les items

### Ajouter un article au panier
```
POST /api/cart/items
```
- **Accès:** ROLE_USER
- **Payload:**
```json
{
  "product": "/api/products/5",
  "quantity": 2
}
```

### Modifier la quantité d'un article
```
PATCH /api/cart/items/{itemId}
```
- **Accès:** ROLE_USER
- **Payload:**
```json
{
  "quantity": 3
}
```

### Retirer un article du panier
```
DELETE /api/cart/items/{itemId}
```
- **Accès:** ROLE_USER

### Vider le panier
```
DELETE /api/cart/clear
```
- **Accès:** ROLE_USER


---

## 📊 Codes de Réponse HTTP

- `200` - Succès
- `201` - Ressource créée
- `204` - Succès sans contenu (DELETE)
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Non autorisé
- `404` - Ressource non trouvée
- `422` - Validation échouée
- `500` - Erreur serveur

---

## 🎯 Prochaines étapes

Routes à implémenter avec contrôleurs personnalisés:

1. **Recommandations**
   - `GET /api/recommendations` - Produits recommandés
   - `GET /api/recommendations/similar/{productId}` - Produits similaires

2. **Paiement**
   - `POST /api/payments/intent` - Créer une intention de paiement
   - `POST /api/payments/confirm` - Confirmer un paiement

3. **Statistiques utilisateur**
   - `GET /api/users/me/stats` - Statistiques d'achat
   - `GET /api/users/me/library` - Bibliothèque numérique

---

*Document généré le 26 janvier 2026*
