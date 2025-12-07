---
slug: twig-templates
title: "Switching to Twig template engine"
authors: [exileum]
tags: [announcement, development, v3, templates]
---

This is the first article in the promised series where we'll be talking about what's coming in the new major version of the project.

Today we'll discuss one of the main changes — we've completely reworked the template system.

<!-- truncate -->

## What we had

If you've ever looked into TorrentPier templates, you've probably seen syntax like this:

```html
<!-- IF TERMS_HTML -->
<tr>
    <td class="row1">
        <div class="post_wrap">
            {TERMS_HTML}
        </div>
    </td>
</tr>
<!-- ENDIF -->
<!-- IF IS_ADMIN -->
<tr>
    <td class="row1">
        {TERMS_EDIT}
    </td>
</tr>
<!-- ENDIF -->
```

This is a legacy of phpBB2 and its eXtreme Styles template engine. The old template engine served the project faithfully for many years, but it has one big problem — there's practically no documentation for it. If you wanted to change something in an existing template or write your own, you had to figure it out through trial and error or search for ancient articles from phpBB forums.

## What we have now

In version 3.0, we've replaced this engine with **Twig** — a modern template engine from the creators of Symfony. It's one of the most popular tools in the PHP world, used in thousands of projects, and there's tons of material about it in any language.

Now the same code can be written like this:

```twig
{% if TERMS_HTML %}
<tr>
    <td class="row1">
        <div class="post_wrap">
            {{ TERMS_HTML|raw }}
        </div>
    </td>
</tr>
{% endif %}
{% if IS_ADMIN %}
<tr>
    <td class="row1">
        {{ TERMS_EDIT|raw }}
    </td>
</tr>
{% endif %}
```

You might say the changes are small, but this is one of the simplest examples. There are much more complex ones where you can throw out a huge amount of backend logic, for example easily forming large blocks directly in the template. For you, the main difference is that Twig templates are supported in many IDEs, and you already have a huge number of different modifiers "out of the box" so that some changes can be made automatically in templates rather than in PHP code.

## The main thing: backward compatibility

**Your old templates will continue to work as before.** We wrote a converter that automatically transforms the old syntax to Twig on the fly. You don't need to rewrite anything — just update, and everything will work. Or drop in an old template and it will work too.

At the same time, you now have a choice:

- Leave old templates as they are
- Gradually rewrite to Twig when making modifications
- Write new templates directly in Twig

You can even mix them — use both syntaxes in the same file.

## What this gives you

- **Documentation.** Everything is at [twig.symfony.com](https://twig.symfony.com), plus tutorials and examples in any language
- **Modern features.** Template inheritance, macros, filters — all out of the box
- **Caching.** Compiled templates are smartly cached, no repeated parsing needed
- **Debugging.** In developer mode, a debug panel will appear with information about loaded templates

## What's next

We plan to rewrite the standard template to Twig and visually update it. We'll talk about this in one of the upcoming articles about changes in version 3.0. For now, we simply have the ability to do this conveniently, with a bunch of new features and with hope that now there won't be questions on the forum about the template syntax being unclear.
