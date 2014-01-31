=== mailto:staff ===
Contributors: decafmedia
Tags: users, email, mail, dashboard, team, authors
Requires at least: 3.1
Tested up to: 3.8
Stable tag: 3.0

For multi-author blogs: The Plugin generates a mailto link on the dashboard.

== Description ==

Multi-author blogs: Plugin generates simple mailto links on the dashboard referring to all registered users. Quite handy way of internal staff communication.

**Features:**

* Mailto links can address any of group admins, editors, authors, contributors and subscribers based on current user's role.
* User roles: Subscribers can address admins only. Contributors can address editors and admins. Authors can address authors, editors and admins. Editors and admins can address all users.
* Duplicate mail addys get filtered out.
* BCC mail if recipients contain subscribers (due to privacy concerns!).
* Multi-language, see [notes](http://wordpress.org/extend/plugins/mailtostaff/other_notes/).

The plugin needs JavaScript enabled to function.

== Installation ==

1. Upload the mailto-staff folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==

1. WP 2.7 (WP 2.8 - 3.2 respectively)
2. WP 2.6
3. Default mail
4. BCC mail if recipients contain subscribers (-> privacy!)

== Changelog ==

= 2.5 (12-30-2013) =
* WP 3.8 compatibility
* some cleanup and refactoring

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

