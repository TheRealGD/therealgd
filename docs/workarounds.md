Workarounds
===

This file describes ugly hacks and workarounds that have been deployed to work
around limitations in the libraries and frameworks we use. The underlying raison
d'etre for these issues should regularly be reinvestigated and the workarounds
removed when better solutions exist.

## `Raddit\AppBundle\Validator\Constraints\UniqueTheme`

Symfony's `UniqueEntity` constraint does not support DTOs, despite the existence
of an `entityClass` field. `UniqueTheme` is a special constraint that works
around this problem for the `Theme` entity and its `ThemeData` DTO.

The `ThemeData::$entityId` property is kept around for the validator's sake.

If/when Symfony gains a UniqueEntity validator that can deal with DTOs, the
aforementioned constraint, validator and DTO property can be removed.

See <https://github.com/symfony/symfony/issues/22592>.
