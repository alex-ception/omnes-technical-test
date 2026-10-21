# Exercice technique : gestion des sessions de formation

**Durée indicative : 1 heure.** Il n'y a pas de chronomètre. L'exercice est dimensionné pour tenir dans ce temps, inutile d'y passer la soirée.

**L'usage d'assistants IA est autorisé et encouragé** (ChatGPT, Claude, Copilot, Cursor, ou autre). Nous évaluons votre façon de travailler autant que le résultat.

## Contexte

Vous rejoignez l'équipe qui maintient l'application interne de gestion des sessions de formation. Elle expose :

- une page `/sessions` qui liste les sessions pour l'équipe pédagogique ;
- une API `/api/sessions` consommée par le portail stagiaire.

Commencez par lire le `README.md` : installation, architecture, conventions et règles métier du projet.

## Ticket n° 142 : places restantes et sessions complètes

**Signalé par** : l'équipe pédagogique
**Priorité** : haute

> 1. Le nombre de places restantes affiché sur la liste des sessions est faux pour certaines sessions. Merci de corriger.
> 2. Aujourd'hui, un stagiaire peut encore s'inscrire à une session qui n'a plus de place. Il faut l'empêcher.

L'équipe pédagogique a apporté une précision sur ce ticket. Elle figure dans le message qui accompagnait le lien de ce dépôt et fait partie du ticket.

## Ce que nous attendons

- Le code respecte les conventions décrites dans le `README.md`.
- `composer test` passe. Un test échoue au départ : c'est le bug du point 1, il doit passer à la fin.
- Vous ajoutez les tests que vous jugez utiles pour couvrir ce que vous avez fait.
- Vous remplissez `NOTES.md`. Si un point de l'énoncé vous paraît ambigu, tranchez et notez votre hypothèse. Nous ne répondrons pas aux questions pendant l'exercice : votre hypothèse écrite vaut réponse.
- Vous commitez au fil de l'eau, avec des messages clairs. Plusieurs petits commits valent mieux qu'un seul.

## Rendu

Au choix :

- un lien vers un dépôt git privé auquel vous nous donnez accès ;
- une archive zip du projet, **sans** les dossiers `vendor/` et `var/`, en conservant le dossier `.git/`.

Bon courage.
