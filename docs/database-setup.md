# Setting up a database

PostgreSQL can be a bit of a pain to configure if you don't know where to begin.
Basically, your goal is to create a database, create a user account that has
access to that database, and allow access to that user account over a network
connection (which means it must have a password).

The following instructions have been tested to work on Ubuntu 16.04, but will
probably work on other Debian-based distros too.

## Installation

    $ sudo apt-get install postgresql

## Creating a user account

    $ sudo -u postgres createuser -P <username>

You will be prompted for a password that you'll need later.

## Creating a database

    $ sudo -u postgres createdb -O <username> <database name>

This database will be owned by the user you've specified. We recommend the
database name be the same as the username.

## Testing access

To test that you're able to connect (type `\q` to quit after you log in):

    $ psql -U <username> -h localhost <database name>

You can omit the database name here if it's the same as the username.

## Database URL

Finally, once everything is figured out, the `DATABASE_URL` parameter you're
prompted for during install will look something like this:

    pgsql://<username>:<password>@localhost/<database name>?serverVersion=9.5

The server version should be set to the version of PostgreSQL provided by your
distro. For Ubuntu 16.04, it's '9.5'. If unsure, you can omit this parameter
entirely and things should still work.

## Other resources

* <https://help.ubuntu.com/community/PostgreSQL>
* <https://www.postgresql.org/docs/9.5/static/app-createdb.html>
* <https://www.postgresql.org/docs/9.5/static/app-createuser.html>
