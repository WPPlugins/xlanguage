=== xLanguage ===
Contributors: sam0737
Donate link: http://hellosam.net/contribute
Tags: language, babel, multilanguage, multilingual, l10n, i18n
Requires at least: 2.3.2
Tested up to: 2.7.0
Stable tag: trunk

Allows you to blog in multi-language, and users to select which to read.  Works on every blog UI elements, not just the post.

== Description ==

If your want to present your blog in different languages (totally with title, rss, categories working, not just the post) and allows visitors to pick the language they want, this plugin is for you.

xLanguage is a full featured plugin allows you to blog in different language, and allows user to select which version to read.  It works for blog post, page, tags, categories.  The user language preferences will also select the right theme and plugins MO files.

Customization is the No. 1 design goal, after all that's probably the reason why you want to setup a multilingual blog.   The language configuration combination is highly customizable to facilitate multiple fallbacks.  Every UI is extracted out, which could be optionally overridden by theme design without touching the plugin file at all.


The language preference is detected from browser's preferences, as well as from Cookie if user visited before. Widget and template functions are also provided to allow user to switch the language easily. The presentation of the template functions can also be customized in your theme folder without touching the code in the plugins.

See `Other Notes` for the comparisons against similar plugins.

= Feature Highlights =

* Does not touch the Wordpress database schema at all. All language text are stored in the same post and hence more friendly to other plugins.
* For post that written in reader's foreign language. You could hide them, or optionally show them with a notice prepended. Your choice!
* Optionally present a total independent view to the search engine crawlers. So that you can have all languages text being crawled under a single URL. Think of SEO!
* Each language can be presented in a different theme.
* Language composing are done in inline instead of separated side by side posting, see Screenshot 1. The former is like Simplified/Traditional Chinese within Wikipedia-ZH, while latter is like Wikipedia-Zh and -EN version.
* Many-to-many mappings between viewable language and authoring language is possible. Hong Kong-based reader could read Cantonese and Chinese, but average Chinese reader could only read Chinese, xLanguage specifically enables this scenario!

= Usage Guide =

