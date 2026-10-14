# Refuge Animalier

Application web de gestion d'un refuge animalier développée avec **Symfony 7.4** (PHP 8.2+).

Elle permet de centraliser les informations sur les animaux accueillis, de gérer les
demandes d'adoption et d'organiser les niveaux d'accès (visiteur, bénévole, administrateur).

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Lancer l'application](#lancer-lapplication)
- [Structure du projet](#structure-du-projet)
- [Stack technique](#stack-technique)
- [Feuille de route](#feuille-de-route)

## Fonctionnalités

- Consultation publique des animaux accueillis et de leur fiche détaillée.
- Dépôt d'une demande d'adoption par les visiteurs.
- Gestion des animaux (ajout, modification, statut) par les membres autorisés.
- Suivi et traitement des demandes d'adoption par les membres.
- Niveaux d'accès : visiteur, bénévole, administrateur.
- Back-office d'administration (gestion des utilisateurs, supervision).

## Prérequis

- PHP >= 8.2 avec les extensions `ctype`, `iconv` (fournies avec Symfony Flex).
- Composer 2.
- Node.js >= 18 et npm (compilation des assets via Webpack Encore).
- Un serveur de base de données MySQL/MariaDB (ou SQLite pour un démarrage rapide).
- Symfony CLI (optionnel, mais recommandé).

## Installation

```bash
# 1. Cloner le dépôt
git clone git@github.com:FlavianJost/Refuge.git
cd Refuge

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances front
npm install

# 4. Compiler les assets
npm run dev        # développement
# ou
npm run build      # production

# 5. Préparer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de démonstration
```

## Configuration

Les variables d'environnement sont définies dans les fichiers `.env` :

| Variable | Description |
| --- | --- |
| `APP_ENV` | Environnement courant (`dev`, `test`, `prod`). |
| `APP_SECRET` | Clé secrète utilisée par Symfony. |
| `DATABASE_URL` | Connexion à la base de données (MySQL/MariaDB). |
| `OAUTH_GOOGLE_CLIENT_ID` / `OAUTH_GOOGLE_CLIENT_SECRET` | Identifiants Google (connexion OAuth). |

Pour vos réglages locaux, créez un fichier `.env.local` (non versionné) et **ne committez
jamais** de secrets dans les fichiers `.env` versionnés.

## Lancer l'application

```bash
# Avec la Symfony CLI
symfony serve

# Ou avec le serveur PHP intégré
php -S localhost:8000 -t public
```

L'application est ensuite accessible sur `http://localhost:8000`.

En développement, pour recompiler les assets automatiquement :

```bash
npm run watch
```

## Structure du projet

```
assets/            # Sources JS/CSS (Bootstrap 5, styles du thème) compilées par Encore
config/            # Configuration Symfony (packages, routes, services)
migrations/        # Migrations Doctrine
public/            # Point d'entrée (index.php) et assets compilés (build/)
src/
  Controller/      # Contrôleurs
  Entity/          # Entités Doctrine
  Repository/      # Repositories Doctrine
  DataFixtures/    # Jeu de données de démonstration
templates/         # Templates Twig (base, partials, pages)
tests/             # Tests unitaires et fonctionnels
```

Le projet adopte le pattern **MVC**, avec les entités Doctrine comme modèle, les
contrôleurs Symfony comme couche de contrôle et les templates Twig pour la vue. Les assets
front sont compilés avec **Webpack Encore** et le style repose sur **Bootstrap 5**.

## Stack technique

- **Symfony 7.4** — framework principal.
- **Doctrine ORM** — persistance et migrations.
- **Twig** — moteur de templates.
- **Bootstrap 5** via **Webpack Encore** — interface responsive.
- **EasyAdmin 5** — back-office d'administration.
- **HWIOAuthBundle** — authentification via fournisseurs OAuth (Google).
- **PHPUnit** — tests.

## Feuille de route

- [x] Feature 1 — Base du projet, layout Bootstrap 5 et page d'accueil.
- [ ] Feature 2 — Modèle de données (User, Animal, AdoptionRequest).
- [ ] Feature 3 — Authentification et gestion des rôles.
- [ ] Feature 4 — Catalogue public des animaux.
- [ ] Feature 5 — Gestion des animaux (bénévoles).
- [ ] Feature 6 — Demandes d'adoption.
- [ ] Feature 7 — Back-office EasyAdmin.
- [ ] Feature 8 — Connexion Google (OAuth).
- [ ] Feature 9 — Tests unitaires et fonctionnels.
