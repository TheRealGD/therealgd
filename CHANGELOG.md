# Change Log

## v0.4.0 (2017-xx-xx)

* Added links for submit/create forum everywhere.
* The submission form now lets you choose the forum you want to post in. 
  Accessing the form through a forum makes that forum selected by default.
* Replaced the popular submission ranking algorithm with one that makes sense.

## v0.3.1 (2017-04-11)

* Fixed recursion bug in JS which would make the browser consume 100% CPU.
* Fixed nasty bug where submitting the user form without a password would erase
  the existing password.

## v0.3.0 (2017-03-26)

* Bumped the minimum PHP version to 7.0 as 5.6 is no longer supported.
* Much future-proofing and many improvements to frontend assets.
    * webpack/gulp-based build system.
    * JS is written in ES2015 and transpiled to ES5 on build.
    * jQuery is used for DOM manipulation & traversal, and Ajax calls.
    * Individual JS 'plugins' are now reusable and can be applied to e.g. new
      DOM elements created after an Ajax request.
    * CSS rules have been grouped into files.
    * Many style improvements have been made.
* Added the ability to edit:
    * Comments
    * Forums
    * Submissions
    * User accounts
* Added the ability to remove:
    * Forums
    * Submissions
* Added a dropdown menu for user actions.
* Added the ability to create user accounts via the command line.
* Remove the distinction between 'Post submissions' and 'URL submissions'.
* Show notices when certain actions are performed.
* Users can now be administrators.
    * Added an `--admin` option to the `raddit:add-user` command.
* Users can now reset their passwords via email.
* Voting on posts via Ajax (non-JS fallback still available.)

## v0.2.0 (2017-01-06)

* Sort comments by descending net score.
* Usernames and forum names must now be case-insensitively unique. Duplicates
  are renamed upon running database migrations.
* Ability to delete comments.
    * Users can delete their own comments, but they will not disappear entirely
      if they have replies. These partially deleted comments are to be called
      *soft-deleted*, i.e. their entry remains in the database, but the comment
      body is blanked out.
    * Forum moderators and site administrators can delete comments in their
      respective realms. If a comment has replies, they can choose to delete the
      entire comment thread, or to merely soft-delete the original post.

## v0.1.2 (2017-01-02)

* Make use of Doctrine migrations.
* Add missing 'create forum' link in the menu on the front page.
* Add a form theme and CSS so all forms look OK.
* Have `rel="nofollow` added to link elements in user-submitted Markdown.
* Update fixtures to have the author upvote their contributions.

## v0.1.1 (2016-12-29)

* Added the ability for the user to choose how to sort submission listings.
* Minor accessibility improvement to voting buttons.
* Block undesired embedding of external resources in user-submitted Markdown.
  External embedding was never intended to be allowed in the first place.
* Autolinkify URLs in user-submitted Markdown.
* Have `target="_blank"` and `rel="noreferrer"` added to link elements in
  user-submitted Markdown.

## v0.1.0 (2016-12-28)

* First release.
