# Architecture DTO (Data Transfer Object)

## 📐 Principe

L'architecture DTO sépare complètement :
- **La représentation API** (ce que l'API expose)
- **La persistance en base de données** (entité Doctrine)

Cette séparation apporte plusieurs avantages :

### ✅ Avantages

1. **Séparation des préoccupations**
   - L'API ne dépend pas de la structure de la base de données
   - Vous pouvez changer la BDD sans toucher à l'API

2. **Sécurité**
   - Le mot de passe hashé n'est jamais exposé dans le DTO
   - Contrôle fin sur ce qui est exposé/accepté

3. **Flexibilité**
   - Transformer les données entre l'API et la BDD
   - Agréger plusieurs entités dans un seul DTO
   - Calculer des champs dérivés

4. **Testabilité**
   - Tester la logique métier indépendamment de la persistance
   - DTOs simples à mocker

## 🏗️ Structure

```
src/
├── ApiResource/          # Ressources API (DTOs)
│   └── User.php         # DTO exposé par l'API
│
├── Entity/              # Entités Doctrine (BDD)
│   └── User.php        # Entité de base de données
│
└── State/               # Logique de transformation
    ├── UserProvider.php        # Entity → DTO (lecture)
    └── UserStateProcessor.php  # DTO → Entity (écriture)
```

## 🔄 Flux de données

### Lecture (GET)

```
Client
  ↓
  GET /api/users/1
  ↓
API Platform
  ↓
UserProvider
  ↓
Repository → Entity (User)
  ↓
entityToDto() → DTO (User)
  ↓
Serializer
  ↓
JSON Response
```

### Création/Modification (POST/PUT/PATCH)

```
Client
  ↓
  POST /api/users
  {email, password, ...}
  ↓
API Platform
  ↓
Deserializer → DTO (User)
  ↓
Validation
  ↓
UserStateProcessor
  ↓
dtoToEntity() → Entity (User)
  ↓
Password Hashing
  ↓
EntityManager → Database
  ↓
entityToDto() → DTO (User)
  ↓
JSON Response
```

## 📁 Fichiers détaillés

### 1. `ApiResource/User.php` (DTO)

**Rôle :** Représentation de l'utilisateur dans l'API

```php
#[ApiResource(
    provider: UserProvider::class,
    processor: UserStateProcessor::class,
)]
class User
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $plainPassword = null;  // Input uniquement
    // ...
}
```

**Caractéristiques :**
- Propriétés publiques (simple DTO)
- Annotations de validation
- Groupes de sérialisation
- Pas d'annotations Doctrine

### 2. `Entity/User.php` (Entité Doctrine)

**Rôle :** Persistance en base de données

```php
#[ORM\Entity]
class User implements UserInterface
{
    #[ORM\Column]
    private ?string $password = null;  // Hashé
    
    private ?string $plainPassword = null;  // Temporaire
    // ...
}
```

**Caractéristiques :**
- Propriétés privées
- Annotations Doctrine (ORM\Column, etc.)
- Pas d'annotations API Platform
- Implémente UserInterface pour Symfony Security

### 3. `State/UserProvider.php`

**Rôle :** Transformer Entity → DTO pour les lectures

```php
public function provide(Operation $operation, ...): object|array|null
{
    $entity = $this->userRepository->find($id);
    return $this->entityToDto($entity);
}
```

**Responsabilités :**
- Récupérer les entités depuis la BDD
- Les transformer en DTOs
- Gérer les collections et les items individuels

### 4. `State/UserStateProcessor.php`

**Rôle :** Transformer DTO → Entity pour les écritures

```php
public function process(mixed $data, Operation $operation, ...): ?UserDto
{
    $entity = $this->dtoToEntity($data);
    $this->hashPassword($entity);
    $this->entityManager->persist($entity);
    return $this->entityToDto($entity);
}
```

**Responsabilités :**
- Transformer le DTO en entité
- Appliquer la logique métier (hash password)
- Persister en base
- Retourner le DTO résultat

## 🔒 Sécurité du mot de passe

### Flux du mot de passe

1. **Client envoie** : `plainPassword` (clair)
2. **DTO reçoit** : `plainPassword` via groupe `user:write`
3. **Processor** :
   - Hashe le `plainPassword`
   - Stocke dans `Entity->password`
   - Efface le `plainPassword`
4. **Database stocke** : `password` (hashé)
5. **Response retourne** : Rien (password exclus du groupe `user:read`)

### Pourquoi 2 champs ?

- **`plainPassword`** : Temporaire, jamais en BDD, pour recevoir le mot de passe
- **`password`** : Hashé, en BDD, jamais exposé par l'API

## 🎯 Cas d'usage

### Exemple 1 : Ajouter un champ calculé

```php
// Dans UserProvider::entityToDto()
$dto->fullName = $entity->getFirstName() . ' ' . $entity->getLastName();
```

Le champ `fullName` est calculé à la volée, pas stocké en BDD.

### Exemple 2 : Transformer un format

```php
// Dans UserStateProcessor::dtoToEntity()
if ($dto->phoneNumber) {
    // Normaliser le format du téléphone
    $entity->setPhoneNumber($this->phoneFormatter->format($dto->phoneNumber));
}
```

### Exemple 3 : Agréger plusieurs entités

```php
class UserDto
{
    public ?int $id;
    public ?string $email;
    public array $orders;  // Liste des commandes
}

// Dans le Provider
$dto->orders = array_map(
    fn($order) => $this->orderToDto($order),
    $entity->getOrders()->toArray()
);
```

## 📊 Comparaison avec l'approche classique

| Aspect | Approche Classique | Approche DTO |
|--------|-------------------|--------------|
| **Fichiers** | 1 fichier (Entity + API) | 3 fichiers (DTO + Entity + State) |
| **Complexité** | ⭐ Simple | ⭐⭐⭐ Plus complexe |
| **Séparation** | ❌ Couplage | ✅ Découplage total |
| **Transformation** | ❌ Limitée | ✅ Totale liberté |
| **Maintenance** | ⭐⭐ Facile pour petit projet | ⭐⭐⭐ Meilleure pour gros projet |
| **Tests** | ⭐⭐ Moyens | ⭐⭐⭐ Excellents |

## 🚀 Bonnes pratiques

### 1. Nommage cohérent
- DTO : `App\ApiResource\User`
- Entity : `App\Entity\User`
- Provider : `App\State\UserProvider`
- Processor : `App\State\UserStateProcessor`

### 2. Groupes de sérialisation
- `user:read` : Ce qui est retourné par l'API
- `user:write` : Ce qui est accepté en input
- `user:create` : Validations spécifiques à la création

### 3. Validation
- Valider dans le DTO (contraintes API)
- Valider dans l'Entity (contraintes métier/BDD)

### 4. Réutilisation
- Créer des méthodes `entityToDto()` et `dtoToEntity()` réutilisables
- Centraliser la logique de transformation

## 🔄 Migration depuis l'approche classique

Si vous avez déjà une entité avec `#[ApiResource]` :

1. **Créer le DTO** dans `ApiResource/`
2. **Retirer** `#[ApiResource]` de l'entité
3. **Créer** le Provider et Processor
4. **Migrer** les groupes de sérialisation vers le DTO
5. **Tester** !

## 📚 Ressources

- [API Platform State Providers](https://api-platform.com/docs/core/state-providers/)
- [API Platform State Processors](https://api-platform.com/docs/core/state-processors/)
- [DTO Pattern](https://martinfowler.com/eaaCatalog/dataTransferObject.html)
