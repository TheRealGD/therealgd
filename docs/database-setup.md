# Setting up a database

If you're reading this, you're taking on a role of DBA at this project.
Congrats, now buckle up and put on your shit kickers.

## Local - Dev Only
Okay, maybe you got scared of that DBA thing and you're just a dev.
Do this:

    $ sudo apt-get install postgresql
    $ sudo -u postgres createuser -P youwish_root
    $ sudo -u postgres createdb -O youwish_root youwish_devlocal
    # test it's there
    $ psql -U youwish_root -h localhost youwish_devlocal

Modify your .env with following URL:

    pgsql://youwish_root:SRONGPASSWORDlol@localhost/youwish_devlocal?serverVersion=9.6

Enjoy!

## Real Deal

> DO NOT SIMPLY COPY-PASTE!
> CHANGE ROLES (youwish_devuser, youwish_testuser, etc) AND PASSWORD/DB VALUES.
> IF YOU NEED ONLY dev (test, prod) - DO ONLY dev (test, prod)
> DON'T FORGET TO ADD REAL VALUES TO PASSWORD SAFE if it's prod or shared dbs.

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
     SET search_path = schma,public;
     ALTER ROLE youwish_prodadmin IN DATABASE youwish_prod SET search_path = schma,public;
     ALTER ROLE youwish_produser IN DATABASE youwish_prod SET search_path = schma,public;
     GRANT USAGE ON SCHEMA schma TO youwish_produser;
     GRANT CREATE ON SCHEMA schma TO youwish_prodadmin;
     ALTER DEFAULT PRIVILEGES FOR ROLE youwish_prodadmin GRANT INSERT, UPDATE, DELETE, TRUNCATE ON TABLES TO youwish_prod;
     ALTER DEFAULT PRIVILEGES FOR ROLE youwish_prodadmin GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO youwish_prod;
     GRANT TEMP ON DATABASE youwish_prod TO youwish_produser;
     //< REPEAT STEPS ^ABOVE^ FOR test and dev, except combine roles since there is no admin/user distinction
     
## Manually Copying One DB to Another 
This is the same for prod to dev/test or one RDS to another one...

```
   pg_dump -Fc --no-acl -n public -h RDS_URL_BLA_BLA.amazonaws.com -U userthatcanreadolddb dbname > ./sql.dump
   pg_restore -U userThatCanWriteToNewDB -d NEW_DB -h NEW_RDS_BLA_LBA.amazonaws.com sql.dump
```

## Links Used for Real Deal

* <https://dba.stackexchange.com/questions/117109/how-to-manage-default-privileges-for-users-on-a-database-vs-schema>
* <https://www.postgresql.org/docs/9.0/static/sql-alterdefaultprivileges.html>
* <https://www.postgresql.org/docs/9.1/static/sql-alterdatabase.html>
* <https://www.postgresql.org/docs/current/static/sql-grant.html>
