# How to upgrade Postmill

This document will help you upgrade from one version of Postmill to another.

## Step one: activate maintenance mode

If you're upgrading a live site, you should activate maintenance mode. This is
done by running `bin/console app:maintenance`.

## Step two: pulling the latest version

Assuming your Postmill install is managed with git, you should be able to run
`git pull`. If you've changed any of Postmill's version-managed files, this will
fail and you'll be on your own to fix it.

## Step three: determining which steps to perform

Looking at the files that have been updated by pulling the latest changes, you
can use this list to determine which steps have to be performed next:

| If these files have changed...      | ... then                          |
|-------------------------------------|-----------------------------------|
| `assets/*`                          | Rebuild assets                    |
| `package.json`, `package-lock.json` | Run `npm install`, rebuild assets |
| `composer.json`, `composer.lock`    | Run `composer install`            |
| `config/app_routes/*`               | Run `composer install`*           |
| `src/Migrations/*`                  | Perform database migrations       |
| `translations/*`                    | Run `composer install`*           |

*) Only necessary in production

### Rebuilding assets

If you've determined you need to rebuild assets, this is done by running
`npm run build-dev` for a dev environment or `npm run build-prod` for a
production site.

If you need to run `npm install`, this should be done before rebuilding assets.

### Performing database migrations

Migrations are performed by running `bin/console doctrine:migrations:migrate`.

If you need to run `composer install`, this should be done before running
migrations.

## Step four: Clearing cache

It is always a good idea to always clear the cache after performing updates.
Simply run `bin/console cache:clear`.

## Step five: deactivating maintenance mode

Run `bin/console app:maintenance -d` to deactivate maintenance mode. Your site
should hopefully return to normal, and be running on the latest version of
Postmill.

## About more elaborate updates

Some updates bring with them big changes, e.g. a new location for the front
controller or a completely new application structure. In these cases, it's
probably best to back up your data and go for a clean install instead.
