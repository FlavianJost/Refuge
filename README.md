# Refuge Animalier

Application web de gestion d'un refuge animalier développée avec **Symfony 7.4** (PHP 8.2+).

Elle permet de présenter publiquement les animaux à l'adoption, de recueillir les demandes
d'adoption et les dons, de partager des avis, d'échanger par messagerie interne et de gérer le
refuge via un back-office, avec une gestion fine des rôles (visiteur, utilisateur, bénévole,
administrateur).

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Lancer l'application](#lancer-lapplication)
- [Modèle de données](#modèle-de-données)
- [Authentification et rôles](#authentification-et-rôles)
- [Authentification à deux facteurs (TOTP)](#authentification-à-deux-facteurs-totp)
- [Catalogue des animaux (public)](#catalogue-des-animaux-public)
- [Demandes d'adoption](#demandes-dadoption)
- [Espace utilisateur](#espace-utilisateur)
- [Messagerie](#messagerie)
- [Avis sur le refuge](#avis-sur-le-refuge)
- [Dons (démonstration)](#dons-démonstration)
- [Gestion du refuge (bénévoles)](#gestion-du-refuge-bénévoles)
- [Back-office d'administration (EasyAdmin)](#back-office-dadministration-easyadmin)
- [Tests](#tests)
- [Structure du projet](#structure-du-projet)
- [Stack technique](#stack-technique)

## Fonctionnalités

- Consultation publique des animaux accueillis et de leur fiche détaillée.
- Dépôt d'une demande d'adoption par les utilisateurs connectés.
- Espace profil : informations du compte, modification des données et photo de profil,
  changement de mot de passe et suivi de ses demandes.
- Messagerie interne : fil de discussion entre chaque utilisateur et l'équipe du refuge,
  avec avatars des interlocuteurs, statut en ligne/hors ligne et rafraîchissement automatique
  des nouveaux messages.
- Authentification à deux facteurs (TOTP) activable depuis le profil.
- Avis sur le refuge : consultation publique et dépôt par les utilisateurs connectés.
- Page de don (démonstration) avec enregistrement des dons en base.
- Gestion des animaux et traitement des demandes d'adoption par les bénévoles.
- Back-office d'administration (animaux, demandes, dons, avis, utilisateurs, supervision).
- Niveaux d'accès hiérarchisés : visiteur, utilisateur, bénévole, administrateur.

## Prérequis

- **PHP >= 8.2** avec les extensions `ctype` et `iconv` (requises par Symfony Flex) et `gd`
  (nécessaire au rendu du QR code de l'authentification à deux facteurs).
- **Composer 2**.
- Un serveur **MySQL/MariaDB** (la configuration d'exemple utilise `127.0.0.1:3307`).
- **Symfony CLI** (optionnel, mais recommandé pour lancer le serveur local).

Aucune étape de build front n'est nécessaire : les assets sont gérés par **AssetMapper**
(importmap natif, sans Node.js).

## Installation

```bash
# 1. Cloner le dépôt
git clone git@github.com:FlavianJost/Refuge.git
cd Refuge

# 2. Installer les dépendances PHP
composer install

# 3. Récupérer les assets front (Bootstrap 5, Stimulus, Turbo)
php bin/console importmap:install

# 4. Préparer la base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load   # données de démonstration
```

> `assets/vendor/` n'est pas versionné : l'étape `importmap:install` est donc nécessaire après
> chaque clone.

## Configuration

Les variables d'environnement sont définies dans les fichiers `.env`, complétés par `.env.dev`
et `.env.test` pour les valeurs propres à chaque environnement :

| Variable | Description |
| --- | --- |
| `APP_ENV` | Environnement courant (`dev`, `test`, `prod`). |
| `APP_SECRET` | Clé secrète utilisée par Symfony. |
| `DATABASE_URL` | Connexion à la base de données (MySQL/MariaDB), avec le numéro de version du serveur. |

Pour vos réglages locaux, créez un fichier `.env.local` (non versionné) et **ne committez jamais**
de secrets dans les fichiers `.env` versionnés.

## Lancer l'application

```bash
symfony server:start
```

L'application est ensuite accessible sur l'adresse indiquée par la Symfony CLI (par défaut
`https://127.0.0.1:8000`).

Les assets sont servis dynamiquement en développement. Pour la production, compilez-les :

```bash
php bin/console asset-map:compile
```

## Modèle de données

L'application s'appuie sur **douze entités Doctrine** :

| Entité | Description | Champs principaux |
| --- | --- | --- |
| `User` | Utilisateur de l'application (visiteur, bénévole, administrateur). | email, rôles, mot de passe, prénom, nom, photo, dernière connexion, secret TOTP |
| `LoginRecord` | Trace d'une connexion réussie (date et adresse IP), affichée dans l'espace utilisateur. | utilisateur, adresse IP, date |
| `Animal` | Animal accueilli au refuge. | nom, slug, espèce, race, âge, description, statut, photo, nombre de vues |
| `AnimalPhoto` | Photo supplémentaire rattachée à la galerie d'un animal (la photo principale reste portée par `Animal`). | animal, nom de fichier, date |
| `MedicalRecord` | Acte médical consigné dans le dossier médical d'un animal (accès réservé à l'équipe). | animal, date, type d'acte, motif, détails, vétérinaire |
| `AdoptionRequest` | Demande d'adoption déposée par un utilisateur pour un animal. | animal, utilisateur, message, questionnaire pré-adoption, statut, traitement |
| `AdoptionRequestEvent` | Étape du parcours d'une demande d'adoption (dépôt, prise en charge, acceptation, refus). | demande, statut, commentaire, auteur, date |
| `VisitAppointment` | Rendez-vous de visite proposé par un utilisateur pour une demande d'adoption, confirmé ou refusé par l'équipe. | demande, date proposée, statut, note, réponse, traitée par |
| `Donation` | Don effectué via la page de démonstration (paiement simulé). | montant (en centimes), donateur, email, message, statut, date |
| `Review` | Avis publié par un utilisateur sur le refuge. | utilisateur, note (1 à 5), commentaire, date |
| `Conversation` | Fil de discussion entre un utilisateur et le refuge (un fil par utilisateur). | utilisateur, compteurs de messages non lus, dates |
| `Message` | Message publié dans une conversation, par un utilisateur ou un membre du refuge. | conversation, auteur, texte, émis par l'équipe, date |

Sept énumérations typent les valeurs métier, avec des libellés français :

- `Species` : chien, chat, lapin, oiseau, rongeur, autre.
- `AnimalStatus` : disponible, en cours d'adoption, adopté.
- `AdoptionRequestStatus` : nouvelle, en cours de traitement, acceptée, refusée.
- `DonationStatus` : confirmé, en attente, échoué.
- `MedicalRecordCategory` : vaccination, traitement, soins, visite vétérinaire, chirurgie, autre.
- `HousingType` : appartement, maison, autre.
- `VisitAppointmentStatus` : en attente, confirmé, refusé.

Elles implémentent `TranslatableInterface`, ce qui permet à EasyAdmin d'afficher directement leurs
libellés français dans les formulaires, les listes et les filtres.

### Données de démonstration

Le jeu de données (`AppFixtures`) crée des comptes, des animaux (dont deux homonymes pour illustrer
l'unicité des slugs), leurs photos de couverture et de galerie, un compteur de vues, des dossiers
médicaux, des demandes d'adoption (avec questionnaire, parcours et rendez-vous de visite), un
historique de connexions et des conversations de messagerie. La couverture et la galerie des animaux
se renseignent dans le tableau des fixtures : une URL est téléchargée au chargement dans
`public/uploads/animals/`, ou un simple nom de fichier déjà présent dans ce dossier est utilisé tel quel.

Les comptes disponibles sont :

| Email | Mot de passe | Rôle |
| --- | --- | --- |
| `admin@refuge.test` | `password` | Administrateur |
| `benevole@refuge.test` | `password` | Bénévole |
| `camille@refuge.test` | `password` | Utilisateur |
| `david@refuge.test` | `password` | Utilisateur |

## Authentification et rôles

L'authentification repose sur le composant **Security** de Symfony (fournisseur Doctrine, formulaire
de connexion, déconnexion).

- Inscription : `GET|POST /inscription` (`app_register`).
- Connexion : `GET|POST /login` (`app_login`).
- Déconnexion : `/deconnexion` (`_logout_main`).

Les rôles sont hiérarchisés dans `config/packages/security.yaml` :

| Rôle | Portée |
| --- | --- |
| `ROLE_USER` | Visiteur authentifié (profil, demandes, messagerie, avis). |
| `ROLE_VOLUNTEER` | Bénévole : gestion des animaux, des demandes et de la messagerie. |
| `ROLE_ADMIN` | Administrateur : supervision complète (hérite de `ROLE_VOLUNTEER`). |

La protection CSRF est **sans état (stateless)** pour les formulaires et l'authentification
(`config/packages/csrf.yaml`). La date de dernière connexion est enregistrée automatiquement à
chaque authentification (`App\EventListener\LoginListener`). En complément des `access_control`, les
zones sensibles sont protégées dans les contrôleurs par l'attribut `#[IsGranted]` (défense en
profondeur).

## Authentification à deux facteurs (TOTP)

En complément du mot de passe, chaque utilisateur peut activer une **authentification à deux
facteurs** basée sur le temps (TOTP), compatible avec les applications d'authentification
(Google Authenticator, Authy, FreeOTP...). Elle s'appuie sur le bundle **`scheb/2fa-bundle`** et le
générateur de QR codes **`endroid/qr-code`**.

- `GET|POST /profil/2fa/activer` (`app_profile_2fa_enable`) — affiche un **QR code** (et la clé
  manuelle) à scanner, puis vérifie le premier code avant d'activer la 2FA. Le secret n'est
  enregistré qu'après validation du code.
- `POST /profil/2fa/desactiver` (`app_profile_2fa_disable`) — désactive la 2FA et supprime le secret
  enregistré (protégé par un jeton CSRF).

Une fois la 2FA activée, la connexion (`/login`) demande, dans un second écran (`GET /2fa`, route
`2fa_login`), le code à 6 chiffres généré par l'application (`POST /2fa_check`, route
`2fa_login_check`). En cas d'échec, l'utilisateur peut annuler et se déconnecter.

Le secret est stocké sur l'utilisateur (`totp_secret`, `totp_enabled`), qui implémente
`Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface`. La configuration du bundle se trouve dans
`config/packages/scheb_2fa.yaml` et ses routes dans `config/routes/scheb_2fa.yaml`.

## Catalogue des animaux (public)

- `GET /animaux` (`app_animal_index`) — liste des animaux avec filtres par **espèce** et
  **situation**. Affiche par défaut les animaux disponibles.
- `GET /animaux/{slug}` (`app_animal_show`) — fiche détaillée d'un animal, identifiée par un slug
  unique. Chaque consultation **incrémente un compteur de vues** (`Animal.views`), affiché sur la
  fiche et dans l'espace de gestion.

Le slug est généré à partir du nom via `Symfony\Component\String\Slugger\SluggerInterface` (service
`App\Service\AnimalSlugger`). Plusieurs animaux peuvent porter le **même nom** : en cas de doublon,
un suffixe numérique est ajouté pour garantir l'unicité (`rex`, `rex-2`, `rex-3`, ...).

Ces pages sont accessibles aux visiteurs non authentifiés. Les photographies sont servies depuis
`public/uploads/animals/`. La fiche d'un animal présente une **galerie** : la photo principale
(`Animal.photo`) suivie des photos supplémentaires de la galerie (entité `AnimalPhoto`), affichées
dans un carrousel.

## Demandes d'adoption

### Côté utilisateur connecté (`ROLE_USER`)

- `GET|POST /animaux/{id}/adopter` (`app_adoption_request_new`) — formulaire de demande d'adoption,
  accessible depuis la fiche de l'animal. La demande est **réservée aux utilisateurs connectés** :
  un visiteur est redirigé vers la page de connexion. Elle est rattachée au compte de l'utilisateur
  (pas de saisie de coordonnées) et une demande en doublon pour le même animal est refusée.
  Le formulaire intègre un **questionnaire pré-adoption** (type de logement, jardin, autres animaux,
  enfants, expérience) en plus du message libre.
- `GET /profil/adoptions/{id}` (`app_adoption_request_show`) — suivi d'une demande : statut courant,
  **parcours** complet et **rendez-vous de visite**. Seul le propriétaire de la demande peut y accéder.
- `POST /profil/adoptions/{id}/rendez-vous` (`app_visit_request_new`) — proposition d'une date de
  visite depuis cette page de suivi.

Chaque demande possède un **parcours** (entité `AdoptionRequestEvent`) qui enregistre ses étapes
successives : dépôt par l'utilisateur, puis chaque changement de statut effectué par l'équipe, avec
la date, l'auteur et un commentaire facultatif. L'utilisateur peut également **proposer un rendez-vous
de visite** (entité `VisitAppointment`) ; l'équipe le confirme ou le refuse.

### Côté bénévole (`ROLE_VOLUNTEER`)

- `GET /gestion/demandes` (`app_adoption_manage_index`) — liste des demandes avec filtre par statut.
- `GET /gestion/demandes/{id}` (`app_adoption_manage_show`) — détail d'une demande, de son parcours et
  de ses rendez-vous.
- `POST /gestion/demandes/{id}/statut` (`app_adoption_manage_status`) — changement de statut, avec un
  commentaire facultatif ajouté au parcours.
- `POST /gestion/demandes/{id}/rendez-vous/{appointment}/statut` (`app_adoption_manage_visit_status`) —
  confirmation ou refus d'un rendez-vous de visite, avec une réponse facultative.

Les transitions sont gérées par le service `App\Service\AdoptionRequestProcessor` :

- **Acceptée** : l'animal passe au statut *Adopté* et les autres demandes en cours pour cet animal
  sont automatiquement refusées.
- **Refusée** : la demande est clôturée, horodatée et associée au bénévole.
- **Nouvelle / En cours** : la demande est réouverte ; si l'animal n'a plus de demande acceptée, il
  redevient *Disponible*.

## Espace utilisateur

Espace personnel des utilisateurs connectés (`ROLE_USER`), protégé par `access_control` et
l'attribut `#[IsGranted]`.

- `GET /profil` (`app_profile`) — affiche les informations du compte (nom, email, rôle, date
  d'inscription, dernière connexion, nombre de demandes), le suivi de ses demandes d'adoption ainsi
  que ses avis. Un lien mène à sa **messagerie** et une section « Sécurité » permet de gérer
  l'**authentification à deux facteurs**.
- `GET|POST /profil/modifier` (`app_profile_edit`) — permet de modifier le prénom, le nom et
  l'adresse email, d'ajouter ou remplacer une **photo de profil** (JPEG, PNG ou WebP, 4 Mo max) et de
  **changer le mot de passe** (le mot de passe actuel est exigé ; laissez les champs vides pour le
  conserver). Les photos sont stockées dans `public/uploads/users/` via le service
  `App\Service\PhotoUploader`.
- `GET|POST /animaux/{id}/adopter` (`app_adoption_request_new`) — dépôt d'une demande d'adoption.
- `GET|POST /profil/messages` (`app_message_index`) — messagerie avec l'équipe du refuge.
- `GET|POST /avis/nouveau` (`app_review_new`) et `POST /avis/{id}/supprimer` (`app_review_delete`) —
  dépôt et suppression d'un avis.
- `GET /profil/export` (`app_profile_export`) — **export RGPD** : téléchargement d'un fichier JSON
  contenant le profil, les demandes d'adoption (avec parcours et rendez-vous), les avis, les messages
  et l'historique de connexions.
- `POST /profil/supprimer` (`app_profile_delete`) — **suppression du compte** (après confirmation du
  mot de passe), avec effacement en cascade des données associées.

L'espace utilisateur affiche également un **historique des connexions** (entité `LoginRecord`) : les
dix dernières connexions réussies avec leur date et leur adresse IP.

## Messagerie

Fil de discussion entre chaque utilisateur connecté (`ROLE_USER`) et l'équipe du refuge (bénévoles
`ROLE_VOLUNTEER` et administrateurs). Les échanges se font directement dans l'application, sans email.

### Côté utilisateur

- `GET|POST /profil/messages` (`app_message_index`) — affiche le fil de discussion et permet
  d'envoyer un message au refuge.
- `GET /profil/messages/nouveaux` (`app_message_latest`) — endpoint JSON utilisé pour le
  rafraîchissement automatique.

### Côté équipe du refuge (`ROLE_VOLUNTEER`)

- `GET /gestion/messages` (`app_message_manage_index`) — liste des conversations triées par activité,
  avec le nombre de messages non lus.
- `GET|POST /gestion/messages/{id}` (`app_message_manage_show`) — consultation d'une conversation et
  réponse de l'équipe.
- `GET /gestion/messages/{id}/nouveaux` (`app_message_manage_latest`) — endpoint JSON de
  rafraîchissement.

Les échanges sont enregistrés dans les tables `conversation` et `message` (entités `Conversation` et
`Message`). Chaque utilisateur dispose d'une seule conversation ; les compteurs `unread_for_user` et
`unread_for_staff` alimentent les badges de messages non lus. Les nouveaux messages sont chargés
automatiquement toutes les quelques secondes par un contrôleur Stimulus
(`assets/controllers/message_poll_controller.js`), ce qui donne une impression de discussion en
direct, sans WebSocket.

Les interlocuteurs sont identifiés par un **avatar** (photo de profil, à défaut les initiales) et un
**statut en ligne / hors ligne**. Ce statut s'appuie sur la colonne `last_seen_at` de l'utilisateur,
mise à jour à chaque requête authentifiée par `src/EventListener/UserActivityListener.php` (au plus
une fois par minute). Un utilisateur est considéré en ligne s'il a été vu il y a moins de cinq
minutes ; côté utilisateur, l'équipe du refuge est affichée comme en ligne si au moins un bénévole
l'est.

## Avis sur le refuge

Avis généraux portant sur le refuge (et non sur un animal en particulier).

- `GET /avis` (`app_review_index`) — page **publique** listant les avis, avec la note moyenne et le
  nombre d'avis. Un aperçu des derniers avis est également affiché sur la page d'accueil.
- `GET|POST /avis/nouveau` (`app_review_new`) — dépôt d'un avis (note de 1 à 5 et commentaire),
  réservé aux utilisateurs connectés (`ROLE_USER`). Les avis sont **publiés immédiatement** : il n'y
  a pas de modération préalable.
- `POST /avis/{id}/supprimer` (`app_review_delete`) — suppression d'un avis par son auteur (ou par un
  administrateur), protégée par un jeton CSRF.

Les avis d'un utilisateur sont également visibles dans la section « Mes avis » de son profil.

## Dons (démonstration)

Page publique de soutien au refuge :

- `GET|POST /faire-un-don` (`app_donation_new`) — formulaire de don avec **montant libre**,
  coordonnées facultatives du donateur (nom, email, message) et un **faux formulaire bancaire**
  (numéro de carte, expiration, CVC). Le paiement est **simulé** : aucune donnée bancaire n'est
  transmise ni enregistrée, et l'utilisateur n'a pas besoin d'être connecté.
- `GET /faire-un-don/merci` (`app_donation_thanks`) — page de confirmation après un don.

Chaque don valide est enregistré dans la table `donation` (entité `Donation`) avec son montant stocké
en **centimes** et le statut `DonationStatus::PAID` (libellé « Confirmé »). Les dons sont consultables
et leur total est affiché dans le back-office.

## Gestion du refuge (bénévoles)

Espace réservé aux utilisateurs disposant du rôle `ROLE_VOLUNTEER` (et `ROLE_ADMIN`).

### Animaux

- `GET /gestion/animaux` (`app_animal_manage_index`) — tableau de bord des animaux.
- `GET|POST /gestion/animaux/nouveau` (`app_animal_manage_new`) — ajout d'un animal.
- `GET|POST /gestion/animaux/{id}/modifier` (`app_animal_manage_edit`) — édition.
- `POST /gestion/animaux/{id}/photos/{photo}/supprimer` (`app_animal_manage_delete_photo`) —
  suppression d'une photo de la galerie.
- `POST /gestion/animaux/{id}/supprimer` (`app_animal_manage_delete`) — suppression avec jeton CSRF.

L'upload de photo est pris en charge par le service `App\Service\PhotoUploader` : les fichiers
(JPEG, PNG ou WebP, 4 Mo max) sont enregistrés dans `public/uploads/animals/`. La photo principale
précédente est supprimée lors d'un remplacement. Le formulaire permet également d'**ajouter
plusieurs photos** à la galerie d'un animal en une seule fois ; chaque photo y est ensuite listée et
peut être supprimée individuellement.

### Dossier médical

Chaque animal dispose d'un **dossier médical** accessible uniquement à l'équipe (`ROLE_VOLUNTEER`) :

- `GET|POST /gestion/animaux/{id}/medical` (`app_medical_manage_index`) — historique des actes et
  ajout d'un nouvel acte.
- `GET|POST /gestion/animaux/{id}/medical/{record}/modifier` (`app_medical_manage_edit`) — édition.
- `POST /gestion/animaux/{id}/medical/{record}/supprimer` (`app_medical_manage_delete`) — suppression
  avec jeton CSRF.

Chaque acte est enregistré dans la table `medical_record` (entité `MedicalRecord`) avec sa date, son
type (`MedicalRecordCategory`), son motif, ses détails et le vétérinaire concerné.

### Demandes d'adoption et messagerie

- `GET /gestion/demandes` et `GET|POST /gestion/demandes/{id}` — suivi des demandes (voir la section
  [Demandes d'adoption](#demandes-dadoption)).
- `GET /gestion/messages` et `GET|POST /gestion/messages/{id}` — réponses aux utilisateurs (voir la
  section [Messagerie](#messagerie)).

## Back-office d'administration (EasyAdmin)

Espace réservé aux utilisateurs disposant du rôle `ROLE_ADMIN`, généré avec **EasyAdmin 5** sous le
chemin `/admin` (route `admin`, protégée par `access_control` et l'attribut `#[IsGranted]`).

- `/admin` — tableau de bord (nombre d'animaux, de demandes d'adoption et d'utilisateurs, nombre et
  total des dons, nombre d'avis, nombre de conversations et de messages non lus).
- Gestion **des animaux**, **des demandes d'adoption**, **des dons**, **des avis** et **des
  utilisateurs** via les contrôleurs CRUD d'EasyAdmin. La **messagerie** est gérée depuis une page
  dédiée (voir la section [Messagerie](#messagerie)).

Le tableau de bord et les contrôleurs CRUD se trouvent dans `src/Controller/Admin/` et configurent
l'affichage en français (libellés, badges de statut, filtres). Le changement de statut d'une demande
depuis le back-office réutilise la logique métier du service `App\Service\AdoptionRequestProcessor`
(statut de l'animal, refus des demandes concurrentes), et le slug d'un animal est (re)généré
automatiquement à l'enregistrement.

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
  (`AnimalSlugger`, `PhotoUploader`, `AdoptionRequestProcessor`).
- Les tests **fonctionnels** s'appuient sur le `KernelBrowser` de Symfony et vérifient la page
  d'accueil, le contrôle d'accès, l'authentification, l'inscription, le catalogue des animaux et le
  profil utilisateur. Chaque test s'exécute dans une transaction annulée à la fin
  (`DatabaseWebTestCase`) : la base de données n'est pas modifiée.
- En environnement de test, les uploads sont redirigés vers le cache (`config/services_test.yaml`)
  afin de ne pas polluer `public/uploads/`.

## Structure du projet

```
assets/            # Sources JS/CSS (Bootstrap 5, contrôleurs Stimulus, styles du thème)
assets/controllers # Contrôleurs Stimulus (ex. rafraîchissement de la messagerie)
importmap.php      # Déclaration des paquets front (bootstrap, stimulus, turbo...)
config/            # Configuration Symfony (packages, routes, services)
migrations/        # Migrations Doctrine
public/            # Point d'entrée (index.php), assets compilés et uploads
src/
  Controller/      # Contrôleurs (organisés par domaine : Animals, Adoptions, Message...)
  Entity/          # Entités Doctrine
  Enum/            # Énumérations métier
  Form/            # Formulaires Symfony (organisés par domaine)
  Repository/      # Repositories Doctrine
  Service/         # Services applicatifs (upload, slug, traitement des demandes)
  EventListener/   # Écouteurs d'événements (connexion)
  DataFixtures/    # Jeu de données de démonstration
templates/         # Templates Twig (layout de base + dossiers par domaine)
tests/             # Tests unitaires et fonctionnels
```

Le projet adopte le pattern **MVC**, avec les entités Doctrine comme modèle, les contrôleurs Symfony
comme couche de contrôle et les templates Twig pour la vue. Les assets front sont servis par
**AssetMapper** (importmap natif) et le style repose sur **Bootstrap 5**.

## Stack technique

- **Symfony 7.4** — framework principal.
- **Doctrine ORM + Migrations** — persistance et migrations.
- **Twig** — moteur de templates.
- **AssetMapper + Stimulus + Turbo + Bootstrap 5** — assets front, interactivité et interface
  responsive.
- **EasyAdmin 5** — back-office d'administration.
- **scheb/2fa-bundle + scheb/2fa-totp** — authentification à deux facteurs (TOTP).
- **endroid/qr-code** — génération des QR codes d'authentification.
- **PHPUnit** — tests unitaires et fonctionnels.
