=== WP Site Connector ===
Contributors: inopik
Tags: sync, synchronization, multisite, content, posts
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connects local WordPress site with remote WordPress site to sync content changes in real-time.

== Description ==

WP Site Connector enables real-time synchronization between a local WordPress site and a remote WordPress site. Any changes made to posts and pages on the local site are automatically reflected on the remote site.

= Features =

* Real-time synchronization of posts and pages
* Secure API key authentication
* Selective content type synchronization
* Featured image support
* Category and tag synchronization
* Detailed sync logging
* Easy configuration through admin interface
* Connection testing tool

= Use Cases =

* Sync content from development to production
* Maintain multiple sites with the same content
* Create backup content mirrors
* Distribute content across multiple domains

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/wp-site-connector/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Install and activate on both local and remote sites
4. Configure through Settings → Site Connector

== Frequently Asked Questions ==

= Does this work with Multisite? =

Yes, the plugin works with WordPress Multisite installations.

= Can I sync custom post types? =

Currently only posts and pages are supported out of the box, but the plugin can be extended to support custom post types.

= Is the connection secure? =

Yes, the plugin uses API keys for authentication and should be used over HTTPS for encrypted communication.

= What happens if the remote site is down? =

The sync will fail and be logged. You can retry manually or wait for the remote site to come back online.

== Screenshots ==

1. Settings page with connection configuration
2. Sync logs showing successful synchronizations
3. Test connection interface

== Changelog ==

= 1.0.0 =
* Initial release
* Posts and pages synchronization
* REST API endpoints
* Admin interface
* Sync logging

== Upgrade Notice ==

= 1.0.0 =
Initial release of WP Site Connector.