To fully enjoy the power of customization, please refer to the usage guide hosted on my [blog project page](http://hellosam.net/project/xlanguage).  Also see my blog for live demostration.

There are some [known problems and limitations](http://hellosam.net/project/xlanguage), please make sure you are aware of those.

= Change Log =

Please refer to my [blog page](http://hellosam.net/project/xlanguage) for recent changes.

= Upgrade Notice =

Permalinks `Postfix` appending position is known to be not working with Wordpress 2.7 paged comments mode and potentially causing conflicts with many other plugins.  The mode will be changed to `Prefix` when you upgrade from v1.x. If you are using the `Postfix` mode and want to keep using it, please change the settings manually.

== Comparisons ==

= Compare to qTranslate v2.0.2 (Jan 2009) =

The logic behind qTranslate is similar to that of xLanguage - both using HTML tag to differentiate the contents of languages and requires no database modification. qTranslate uses HTML comment tag while xLanguage uses the lang=".." attribute.

The major differences lie within the admin interface, qTranslate provides strong Admin UI integration such that user enters the title, the category name and so on in independent textbox. The post is also to be post to an independent editor. While xLanguage, user enters the data in the same input box. For authoring in language that are very similar (like Cantonese and Written Chinese), and that cross-authoring is common, the method that xLanguage use will reduce much duplication work.

Due to the above restriction, qTranslate supports only one-to-one mapping between authoring and viewable lanugage. while xLanguage does not have this restriction. And hence, xLanguage allows all texts to be presented under one single URL for search engine to crawl with. (Think of SEO)

Feature-wise, xLanguage currently lacks of Pre-domain URL Permalinks mode, as well as automatic MO file download. (Note: MO does work in xLanguage but just have to be downloaded manually). Contribution would certainly help in accelerate these getting done :).

= Compare to Gengo v2.5.3 (Jan 2009) =

Gengo v2.5.3 is not tested with Wordpress 2.7 according to the published plugin info.

In Gengo, each translations is a standalone post, linked to the original post.  It enables you to create maps between different content.  Think of different language version of Wikipedia, basically Gengo is the same - and it helps you to maintain such link table.

In xLanguage, everything is in the same post.  The xLanguage approaches make no structural changes to the WordPress itself, and hence should be easier maintain.  No extra magic in xLanguage is needed to keep the commenting, searching, popularity counter works.

= Compare to Language Switcher v1.11 (Jan 2008) =

This is actually the plugin that inspired me to build the xLanguage.  The Language Switcher and the xLanguage is working in a very similar way -- Both enable multilingual post by tagging the it.  The Language Switcher uses square bracket instead of HTML tag, but have no assisting tools when composing the post.  The language code is limited to the length of 2, and offer no fallbacking and overlapping possibility.  The permalink url tag must be placed at the end.

Same as xLanguage, it allows user to enable multilingual functionality on single line content like tag, title and such.

= Compare to jLanguage v1.4 (Jan 2008) =

Almost the same as Language Switcher, both functionality and working principle.  However, jLanguage v1.4 does not have widget for user to select their favorite language.  In addition, It currently offer almost no customization, plus it does not work for single line content.  The language selection could not affect the MO file selection.

It enables the language tagging by using square brakets instead of standard tag, similar to that of Language Switcher.

= Compare to Multilingual post v0.2 (Jan 2008) =

Please be aware that Multilingual post v0.2 does not belong to this category at all.  It does not enable poster to publish mixed language in a post.  The user also cannot selectively view the post in a particular language.

It only does insert the lang=".." tag at the HEAD such that browser can render the page better.  xLanguage also does this.

= Compare to Bunny's Language Linker v0.2 (Jan 2008) =

Bunny's Language Linker's way to enable multilanguage blog is minimal and simple, yet the integration is very loose.  It works like Gengo, the pros and cons compare to xLanguage is very similar.

= Compare to Basic Bilingual v0.31 (Jan 2008) =

Basic Bilingual v0.31 allows you to insert a summaries in a different language.  It is yet another very simple plugin.

== Installation ==

This plugin can be installed like all the other plugin.  It requires no extra configuration except being activated.

1. Extract the downloaded zip file to the `/wp-content/plugins/` directory.  You might also want to use [OneClick Installer](http://wordpress.org/extend/plugins/oneclick/) to automate this step.
2. In the Admin control panel, goes to the `Plugins` page and activate the `xLanguage` plugin.
3. Configure the plugin under the `Options` page.
4. Start blogging with the multi-langual functionalities!

There are some [known problems and limitations](http://hellosam.net/project/xlanguage), please make sure you are aware of those. [Full usage guide](http://hellosam.net/project/xlanguage) is kept at the plugin homepage.

== Screenshots ==

1. Posting with xLanguage under WordPress 2.7
2. Basic configuration
3. Advanced configuration
4. Options
5. Template function and widget at work!

== Frequently Asked Questions ==

[Full usage guide](http://hellosam.net/project/xlanguage) is kept updated at the plugin homepage.

= How to use? =

First, you have to configure which language are available to the users, and defining the properties of the language.  A complex, non one-to-one arrangement is allowed which is very useful if one of the language is "overlapped" with another.

Then, you can start blogging with the xLanguage toolbar provided which can help you to specify which paragraphs in the post belong to which language.  In addition, you may want customized the name of the categories and tags to specified languages.

Also, put the xLanguage widget up so the users are allowed to switch language as needed.

When a user finally comes to your blog, the plugin will determine the most favorable language from his browser preferences.  The content shown will have the correct part extracted.  His choice of language will also be saved to cookie.  Permalinks will also be modified to include the language information.

There are some [known problems and limitations](http://hellosam.net/project/xlanguage), please make sure you are aware of those. [Full usage guide](http://hellosam.net/project/xlanguage) is kept at the plugin homepage.

= How does this work under the hood? =

The HTML are tagged with W3C compliants tag <span lang="..."> or plain text square bracket format, or split with "|" for the single line content like post title and categories name.  This plugins hooked into number of filters, and the correct part of the content will be extracted on the fly when served.

For content filtering, the available languages are stored in the meta-data and is cross-referenced in WP Query join/where.
