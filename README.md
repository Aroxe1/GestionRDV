# Salon Éclat · Gestionnaire de réservations

Application web de réservation en ligne pour un petit commerce (ici, un salon de coiffure). Les clients réservent un créneau en quelques clics, à toute heure, et la commerçante suit son planning en un coup d'œil, sans double réservation possible.

Projet personnel réalisé avec **Laravel** dans le cadre de ma formation en BUT Informatique.

## Le problème résolu

Un petit commerçant perd du temps au téléphone, note ses rendez-vous sur un carnet et risque les doubles réservations. Ses clients, eux, ne peuvent pas réserver le soir ou le week-end.

## Fonctionnalités

**Côté client**
- Inscription, connexion et déconnexion
- Choix d'une prestation et d'une date, avec affichage des **créneaux réellement disponibles**
- Confirmation de la réservation
- Page « Mes réservations » : rendez-vous à venir, historique, annulation

**Côté commerçant**
- Planning hebdomadaire (FullCalendar) avec tous les rendez-vous
- Annulation d'un rendez-vous en un clic (le créneau se libère automatiquement)
- Pages protégées : réservées au rôle `merchant`

**Page d'accueil publique**
- Prestations (durée, prix) et horaires d'ouverture lus directement dans la base de données
- Affichage adapté au téléphone (responsive)

## Captures d'écran

| Accueil | Choix du créneau |
|---|---|
| ![Accueil](docs/screenshots/accueil.png) | ![Réservation](docs/screenshots/reservation.png) |

| Mes réservations | Planning du commerçant |
|---|---|
| ![Mes réservations](docs/screenshots/mes-reservations.png) | ![Planning](docs/screenshots/planning.png) |

## Technologies

- **Laravel 13** (PHP 8.2+), architecture MVC
- **Laravel Breeze** (Blade) pour l'authentification
- **Tailwind CSS** et **Vite** pour le front
- **FullCalendar 6** pour le planning
- **SQLite** par défaut (MySQL possible via le fichier `.env`)
- **PHPUnit** pour les tests automatisés

## Points techniques intéressants

**Calcul des créneaux disponibles** : la classe `App\Services\SlotGenerator` croise les horaires d'ouverture du jour, la durée de la prestation choisie et les réservations existantes. Elle propose un créneau toutes les 30 minutes, écarte les créneaux passés et ceux qui déborderaient après la fermeture.

**Anti double réservation** : deux plages horaires se chevauchent si le début de l'une est avant la fin de l'autre **et** que sa fin est après le début de l'autre. Cette règle est appliquée à l'affichage des créneaux, puis vérifiée une seconde fois, dans une transaction, juste avant l'enregistrement.

**Sécurité**
- Middleware `auth` et middleware de rôle `merchant` sur les pages du commerçant (erreur 403 sinon)
- Un client ne peut annuler que ses propres réservations (vérification côté serveur)
- Protection CSRF sur tous les formulaires, validation des données reçues

**Annulation sans suppression** : une réservation annulée change de statut (`cancelled`) au lieu d'être effacée, ce qui conserve l'historique.

## Modèle de données

| Table | Rôle |
|---|---|
| `users` | comptes, avec une colonne `role` (`client` ou `merchant`) |
| `services` | prestations : nom, durée en minutes, prix en centimes |
| `business_hours` | horaires d'ouverture par jour de la semaine |
| `reservations` | client, prestation, début, fin, statut |

Relations : un utilisateur et un service ont plusieurs réservations ; chaque réservation appartient à un utilisateur et à un service.

## Installation

Prérequis : PHP 8.2 ou plus (extensions `fileinfo`, `mbstring`, `openssl`, `curl`, `zip`, `pdo_sqlite`), Composer, Node.js.

```bash
git clone https://github.com/Aroxe1/GestionRDV.git
cd GestionRDV

composer install
cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate:fresh --seed

npm install
```

Lancer le projet dans deux terminaux :

```bash
php artisan serve
```
```bash
npm run dev
```

Puis ouvrir http://127.0.0.1:8000.

## Comptes de démonstration

Le jeu de données (services, horaires, clients et réservations autour de la date du jour) est créé par `php artisan migrate:fresh --seed`.

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Commerçante | `commercant@demo.fr` | `password` |
| Client | `client@demo.fr` | `password` |

Ces adresses sont fictives. Les réservations de démo étant générées par rapport à la date du jour, relance `php artisan migrate:fresh --seed` pour recentrer le planning sur la semaine en cours.

## Tests

```bash
php artisan test
```

Les tests couvrent les règles importantes : jour fermé sans créneau, blocage d'une prestation d'une heure par un rendez-vous voisin, libération du créneau après annulation, refus de la double réservation, et contrôle des droits d'accès (un client ne peut ni annuler la réservation d'un autre ni ouvrir le planning).

## Pistes d'amélioration

- E-mail de confirmation et rappel avant le rendez-vous (SMS ou e-mail)
- Verrou en base de données (`lockForUpdate`) pour garantir l'unicité du créneau même en cas de réservations strictement simultanées
- Gestion de plusieurs coiffeurs, chacun avec son planning
- Paiement d'un acompte en ligne
- Déplacement d'un rendez-vous par glisser-déposer dans le planning
- Déploiement en ligne

## Auteur

**Maxence Royon** · étudiant en BUT Informatique (Université d'Artois), à la recherche d'une alternance en développement.
GitHub : [Aroxe1](https://github.com/Aroxe1)
