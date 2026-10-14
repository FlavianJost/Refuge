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
- Dépôt d'une demande d'adoption par les utilisateurs connectés.
- Espace profil : informations du compte et suivi de ses demandes d'adoption.
- Gestion des animaux (ajout, modification, statut) par les membres autorisés.
- Suivi et traitement des demandes d'adoption par les membres.
- Niveaux d'accès : visiteur, bénévole, administrateur.
- Back-office d'administration (gestion des utilisateurs, supervision).

## Prérequis

- PHP >= 8.2 avec les extensions `ctype`, `iconv` (fournies avec Symfony Flex).
- Composer 2.
- Un serveur de base de données MySQL/MariaDB (ou SQLite pour un démarrage rapide).
- Symfony CLI (optionnel, mais recommandé).

Aucune étape de build front n'est nécessaire : les assets sont gérés par **AssetMapper**
(importmap natif, sans Node.js).

## Installation

```bash
# 1. Cloner le dépôt
git clone git@github.com:FlavianJost/Refuge.git
cd Refuge

# 2. Installer les dépendances PHP
composer install

# 3. Télécharger les assets front (Bootstrap 5, Stimulus, Turbo)
php bin/console importmap:install

# 4. Préparer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de démonstration
```

> Si aucune migration n'existe encore, générez-la à partir des entités :
> `php bin/console make:migration`.

## Modèle de données

L'application s'appuie sur trois entités Doctrine :

| Entité | Description | Champs principaux |
| --- | --- | --- |
| `User` | Utilisateur de l'application (visiteur, bénévole, administrateur). | email, rôles, mot de passe, prénom, nom, dernière connexion |
| `Animal` | Animal accueilli au refuge. | nom, espèce, race, âge, description, statut, photo |
| `AdoptionRequest` | Demande d'adoption déposée par un utilisateur pour un animal. | animal, utilisateur, message, statut, traitement |

Deux énumérations typent les états : `AnimalStatus` (disponible, en cours d'adoption,
adopté) et `AdoptionRequestStatus` (nouvelle, en cours, acceptée, refusée).

### Données de démonstration

Le jeu de données (`AppFixtures`) crée des comptes, plusieurs animaux et des demandes d'adoption
de démonstration :

| Email | Mot de passe | Rôle |
| --- | --- | --- |
| `admin@refuge.test` | `password` | Administrateur |
| `benevole@refuge.test` | `password` | Bénévole |
| `camille@refuge.test` | `password` | Utilisateur (candidat) |
| `david@refuge.test` | `password` | Utilisateur (candidat) |

## Authentification et rôles

L'authentification repose sur le composant **Security** de Symfony (fournisseur Doctrine,
formulaire de connexion, déconnexion).

- Inscription : `GET|POST /inscription` (`app_register`).
- Connexion : `GET|POST /login` (`app_login`).
- Déconnexion : route générée `_logout_main` (`/deconnexion`).

Les rôles sont hiérarchisés dans `security.yaml` :

| Rôle | Portée |
| --- | --- |
| `ROLE_USER` | Visiteur authentifié. |
| `ROLE_VOLUNTEER` | Bénévole : gestion des animaux et des demandes. |
| `ROLE_ADMIN` | Administrateur : supervision complète (hérite de `ROLE_VOLUNTEER`). |

La protection CSRF est **sans état (stateless)** pour les formulaires et l'authentification
(configuration dans `config/packages/csrf.yaml`). La date de dernière connexion est enregistrée
automatiquement à chaque authentification (`App\EventListener\LoginListener`).

Les zones sensibles sont également protégées au niveau des contrôleurs par l'attribut
`#[IsGranted]` (défense en profondeur en plus des `access_control`).

## Catalogue des animaux (public)

- `GET /animaux` — liste des animaux avec filtres par **espèce** et **situation**
  (`app_animal_index`). Affiche par défaut les animaux disponibles.
- `GET /animaux/{id}` — fiche détaillée d'un animal (`app_animal_show`).

Ces pages sont accessibles aux visiteurs non authentifiés. Les photographies sont servies
depuis `public/uploads/animals/`.

## Gestion des animaux (bénévoles)

Espace réservé aux utilisateurs disposant du rôle `ROLE_VOLUNTEER` (et `ROLE_ADMIN`).

