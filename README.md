# Dev Dashboard API

![CI](https://github.com/Azzeddine45/dev-dashboard-api/actions/workflows/ci.yml/badge.svg)

API REST en **Symfony 7.4 / PHP 8.4** qui agrège des API externes (GitHub, Open-Meteo) et expose des données normalisées. Elle reste disponible quand une source tombe : cache court, repli sur la dernière copie connue et statut par source.

## Pourquoi ce projet

Montrer une approche de production sur un cas simple :
- une **couche fournisseur** par API externe, derrière une interface, interchangeable et testable ;
- un **cache résilient** commun (données fraîches 5 à 10 min, copie périmée gardée 24 h) ;
- une **dégradation propre** : une source en panne ne fait pas tomber l'agrégation ;
- des **tests sans réseau** (`MockHttpClient`, faux fournisseurs en environnement `test`) ;
- une **CI** qui bloque le code non formaté, mal typé ou non testé.

## Architecture

```
Controller  ->  DashboardAggregator  ->  CachedXxxProvider  ->  XxxProvider  ->  API externe
                                              |
                                        ResilientCache (frais + périmé)
```

- `src/Provider` : une interface + un fournisseur HTTP + un décorateur de cache par source.
- `src/Cache/ResilientCache` : logique « frais, sinon appel, sinon copie périmée », partagée par toutes les sources.
- `src/Service/DashboardAggregator` : collecte les sources et isole les pannes.
- `src/Dto` : objets immuables (`readonly`) renvoyés par les fournisseurs.

## Endpoints

| Méthode | Route | Description |
| --- | --- | --- |
| GET | `/api/github/{username}` | Profil GitHub normalisé |
| GET | `/api/weather?lat=..&lon=..` | Météo actuelle |
| GET | `/api/dashboard?github=..&lat=..&lon=..` | Agrégation des deux sources |

Documentation complète : [`docs/openapi.yaml`](docs/openapi.yaml).

Exemple :

```bash
curl "http://localhost:8000/api/dashboard?github=octocat&lat=47.9&lon=1.9"
```

Chaque source renvoie un statut `ok`, `stale` (données périmées servies faute de mieux) ou `unavailable`.

## Installation

Prérequis : PHP 8.2+ (testé en 8.4), Composer.

```bash
git clone https://github.com/Azzeddine45/dev-dashboard-api.git
cd dev-dashboard-api
composer install
php -S localhost:8000 -t public
```

## Qualité

```bash
composer cs:check   # style (PHP-CS-Fixer)
composer stan       # analyse statique (PHPStan, niveau 6)
composer test       # tests (PHPUnit)
```

Ces trois commandes tournent automatiquement sur chaque push via GitHub Actions.

## Pistes d'évolution

- Dockerisation (PHP-FPM, Nginx) ;
- troisième source (actualités tech) ;
- cache Redis et limitation de débit ;
- front React consommant l'API.
