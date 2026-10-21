# Azurine — boutique maquillage & montres (PHP)

Site dynamique en français (Suisse), prix en **CHF**, prévu pour être déposé dans `htdocs/daris2/` et servi sous `/daris2/`.

## Prérequis

- PHP 8.2+ (extensions : `pdo_sqlite`, `mbstring`, `json`)
- Apache avec `mod_rewrite` **ou** serveur intégré PHP pour le développement

## Installation

```bash
cd htdocs/daris2
cp .env.example .env
# Éditez .env : APP_SECRET (32+ caractères), coordonnées, APP_URL en production
composer install   # optionnel (autoload de secours sans Composer)
```

Le dossier `var/` doit être inscriptible (sessions, SQLite, mails de dev, secret local).

## Développement local

Depuis la racine du dépôt :

```bash
php -S localhost:8080 -t htdocs htdocs/daris2/router.php
```

Ouvrir [http://localhost:8080/daris2/](http://localhost:8080/daris2/).

## Apache

Copier le dossier `daris2` dans `htdocs`. Le fichier `.htaccess` réécrit les URLs vers `index.php`. Les dossiers `src/`, `config/`, `data/`, `templates/` et `var/` ne sont pas accessibles depuis le web.

## Fonctionnalités

- Catalogue JSON (maquillage, montres), fiches produit, panier, codes promo, commande (SQLite)
- Paiement simulé, e-mails (fichiers dans `var/mail/` si `MAIL_ENABLED=false`)
- Conseillère de style (FAQ + réponses guidées), contact, newsletter
- CSRF, en-têtes de sécurité, limitation de débit, honeypot anti-spam

## Vérifications

```bash
find htdocs/daris2 -name '*.php' -print0 | xargs -0 -n1 php -l
```
