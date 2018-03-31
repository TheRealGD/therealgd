**Notice**: New Fork of Postmill for gundeals.io project.
Lets see what we can do ladies an gents!
Message @TKsM151 in OUR discord to become a collaborator/dev team member.

***

# ![](docs/assets/postmill-128.png) Postmill

**Postmill** is a free, web-based, social link aggregator with voting and threaded comments.
It is built on the [Symfony](https://symfony.com/) framework.

## Requirements

* For AWS - Use Image provided by @psineur for QuickStart
* It's based on Ubuntu 16.04 and installs PHP7.1 Postgress and other stuff that is required.
* For prev. README and requirements - see README-old.md

## Getting started

Before you start hacking - run `git config --global --edit` and set up your name correctly, please.
We also recommend you forward your SSH client to your github from you computer instead of storing any keys on devbox directly.
(You will need to setup .ssh forwarding in .ssh/config on your local machine).

* Image linked in Discord should provide everything you need, make sure to pull latest master branch.
* Then simply Run `cp .env.dev .env; sudo bin/console server:run *:80` to start the application.
* Navigate to <http://YOURBOXIP:80/>. Log in with:
  * gundealsdev
  * senditree
9. dbname and pass is in .env file

## Development in docker-compose

There is a docker-compose file in the main directory that will let you run everything locally from the repo.

To get started:

1. install Docker
2. run `docker-compose up` from the main directory

If you don't have php or node handy:
* run `scripts/assets.sh && scripts/vendor.sh`
* `docker-compose run php bash -c './bin/console assets:install'`
* `docker-compose run php bash -c './bin/console assets:install'`
* `docker-compose run php bash -c './bin/console doctrine:migrations:migrate'`

## License

The software is released under the zlib license. See the `LICENSE` file for
details.

## GUNDEALS MAIN REPO:
https://github.com/TheRealGD/therealgd
Please request access from @TKsM151 for this repo.
Please use pull requests from your own fork for any non-trivial changes, so we can have code review.
@psineur volunteers to help with code-review.

## ORIGINAL AUTHOR:

* https://gitlab.com/edgyemma/Postmill
