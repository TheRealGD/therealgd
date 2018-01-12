Customising Postmill with overrides
===

Postmill includes a mechanism for overriding its templates and translations with
your own custom definitions. Using this mechanism, you can tweak the appearance
and, to a limited degree, the functionality of your Postmill instance.

Template overriding
---

Templates are overridden by creating template files with a similar path to the
originals in `templates/overrides`. These templates will take precedence over
Postmill's own templates. Using this allows you to add custom HTML or replace
some of Postmill's HTML with your own.

Templates are written in the [Twig templating language][twig]. You should
familiarise yourself with some basic Twig concepts before writing your own
templates.

[twig]: https://twig.symfony.com/doc/2.x/

### Example: replacing the site's footer

To demonstrate, let's override the footer in the base template with our own. The
footer's HTML resides in a block named `site_footer`. To override this footer,
we need to create a new base template that inherits Postmill's base template,
but replaces the `site_footer` block. We can do this like so:

~~~twig
{% extends '@Postmill/base.html.twig' %}

{% block site_footer %}
  <footer class="my-fancy-new-footer">
    <p>This is a fancy, new footer!</p>
  </footer>
{% endblock %}
~~~

This must be saved in `templates/overrides/base.html.twig`.

The `@Postmill` syntax is used for loading one of Postmill's own templates.
Without this prefix, extending from `base.html.twig` would result in an error
about circular references, as the overriding template would try extending from
itself.

### Caveats

Template overriding is a very new addition. As such, there are a lot of things
that seem like they should be overridable, but aren't, as the templates weren't
created with third-party overriding in mind. If you run into an issue trying to
accomplish something with overrides, please file a report on our issue tracker.


Translation overrides
---

As with templates, you can also override translations. Stick new translation
files in `translations/overrides`, and you're good to go.

Translation overrides are best used for making new strings in your template
overrides translatable. This can be done like so:

#### `templates/overrides/some/template.html.twig`

~~~twig
<p>{{ 'my.text'|trans }}</p>
~~~

#### `translations/overrides/messages.en.yml`

After adding a translation file, you must clear your cache (even in dev). Run
`bin/console cache:clear` to accomplish this.

~~~yaml
my.text: My text
~~~

[formats]: https://symfony.com/doc/current/components/translation.html#loading-message-catalogs

### Domain

The `messages` part of `messages.en.yml` is actually the translation domain,
which defaults to `messages` in most instances. Other translation domains may be
used in some cases--if in doubt, use the translation panel in the development
toolbar to find the domain.

### Language

The language part of the translation's filename is an [ISO 639-1 language
code][iso639-1]. If you add translations for languages that aren't already in
Postmill, there will be new language options available in the user settings. As
such, it probably makes sense to only add mostly complete translations for new
languages, as the language option would deliver a terribly incomplete
translation incomplete otherwise.

[iso639-1]: https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes

### File format

Postmill supports every translation format supported by Symfony, including
XLIFF, gettext (.po, .mo), plain PHP, JSON, CSV and INI. You can use whichever
one you want. See [Symfony's documentation][formats] to learn more.

