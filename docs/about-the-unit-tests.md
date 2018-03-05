About the unit tests
===

You should have a separate database for running unit tests. Here's why:

Because the bcrypt algorithm is slow and computationally expensive, the `test`
environment is configured to not use bcrypt-encoded passwords for user accounts.
Instead, any users created in the test environment will store its passwords in
plain text in the database, making authentication incompatible between a test
environment and a regular database, and vice versa.

The data fixtures from `tests/Fixtures/` must be loaded in the database. The
database must be in an otherwise unmodified state, or the assumptions the tests
hold about the state of the database won't hold true, possibly leading to
failing tests.

After [creating a separate database for tests](database-setup.md), run the
following commands to initialise it for testing:

```
$ export DATABASE_URL='pgsql://user:pass@host:port/some_test_db?serverVersion=9.x'
$ bin/console doctrine:migrations:migrate -n --env=test
$ bin/console doctrine:fixtures:load -n --env=test
```

If successful, you can now run unit tests using `bin/phpunit`.
