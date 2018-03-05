Workarounds
===

This file describes ugly hacks and workarounds that have been deployed to work
around limitations in the libraries and frameworks we use. The underlying raison
d'etre for these issues should regularly be reinvestigated and the workarounds
removed when better solutions exist.

## Class alias for `App\Entity\User` -> `(Raddit\)AppBundle\Entity\User`

These aliases (found in src/Kernel.php) were added to keep serialised instances
of the class working after the software switched names.
