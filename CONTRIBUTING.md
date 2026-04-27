# 🤝 Guide de Contribution

Merci de votre intérêt pour contribuer à **Maison de l'Épouvante** ! Ce guide vous aidera à soumettre des contributions de qualité.

## 📋 Avant de commencer

1. Assurez-vous d'avoir lu le [README.md](README.md)
2. Consultez les [issues ouvertes](https://github.com/USERNAME/REPO/issues) pour voir si votre idée existe déjà
3. Familiarisez-vous avec notre [architecture](README.md#-architecture)

## 🔄 Processus de contribution

### 1. Fork et Clone

```bash
# Forkez le dépôt via GitHub, puis clonez votre fork
git clone https://github.com/VOTRE_USERNAME/maison-de-lepouvante.git
cd maison-de-lepouvante/back

# Ajoutez le dépôt principal comme remote
git remote add upstream https://github.com/USERNAME/REPO.git
```

### 2. Créez une branche

Utilisez une nomenclature claire :

```bash
# Pour une nouvelle fonctionnalité
git checkout -b feature/nom-fonctionnalite

# Pour une correction de bug
git checkout -b fix/description-bug

# Pour une amélioration
git checkout -b improvement/description
```

### 3. Développez

#### Installation

```bash
composer install
./start.sh  # Configure la base de données
```

#### Bonnes pratiques

- **Code** : Suivez les conventions PSR-12
- **Commits** : Messages clairs et descriptifs en français
  ```
  feat: ajout de l'authentification OAuth
  fix: correction du calcul du total de commande
  docs: mise à jour du README
  test: ajout des tests pour OrderService
  ```
- **Tests** : **Tous les nouveaux codes doivent avoir des tests** ✅

### 4. Testez votre code

**IMPORTANT** : Les tests sont obligatoires et exécutés automatiquement par le CI.

```bash
# Exécuter les tests (simule l'environnement CI)
./run-tests.sh

# Ou manuellement
php bin/phpunit

# Tests spécifiques
php bin/phpunit tests/Controller/VotreControllerTest.php

# Avec couverture de code
php bin/phpunit --coverage-html coverage
```

#### Critères d'acceptation

- ✅ **Tous les tests doivent passer** (0 erreurs, 0 échecs)
- ✅ **Couverture de code** : Visez au moins 80% pour le nouveau code
- ✅ **Tests unitaires** pour les services et entités
- ✅ **Tests d'intégration** pour les contrôleurs et API

### 5. Vérifiez la qualité du code

```bash
# Vérification de la syntaxe
find src tests -name "*.php" -print0 | xargs -0 -n1 php -l

# PHPStan (si configuré)
vendor/bin/phpstan analyse src tests
```

### 6. Commitez et Pushez

```bash
git add .
git commit -m "feat: description claire de votre contribution"
git push origin feature/votre-branche
```

### 7. Créez une Pull Request

1. Allez sur GitHub et créez une Pull Request vers `main`
2. Remplissez le template de PR :
   - **Description** : Expliquez ce que fait votre PR
   - **Motivation** : Pourquoi ce changement est nécessaire
   - **Tests** : Comment avez-vous testé ?
   - **Screenshots** : Si applicable (pour les changements UI/API)

## 🤖 CI/CD Automatique

Lorsque vous créez une Pull Request, **GitHub Actions exécute automatiquement** :

### ✅ Workflow de Tests

1. **Installation** de PHP 8.2 et des dépendances
2. **Configuration** de l'environnement de test (SQLite, JWT)
3. **Exécution** de tous les tests PHPUnit
4. **Génération** du rapport de couverture
5. **Vérification** de la qualité du code

**La PR ne peut pas être mergée si les tests échouent** ❌

### 📊 Résultats

Vous verrez les résultats directement sur votre PR :
- ✅ Tests passed (verts) → PR peut être mergée
- ❌ Tests failed (rouges) → Corrigez les erreurs
- 📊 Couverture de code affichée par Codecov

## 📝 Standards de code

### Structure des fichiers

```
src/
├── ApiResource/       # DTOs pour API Platform
├── Controller/        # Contrôleurs (logique métier minimale)
├── Entity/            # Entités Doctrine
├── Service/           # Services (logique métier)
├── Repository/        # Repositories Doctrine
├── EventSubscriber/   # Écouteurs d'événements
└── Trait/             # Traits réutilisables

tests/
├── Controller/        # Tests de contrôleurs
├── Entity/            # Tests d'entités
├── Service/           # Tests de services
└── WebTestCase.php    # Classe de base pour les tests
```

### Conventions de nommage

- **Classes** : `PascalCase` (ex: `UserController`, `OrderService`)
- **Méthodes** : `camelCase` (ex: `getUserById`, `calculateTotal`)
- **Constantes** : `SCREAMING_SNAKE_CASE` (ex: `MAX_ITEMS`, `API_VERSION`)
- **Variables** : `camelCase` (ex: `$userData`, `$totalPrice`)

### Commentaires et documentation

```php
/**
 * Crée une nouvelle commande pour l'utilisateur.
 *
 * @param User $user L'utilisateur qui passe la commande
 * @param array $items Les articles de la commande
 * @return Order La commande créée
 * @throws InvalidArgumentException Si les articles sont vides
 */
public function createOrder(User $user, array $items): Order
{
    // Logique métier...
}
```

## 🧪 Écrire des tests

### Tests de contrôleurs

```php
<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class ExampleControllerTest extends WebTestCase
{
    public function testCreateResource(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/resources', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Test Resource',
        ]));

        $this->assertResponseStatusCodeSame(201);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('Test Resource', $data['data']['name']);
    }
}
```

### Tests d'entités

```php
public function testEntityValidation(): void
{
    $entity = new MyEntity();
    $entity->setName(''); // Invalide
    
    $errors = $this->validator->validate($entity);
    $this->assertCount(1, $errors);
}
```

### Tests avec authentification

```php
public function testProtectedEndpoint(): void
{
    $auth = $this->getAuthenticatedClient();
    $client = $auth['client'];
    
    $client->request('GET', '/api/protected', [], [], 
        $this->getAuthHeaders($auth['token'])
    );

    $this->assertResponseIsSuccessful();
}
```

## 🚫 Ce qui ne sera PAS accepté

- ❌ Code sans tests
- ❌ Tests qui échouent
- ❌ Code qui casse les tests existants
- ❌ Commits avec des secrets ou credentials
- ❌ Code non formaté ou non conforme PSR-12
- ❌ PR sans description

## ✅ Checklist avant de soumettre une PR

- [ ] J'ai créé une branche depuis `main`
- [ ] Mon code suit les conventions du projet
- [ ] J'ai ajouté des tests pour mon code
- [ ] Tous les tests passent (`./run-tests.sh`)
- [ ] J'ai mis à jour la documentation si nécessaire
- [ ] Mon code ne contient pas de secrets ou credentials
- [ ] J'ai testé manuellement mes changements
- [ ] Ma PR a une description claire

## 🆘 Besoin d'aide ?

- 📖 Consultez le [README.md](README.md) et [CI_CD.md](CI_CD.md)
- 💬 Ouvrez une [Discussion](https://github.com/USERNAME/REPO/discussions)
- 🐛 Signalez un bug via une [Issue](https://github.com/USERNAME/REPO/issues)

## 📄 Licence

En contribuant à ce projet, vous acceptez que vos contributions soient sous la même licence que le projet.

---

**Merci pour votre contribution ! 🎃**
