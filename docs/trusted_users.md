# Trusted users

Every `User` has a `trusted` field associated with it. When the field is set to
true, that user is granted the `ROLE_TRUSTED_USER` role, the purpose of which is
to allow vetted users to bypass aggressive spam protections.

Users who have proven to be legitimate and not spammers should be considered
trusted.
