=== mailto:staff ===
Contributors: decafmedia
Tags: users, email, mail, dashboard
Requires at least: 2.3.1
Tested up to: 3.5
Stable tag: 2.4.2

*** New version 3.0 upcoming, see changelog! *** Multi-author blogs: Plugin generates mailto links on the dashboard referring to all registered users. Quite handy way of internal staff communication.

== Description ==

Multi-author blogs: Plugin generates mailto links on the dashboard referring to all registered users. Quite handy way of internal staff communication.

**Features:**

* Mailto links can address any of group admins, editors, authors, contributors and subscribers based on current user's role.
* User roles: Subscribers can address admins only. Contributors can address editors and admins. Authors can address authors, editors and admins. Editors and admins can address all users.
* Duplicate mail addys are removed.
* BCC mail if recipients contain subscribers (-> privacy!).
* Multi-language, see [notes](http://wordpress.org/extend/plugins/mailtostaff/other_notes/).

The plugin needs JavaScript enabled to function.

== Installation ==

1. Upload the mailto-staff folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. Upcoming version of plugin on WordPress 3.8 dashboard.
2. Plugin supports not only default user roles but custom roles now.
3. Mailto link with your staff being addressed directly.
4. BCC mail if recipients contain subscribers. Privacy FTW!

== Changelog ==

= 3.0 (upcoming) =
* Supports custom roles now.
* WP 3.8 compatibility
* Doesn't use the activity widget (»Right now«) any longer but comes with a shiny new own widget now.
* New style, new icons, new code.

= 2.4.2 (12-30-2012) =
* WP 3.5 compatibility

= 2.4.1 (07-23-2011) =
* Yuck, previous release was incomplete

= 2.4 (07-23-2011) =
* WP 3.2 compatibility
* Use separate widget for subscribers as they don't get the activity widget (»Right now«)

= 2.3.1 (09-22-2009) =
* Make plugin work with WP < 2.8 again. (Sorry for the inconvenience!)

= 2.3 (09-15-2009) =
* i18n bugfix on user roles

= 2.2 (06-26-2009) =
* saves selection in cookie now
* some minor changes

= 2.1 (06-15-2009) =
* improvement: select admins if editors are missing

== Localization (L10n) ==

Plugin makes use of WordPress' language files and therefore doesn't need separate localization.

== Credits ==

Plugin uses icon stuff from famfamfam's [silk](http://www.famfamfam.com/lab/icons/silk/) which is licensed under a Creative Commons Attribution 2.5 License. Thank you, Mark!

== Alternative Plugins? ==

Have a look at [Email Users](http://wordpress.org/extend/plugins/email-users/) or [User Messages](http://wordpress.org/extend/plugins/user-messages/) if you're looking for an internal messaging system instead of simple mailto links.