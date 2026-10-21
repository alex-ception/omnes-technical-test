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

```
src/
  Controller/        Controllers HTTP (page HTML et API JSON)
  Entity/            Modèle : Formation, Session, Stagiaire, Inscription, StatutInscription
  Repository/        Accès aux données
  Service/           Services métier (InscriptionService)
  Exception/         Exceptions métier
  DataFixtures/      Jeu de données de démonstration, utilisé aussi par les tests
templates/           Vues Twig
tests/               PHPUnit : Entity (unitaires), Service et Controller (avec la base de test)
```

Base de données : SQLite, un fichier par environnement dans `var/`. Pas de migrations : le schéma est créé depuis les entités par `composer setup`.

Tests : chaque test s'exécute dans une transaction annulée à la fin (bundle `dama/doctrine-test-bundle`). Les fixtures sont donc toujours dans leur état initial au début de chaque test.

## Règles métier

- Une session a une capacité maximale de places.
- Une inscription a un statut (`StatutInscription`) : confirmée, annulée ou en liste d'attente.
- Une inscription annulée libère sa place.
- Une inscription en liste d'attente ne consomme pas de place.
- Un stagiaire ne peut avoir qu'une seule inscription non annulée par session.

## Conventions du projet

Elles s'appliquent à tout code ajouté ou modifié.

1. **Les règles métier vivent dans le modèle (`src/Entity`) ou dans les services (`src/Service`).** Jamais dans les controllers, jamais dans les templates Twig.
2. **Les controllers orchestrent, ils ne calculent pas.** Un controller lit la requête, appelle un repository ou un service, renvoie une réponse. Pas de calcul ni de condition métier dedans.
3. **Un comportement métier est couvert par un test de son propre niveau** (`tests/Entity` ou `tests/Service`), pas uniquement par un test HTTP.
4. **PHP 8.3 moderne** : `declare(strict_types=1)`, typage complet, attributs PHP (pas d'annotations), `DateTimeImmutable`, `readonly` quand c'est pertinent.
5. **Pas de nouvelle dépendance Composer** sans justification dans `NOTES.md`.
6. **Zéro dépréciation.** PHPUnit est configuré avec `failOnDeprecation` : un code qui déclenche une dépréciation fait échouer la suite.
7. **Ne modifiez pas les fixtures** : les tests s'appuient dessus.
8. **Compatibilité** : les routes existantes, leurs codes HTTP et la signature de `InscriptionService::inscrire()` sont utilisés par le portail stagiaire. Vous pouvez enrichir, pas casser. Une règle métier qui bloque une inscription se signale par `InscriptionImpossibleException`, que l'API traduit en `409 Conflict`.

## Dépannage

- `SQLSTATE[HY000]: General error: 1 no such table` : la base n'existe pas ou n'est plus à jour. Relancez `composer setup` (dev) ou `composer test` (test).
- `Permission denied` sur `var/` avec Docker : `sudo chown -R $USER var` ou supprimez `var/` et relancez.
