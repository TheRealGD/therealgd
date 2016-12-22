raddit
======

raddit is a reddit clone made with [Symfony](https://symfony.com/).

This might end up being the backend for <http://raddit.me>. If not, the project
will be renamed and most likely abandoned.

## Status

This is an unfinished pre-release version. Functionality is missing and 
backwards compatibility will break.

## Requirements

* PHP >= 5.6 with the PDO_PGSQL extension
* PostgreSQL >= 9.2
* [Composer](https://getcomposer.org/)

My dev environment runs PHP 7.1 and PostgreSQL 9.4 under macOS Sierra. If any
compatibility issues with other software versions or operating systems should
arise, a bug report would be most appreciated.

## Getting started

1. Clone the repository somewhere.
2. Run `composer install`. You should be prompted for database credentials, mail
   sending stuff, and a secret token. You can leave the default values for the
   mail stuff, but you must supply valid database credentials.

   If this step fails, you can remove or edit `app/config/parameters.yml` and
   run `composer install` to try again.
3. Run `bin/symfony_requirements` to check that your environment meets the
   requirements needed to run the software. Fix any errors that arise.
4. Run `bin/console doctrine:schema:create` to load the database schema.
5. Run `bin/console doctrine:fixtures:load` to load example data to play around
   with.
6. Run `bin/console server:run` to start the application.
7. Navigate to <http://localhost:8000/>. Log in with `emma`/`goodshit`.

## Contributions

Before contributing new features, please open an issue so we can discuss the
direction in which to take the project and avoid hurt feelings. Bug fixes are 
always welcome, however.

## License

The software is released under the zlib license. See the `LICENSE` file for 
details.
