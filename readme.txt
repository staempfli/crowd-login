=== Stämpfli Crowd Login ===

Contributors: florianauderset
Donate link: https://www.staempfli.com
Tags: login, atlassian, crowd
Requires at least: 5.4
Tested up to: 5.4
Stable tag: 5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A loginprovider for atlassian crowd.

== Description ==

This plugin enables you to login to WordPress using Atlassian Crowd.
Plugin was tested with Atlassian Crowd 4.1.0 but will possibly work with oder versions.

Login Modes:
- login only (User can be created manually after login)
- create user (User is created after successful authentication)
- create user when in group (User is only created if user is member of a specified group)

You can choose what user role should be assigned on user creation.

Security Modes:
- normal (Mixed mode. Fist authenticate against Atlassian Crowd, then Fallback to default WordPress authentication)
- strict (only use Atlassian Crowd, disable default WordPress autentication)

All product names, logos, and brands are property of their respective owners.

== Installation ==

1. Upload `staempfli-crowd-login` folder to the `/wp-content/plugins/` directory or install from WordPress backend
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure plugin

== Screenshots ==

1. Crowd Login settings page

== Changelog ==

## 1.0.0
* First release