- `GET /gestion/animaux` — tableau de bord des animaux (`app_animal_manage_index`).
- `GET|POST /gestion/animaux/nouveau` — ajout d'un animal (`app_animal_manage_new`).
- `GET|POST /gestion/animaux/{id}/modifier` — édition (`app_animal_manage_edit`).
- `POST /gestion/animaux/{id}/supprimer` — suppression avec jeton CSRF (`app_animal_manage_delete`).

L'upload de photo est pris en charge par le service `App\Service\AnimalPhotoUploader` : les
fichiers (JPEG, PNG ou WebP, 4 Mo max) sont enregistrés dans `public/uploads/animals/`. La
photo précédente est supprimée lors d'un remplacement ou de la suppression de l'animal.

## Demandes d'adoption

### Côté utilisateur connecté (`ROLE_USER`)

- `GET|POST /animaux/{id}/adopter` — formulaire de demande d'adoption (`app_adoption_request_new`),
  accessible depuis la fiche de l'animal. La demande est **réservée aux utilisateurs connectés** :
  un visiteur est redirigé vers la page de connexion. La demande est rattachée au compte de
  l'utilisateur (pas de saisie de coordonnées) et une demande en doublon pour le même animal est
  refusée.

### Côté bénévole (`ROLE_VOLUNTEER`)

- `GET /gestion/demandes` — liste des demandes avec filtre par statut (`app_adoption_manage_index`).
- `GET /gestion/demandes/{id}` — détail d'une demande (`app_adoption_manage_show`).
- `POST /gestion/demandes/{id}/statut` — changement de statut (`app_adoption_manage_status`).

Les transitions sont gérées par le service `App\Service\AdoptionRequestProcessor` :

- **Acceptée** : l'animal passe au statut *Adopté* et les autres demandes en cours pour cet animal
  sont automatiquement refusées.
- **Refusée** : la demande est clôturée, horodatée et associée au bénévole.
- **Nouvelle / En cours** : la demande est réouverte ; si l'animal n'a plus de demande acceptée,
  il redevient *Disponible*.

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

Les assets sont servis dynamiquement en développement. Pour la production, compilez-les :

```bash
php bin/console asset-map:compile
```

## Structure du projet

```
assets/            # Sources JS/CSS (Bootstrap 5, styles du thème) servies par AssetMapper
importmap.php      # Déclaration des paquets front (bootstrap, stimulus, turbo...)
config/            # Configuration Symfony (packages, routes, services)
migrations/        # Migrations Doctrine
public/            # Point d'entrée (index.php) et assets compilés (assets/)
src/
  Controller/      # Contrôleurs (organisés par domaine)
  Entity/          # Entités Doctrine
  Enum/            # Énumérations métier
  Form/            # Formulaires Symfony (organisés par domaine)
  Repository/      # Repositories Doctrine
  Service/         # Services applicatifs (upload, etc.)
  EventListener/   # Écouteurs d'événements (connexion)
  DataFixtures/    # Jeu de données de démonstration
templates/         # Templates Twig (layout de base + dossiers par domaine)
tests/             # Tests unitaires et fonctionnels
```

Le projet adopte le pattern **MVC**, avec les entités Doctrine comme modèle, les
contrôleurs Symfony comme couche de contrôle et les templates Twig pour la vue. Les assets
front sont servis par **AssetMapper** (importmap natif) et le style repose sur **Bootstrap 5**.

## Stack technique

- **Symfony 7.4** — framework principal.
- **Doctrine ORM** — persistance et migrations.
- **Twig** — moteur de templates.
- **AssetMapper + Bootstrap 5** — gestion des assets et interface responsive.
- **EasyAdmin 5** — back-office d'administration.
- **HWIOAuthBundle** — authentification via fournisseurs OAuth (Google).
- **PHPUnit** — tests.

## Feuille de route

- [x] Feature 1 — Base du projet, layout Bootstrap 5 et page d'accueil.
- [x] Feature 2 — Modèle de données (User, Animal, AdoptionRequest).
- [x] Feature 3 — Authentification et gestion des rôles.
- [x] Feature 4 — Catalogue public des animaux.
- [x] Feature 5 — Gestion des animaux (bénévoles).
- [x] Feature 6 — Demandes d'adoption.
- [x] Profil utilisateur.
- [ ] Feature 7 — Back-office EasyAdmin.
- [ ] Feature 8 — Connexion Google (OAuth).
- [ ] Feature 9 — Tests unitaires et fonctionnels.
