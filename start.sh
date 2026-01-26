#!/bin/bash

echo "🏚️  Maison de l'Épouvante - Démarrage du backend"
echo ""

# Vérifier si Docker est en cours
if ! docker info > /dev/null 2>&1; then
    echo "⚠️  Docker ne semble pas être en cours d'exécution."
    echo "   Démarrez Docker ou configurez manuellement votre base de données."
    exit 1
fi

# Démarrer la base de données
echo "🐘 Démarrage de PostgreSQL..."
docker compose up -d database

# Attendre que la base de données soit prête
echo "⏳ Attente du démarrage de la base de données..."
sleep 5

# Vérifier si la base de données existe
DB_EXISTS=$(php bin/console doctrine:database:create --if-not-exists 2>&1 | grep -c "already exists")

if [ "$DB_EXISTS" -eq 0 ]; then
    echo "✅ Base de données créée"
else
    echo "ℹ️  Base de données déjà existante"
fi

# Vérifier s'il y a des migrations à exécuter
MIGRATIONS_STATUS=$(php bin/console doctrine:migrations:status --no-interaction 2>&1)

if echo "$MIGRATIONS_STATUS" | grep -q "Available Migrations: 0"; then
    echo "📝 Génération de la migration initiale..."
    php bin/console make:migration --no-interaction
fi

echo "⚡ Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo ""
echo "✅ Backend prêt !"
echo ""
echo "🚀 Pour démarrer le serveur :"
echo "   symfony server:start"
echo "   ou"
echo "   php -S localhost:8000 -t public"
echo ""
echo "📚 Documentation API : http://localhost:8000/api"
echo ""
echo "👤 Pour créer un utilisateur admin :"
echo "   php bin/console app:create-user admin@example.com admin123 --admin --verified"
