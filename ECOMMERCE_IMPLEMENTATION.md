# Architecture E-Commerce - Implémentation

Documentation technique de l'implémentation complète du système e-commerce.

---

## ✅ Structure Créée

### 📦 Entités

#### 1. **Category** (Catégories)
- Hiérarchique (parent/enfant)
- Slug unique pour SEO
- Relations: `Product[]`

#### 2. **Product** (Produits)
- Types: physical, digital, subscription
- Gestion du stock
- Images multiples (JSON)
- Exclusivité web (flag)
- Métadonnées extensibles
- Relations: `Category`, `OrderItem[]`, `CartItem[]`, `DigitalContent`

#### 3. **Cart** (Panier)
- Un panier par utilisateur (OneToOne)
- Méthode `getTotal()` calculée
- Relations: `User`, `CartItem[]`

#### 4. **CartItem** (Articles du panier)
- Quantité par produit
- Relations: `Cart`, `Product`

#### 5. **Order** (Commandes)
- Numéro de commande unique auto-généré
- Statuts: pending, processing, paid, shipped, delivered, cancelled, refunded
- Adresses de livraison et facturation (JSON)
- Tracking des dates (paiement, expédition, livraison)
- Notes client et admin
- Relations: `User`, `OrderItem[]`

#### 6. **OrderItem** (Lignes de commande)
- Snapshot du produit (nom, SKU, prix) au moment de l'achat
- Prix unitaire et total
- Relations: `Order`, `Product`

#### 7. **DigitalContent** (Contenu numérique)
- Types: fanzine, ebook, video, audio, other
- Stockage fichier (filePath)
- Métadonnées: issueNumber, pageCount
- Flag `requiresSubscription`
- Relation OneToOne avec `Product`

#### 8. **SubscriptionPlan** (Plans d'abonnement)
- Intervalles: monthly, quarterly, yearly
- Formats: paper, digital, both
- Durée en mois
- Flag actif/inactif
- Relations: `Subscription[]`

#### 9. **Subscription** (Abonnements utilisateur)
- Statuts: pending, active, cancelled, expired
- Dates de début/fin
- Auto-renouvellement
- Méthode `isActive()` calculée
- Relations: `User`, `SubscriptionPlan`

---

## 📂 Repositories Créés

Tous les repositories avec méthodes de recherche personnalisées:

- `CategoryRepository`
- `ProductRepository` - `findActiveProducts()`, `findByCategory()`
- `CartRepository`
- `CartItemRepository`
- `OrderRepository` - `findByUser()`
- `OrderItemRepository`
- `DigitalContentRepository`
- `SubscriptionPlanRepository` - `findActivePlans()`
- `SubscriptionRepository` - `findActiveByUser()`

---

## 🌐 API Resources & Routes

### Routes Publiques
```
GET  /api/categories
GET  /api/categories/{id}
GET  /api/products (filtres: name, type, category.id, price, active, exclusiveOnline)
GET  /api/products/{id}
GET  /api/subscription-plans
GET  /api/subscription-plans/{id}
```

### Routes Utilisateur (ROLE_USER)
```
# Panier
GET    /api/cart/me
POST   /api/cart/items
PATCH  /api/cart/items/{itemId}
DELETE /api/cart/items/{itemId}
DELETE /api/cart/clear

# Commandes
GET  /api/orders
GET  /api/orders/{id}
POST /api/orders/checkout

# Contenu numérique
GET /api/digital-contents
GET /api/digital-contents/{id}
GET /api/digital-contents/{id}/download

# Abonnements
GET   /api/subscriptions
GET   /api/subscriptions/{id}
POST  /api/subscriptions/subscribe
PATCH /api/subscriptions/{id}/cancel
PATCH /api/subscriptions/{id}/renew
```

### Routes Admin (ROLE_ADMIN)
```
POST   /api/categories
PUT    /api/categories/{id}
PATCH  /api/categories/{id}
DELETE /api/categories/{id}

POST   /api/products
PUT    /api/products/{id}
PATCH  /api/products/{id}
DELETE /api/products/{id}

PATCH /api/orders/{id}

POST   /api/subscription-plans
PUT    /api/subscription-plans/{id}
PATCH  /api/subscription-plans/{id}
DELETE /api/subscription-plans/{id}
```

---

## 🔐 Sécurité Implémentée

### Contrôle d'Accès

#### Produits & Catégories
- Lecture: Public
- Écriture: ROLE_ADMIN uniquement

#### Panier
- Toutes opérations: ROLE_USER
- Accès uniquement au panier personnel

