# Change Log

## v0.3.0 (2017-xx-xx)

* Much future-proofing and many improvements to frontend assets.
    * webpack/gulp-based build system.
    * JS is written in ES2015 and transpiled to ES5 on build.
    * jQuery is used for DOM manipulation & traversal, and Ajax calls.
    * Individual 'plugins' are now reusable and can be applied to e.g. new DOM
      elements created after an Ajax request.
    * Styles have been organised into modules.
* Added the ability to edit user accounts.
* Voting on posts via Ajax (non-JS fallback still available.)
* Remove the distinction between 'Post submissions' and 'URL submissions'.

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
