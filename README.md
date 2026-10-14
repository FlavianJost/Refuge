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
- [Tests](#tests)
- [Structure du projet](#structure-du-projet)
- [Stack technique](#stack-technique)
- [Feuille de route](#feuille-de-route)

## Fonctionnalités

- Consultation publique des animaux accueillis et de leur fiche détaillée.
- Dépôt d'une demande d'adoption par les utilisateurs connectés.
- Espace profil : informations du compte, modification des données et photo de profil, suivi des demandes d'adoption.
- Gestion des animaux (ajout, modification, statut) par les membres autorisés.
- Suivi et traitement des demandes d'adoption par les membres.
- Niveaux d'accès : visiteur, bénévole, administrateur.
- Page de don (démonstration) avec enregistrement des dons en base.
- Avis sur le refuge : consultation publique et dépôt d'avis par les utilisateurs connectés.
- Formulaire de contact dans l'espace client, avec suivi des messages dans le back-office.
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
| `Donation` | Don effectué via la page de démonstration (paiement simulé). | montant (en centimes), donateur, email, message, statut, date |
| `Review` | Avis publié par un utilisateur sur le refuge. | utilisateur, note (1 à 5), commentaire, date |
| `ContactMessage` | Message envoyé au refuge depuis l'espace client. | utilisateur, sujet, message, traité, date |

Deux énumérations typent les états : `AnimalStatus` (disponible, en cours d'adoption,
adopté) et `AdoptionRequestStatus` (nouvelle, en cours, acceptée, refusée). Les dons
utilisent l'énumération `DonationStatus` (confirmé, en attente, échoué).

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
- `GET /animaux/{slug}` — fiche détaillée d'un animal (`app_animal_show`), identifiée par un slug unique.

Le slug est généré à partir du nom via `Symfony\Component\String\Slugger\SluggerInterface`
(service `App\Service\AnimalSlugger`). Plusieurs animaux peuvent porter le **même nom** : en cas
de doublon, un suffixe numérique est ajouté pour garantir l'unicité (`rex`, `rex-2`, `rex-3`, ...).

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

## Back-office d'administration (EasyAdmin)

Espace réservé aux utilisateurs disposant du rôle `ROLE_ADMIN`, généré avec **EasyAdmin 5**
sous le chemin `/admin` (route `admin`, protégée par `access_control` et l'attribut `#[IsGranted]`).

- `/admin` — tableau de bord (nombre d'animaux, de demandes d'adoption et d'utilisateurs, nombre et
  total des dons, nombre d'avis, nombre de messages de contact).
- Gestion **des animaux**, **des demandes d'adoption**, **des dons**, **des avis**,
  **des messages de contact** et **des utilisateurs** via les contrôleurs CRUD d'EasyAdmin.

Le tableau de bord et les contrôleurs CRUD se trouvent dans `src/Controller/Admin/` et
configurent l'affichage en français (libellés, badges de statut, filtres). Les énumérations
(`Species`, `AnimalStatus`, `AdoptionRequestStatus`, `DonationStatus`) implémentent
`Symfony\Contracts\Translation\TranslatableInterface` afin qu'EasyAdmin affiche leurs libellés
français dans les formulaires, les listes et les filtres.

Le changement de statut d'une demande depuis le back-office réutilise la logique métier du
service `App\Service\AdoptionRequestProcessor` (statut de l'animal, refus des demandes
concurrentes), et le slug d'un animal est (re)généré automatiquement à l'enregistrement.

## Profil utilisateur

Espace personnel des utilisateurs connectés (`ROLE_USER`), protégé par `access_control` et
l'attribut `#[IsGranted]`.

- `/profil` (route `app_profile`) — affiche toutes les informations du compte (nom, email, rôles,
  date d'inscription, dernière connexion, nombre de demandes), le suivi de ses demandes
  d'adoption ainsi que ses avis. Un lien permet de **contacter le refuge**.
- `/profil/modifier` (route `app_profile_edit`) — permet de modifier le prénom, le nom et l'adresse
  email, et d'ajouter ou remplacer une **photo de profil** (JPEG, PNG ou WebP, 4 Mo max). Les photos
  sont stockées dans `public/uploads/users/` (répertoire non versionné) via le service
  `App\Service\UserPhotoUploader`.

## Dons (démonstration)

Page publique de soutien au refuge :

- `GET|POST /faire-un-don` (route `app_donation_new`) — formulaire de don avec **montant libre**,
  coordonnées facultatives du donateur (nom, email, message) et un **faux formulaire bancaire**
  (numéro de carte, expiration, CVC). Le paiement est **simulé** : aucune donnée bancaire n'est
  transmise ni enregistrée, et l'utilisateur n'a pas besoin d'être connecté.
- `GET /faire-un-don/merci` (route `app_donation_thanks`) — page de confirmation après un don.

Chaque don valide est enregistré dans la table `donation` (entité `Donation`) avec son montant
stocké en **centimes** et le statut `DonationStatus::PAID`. Les dons sont consultables et leur
total est affiché dans le back-office (voir la section suivante).

## Avis sur le refuge

Avis généraux portant sur le refuge (et non sur un animal en particulier).

- `GET /avis` (route `app_review_index`) — page **publique** listant les avis, avec la note moyenne
  et le nombre d'avis. Un aperçu des derniers avis est également affiché sur la page d'accueil.
- `GET|POST /avis/nouveau` (route `app_review_new`) — dépôt d'un avis (note de 1 à 5 et
  commentaire), réservé aux utilisateurs connectés (`ROLE_USER`). Les avis sont **publiés
  immédiatement** : il n'y a pas de modération préalable.
- `POST /avis/{id}/supprimer` (route `app_review_delete`) — suppression d'un avis par son auteur
  (ou par un administrateur), protégée par un jeton CSRF.

Les avis d'un utilisateur sont également visibles dans la section « Mes avis » de son profil.

## Contact

Formulaire de contact destiné aux utilisateurs connectés (`ROLE_USER`), accessible depuis l'espace
client (menu utilisateur et page de profil).

- `GET|POST /profil/contact` (route `app_contact`) — envoi d'un message au refuge (sujet et message),
  rattaché au compte de l'utilisateur. La page liste également les messages déjà envoyés et leur état
  de traitement.

Les messages sont enregistrés dans la table `contact_message` (entité `ContactMessage`) et sont
consultables depuis le back-office, où ils peuvent être marqués comme **traités**.

## Configuration

Les variables d'environnement sont définies dans les fichiers `.env` :

| Variable | Description |
| --- | --- |
| `APP_ENV` | Environnement courant (`dev`, `test`, `prod`). |
| `APP_SECRET` | Clé secrète utilisée par Symfony. |
| `DATABASE_URL` | Connexion à la base de données (MySQL/MariaDB). |

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

## Tests

Le projet est couvert par des **tests unitaires** (`tests/Unit/`) et des **tests fonctionnels**
(`tests/Functional/`), exécutés avec **PHPUnit**.

```bash
# 1. Créer et migrer la base de données de test
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# 2. Lancer toute la suite
php bin/phpunit

# Cibler un dossier ou un fichier
php bin/phpunit tests/Unit
php bin/phpunit tests/Functional/ProfileTest.php
```

- Les tests **unitaires** couvrent les énumérations (`Species`, `AnimalStatus`,
  `AdoptionRequestStatus`), les entités (`User`, `Animal`, `AdoptionRequest`) et les services
  (`AnimalSlugger`, `AnimalPhotoUploader`, `UserPhotoUploader`, `AdoptionRequestProcessor`).
- Les tests **fonctionnels** s'appuient sur le `KernelBrowser` de Symfony et vérifient la page
  d'accueil, le contrôle d'accès, l'authentification, l'inscription, le catalogue des animaux et
  le profil utilisateur. Chaque test s'exécute dans une transaction annulée à la fin
  (`DatabaseWebTestCase`) : la base de données n'est pas modifiée.
- En environnement de test, les uploads sont redirigés vers le cache
  (`config/services_test.yaml`) afin de ne pas polluer `public/uploads/`.

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
- **PHPUnit** — tests.

## Feuille de route

- [x] Feature 1 — Base du projet, layout Bootstrap 5 et page d'accueil.
- [x] Feature 2 — Modèle de données (User, Animal, AdoptionRequest).
- [x] Feature 3 — Authentification et gestion des rôles.
- [x] Feature 4 — Catalogue public des animaux.
- [x] Feature 5 — Gestion des animaux (bénévoles).
- [x] Feature 6 — Demandes d'adoption.
- [x] Profil utilisateur (consultation, modification des informations et photo de profil).
- [x] Feature 7 — Back-office EasyAdmin.
- [ ] Connexion Google (OAuth) — retirée de cette version, à réintroduire ultérieurement.
- [x] Feature 9 — Tests unitaires et fonctionnels.
- [x] Page de don (démonstration) et suivi des dons dans le back-office.
- [x] Avis généraux sur le refuge (page publique, dépôt par les utilisateurs, modération en back-office).
- [x] Formulaire de contact dans l'espace client et suivi des messages en back-office.
