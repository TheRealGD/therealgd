**Notice**: Now that the community Postmill was created for has become
unpalatable for me, the future of this project is uncertain. Previously, I
could spend time implementing a feature or improving something, and see the
community embrace (or, on occasion, deride) my hard work. Now that I have no
community, this is no longer the case, and I cannot imagine what motivation I
could have to keep working on this project.

For now, if you want to report issues or submit pull requests, I'll take a look
at them. Any security holes I become aware of (either in Postmill itself or in
its dependencies) will be fixed. But active development probably won't happen
unless someone steps up. Consider Postmill to be on life support.

I'm passionate about building communities, and I will probably make another
effort to build something cool in the future, using the experience I made from
Postmill. Until then, I'm taking a break.

Thanks to everyone who contributed to Postmill, or merely sent kind words my
way. You made it all worth it.

-- Emma

***

# ![](docs/assets/postmill-128.png) Postmill

**Postmill** is a free, web-based, social link aggregator with voting and
threaded comments. It is built on the [Symfony](https://symfony.com/) framework.
Any similarities between this software and that of a large community symbolised
by an alien logo are purely coincidental.

## Requirements

* PHP >= 7.1 with the APCu, curl, gd, mbstring, PDO_PGSQL and xml extensions.
* PostgreSQL >= 9.3
* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/en/) (>= 8.x preferred)

Postmill should be able to run under any Unix environment. It has been tested to
work under Linux, macOS, and Windows 10's Linux subsystem. Running directly on
Windows may work, but is unsupported.

## Getting started

Clone the repository somewhere and navigate there with the command line.

### Building frontend assets

1. Run `npm install`.

2. Run `npm run build-dev`. The `public/build/` directory should now contain
   some files.

### Setting up the backend

1.  Run `composer install`.

2.  You should now have a `.env` file in the project root. Edit this to your
    liking or leave the defaults alone, but you *must* change `DATABASE_URL`.

    Instructions for setting up a database can be found at
    [docs/database-setup.md](docs/database-setup.md).

3.  Run `vendor/bin/requirements-checker` to ensure your environment meets
    necessary requirements needed to run Postmill. Fix any errors that arise.

4.  Run `bin/console doctrine:migrations:migrate` to load the database schema.

5.  Run `bin/console app:user:add <username> --admin` to create a user account.

6.  Run `bin/console server:run` to start the application.

7.  Navigate to <http://localhost:8000/>. Log in with the credentials you chose
    in step 2.

## Reporting issues

* Bugs and feature requests should be reported on the [issue tracker][issues].
* Get support on Postmill's Matrix channel: [#postmill:matrix.org][matrix].

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
[matrix]: https://matrix.to/#/#postmill:matrix.org
