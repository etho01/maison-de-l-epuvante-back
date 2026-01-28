# Configuration Docker - Maison de l'Épouvante

## Services

- **API** : Symfony 7.4 avec PHP 8.3
  - HTTP: http://localhost:8882
  - HTTPS: https://localhost:8881
- **Base de données** : MariaDB 11.3
  - Port: 3306
- **phpMyAdmin** : http://localhost:8880
- **MailHog** : http://localhost:8025

## Démarrage

### Première installation

```bash
# Construire et démarrer les conteneurs
docker-compose up -d --build

# Installer les dépendances PHP
docker-compose exec api composer install

# Créer la base de données
docker-compose exec api php bin/console doctrine:database:create

# Exécuter les migrations
docker-compose exec api php bin/console doctrine:migrations:migrate --no-interaction

# Générer les clés JWT
docker-compose exec api php bin/console lexik:jwt:generate-keypair
```

### Démarrage normal

```bash
docker-compose up -d
```

### Arrêt

```bash
docker-compose down
```

### Arrêt avec suppression des volumes

```bash
docker-compose down -v
```

## Commandes utiles

### Accéder au conteneur API

```bash
docker-compose exec api bash
```

### Voir les logs

```bash
# Tous les services
docker-compose logs -f

# Un service spécifique
docker-compose logs -f api
docker-compose logs -f database
```

### Exécuter des commandes Symfony

```bash
docker-compose exec api php bin/console [commande]
```

### Clear cache

```bash
docker-compose exec api php bin/console cache:clear
```

## Base de données

- **Nom** : maison_epouvante
- **Utilisateur** : root
- **Mot de passe** : (vide)
- **Host** : database (dans les conteneurs) ou localhost:3306 (depuis l'hôte)

## Variables d'environnement

Les variables d'environnement sont définies dans `.env.docker` pour l'environnement Docker.

## Troubleshooting

### Port déjà utilisé

Si un port est déjà utilisé, modifiez les ports dans `docker-compose.yaml`.

### Problème de permissions

```bash
sudo chown -R $USER:$USER .
docker-compose exec api chown -R www-data:www-data var/
```

### Reconstruire les conteneurs

```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```
