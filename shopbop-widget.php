<?php
/*

Plugin Name: Shopbop
Plugin URI: http://www.shopbop.com/go/widgets
Description: This plugin allows you to add the official Shopbop widget to the sidebar of your Wordpress blog.
Version: 3.1.0
Author: Stickyeyes
Author URI: http://www.stickyeyes.com/
License: GPL2

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

if (!version_compare(phpversion(), "5.3.0", ">=")) {
	add_action('admin_init', 'shopbop_plugin_deactivate');
	add_action('admin_notices', 'shopbop_php_version_admin_notice');

	function shopbop_plugin_deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	function shopbop_php_version_admin_notice()
	{
		$class = 'notice notice-error';
		$message = "The Shopbop Widget plugin is not run under PHP version " . phpversion() . ". Shopbop Widget plugin requires at least PHP version 5.3 or greater. The Shopbop Widget plugin has been deactivated";
		printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

		if (isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
	}
} else {
	include_once __DIR__ . "/load.php";
}