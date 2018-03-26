# Setting up a database

If you're reading this, you're taking on a role of DBA at this project.
Congrats, now buckle up and put on your shit kickers.

## Local - Dev Only
Okay, maybe you got scared of that DBA thing and you're just a dev.
Do this:

    $ sudo apt-get install postgresql
    $ sudo -u postgres createuser -P <username>
    $ sudo -u postgres createdb -O <username> <database name>
    # test it's there
    $ psql -U <username> -h localhost <database name>

Modify your .env with following URL:

    pgsql://<username>:<password>@localhost/<database name>?serverVersion=9.6

Enjoy!

## Real Deal

Still here? Awesome. Get Some!

     // Create Users
     CREATE ROLE youwish_devuser WITH LOGIN PASSWORD 'oooh_heees_trying';
     CREATE ROLE youwish_testuser WITH LOGIN PASSWORD 'oooh_heees_trying';
     CREATE ROLE youwish_produser WITH LOGIN PASSWORD 'oooh_heees_trying';
     CREATE ROLE youwish_prodadmin WITH LOGIN PASSWORD 'oooh_heees_trying';
     // Give all other user's permissions to DB master/root user
     GRANT youwish_devuser TO youwish_root;
     GRANT youwish_testuser TO youwish_root;
     GRANT youwish_produser TO youwish_root;
     GRANT youwish_prodadmin TO youwish_root;
     // Give admin prod role everything prod-readWrite has
     GRANT youwish_produser TO youwish_prodadmin;
     // Create Databases
     CREATE DATABASE youwish_prod;
     REVOKE ALL ON DATABASE youwish_prod FROM public;
     CREATE DATABASE youwish_dev;
     REVOKE ALL ON DATABASE youwish_dev FROM public;
     CREATE DATABASE youwish_test;
     REVOKE ALL ON DATABASE youwish_test FROM public;
     // Prod and ProdAdmin can both connect to Prod, but Owner is ProdAdmin
     ALTER DATABASE youwish_prod OWNER to youwish_prodadmin;
     GRANT CONNECT ON DATABASE youwish_prod TO youwish_prodadmin;
     GRANT CONNECT ON DATABASE youwish_prod TO youwish_produser;  
     // Dev and Test user===admin
     ALTER DATABASE youwish_dev OWNER to youwish_devuser;
     ALTER DATABASE youwish_test OWNER to youwish_testuser;
     GRANT CONNECT ON DATABASE youwish_dev TO youwish_devuser;
     GRANT CONNECT ON DATABASE youwish_test TO youwish_testuser;
     // For each db - create schema and default priveleges
     \connect youwish_prod
     CREATE SCHEMA schma AUTHORIZATION youwish_prodadmin;
     SET search_path = schma;
     ALTER ROLE youwish_prodadmin IN DATABASE youwish_prod SET search_path = schma;
     ALTER ROLE youwish_produser IN DATABASE youwish_prod SET search_path = schma;
     GRANT USAGE ON SCHEMA schma TO youwish_produser;
     GRANT CREATE ON SCHEMA schma TO youwish_prodadmin;
     ALTER DEFAULT PRIVILEGES FOR ROLE youwish_prodadmin GRANT INSERT, UPDATE, DELETE, TRUNCATE ON TABLES TO youwish_prod;
     ALTER DEFAULT PRIVILEGES FOR ROLE youwish_prodadmin GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO youwish_prod;
     GRANT TEMP ON DATABASE youwish_prod TO youwish_produser;
     //< REPEAT STEPS ^ABOVE^ FOR test and dev, except combine roles since there is no admin/user distinction

## Links Used for Real Deal

* <https://dba.stackexchange.com/questions/117109/how-to-manage-default-privileges-for-users-on-a-database-vs-schema>
* <https://www.postgresql.org/docs/9.0/static/sql-alterdefaultprivileges.html>
* <https://www.postgresql.org/docs/9.1/static/sql-alterdatabase.html>
* <https://www.postgresql.org/docs/current/static/sql-grant.html>
