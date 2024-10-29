<?php
/**
 * Plugin Name: Access Pages by Role for Admin
 * Plugin URI: https://wordpress.org/plugins/access-pages-by-role-for-admin/
 * Description: Allows an administrator to control access to pages by user role.
 * Author:      WacoMart
 * Author URI: 	https://wacomart.ru
 * Version:     1.0
 * Text Domain: access-pages-by-role-for-admin
 * Domain Path: /languages/
 * WP requires at least: 4.6
 * License: GPLv2 or later
 */
/*
Copyright 2020 WacoMart (email : info@wacomart.ru)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AP_BR_FA' ) ) {

	class AP_BR_FA {

		function __construct() {

			// Include required files
			add_action( 'plugins_loaded', array( &$this, 'apbrfa_includes' ) );

			// Load translation
			add_action( 'init', array( &$this, 'apbrfa_init' ) );


		}

		function apbrfa_includes() {
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class.settings-api.php' );
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/apbrfa-settings.php' );
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/apbrfa-functions.php' );
		}

		function apbrfa_init() {
			load_plugin_textdomain( 'access-pages-by-role-for-admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		static function apbrfa_uninstall() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			delete_option( 'access_pages_by_role_general' );

			$allposts = get_posts( 'numberposts=-1&meta_key=apbrfa-access-is-closed&post_type=any&post_status=any' );

			if ( ! empty( $allposts ) ) {
				foreach ( $allposts as $postinfo ) {
					delete_post_meta( $postinfo->ID, 'apbrfa-access-is-closed' );
				}
			}

			$allterms = get_terms( 'fields=all&hide_empty=0&meta_key=apbrfa-access-is-closed' );

			if ( ! empty( $allterms ) ) {
				foreach ( $allterms as $terminfo ) {
					delete_term_meta( $terminfo->term_id, 'apbrfa-access-is-closed' );
				}
			}

		}

	}

	$ap_br_fa = new AP_BR_FA();

	register_uninstall_hook( __FILE__, array( 'AP_BR_FA', 'apbrfa_uninstall' ) );

} // If not class exists

?>