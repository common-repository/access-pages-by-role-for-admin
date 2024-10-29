<?php
/**
 * Access Pages by Role Settings
 *
 * Uses APBRFA_Settings_API Class
 */

if ( ! class_exists( 'Generate_APBRFA_Settings' ) ) :

	class Generate_APBRFA_Settings {

		private $settings_api;

		function __construct() {
			$this->settings_api = new APBRFA_Settings_API;

			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		function admin_init() {

			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			$this->settings_api->admin_init();
		}

		function admin_menu() {
			add_options_page( 'Access Pages by Role Settings', __( 'Access Pages Control', 'access-pages-by-role-for-admin' ), 'manage_options', 'access-pages-by-role-settings', array(
				$this,
				'plugin_page'
			) );
		}

		function get_settings_sections() {
			$sections = array(
				array(
					'id'    => 'access_pages_by_role_general',
					'title' => esc_attr__( 'General', 'access-pages-by-role-for-admin' )
				),
				array(
					'id'            => 'access_pages_by_role_help',
					'title'         => esc_attr__( 'Help', 'access-pages-by-role-for-admin' ),
					'submit_button' => false
				)
			);

			return $sections;
		}

		function get_settings_fields() {

			$post_type_default = array(
				'page' => __( 'Pages', 'access-pages-by-role-for-admin' ),
				'post' => __( 'Posts', 'access-pages-by-role-for-admin' )
			);

			$post_types = get_post_types( array( '_builtin' => false ), 'objects', 'and' );

			$post_types_arr = array();

			if ( isset( $post_types ) && is_array( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					$post_types_arr[ $post_type->name ] = $post_type->label;
				}
			}

			$post_types_arr = array_merge( $post_type_default, $post_types_arr );

			$taxonomies_types = get_taxonomies( array( 'public' => true ), 'objects' );

			$taxonomies_types_arr = array();

			if ( isset( $taxonomies_types ) && is_array( $taxonomies_types ) ) {
				foreach ( $taxonomies_types as $taxonomies_type ) {
					$taxonomies_types_arr[ $taxonomies_type->name ] = $taxonomies_type->label;
				}
				unset( $taxonomies_types_arr['post_format'] );
			}

			$settings_fields = array(
				'access_pages_by_role_general' => array(
					array(
						'name'  => 'apbrfa_users_not_logged',
						'title' => esc_attr__( 'Allow users not logged in access', 'access-pages-by-role-for-admin' ),
						'label' => esc_attr__( 'Turn it on if you want non-authorized users (guests) to be able to visit restricted pages (pages that have a restriction for any role).', 'access-pages-by-role-for-admin' ),
						'type'  => 'checkbox'
					),
					array(
						'name'              => 'apbrfa_default_redirect_url',
						'title'             => esc_attr__( 'Default Redirect URL', 'access-pages-by-role-for-admin' ),
						'desc'              => esc_attr__( 'The user will be redirected to this URL without access to the page', 'access-pages-by-role-for-admin' ),
						'type'              => 'textarea',
						'default'           => get_site_url(),
						'sanitize_callback' => ''
					),

					array(
						'name'  => 'apbrfa_redirect_login',
						'title' => esc_attr__( 'Redirect to login', 'access-pages-by-role-for-admin' ),
						'label' => esc_attr__( 'Turn it on if you want an unauthorized user to be redirected to the login page instead Default Redirect URL.', 'access-pages-by-role-for-admin' ),
						'type'  => 'checkbox'
					),
					array(
						'name'    => 'apbrfa_posts',
						'title'   => esc_attr__( 'Types of Posts', 'access-pages-by-role-for-admin' ),
						'desc'    => esc_attr__( 'Choose what types of posts you want to control access to. Hold CTRL to select multiple.', 'access-pages-by-role-for-admin' ),
						'type'    => 'select_multiple',
						'options' => $post_types_arr
					),
					array(
						'name'    => 'apbrfa_taxonomies',
						'title'   => esc_attr__( 'Types of Taxonomies', 'access-pages-by-role-for-admin' ),
						'desc'    => esc_attr__( 'Choose what types of taxonomies you want to control access to. Hold CTRL to select multiple.', 'access-pages-by-role-for-admin' ),
						'type'    => 'select_multiple',
						'options' => $taxonomies_types_arr
					)
				),
				'access_pages_by_role_help'    => array(
					array(
						'name'  => 'apbrfa_users_not_logged',
						'title' => esc_attr__( 'How do I get support?', 'access-pages-by-role-for-admin' ),
						'desc'  => __( 'If you have questions or problems when working with this plugin, you can get help on the support forum <a href="https://wordpress.org/support/plugin/access-pages-by-role-for-admin/" target="_blank">wordpress.org</a>', 'access-pages-by-role-for-admin' ),
						'type'  => 'html'
					)
				),
			);

			return $settings_fields;
		}

		function plugin_page() {
			echo '<div class="wrap">';
			echo '<h1>' . esc_attr__( 'Access Pages by Role Settings', 'access-pages-by-role-for-admin' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
		}

		function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}
	}

	$generate_apbrfa_settings = new Generate_APBRFA_Settings();

endif;