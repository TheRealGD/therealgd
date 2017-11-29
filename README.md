![](web/apple-touch-icon-precomposed.png) Postmill
==================================================

**Postmill** is a free, web-based, social link aggregator with voting and
threaded comments. It is built on the [Symfony](https://symfony.com/) framework.
Any similarities between this software and that of a large community symbolised
by an alien logo are purely coincidental.

## Requirements

* PHP >= 7.0 with the APCu, curl, and PDO_PGSQL extensions.
* PostgreSQL >= 9.3
* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/en/) (>= 8.x preferred)

My dev environment runs PHP 7.1 and PostgreSQL 9.5 under macOS Sierra. If any
compatibility issues with other software versions or operating systems should
arise, a bug report would be most appreciated.

## Getting started

Clone the repository somewhere and navigate there with the command line.

### Building frontend assets

1. Run `npm install`.

2. Run `npm run build-dev`. The `web/build/` directory should now contain some
   files.

### Setting up the backend

1.  Run `composer install`. You should be prompted for database credentials,
    mail sending stuff, and a secret token. You can leave the default values for
    the mail stuff, but you must supply valid database credentials.

    If this step fails, you can remove or edit `app/config/parameters.yml` and
    run `composer install` to try again.

2.  Run `bin/symfony_requirements` to check that your environment meets the
    requirements needed to run the software. Fix any errors that arise.

3.  Run `bin/console doctrine:migrations:migrate` to load the database schema.

4.  Run `bin/console app:user:add <username> --admin` to create a user account.

5.  Run `bin/console server:run` to start the application.

6.  Navigate to <http://localhost:8000/>. Log in with the credentials you chose
    in step 4.

## Reporting issues

* Bugs should be reported on the [issue tracker][issues].
* Feature requests should be discussed on Raddle's [/f/meta][meta].

You can email emma1312@protonmail.ch to disclose or discuss something in private
with the creator of the software.

## Contributions

You are always welcome to submit pull requests for things like bug fixes,
documentation, and new translations. Pull requests for new/altered functionality
are likely to be rejected, as this must be discussed with the community
beforehand.

If you'd like to support me with money, you can send Bitcoins to
`1AXAH2ZaHfVsq2xnbXRN9497FpUAri8x72`.

## License

The software is released under the zlib license. See the `LICENSE` file for
details.


[issues]: https://gitlab.com/edgyemma/Postmill/issues
[meta]: https://raddle.me/f/meta
