# Collectinfos

Plateforme journalistique panafricaine — site public et back-office d'administration, développée avec **Laravel**.

Inspirée de [collectinfos.org](https://collectinfos.org/).

## Fonctionnalités

### Site public
- Accueil, catalogue de contenus, fiches détaillées
- Multilingue (FR / EN / PT)
- Authentification visiteurs, panier et achats simulés
- Pages institutionnelles : contact, collaboration, produits, presse, fact-checking
- Newsletter

### Administration (`/admin`)
- Tableau de bord et gestion des contenus (CRUD)
- Référentiels : catégories, thèmes, types, statistiques accueil
- Partenaires médias, offres produits, coordonnées
- Pages éditables : relations presse, fact-checking
- Messages contact, candidatures, newsletter, enquêtes

## Prérequis

- PHP 8.2+
- Composer
- MySQL (ou MariaDB)
- Extension PHP : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`

## Installation

```bash
# Cloner le dépôt
git clone https://github.com/VOTRE_UTILISATEUR/CollectinfosL.git
cd CollectinfosL

# Dépendances PHP
composer install

# Configuration
cp .env.example .env
php artisan key:generate

# Éditer .env : base de données, APP_URL, etc.

# Base de données
php artisan migrate --force
php artisan db:seed --force

# Lien storage (médias uploadés)
php artisan storage:link

# Lancer le serveur de développement
php artisan serve
```

Site : `http://localhost:8000`  
Admin : `http://localhost:8000/admin`

## Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|------|--------|--------------|
| Visiteur | `demo@collectinfos.org` | `demo123` |
| Admin | `admin@collectinfos.org` | `admin123` |

## Stack technique

- Laravel (PHP, sans Node/Vite obligatoire pour le front)
- CSS/JS dans `public/`
- Font Awesome (local)
- Sessions et cache fichier
- MySQL

## Structure utile

```
app/                 Modèles, contrôleurs, services
config/              collectinfos.php, locales.php
database/            Migrations et seeders
lang/                Traductions FR, EN, PT
public/css/          Styles site + admin
resources/views/     Vues Blade (site + admin)
routes/web.php       Routes
```

## Licence

Projet privé — tous droits réservés.
