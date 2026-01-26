# 🎃 Maison de l'Épouvante - Guide de démarrage rapide

## Démarrage en 3 étapes

### 1. Démarrer la base de données et initialiser

```bash
./start.sh
```

Ce script va :
- Démarrer PostgreSQL avec Docker
- Créer la base de données
- Générer et exécuter les migrations

### 2. Créer un utilisateur admin

```bash
php bin/console app:create-user admin@example.com admin123 --admin --verified
```

### 3. Lancer le serveur

```bash
symfony server:start
```

ou

```bash
php -S localhost:8000 -t public
```

## Tester l'API

### Avec curl

```bash
# Créer un utilisateur
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","plainPassword":"password123","firstName":"Test","lastName":"User"}'

# Se connecter (après avoir vérifié l'email ou désactivé la vérification)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'
```

### Avec l'interface Swagger

Ouvrez dans votre navigateur :
```
http://localhost:8000/api
```

## Désactiver la vérification d'email (développement)

Pour pouvoir vous connecter sans vérifier l'email, commentez les lignes 22-27 dans `src/Controller/AuthController.php` :

```php
// if (!$user->isVerified()) {
//     return $this->json([
//         'message' => 'Veuillez vérifier votre email avant de vous connecter',
//     ], JsonResponse::HTTP_FORBIDDEN);
// }
```

Ou créez toujours vos utilisateurs avec l'option `--verified` :

```bash
php bin/console app:create-user user@example.com password123 --verified
```

## Documentation complète

- [README.md](README.md) - Documentation générale
- [AUTH_README.md](AUTH_README.md) - Documentation de l'authentification
- [api-examples.http](api-examples.http) - Exemples de requêtes HTTP

## Problèmes courants

### Base de données non accessible

Vérifiez que Docker est lancé et que le conteneur PostgreSQL est actif :

```bash
docker compose ps
```

Pour démarrer le conteneur :

```bash
docker compose up -d database
```

### Erreur "could not find driver"

Installez l'extension PHP pour PostgreSQL :

```bash
# Ubuntu/Debian
sudo apt-get install php-pgsql

# macOS (avec Homebrew)
brew install php-pgsql
```

### Les clés JWT sont manquantes

Régénérez les clés :

```bash
php bin/console lexik:jwt:generate-keypair
```

## Commandes utiles

```bash
# Voir les routes disponibles
php bin/console debug:router

# Vider le cache
php bin/console cache:clear

# Voir les migrations disponibles
php bin/console doctrine:migrations:list

# Créer une nouvelle entité
php bin/console make:entity --api-resource
```
