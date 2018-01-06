Workarounds
===

This file describes ugly hacks and workarounds that have been deployed to work
around limitations in the libraries and frameworks we use. The underlying raison
d'etre for these issues should regularly be reinvestigated and the workarounds
removed when better solutions exist.

## Class alias for `App\Entity\User` -> `(Raddit\)AppBundle\Entity\User`

These aliases (found in src/Kernel.php) were added to keep serialised instances
of the class working after the software switched names.

## Bundle versions pinned to specific commits

All bundles have been made compatible with Symfony 4.0, but a few haven't tagged
compatible releases yet. Because these bundles are fairly low in development
activity and unlikely to break completely, we decided to pin specific commits in
the version constraints.
