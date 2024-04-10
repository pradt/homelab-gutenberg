=== Homelab Plugin ===
Contributors: Pratheepan Thevadasan
Tags: homelab, services, monitoring, status
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 0.1
License: GPLv2 or later


A plugin to manage and monitor homelab services within WordPress.

== Description ==
I'm run a homelab and I was frustrated with various dashboard applications, they were often clunky in the way that you need to update them (update configuration text files and/or compile the application to get your changes through),
they were not easy to use and they were not quick. However, I wanted more from my dashboard I wanted to link it to my internal wordpress site. So I created this plugin that will allow you to have a dashboard, and you can design your own using Gutenberg editor using the Gutenberg Block. 

This plugin will create additional tables within the wordpress database.

The Homelab plugin allows you to manage and monitor your homelab services directly from your WordPress site. With this plugin, you can:

- Add and manage your homelab services
- Monitor the status of your services
- Display service information and status using a custom Gutenberg block
- Customize the appearance and functionality of the service block
- Receive notifications when a service goes down or experiences issues

The plugin provides an intuitive interface for managing services, including the ability to add service details, configure monitoring settings, and view service status history. It also includes a custom Gutenberg block that allows you to easily display service information and status on your WordPress pages or posts.

== Installation ==

Option 1

1. Upload the `homelab-plugin` directory to the `/wp-content/plugins/` directory of your WordPress site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin settings and add your homelab services through the 'Homelab' menu in the WordPress admin area.
4. Use the provided Gutenberg block to display service information and status on your pages or posts.

Option 2
1. Upload the `homelab-plugin` .zip file to the plugins area through the Wordpress UI.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin settings and add your homelab services through the 'Homelab' menu in the WordPress admin area.
4. Use the provided Gutenberg block to display service information and status on your pages or posts.

== Usage ==

1. Go to the 'Homelab' menu in the WordPress admin area.
2. Click on 'Add New Service' to add a new homelab service.
3. Fill in the service details, including name, description, URL, monitoring settings, and notification preferences.
4. Save the service.
5. To display a service on a page or post, use the 'Homelab Service' block in the Gutenberg editor.
6. Configure the block settings to customize the appearance and information displayed for the service.
7. Publish or update the page or post to see the service information and status.

== Frequently Asked Questions ==

= How do I add a new homelab service? =

Go to the 'Homelab' menu in the WordPress admin area and click on 'Add New Service'. Fill in the service details and save the service.

= How can I display a service on my website? =

Use the 'Homelab Service' block in the Gutenberg editor when creating or editing a page or post. Configure the block settings to customize the appearance and information displayed for the service.

= Can I customize the appearance of the service block? =

Yes, the 'Homelab Service' block provides various options to customize the appearance, including the ability to show/hide certain elements and configure colors and styles.

= How can I receive notifications when a service goes down? =

When adding or editing a service, you can configure the notification settings to specify an email address where notifications will be sent when the service goes down or experiences issues.

== Feedback and Issues ==
If you have any Feedback, Feature Request, and/or have come across any problems please log an issue on the project's github page. 

== Changelog ==

= 0.1 =
* Initial release of the Homelab plugin.

== Upgrade Notice ==

= 0.1 =
Initial release of the Homelab plugin. No upgrade necessary.