#### Commandes
- Lecture: Propriétaire ou ADMIN
- Création: ROLE_USER
- Modification: ROLE_ADMIN uniquement

#### Abonnements
- Lecture: Propriétaire ou ADMIN
- Souscription/Annulation: Propriétaire uniquement
- Plans: Lecture publique, gestion ADMIN

#### Contenu Numérique
- Accès: ROLE_USER avec vérification d'achat/abonnement
- Téléchargement: Authentification requise

---

## 🗄️ Base de Données

### Migration Créée: `Version20260126000001.php`

**Tables créées:**
- `categories` (avec auto-référence parent_id)
- `products`
- `carts`
- `cart_items`
- `orders`
- `order_items`
- `digital_contents`
- `subscription_plans`
- `subscriptions`

**Contraintes:**
- Clés étrangères avec CASCADE approprié
- Index sur les colonnes fréquemment recherchées
- Contraintes UNIQUE sur slugs et order_number

**Relations User:**
- `User` ↔ `Cart` (OneToOne)
- `User` → `Order[]` (OneToMany)
- `User` → `Subscription[]` (OneToMany)

---

## 🎨 Filtres API Platform

### Product
- **SearchFilter**: name (partiel), type (exact), category.id (exact)
- **RangeFilter**: price
- **BooleanFilter**: active, exclusiveOnline

Exemples:
```
/api/products?name=figurine
/api/products?type=digital
/api/products?price[gte]=10&price[lte]=50
/api/products?active=true
```

---

## 📋 Groupes de Sérialisation

Chaque ressource utilise des groupes de normalisation/dénormalisation:

- `category:read`, `category:write`, `category:list`, `category:detail`
- `product:read`, `product:write`, `product:list`, `product:detail`
- `cart:read`, `cart:write`, `cart:detail`
- `order:read`, `order:create`, `order:update`, `order:list`, `order:detail`
- `digital_content:read`, `digital_content:list`, `digital_content:detail`
- `subscription_plan:read`, `subscription_plan:write`, `subscription_plan:list`, `subscription_plan:detail`
- `subscription:read`, `subscription:create`, `subscription:renew`, `subscription:list`, `subscription:detail`

---

## 🚀 Prochaines Étapes

### 1. Contrôleurs Custom à Implémenter

#### CartController
- Logique d'ajout intelligent (merge si produit existe)
- Vérification de stock
- Calcul du total

#### OrderController
- Process de checkout complet
- Création des OrderItems depuis le Cart
- Vidage du panier après commande
- Calcul des frais de port et taxes
- Intégration paiement

#### DigitalContentController
- Vérification des droits d'accès
- Streaming/téléchargement sécurisé
- Watermarking optionnel

#### SubscriptionController
- Vérification des doublons
- Calcul des dates de fin
- Gestion du renouvellement automatique

#### RecommendationController
- Algorithme de recommandation basé sur:
  - Historique d'achats
  - Produits consultés
  - Catégories favorites
  - Produits similaires

### 2. Services à Créer

- **PaymentService** - Intégration Stripe/PayPal
- **StockService** - Gestion du stock et réservations
- **EmailService** - Notifications commandes, abonnements
- **RecommendationEngine** - ML pour recommandations
- **DigitalLibraryService** - Accès aux contenus numériques

### 3. Event Listeners/Subscribers

- **OrderSubscriber** - Envoi emails, mise à jour stock
- **SubscriptionSubscriber** - Notifications renouvellement
- **CartSubscriber** - Nettoyage paniers abandonnés
- **ProductSubscriber** - Génération auto du slug

### 4. Validations Business

- Stock suffisant lors de l'ajout au panier
- Validation adresses lors du checkout
- Vérification abonnement avant téléchargement
- Prix minimum commande
- Limite quantité par produit

### 5. Tests

- Tests unitaires pour chaque entité
- Tests fonctionnels pour les routes API
- Tests d'intégration pour le flux de commande complet

---

## 📊 Statistiques du Code

- **Entités:** 9 (+ User modifié)
- **Repositories:** 9
- **API Resources:** 7
- **Routes publiques:** 6
- **Routes authentifiées:** 20+
- **Fichiers créés:** 28
- **Lignes de code:** ~2000+

---

## 📚 Documentation

- [ECOMMERCE_ROUTES.md](ECOMMERCE_ROUTES.md) - Documentation complète des routes API
- [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md) - Contexte et objectifs du projet

---

*Implémentation réalisée le 26 janvier 2026*
