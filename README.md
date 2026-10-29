# Gestion des sessions de formation

Application interne d'un centre de formation : catalogue de formations, sessions planifiées, inscriptions des stagiaires.

L'énoncé de l'exercice est dans [`ENONCE.md`](ENONCE.md). Vos notes vont dans [`NOTES.md`](NOTES.md).

## Prérequis

- PHP 8.3 ou supérieur, avec les extensions `pdo_sqlite`, `ctype`, `iconv`, `mbstring`
- Composer 2
- git

Pas de PHP sur votre poste ? Docker suffit, voir plus bas.

## Installation

```bash
composer install
composer setup          # crée la base SQLite de dev et charge les fixtures
php -S localhost:8000 -t public
```

Puis ouvrez <http://localhost:8000/sessions> et <http://localhost:8000/api/sessions>.

Si vous avez la [CLI Symfony](https://symfony.com/download), `symfony serve` fonctionne aussi.

### Avec Docker

```bash
docker compose run --rm php composer install
docker compose run --rm php composer setup
docker compose run --rm php composer test
docker compose up        # http://localhost:8000/sessions
```

## Commandes utiles

| Commande | Effet |
|---|---|
| `composer setup` | Recrée la base de dev et recharge les fixtures |
| `composer test` | Recrée la base de test, recharge les fixtures, lance PHPUnit |
| `php bin/phpunit` | Relance PHPUnit seul (la base de test doit exister) |
| `php bin/phpunit --filter NomDuTest` | Lance un seul test |
| `php bin/console debug:router` | Liste les routes |

## Architecture

Le code est organisé par contexte métier (`Session/`), découpé en couches domaine, application et infrastructure (DDD), avec une séparation des commandes (écriture) et des requêtes (lecture) (CQRS) :

```
src/
  Session/
    Domain/                  Modèle métier pur, sans dépendance à Doctrine ni à Symfony
      Model/                 Session, Inscription, Stagiaire, Formation, StatutInscription : règles métier
      Repository/            Interfaces des repositories (SessionRepository, StagiaireRepository)
      Exception/             Exceptions métier (InscriptionImpossibleException)
    Application/             Cas d'usage
      Command/               Écriture : InscrireStagiaire et son handler (command.bus)
      Query/                 Lecture : ListerSessions, son handler (query.bus) et le DTO ResumeSession
      InscriptionService     Façade conservée pour le portail stagiaire, délègue à InscrireStagiaire
    Infrastructure/          Adaptateurs vers l'extérieur
      Doctrine/
        Entity/              Modèle de persistance : entités Doctrine, mapping par attributs PHP
        Mapper/              Conversion des entités Doctrine en objets du domaine
        Repository/          Implémentations Doctrine des repositories du domaine
      Http/                  Une action invokable par route (page HTML, et API JSON dans Api/)
  Shared/                    Briques communes à tous les contextes
    Domain/Exception/        Interfaces RessourceIntrouvableException et RegleMetierEnfreinteException,
                             implémentées par les exceptions métier des contextes
    Application/             BusDeRequetes
    Infrastructure/Http/     ExceptionSubscriber : traduit les exceptions de l'API en JSON {"erreur": "..."}
                             (404, 409, ou le code d'une exception HTTP de Symfony)
  DataFixtures/              Jeu de données de démonstration, utilisé aussi par les tests
templates/                   Vues Twig
tests/                       PHPUnit, même arborescence que src/ : Domain (unitaires), Application
                             et Infrastructure (avec la base de test)
```

Les commandes et les requêtes passent par `symfony/messenger`, en mode synchrone : `command.bus` (avec transaction Doctrine) et `query.bus`, sans transport.

Base de données : SQLite, un fichier par environnement dans `var/`. Pas de migrations : le schéma est créé depuis les entités Doctrine par `composer setup`.

Tests : chaque test s'exécute dans une transaction annulée à la fin (bundle `dama/doctrine-test-bundle`). Les fixtures sont donc toujours dans leur état initial au début de chaque test.

## Règles métier

- Une session a une capacité maximale de places.
- Une inscription a un statut (`StatutInscription`) : confirmée, annulée ou en liste d'attente.
- Une inscription annulée libère sa place.
- Une inscription en liste d'attente ne consomme pas de place.
- Un stagiaire ne peut avoir qu'une seule inscription non annulée par session.

## Conventions du projet

Elles s'appliquent à tout code ajouté ou modifié.

1. **Les règles métier vivent dans le modèle du domaine (`src/Session/Domain`).** Les handlers de la couche application orchestrent ; jamais de règle dans les controllers, les templates Twig ou les entités Doctrine.
2. **Les controllers orchestrent, ils ne calculent pas.** Un controller lit la requête, envoie une commande ou une requête (ou appelle un repository ou la façade), renvoie une réponse. Pas de calcul ni de condition métier dedans.
3. **Un comportement métier est couvert par un test de son propre niveau** (`tests/Session/Domain` ou `tests/Session/Application`), pas uniquement par un test HTTP.
4. **PHP 8.3 moderne** : `declare(strict_types=1)`, typage complet, attributs PHP (pas d'annotations), `DateTimeImmutable`, `readonly` quand c'est pertinent.
5. **Pas de nouvelle dépendance Composer** sans justification dans `NOTES.md`.
6. **Zéro dépréciation.** PHPUnit est configuré avec `failOnDeprecation` : un code qui déclenche une dépréciation fait échouer la suite.
7. **Ne modifiez pas les fixtures** : les tests s'appuient dessus.
8. **Compatibilité** : les routes existantes, leurs codes HTTP et la signature de `InscriptionService::inscrire()` sont utilisés par le portail stagiaire. Vous pouvez enrichir, pas casser. Une règle métier qui bloque une inscription se signale par `InscriptionImpossibleException`, que l'API traduit en `409 Conflict`.

## Dépannage

- `SQLSTATE[HY000]: General error: 1 no such table` : la base n'existe pas ou n'est plus à jour. Relancez `composer setup` (dev) ou `composer test` (test).
- `Permission denied` sur `var/` avec Docker : `sudo chown -R $USER var` ou supprimez `var/` et relancez.
