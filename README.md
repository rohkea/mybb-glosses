Glosses for MyBB 1.0 Beta
=========================

Glosses for MyBB is a plugin that allows to add glosses markup to your forum.
It is especially useful for discussions on linguisic topics.



Installation
------------

Glosses for MyBB has been tested on MyBB 1.6.8.

To install glosses, just copy the code in the inc/ folder to the inc/ folder of
your MyBB installation.

Gloss markup
------------

Glosses for MyBB adds the support for the following BB-code markup:
        [gloss]Das{this} ist{is} ein{an} Beispiel{example}.[/gloss]

Glosses follow the word, enclosed in brackets. A word can have more than
one gloss.

If a unit larger than word needs to be glossed, it can be enclosed in double
backticks:
```[gloss]``Without you``{сенсыз}[/gloss]```

Main words cannot contain BB-codes. If you need to include one, enclose it
in double backticks:
```[gloss]This{Это} is a ``[b]bold[/b]``{полужирное, смелое}
statement{заявление}.[/gloss]```

Glossed text cannot contain brackets. If you need them, replace them with
triple square brackets:
```[gloss][[[Test]]]{[[[test]]]}[/gloss].```

### Arguments for the [gloss] BB-code

Glosses can accept arbitrary arguments:
```[gloss test]Это{this.NOM.SG.N} пример-Ø{example-NOM.SG}.[/gloss]```

Most arguments just end up in a class with a ``gloss-`` prefix. So, for the
example above to actually do something, you have to define a CSS rule for
``.gloss-test`` class, like this:
```css
.gloss-test { color: navy }```

Equation signs (=) are replaced with - in arguments, so [gloss size=large]
will end up as a class .gloss-size-large.

Some arguments have predefined meaning:

* ``nospaces`` deletes spaces in the main text (not in glosses) from the
output; useful for Japanese and Chinese;
* ``above`` makes glosses be displayed above the text, not below; useful for
furigana in Japanese;
* ``rtl`` forces text to be displayed right-to-left; may be useful for Arabic
and Herbew, but consider using romanisation instead;
* ``size=xx-small``, ``size=small``, ``size=medium``, ``size=large``,
``size=x-large``, ``size=xx-large`` change the font size (editable via CSS).

Feedback
--------

I will appreciate any feedback about the code, its performance, as well as bugs
and problems with this plugin. Feel free to contact me at
dmytro_kushnariov@lavabit.com.
