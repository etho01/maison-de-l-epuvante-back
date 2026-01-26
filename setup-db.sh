#!/bin/bash

# Script d'initialisation de la base de données
# Pour le projet Maison de l'Épouvante

echo "🚀 Initialisation de la base de données..."

# Créer la base de données si elle n'existe pas
echo "📦 Création de la base de données..."
php bin/console doctrine:database:create --if-not-exists

# Créer les migrations
echo "🔧 Génération des migrations..."
php bin/console make:migration --no-interaction

# Exécuter les migrations
echo "⚡ Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Base de données initialisée avec succès !"
echo ""
echo "Vous pouvez maintenant :"
echo "  - Lancer le serveur : symfony server:start"
echo "  - Créer un utilisateur via l'API : POST /api/users"
echo "  - Consulter la doc API : http://localhost:8000/api"
