Workarounds
===

This file describes ugly hacks and workarounds that have been deployed to work
around limitations in the libraries and frameworks we use. The underlying raison
d'etre for these issues should regularly be reinvestigated and the workarounds
removed when better solutions exist.

## Class alias for `AppBundle\Entity\User` -> `Raddit\AppBundle\Entity\User`

This alias (found in AppKernel.php) was added to keep serialised instances of
the class working after the software switched names.

## Bundle versions pinned to specific commits

All bundles have been made compatible with Symfony 4.0, but a few haven't tagged
compatible releases yet. Because these bundles are fairly low in development
activity and unlikely to break completely, we decided to pin specific commits in
the version constraints.

## Post-install/update scripts

Since SensioDistributionBundle doesn't work with Symfony 4.0 and Flex takes a
bit of effort to implement, we run shell scripts as post-install/update scripts,
replacing the aforementioned bundle's Composer scripts.

## composer-parameter-handler

The `incenteev/composer-parameter-handler` package is dead and hasn't been
updated for 4.0, so we use the temporary fork
`derrabus/composer-parameter-handler` instead.
