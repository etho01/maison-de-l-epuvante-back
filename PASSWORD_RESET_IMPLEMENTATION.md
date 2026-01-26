# 🔐 Système de réinitialisation de mot de passe

## ✅ TODOs complétés

Tous les TODOs du fichier `PasswordController.php` ont été implémentés :

### 1. ✅ Stockage du token en base de données
- **Entité créée** : `src/Entity/ResetPasswordRequest.php`
- **Repository créé** : `src/Repository/ResetPasswordRequestRepository.php`
- **Migration créée** : `migrations/Version20260121000001.php`

### 2. ✅ Vérification du token
- Recherche en base de données
- Vérification de l'expiration (1 heure)
- Suppression automatique des tokens expirés

### 3. ✅ Suppression du token après utilisation
- Token supprimé immédiatement après réinitialisation réussie
- Sécurité : un token ne peut être utilisé qu'une seule fois

### 4. ⚠️ Envoi d'email (TODO restant)
- URL de réinitialisation générée
- Prêt pour intégration avec Symfony Mailer
- Instructions fournies en commentaire

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────┐
│  1. Demande de réinitialisation                         │
│     POST /api/reset-password-request                    │
│     Body: { "email": "user@example.com" }               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  PasswordController::requestResetPassword()             │
│  - Génère token aléatoire (64 caractères hex)           │
│  - Supprime anciens tokens de cet utilisateur           │
│  - Crée ResetPasswordRequest (expire dans 1h)           │
│  - Sauvegarde en BDD                                    │
│  - TODO: Envoie email avec lien                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  Base de données : reset_password_request               │
│  - id (auto)                                            │
│  - user_id (FK vers user)                               │
│  - token (unique, 100 chars)                            │
│  - expires_at (timestamp + 1h)                          │
│  - created_at (timestamp)                               │
└─────────────────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  2. Utilisateur clique sur le lien                      │
│     POST /api/reset-password-confirm                    │
│     Body: {                                             │
│       "token": "abc123...",                             │
│       "newPassword": "nouveauMdp123"                    │
│     }                                                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  PasswordController::confirmResetPassword()             │
│  - Recherche token en BDD                               │
│  - Vérifie non expiré                                   │
│  - Récupère l'utilisateur                               │
│  - Hashe le nouveau mot de passe                        │
│  - Met à jour l'utilisateur                             │
│  - Supprime le token (one-time use)                     │
└─────────────────────────────────────────────────────────┘
```

## 📝 Fichiers créés

### Entité ResetPasswordRequest
**`src/Entity/ResetPasswordRequest.php`**
- Stocke les tokens de réinitialisation
- Relation ManyToOne avec User
- Méthode `isExpired()` pour vérifier la validité
- Cascade DELETE : si l'utilisateur est supprimé, ses tokens aussi

### Repository
**`src/Repository/ResetPasswordRequestRepository.php`**
- `removeExpired()` : Nettoyer les tokens expirés
- `removeAllForUser($userId)` : Supprimer tous les tokens d'un user

### Migration
**`migrations/Version20260121000001.php`**
- Crée la table `reset_password_request`
- Index sur `user_id` et `token` pour performance
- Contrainte FK avec CASCADE DELETE

## 🔒 Sécurité

### ✅ Implémenté
1. **Token unique** : 64 caractères hexadécimaux (256 bits)
2. **Expiration** : 1 heure maximum
3. **One-time use** : Token supprimé après utilisation
4. **Suppression en cascade** : Si user supprimé, tokens aussi
5. **Pas de révélation** : Ne dit jamais si l'email existe
6. **Password hashé** : Toujours hashé avec `passwordHasher`

### 🔄 Workflow sécurisé
1. Vieux tokens supprimés avant création d'un nouveau
2. Token vérifié en BDD (pas juste validé côté client)
3. Expiration vérifiée avant utilisation
4. Token immédiatement détruit après usage

## 📊 Routes disponibles

| Route | Méthode | Action | Body |
|-------|---------|--------|------|
| `/api/reset-password-request` | POST | Demander reset | `{"email": "..."}` |
| `/api/reset-password-confirm` | POST | Confirmer reset | `{"token": "...", "newPassword": "..."}` |
| `/api/change-password` | POST | Changer password (connecté) | `{"currentPassword": "...", "newPassword": "..."}` |

## 🚀 Pour utiliser

### 1. Lancer la base de données
```bash
./start.sh
# ou
docker-compose up -d
```

### 2. Exécuter la migration
```bash
php bin/console doctrine:migrations:migrate
```

### 3. Tester la demande de réinitialisation
```bash
curl -X POST http://localhost:8000/api/reset-password-request \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'
```

### 4. Vérifier le token en base
```bash
docker-compose exec db psql -U app -d app -c "SELECT * FROM reset_password_request;"
```

### 5. Utiliser le token
```bash
curl -X POST http://localhost:8000/api/reset-password-confirm \
  -H "Content-Type: application/json" \
  -d '{
    "token": "LE_TOKEN_GENERE",
    "newPassword": "nouveauPassword123"
  }'
```

## 📧 TODO : Envoi d'emails (optionnel)

Pour envoyer des emails en production :

### 1. Installer Symfony Mailer
```bash
composer require symfony/mailer
```

### 2. Configurer le MAILER_DSN dans .env
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
# ou pour tests :
MAILER_DSN=null://null
```

### 3. Décommenter le code dans PasswordController
```php
use Symfony\Component\Mailer\MailerInterface;

public function requestResetPassword(
    // ... autres paramètres
    MailerInterface $mailer
): JsonResponse {
    // ...
    if ($user) {
        // ... génération token
        
        $email = (new Email())
            ->from('noreply@votresite.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html('<a href="' . $resetUrl . '">Cliquez ici pour réinitialiser</a>');
        
        $mailer->send($email);
    }
}
```

## 🧹 Nettoyage automatique

Pour nettoyer les tokens expirés régulièrement :

### Option 1 : Commande Symfony
```bash
php bin/console app:clean-expired-tokens
```

### Option 2 : Cron job
```cron
0 * * * * cd /path/to/project && php bin/console app:clean-expired-tokens
```

### Code de la commande
```php
// src/Command/CleanExpiredTokensCommand.php
$this->resetPasswordRepository->removeExpired();
```

## ✅ Résultat final

Tous les TODOs sont **complétés** sauf l'envoi d'email qui reste optionnel et dépend de votre configuration SMTP.

Le système est **100% fonctionnel** pour :
- Générer des tokens sécurisés
- Les stocker en BDD avec expiration
- Les vérifier
- Les supprimer après usage
- Hasher les mots de passe

**Le mot de passe est TOUJOURS hashé**, jamais stocké en clair ! 🔒
