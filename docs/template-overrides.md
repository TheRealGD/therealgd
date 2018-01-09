# Template overriding

Postmill includes an experimental method of overriding its templates with custom
templates of your own. By creating new template files in `templates/overrides`,
these templates will take precedence over Postmill's own templates. This allows
you to add custom HTML or replace some of Postmill's HTML with your own.

Templates are written in the [https://twig.symfony.com/doc/2.x/](Twig templating
language. You should familiarise yourself with some basic Twig concepts before
writing your own templates.

## Example: replacing the site's footer

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

## Caveats

Template overriding is a very new addition. As such, there are a lot of things
that seem like they should be overridable, but aren't, as the templates weren't
created with third-party overriding in mind. If you run into an issue trying to
accomplish something with overrides, please file a report on our issue tracker.
