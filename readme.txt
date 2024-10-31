=== RDP Wiki-Press Embed ===
Contributors: rpayne7264, enej, ejackisch, devindra, ctlt-dev, ubcdev
Tags: mediawiki, wiki, wiki-embed, embed, content framework, wiki inc, pediapress, pediapress embed
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 2.4.7

RDP Wiki-Press Embed lets you embed MediaWiki pages in to your site, from sites like Wikipedia, and PediaPress book pages.

== Description ==

**This plugin is no longer maintained. It's functionality has be split into the following two plug-ins:
[RDP PediaPress Embed](https://wordpress.org/plugins/rdp-pediapress-embed/) & [RDP Wiki Embed](https://wordpress.org/plugins/rdp-wiki-embed/)**


RDP Wiki-Press Embed will pull content from any MediaWiki website (such as wikipedia.org) and from PediaPress.
It strips and reformats the content, allowing you to supply some arguments to dictate how this works.

RDP Wiki-Press Embed also allows lead capture capabilities, utilizing free PediaPress ebooks as an offer.
The default behavior is to display two tabs. The first tab will display whatever text, HTML, and/or another shortcode you desire. The second tab will display the original PediaPress.com book page within an iframe.


= Known Issues =

* CSS clashes with Lightbox Plus Colorbox plug-in



Forked from: [Wiki Embed plugin](http://wordpress.org/plugins/wiki-embed/  "Wiki Embed plugin").

== Frequently Asked Questions ==

= Could you use this to replicate the whole of Wikipedia = 

Maybe, but why would you want to? That is not what the tool was designed to do. 


== Usage ==

If you are not using "Overwrite" mode, make sure to un-tick the box for "Look for all links to wiki sites listed in the Security section..."


= Wiki Content =
RDP Wiki-Press Embed is implemented using the shortcode [wiki-embed]. It accepts the following arguments:

* url: (required) the web address of the wiki article that you want to embed on this page.
* no-edit: Hide the "edit" links from the wiki.
* no-contents: Hide the page's contents box.
* no-infobox: Hide any infobox that appears on the wiki for this page.
* tabs: Replaces the sections of the wiki article with tabs.
* accordion: Replaces the sections of the wiki article with an accordion. This option cannot be used as the same time as 'tabs'.

Example:

[wiki-embed url="http://en.wikipedia.org/wiki/Example" no-edit no-contents no-infobox accordion]


= PediaPress Book =
For embedding PediaPress book pages, the following arguments are accepted:

* url: (required) the web address of the PediaPress book that you want to embed on this page.
* toc_show: 0 (zero) to hide table of contents (TOC) or 1 to show
* toc_links: Default — TOC links are enabled; Logged-in — TOC links are active only when a user is logged in; Disabled — TOC links are completely disabled, all the time
* image_show: 0 (zero) to hide cover image or 1 to show 
* title_show: 0 (zero) to hide book title or 1 to show 
* subtitle_show: 0 (zero) to hide book sub-title  or 1 to show 
* editor_show: 0 (zero) to hide book editor or 1 to show 
* language_show: 0 (zero) to hide book language or 1 to show 
* add_to_cart_show: 0 (zero) to hide Add-to-Cart button or 1 to show 
* book_size_show: 0 (zero) to hide book size or 1 to show 
* cta_button_text: text for call-to-action button
* cta_button_width: integer indicating pixel width of call-to-action button; default is 250; max is 500
* cta_button_top_color: gradient top color
* cta_button_bottom_color: gradient bottom color
* cta_button_font_color: normal button text color
* cta_button_font_hover_color: text color when cursor is on the button
* cta_button_border_color: button border color
* cta_button_box_shadow_color: button's drop shadow color
* cta_button_text_shadow_color: drop shadow color of button's text

Examples:

[wiki-embed url='http://pediapress.com/books/show/american-warplanes-of-wwii-fighters-bombe']

[wiki-embed url='http://pediapress.com/books/show/american-warplanes-of-wwii-fighters-bombe' toc_show='1' toc_links='logged-in']

For the Call-to-Action button to appear, the shortcode must be an enclosing shortcode, containing text, HTML, and/or another shortcode, between the opening and closing shortcode tags.

[wiki-embed url='http://pediapress.com/books/show/american-warplanes-of-wwii-fighters-bombe' toc_show='1' toc_links='logged-in' cta_button_top_color='#eded00' cta_button_text_shadow_color='#cd8a15']`<iframe src="http://www.w3schools.com"></iframe>`[/wiki-embed]


= PediaPress Gallery =
Embedding a PediaPress gallery of books is implemented using the shortcode [wiki-embed-ppgallery]. It accepts the following arguments:

* col: (required) number of columns to display per page
* num: (required) number of books to display per page
* size: accepted values - small, medium, large - dictate image and font size of gallery elements
* cat: comma separated list of category id numbers books must belong to
* tag: comma separated list of tag id numbers books must belong to
* sort_col: column name by which to sort books
* sort_dir: direction to sort books (ASC / DESC)
* cta_button_text: text for call-to-action button
* cta_button_width: integer indicating pixel width of call-to-action button; default is 250; max is 500
* cta_button_top_color: gradient top color
* cta_button_bottom_color: gradient bottom color
* cta_button_font_color: normal button text color
* cta_button_font_hover_color: text color when cursor is on the button
* cta_button_border_color: button border color
* cta_button_box_shadow_color: button's drop shadow color
* cta_button_text_shadow_color: drop shadow color of button's text

The layout for displaying each book in the gallery is based on the template file located at: 

`resources/ppgallery-template/ppgallery.column.results.html`

A sample template that displays buttons is located at: 

`resources/ppgallery-template/ppgallery.column.results-sample.html`


Examples:

[wiki-embed-ppgallery col='3' num='3']

[wiki-embed-ppgallery col='2' num='10' cat='5' tag='7,8' sort_col='post_date' sort_dir='DESC' /]


For the Call-to-Action button to appear, the shortcode must be an enclosing shortcode, containing text, HTML, and/or another shortcode, between the opening and closing shortcode tags.

[wiki-embed-ppgallery col='3' num='3' cta_button_font_hover_color='#8224e3']`<iframe src="http://www.w3schools.com"></iframe>`[/wiki-embed-ppgallery]


== Configuration ==

Settings for the plugin can be found in 'Wiki Embed' -> 'Settings'.
Here you can enable/disable various features, define shortcode defaults, and configure some global settings for the plugin.


== Installation ==

1. Upload `rdp-wiki-embed` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Wiki Embed' -> 'Settings' and change the wiki embed settings to your liking
4. Click the 'Save Changes' button at the bottom of the settings page - even if you do not make any changes - so settings take affect
5. Go to 'Settings' -> 'Permalinks' and click the 'Save Changes' button so the custom RSS feeds will work.

= Gallery Items =
To change the way each book is displayed in gallery view, you will need to copy the template file located at: 

`resources/ppgallery-template/ppgallery.column.results.html`

Put the copy into a folder named *rdp-wiki-press-embed*, in the uploads directory, and then modify it as desired.


A sample template that displays buttons is located at: 

`resources/ppgallery-template/ppgallery.column.results-sample.html`

= Extra =
To make everything pretty, add a wiki.custom.css and pediapress.custom.css file to your theme's folder. Start with the wiki.custom-sample.css and pediapress.custom-sample.css files located in the 'resources/css/' folder.



== Screenshots ==

1. A page that list all the wiki content that is embedded on the site. 
2. A look at the wiki embed settings page. 
3. Wiki page shortcode embed helper form
4. PediaPress book shortcode embed helper form
5. PediaPress gallery shortcode embed helper form
6. Media button to launch shortcode embed helper form
7. Call-to-action button on PediaPress book page


== Changelog ==

= 2.4.7 =
* REFACTOR: modified code to address caching issue
* REFACTOR: modified code to prevent unnecessary execution when an override URL is present

= 2.4.6 =
* REFACTOR: modified code to limit image widths to 400px

= 2.4.5 =
* REFACTOR: added code to address images not having a specified source URL scheme
* REFACTOR: modified code to ensure the Wiki CSS is being referenced at all times
* REFACTOR: modified code to ensure the overwrite feature works with page builder plugins
* FEATURE: added ability to remove warning boxes, regarding no references or sources for article content
* FEATURE: added a way to bulk delete the cache and data entry for wiki pages

= 2.4.4 =
* REFACTOR: tweak shortcode popup CSS to hide tabs

= 2.4.3 =
* REFACTOR: changed parameter #2 on rdp_pp_gallery_item filter to array of template input values
* REFACTOR: hooked gallery scripts and styles to wp_footer hook so they are added in the page footer

= 2.4.2 =
* REFACTOR: fixed shortcode popup form showing at the bottom of the settings page
* REFACTOR: modified code to show full image inside light box when clicking on an image, with "Lightbox" selected on the settings page

= 2.4.1 =
* REFACTOR: modified code to try to convert relative image URLs to absolute URLs
* REFACTOR: modified code to make the shortcode media button show up for all post types

= 2.4.0 =
* FEATURE: added size attribute to gallery shortcode
* FEATURE: added ability to pick a size attribute for gallery shortcode to the shortcode helper form
* REFACTOR: modified common css file to reflect selected gallery size
* REFACTOR: modified HTML of default gallery results template, including hyperlinking the title to the original post
* REFACTOR: made book titles bold, as default styling


= 2.3.0 =
* FEATURE: added code that adds a Table of Contents (TOC) button to pages derived from clicking a TOC link on a single PediaPress book page.

= 2.2.0 =
* REFACTOR: modified code to store book cover images in the uploads directory, inside a folder named rdp-wiki-press-embed
* REFACTOR: modified code to look for plugin-specific custom css files within the theme directory
* REFACTOR: modified code to look for a custom gallery template, a modified copy of the file resources/ppgallery-template/ppgallery.column.results.html, inside a folder named rdp-wiki-press-embed, in the uploads directory

= Changed files: =
* readme.txt
* WikiEmbed.php
* resources/rdpWEPPE.php
* resources/rdpWEPPGallery.php
* resources/css/pediapress.custom-sample.css
* resources/css/wiki.custom-sample.css

= Deleted folders: =
* resources/img-cache

= 2.1.1 =
* REFACTOR: modified code to allow multiple galleries on one page

= 2.1.0 =
* FEATURE: added global setting for additional text, such as Creative Commons verbage, to be displayed under a book's cover image
* FEATURE: added RSS feed capability
* REFACTOR: added code to address interference with WooCommerce product search

= 2.0.0 =
* FEATURE: added shortcode to display gallery of PediaPress books
* FEATURE: added template support for layout of each gallery item
* REFACTOR: added hooks and filters to facilitate custom coding
* REFACTOR: removed cache time for PediaPress books, making the cached data non-expiring
* REFACTOR: re-worked code so that PediaPress book data is cached, but the rendering of the data is always dynamic
* REFACTOR: re-worked code so that PediaPress book cover images are stored locally
* REFACTOR: added code so that saving a post/page will overwrite the cached PediaPress book data
* REFACTOR: added code to address [Fatal error: Cannot use string offset](https://wordpress.org/support/topic/fatal-error-cannot-use-string-offset-3  "Fatal error: Cannot use string offset") issue reported by user ebewley: 

= Changed files: =
* readme.txt
* WikiEmbed.php
* admin/admin-overlay.php
* admin/settings-page.php
* admin/css/admin.css
* admin/js/script.admin-overlay.js
* resources/rdpWEPPE.php
* resources/css/pediapress.common.css
* resources/js/pediapress-overlay.js


= New files: =
* resources/rdpWEPPEGallery.php
* resources/ppgallery-template/ppgallery.column.results-sample.html
* resources/ppgallery-template/ppgallery.column.results.html
* resources/js/pediapress-gallery-overlay.js


= New folders: =
* resources/img-cache
* resources/ppgallery-template


= 1.4.1 =
* REFACTOR: added code to ensure the CTA button always opens the first tab


= 1.4.0 =
* FEATURE: added setting to cache PediaPress book pages
* FEATURE: added button to clear cached PediaPress book pages
* REFACTOR: updated Call-to-Action pop-up lightbox to use tabs - one for custom content and one that displays the PediPress.com page
* REFACTOR: changed text of blue Add-to-Cart button to start with 'Purchase Print Edition'
* REFACTOR: widened right-side book info container
* REFACTOR: completely removed TOC hyperlinks when the links should be disabled
* UPDATE: changed screenshot #4
* UPDATE: changed screenshot #6

= Changed files: =
* readme.txt
* screenshot-4.png
* screenshot-6.png
* WikiEmbed.php
* admin/settings-page.php
* resources/rdpWEPPE.php
* resources/css/pediapress.common.css
* resources/js/pediapress-overlay.js


= 1.3.2 =
* REFACTOR: multiple bug fixes
* REFACTOR: removed extraneous Colorbox script files

= Changed files: =
* readme.txt
* WikiEmbed.php
* admin/css/admin.css
* resources/rdpWEPPE.php
* resources/js/pediapress-overlay.js


= 1.3.1 =
* REFACTOR: renamed Call-to-Action variables and shortcode attributes

= 1.3.0 =
* FEATURE: added Call-to-Action button and pop-up lightbox for PediaPress books
* REFACTOR: expanded global PediaPress shortcode settings options
* REFACTOR: re-worked shortcode pop-up form to reflect expanded PediaPress options
* UPDATE: changed screenshot #2
* UPDATE: changed screenshot #4
* UPDATE: added screenshot #5
* UPDATE: added screenshot #6

= Changed files: =
* readme.txt
* screenshot-2.png
* screenshot-4.png
* WikiEmbed.php
* admin/admin-overlay.php
* admin/admin.php
* admin/css/jquery-ui.css
* admin/js/script.admin-overlay.js
* admin/settings-page.php
* resources/css/pediapress.common.css
* resources/js/pediapress-overlay.js
* resources/rdpWEPPE.php

= New files: =
* admin/css/admin.css
* admin/js/script.settings-page.js
* screenshot-5.png
* screenshot-6.png

= 1.2.0 =
* REFACTOR: re-worked shortcode pop-up form to use tabs and allow creation of PediaPress shortcodes, with attributes to override global settings.
* REFACTOR: re-worked class RDP_WE_PPE to handle shortcode attributes
* REFACTOR: removed label text from media button so that it now only displays the Wikipedia icon
* REFACTOR: added a short circuit to main init() function to prevent links to front-end scripts and styles from being generated on the admin side
* FIX: fixed bug that corrupted AJAX return results
* UPDATE: changed screenshot #3
* UPDATE: added screenshot #4

= Changed files: =
* readme.txt
* screenshot-3.png
* WikiEmbed.php
* admin/admin-overlay.php
* admin/settings-page.php
* resources/rdpWEPPE.php
* resources/css/pediapress.common.css

= New files: =
* screenshot-4.png
* admin/css/jquery-ui.css
* admin/css/jquery-ui.theme.min.css
* admin/js/script.admin-overlay.js

= 1.1.0 =
* FEATURE: added settings to disable PediaPress book TOC links
* FEATURE: added setting to show/hide PediaPress book TOC
* FEATURE: added lightbox with iframe to PediaPress book cover images and 'Add to Cart' button
* REFACTOR: fixed the Colorbox skin
* REFACTOR: fixed layout issues on the settings page
* FIX: updated screenshot #2

= Changed files: =
* readme.txt
* WikiEmbed.php
* screenshot-2.png
* resources/rdpWEPPE.php
* admin/settings-page.php
* resources/css/colorbox.css

= New files: =
* resources/js/pediapress-overlay.js
* resources/css/images/border1.png
* resources/css/images/border2.png
* resources/css/images/loading.gif

= 1.0.1 =
* updated screenshot #2

= 1.0.0 =
* FEATURE: added wiki content overwrite functionality
* FEATURE: added PediaPress content overwrite functionality
* FEATURE: added global overwrite functionality for pages not containing the [wiki-embed] shortcode
* FEATURE: added 'No Caching' settings option
* FEATURE: added check for custom CSS files located in resources/css folder
* FIX: re-worked code to eliminate some 'Undefined index' notices when reading from various variables

= Changed files: =
* readme.txt
* WikiEmbed.php
* admin/settings-page.php
* resources/css/wiki-embed.css
* resources/simple_html_dom.php

= New files: =
* resources/css/pediapress.common.css
* resources/css/pediapress.custom-sample.css
* resources/css/wiki.custom-sample.css
* resources/js/wiki-embed-overwrite.js
* resources/js/script.wcr.js
* resources/js/url.min.js
* resources/rdpWEPPE.php

= Inherited from Original Wiki Embed Plugin =
= 1.4.5 =
* wordpress search queries will now also search wiki-embedded content.

= 1.4.4 =
* allowing to display object and param tags so that we can embed kultura videos

= 1.4.3 =
* wiki_embed cache now not auto loaded.
* better escaped content that is coming from the the wiki
* removed unwanted site_options 
* bug fix: UTF encoding

= 1.4.2 =
* deploy script didn't quite work trying with a different user

= 1.4.1 =
* Bug fix: Prevents the DOMDocument parser from giving PHP Warnings if its given bad HTML from the source page

= 1.4 =
* Enables ability to split article into accordion (via jQuery UI)
* Rewrote function that parses/renders html to no longer use Simple HTML DOM parser as it uses a lot of resources 
* Updated caching: Now if the remote page can't be accessed, cached content will be served if it exists, even if it is past its expiry date.
* More caching: Now if the cache expires the page will show the cached content one last time and refresh it immediately after. This means the user should never really face delays for the wiki content is to be retrieved and parsed.
* Fixed overlay view
* The refresh of the page happens after the page has been set to the browser. 
* Skipping version 1.3 since there are a lot of change in version 1.4

= 1.2.4 =
* Bug fix: Causes errors on 404 pages. Links stop working. Now fixed
* removed un-necessary files

= 1.2.3 =
* Settings page bug fix, wasn't working properly. 
* Always ignore files that link to pdf documents, 
* convert &amp; to & 

= 1.2.2 = 
* removed unnecessary javascript files
* add files for css and js on admin side
* lots of bug fixes 
* better url recognition
* bug fix for table of content anchor linking 

= 1.2.1 = 
* bug fix
* #toc links weren't ignored properly 
* force styles that might not be present

= 1.2 = 
* Added a security feature that only allows certain sites to be wiki embedable in your site.
* Bug fixes, TOC is not hijacked by js any more and is treated as an internal link, update the overlay to use HTML5
* allow default a get parameter to be passed to the url. so you can do stuff like [wiki-embed url='http://en.wikipedia.org/wiki/%page%' get=page default_get=WordPress]
* and now if you go to the page that has that shortcode and pass in a ?page=English in the url
* it will embed the http://en.wikipedia.org/wiki/English instead of the default http://en.wikipedia.org/wiki/WordPress
* allow for plan html view by going to the {siteurl}/wp-admin/admin-ajax.php?url={encoded_url}&action=wiki_embed&plain_html=1
* this make the wiki embed act like a scraper.

= 1.1 = 
* Bug fix will display the admin overlay again, this bug occurs only when wiki embed is network activated 

= 1.0 = 
* removed unneeded code 
* improved setting menu navigation 
* improved Wiki Embed List | added the ability to see embeds that don't have a target url easier
* bug fix. embedded images are not conceded as internal links any more 
* bug fix. editor can now save settings. Added email to setting to which notifications are sent to if a visitor stumbles on a link that is not part of the navigation. 
* improvement easier to add target urls to wiki embeds. 
* version bumped up to 1.0 
* ajaxurl changed to wiki_embed_ajaxurl 
* version bumped up to 1.0

= 0.9.1 = 
* Pagination added 
* Icons added 
* How the content gets saved and stored improved. For example if you are quering the same wiki contetent only you will only request the content from the wiki once.
* Default Settings added
* This is the version before that is undergoing verification testing

= 0.9 =
* Is the pre production release, please help us test it. 
* Default Settings added
* This is the version before that is undergoing verification testing

= 0.9 =
* Is the pre production release, please help us test it.

== Upgrade Notice ==

= 2.2.0 =
Re-worked code to keep cached images, custom css files, custom gallery template from being wiped out during a plugin update via the update procedure used within the WP Admin area
For custom css of PediaPress items, the file pediapress.custom.css needs to be placed in the current theme's folder.
For custom css of Wiki items, the file wiki.custom.css needs to be placed in the current theme's folder.
Book cover images are now cached in the uploads directory, inside a folder named rdp-wiki-press-embed
For a custom gallery template, place a modified copy of the file ppgallery.column.results.html inside a folder named rdp-wiki-press-embed, in the uploads directory
 
== Other Notes ==

== PHP Hook Reference: ==

= rdp_pp_book_scripts_enqueued =

* Param 1: Shortcode attributes
* Param 2: Shortcode content
* Fires after enqueuing scripts and styles for a single book

= rdp_pp_gallery_scripts_enqueued =

* Param 1: Shortcode attributes
* Param 2: Shortcode content
* Fires after enqueuing scripts and styles for a gallery page


== PHP Filter Reference: ==

= rdp_pp_book_cta_button =

* Param: String containing HTML for Call-to-Action button when displaying a single book
* Return: HTML for Call-to-Action button when displaying a single book


= rdp_pp_book_main_content_classes =

* Param: String containing class names for the #mainContent container when displaying a single book
* Return: class names for the #mainContent container when displaying a single book


= rdp_pp_book_atc_href =

* Param: String containing href value for Call-to-Action button when displaying a single book
* Return: href value for Call-to-Action button when displaying a single book


= rdp_pp_gallery_item =

* Param 1: String containing HTML for a single gallery item
* Param 2: Array of input values for the gallery item
* Return: HTML for a single gallery item


== Javascript Hook Reference: ==

= rdp_pp_gallery_colorbox_onOpen =

* Param 1: jQuery Event object
* Param 2: jQuery object that fired the hook
* Fires after updating tab #2 in the Colorbox lightbox


== Gallery Item Merge Code Reference: ==

* %%BookSize%% = book size
* %%CTAButtonText%% = Call-to-Action text
* %%Editor%% = book editor
* %%Excerpt%% = excerpt for the book
* %%FullTitle%% = concatenation of the book title and sub-title
* %%Image%% = URL to book cover image
* %%Language%% = book language
* %%Link%% = URL to PediaPress.com book page
* %%PostID%% = post id for the book
* %%PostLink%% = URL to the post for the book
* %%PriceAmount%% = book price
* %%PriceCurrency%% = currency denomination
* %%Subtitle%% = book sub-title
* %%Title%% = book title
