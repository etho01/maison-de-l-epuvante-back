# Système d'authentification API

## Installation complétée ✅

Le système d'authentification a été installé avec les fonctionnalités suivantes :

### 🔑 Fonctionnalités disponibles

1. **Connexion** - `POST /api/login`
2. **Création d'utilisateur** - `POST /api/users`
3. **Modification d'utilisateur** - `PUT /api/users/{id}` ou `PATCH /api/users/{id}`
4. **Changement de mot de passe** - `POST /api/change-password`
5. **Demande de réinitialisation de mot de passe** - `POST /api/reset-password-request`
6. **Vérification d'email** - `GET /api/verify/email?id={id}&token={token}`
7. **Renvoyer l'email de vérification** - `POST /api/verify/resend`

### 📋 Étapes suivantes

#### 1. Configurer la base de données

Modifiez le fichier `.env` avec vos paramètres de base de données :

```env
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/maison_de_lepouvante?serverVersion=16&charset=utf8"
```

Ou utilisez la base de données Docker fournie dans `compose.yaml`.

#### 2. Créer la base de données et les tables

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer les migrations
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

#### 3. Lancer le serveur

```bash
symfony server:start
```

ou

```bash
php -S localhost:8000 -t public
```

### 🧪 Test de l'API

#### Créer un utilisateur

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "plainPassword": "password123",
    "firstName": "John",
    "lastName": "Doe"
  }'
```

#### Se connecter

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

Réponse (si l'email est vérifié) :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "roles": ["ROLE_USER"]
  }
}
```

#### Changer le mot de passe (authentifié)

```bash
curl -X POST http://localhost:8000/api/change-password \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "currentPassword": "password123",
    "newPassword": "newPassword456"
  }'
```

#### Modifier un utilisateur (authentifié)

```bash
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "firstName": "Jane",
    "lastName": "Smith"
  }'
```

#### Vérifier un email

```bash
curl -X GET "http://localhost:8000/api/verify/email?id=1&token=TOKEN_FROM_EMAIL"
```

#### Demander un nouveau lien de vérification

```bash
curl -X POST http://localhost:8000/api/verify/resend \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

### 🔒 Sécurité

- Les mots de passe sont automatiquement hashés avec l'algorithme `auto` (bcrypt par défaut)
- Les tokens JWT sont signés avec une paire de clés RSA (privée/publique)
- Les utilisateurs doivent vérifier leur email avant de pouvoir se connecter
- Les mots de passe doivent contenir au moins 8 caractères

### 📝 Personnalisation

#### Ajouter l'envoi d'emails

Pour un système de production, vous devez :

1. Installer Symfony Mailer :
```bash
composer require symfony/mailer
```

2. Configurer votre fournisseur d'email dans `.env` :
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:25
```

3. Modifier les controllers pour envoyer des emails :
   - `VerifyEmailController::resendVerificationEmail()` - envoyer le lien de vérification
   - `PasswordController::requestResetPassword()` - envoyer le lien de réinitialisation

#### Désactiver la vérification d'email

Si vous souhaitez permettre la connexion sans vérification d'email, modifiez [src/Controller/AuthController.php](src/Controller/AuthController.php) :

Supprimez ou commentez ce bloc :
```php
if (!$user->isVerified()) {
    return $this->json([
        'message' => 'Veuillez vérifier votre email avant de vous connecter',
    ], JsonResponse::HTTP_FORBIDDEN);
}
```

#### Modifier la durée de validité du JWT

Éditez [config/packages/lexik_jwt_authentication.yaml](config/packages/lexik_jwt_authentication.yaml) :

```yaml
lexik_jwt_authentication:
    token_ttl: 3600  # 1 heure (en secondes)
```

### 📚 Documentation API complète

Accédez à la documentation interactive de l'API sur :
```
http://localhost:8000/api
```

Cette interface vous permet de tester tous les endpoints directement depuis votre navigateur.

### 🎯 Points d'attention

1. **Email de vérification** : Par défaut, les utilisateurs ne peuvent pas se connecter tant que leur email n'est pas vérifié. Pour le développement, vous pouvez désactiver cette vérification ou marquer les utilisateurs comme vérifiés manuellement dans la base de données.

2. **CORS** : Le bundle CORS est configuré pour autoriser localhost. Modifiez `CORS_ALLOW_ORIGIN` dans `.env` pour vos besoins de production.

3. **Sécurité en production** :
   - Changez `APP_SECRET` dans `.env`
   - Utilisez des variables d'environnement pour les secrets
   - Activez HTTPS
   - Configurez un système de rate limiting
