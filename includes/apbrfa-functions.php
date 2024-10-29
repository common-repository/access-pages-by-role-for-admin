<?php
/**
 * access-pages-by-role-for-admin-functions
 *
 * Helper functions used in the plugin
 */

// Adding meta boxes for selected post types
function apbrfa_add_custom_box() {
	$get_selected_types   = get_option( 'access_pages_by_role_general' );
	$selected_types_posts = $get_selected_types['apbrfa_posts'];

	if ( current_user_can( 'activate_plugins' ) ) {
		add_meta_box( 'apbrfa_sectionid', __( 'Access Control', 'access-pages-by-role-for-admin' ), 'apbrfa_meta_box_callback', $selected_types_posts, 'side', 'low' );
	} else {
		return;
	}
}

add_action( 'add_meta_boxes', 'apbrfa_add_custom_box' );

function apbrfa_meta_box_callback( $post ) {

	$apbrfa_roles = get_post_meta( $post->ID, 'apbrfa-access-is-closed', true );

	$apbrfa_roles = is_array( $apbrfa_roles ) ? $apbrfa_roles : array();

	$all_roles = new WP_Roles();
	$roles     = $all_roles->get_names();

	unset( $roles['administrator'] );

	if ( is_array( $roles ) && count( $roles ) > 0 ) {

		$html = '<input type="hidden" name="apbrfa_noncename" id="apbrfa_noncename" value="' . wp_create_nonce( 'apbrfa_noncename' ) . '" />';
		$html .= '<div style="padding:10px;"><label>' . __( 'Select the roles for which you want to block access', 'access-pages-by-role-for-admin' ) . '</label> <br /> <hr />';
		$html .= '<ul class="apbrfa-roles">';

		foreach ( $roles as $value => $label ) {
			$checked = in_array( $value, $apbrfa_roles ) ? 'checked="checked"' : '';

			$html .= '<li><span><input type="checkbox" ' . $checked . ' name="apbrfa_roles[]" value="' . $value . '" />&nbsp;' . $label . ' </span></li>';

		}

		$html .= '</ul></div>';

		echo $html;
	}
}

function apbrfa_save_postdata( $post_id ) {

	if ( isset( $_POST['apbrfa_noncename'] ) && ! wp_verify_nonce( $_POST['apbrfa_noncename'], 'apbrfa_noncename' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$apbrfa_roles = isset( $_POST['apbrfa_roles'] ) && is_array( $_POST['apbrfa_roles'] ) ? $_POST['apbrfa_roles'] : array();
	$apbrfa_roles = array_map( 'sanitize_text_field', $apbrfa_roles );

	delete_post_meta( $post_id, 'apbrfa-access-is-closed' );

	if ( ! empty( $apbrfa_roles ) ) {
		add_post_meta( $post_id, 'apbrfa-access-is-closed', $apbrfa_roles );
	}
}

add_action( 'save_post', 'apbrfa_save_postdata' );

function apbrfa_add_columns( $columns ) {
	$columns['apbrfa'] = __( 'Access Control', 'access-pages-by-role-for-admin' );

	return $columns;
}

add_filter( 'manage_posts_columns', 'apbrfa_add_columns' );
add_filter( 'manage_pages_columns', 'apbrfa_add_columns' );

function apbrfa_post_column( $field, $post_id ) {
	global $wp_roles;

	if ( $field == 'apbrfa' ) {
		$apbrfa_roles = get_post_meta( $post_id, 'apbrfa-access-is-closed', true );
		$apbrfa_roles = is_array( $apbrfa_roles ) ? $apbrfa_roles : array();

		$roles         = $wp_roles->get_names();
		$blocked_roles = array();

		foreach ( $apbrfa_roles as $role ) {
			$blocked_roles[] = isset( $roles[ $role ] ) ? $roles[ $role ] : $role;
		}

		if ( count( $blocked_roles ) > 0 ) {
			echo sprintf( '<span style="color:red;" class="apbrfa-block">%s</span> %s', __( 'Blocked for:', 'access-pages-by-role-for-admin' ), implode( ', ', $blocked_roles ) );
		} else {
			echo __( 'No restrictions', 'access-pages-by-role-for-admin' );
		}
	}
}

add_action( 'manage_posts_custom_column', 'apbrfa_post_column', 10, 2 );
add_action( 'manage_pages_custom_column', 'apbrfa_post_column', 10, 2 );

//Adding meta boxes for selected tax types
function apbrfa_add_columns_tax() {
	$get_selected_types = get_option( 'access_pages_by_role_general' );

	if ( ! empty( $get_selected_types['apbrfa_taxonomies'] ) ) {
		$selected_types_taxs = $get_selected_types['apbrfa_taxonomies'];
	}

	if ( ! empty( $selected_types_taxs ) && current_user_can( 'activate_plugins' ) ) {
		foreach ( $selected_types_taxs as $selected_types_tax ) {
			add_action( $selected_types_tax . '_edit_form_fields', 'apbrfa_edit_form_fields', 10 );

			add_filter( 'manage_edit-' . $selected_types_tax . '_columns', 'apbrfa_add_columns' );
			add_filter( 'manage_' . $selected_types_tax . '_custom_column', 'apbrfa_add_columns_tax_data', 10, 3 );
		}
	} else {
		return;
	}

}

add_action( 'admin_init', 'apbrfa_add_columns_tax' );

function apbrfa_edit_form_fields( $term ) {

	$apbrfa_roles = get_term_meta( $term->term_id, 'apbrfa-access-is-closed', true );

	$apbrfa_roles = is_array( $apbrfa_roles ) ? $apbrfa_roles : array();

	$all_roles = new WP_Roles();
	$roles     = $all_roles->get_names();

	unset( $roles['administrator'] );

	if ( is_array( $roles ) && count( $roles ) > 0 ) {

		$html = '<input type="hidden" name="apbrfa_noncename" id="apbrfa_noncename" value="' . wp_create_nonce( 'apbrfa_noncename' ) . '" />';

		$html .= '<tr class="form-field"><th scope="row" valign="top"><label>'
		         . __( 'Access Control', 'access-pages-by-role-for-admin' ) .
		         '</label></th><td><fieldset><label>'
		         . __( 'Select the roles for which you want to block access', 'access-pages-by-role-for-admin' ) .
		         '</label></fieldset><hr><fieldset>';

		foreach ( $roles as $role_key => $role_name ) {
			$checked = in_array( $role_key, $apbrfa_roles ) ? 'checked="checked"' : '';

			$html .= '<label for="' . $role_key . '"><input id="' . $role_key . '" type="checkbox" name="apbrfa_roles[]" value="' . $role_key . '" ' . $checked . '>' . $role_name . '</label><br />';
		}

		$html .= '</fieldset></td></tr>';

		echo $html;
	}
}

function apbrfa_edit_form_fields_save( $term_id ) {
	if ( isset( $_POST['apbrfa_noncename'] ) && ! wp_verify_nonce( $_POST['apbrfa_noncename'], 'apbrfa_noncename' ) ) {
		return;
	}

	if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	$apbrfa_roles = isset( $_POST['apbrfa_roles'] ) && is_array( $_POST['apbrfa_roles'] ) ? $_POST['apbrfa_roles'] : array();
	$apbrfa_roles = array_map( 'sanitize_text_field', $apbrfa_roles );

	delete_term_meta( $term_id, 'apbrfa-access-is-closed' );

	if ( ! empty( $apbrfa_roles ) ) {
		add_term_meta( $term_id, 'apbrfa-access-is-closed', $apbrfa_roles );
	}
}

add_action( 'edit_terms', 'apbrfa_edit_form_fields_save' );

function apbrfa_add_columns_tax_data( $value, $column_name, $term_id ) {
	global $wp_roles;

	if ( $column_name == 'apbrfa' ) {
		$apbrfa_roles = get_term_meta( $term_id, 'apbrfa-access-is-closed', true );
		$apbrfa_roles = is_array( $apbrfa_roles ) ? $apbrfa_roles : array();

		$roles         = $wp_roles->get_names();
		$blocked_roles = array();

		foreach ( $apbrfa_roles as $role ) {
			$blocked_roles[] = isset( $roles[ $role ] ) ? $roles[ $role ] : $role;
		}

		if ( count( $blocked_roles ) > 0 ) {
			$value = sprintf( '<span style="color:red;" class="apbrfa-block">%s</span> %s', __( 'Blocked for:', 'access-pages-by-role-for-admin' ), implode( ', ', $blocked_roles ) );
		} else {
			$value = __( 'No restrictions', 'access-pages-by-role-for-admin' );
		}
	}

	return $value;
}

//control
function apbrfa_block_access_page() {
	global $wp_query;
	//all options
	$get_options = get_option( 'access_pages_by_role_general' );

	if ( empty( $get_options ) ) {
		return;
	}
	//types
	if ( ! empty( $get_options['apbrfa_posts'] ) ) {
		$get_options_post = $get_options['apbrfa_posts'];
	} else {
		$get_options_post = '';
	}

	if ( ! empty( $get_options['apbrfa_taxonomies'] ) ) {
		$get_options_tax = $get_options['apbrfa_taxonomies'];
	} else {
		$get_options_tax = '';
	}

	$allow_not_loggin_user       = $get_options['apbrfa_users_not_logged'];
	$apbrfa_redirect_login       = $get_options['apbrfa_redirect_login'];
	$apbrfa_default_redirect_url = isset( $get_options['apbrfa_default_redirect_url'] ) ? trim( $get_options['apbrfa_default_redirect_url'] ) : '';

	$page_id = $wp_query->get_queried_object_id();

	$ckeck_access_page = get_post_meta( $page_id, 'apbrfa-access-is-closed', true );
	$ckeck_access_term = get_term_meta( $page_id, 'apbrfa-access-is-closed', true );

	$current_user       = wp_get_current_user();
	$current_user_roles = $current_user->roles;

	if ( ! empty( $ckeck_access_page ) ) {
		if ( ! is_user_logged_in() && count( $ckeck_access_page ) > 0 && $allow_not_loggin_user == 'off' ) {
			$check_access_page = true;
		} else {
			$ckeck_access_page_int = array_intersect( $current_user_roles, $ckeck_access_page );

			if ( count( $ckeck_access_page_int ) > 0 ) {
				$check_access_page = true;
			} else {
				$check_access_page = false;
			}
		}
	} else {
		$check_access_page = false;
	}

	if ( ! empty( $ckeck_access_term ) ) {
		if ( ! is_user_logged_in() && count( $ckeck_access_term ) > 0 && $allow_not_loggin_user == 'off' ) {
			$check_access_term = true;
		} else {
			$ckeck_access_term_int = array_intersect( $current_user_roles, $ckeck_access_term );

			if ( count( $ckeck_access_term_int ) > 0 ) {
				$check_access_term = true;
			} else {
				$check_access_term = false;
			}
		}
	} else {
		$check_access_term = false;
	}

	if ( ! is_user_logged_in() && ( ( is_singular( $get_options_post ) && $check_access_page == true ) || ( ( is_tax( $get_options_tax ) ) || is_archive( $get_options_tax ) ) && $check_access_term == true ) ) {
		if ( $apbrfa_redirect_login == 'on' ) {

			$apbrfa_redirect_login = wp_login_url();

			if ( headers_sent() ) {
				die( "<script>window.location='$apbrfa_redirect_login';</script>" );
			}

			wp_redirect( $apbrfa_redirect_login );
			die();
		} elseif ( is_string($apbrfa_default_redirect_url) && $apbrfa_default_redirect_url != '' ) {

			if ( headers_sent() ) {
				die( "<script>window.location='$apbrfa_default_redirect_url';</script>" );
			}

			wp_redirect( $apbrfa_default_redirect_url );
			die();
		}

		wp_die( __( 'You don\'t have access to this page, contact the website administrator.', 'access-pages-by-role-for-admin' ), 'Access is denied' );

	} elseif ( ( ( is_singular( $get_options_post ) && $check_access_page == true ) || ( ( is_tax( $get_options_tax ) || is_archive( $get_options_tax ) ) && $check_access_term == true ) ) ) {

		if ( is_string($apbrfa_default_redirect_url) && $apbrfa_default_redirect_url != '' ) {
			if ( headers_sent() ) {
				die( "<script>window.location='$apbrfa_default_redirect_url';</script>" );
			}

			wp_redirect( $apbrfa_default_redirect_url );
			die();
		}

		wp_die( __( 'You don\'t have access to this page, contact the website administrator.', 'access-pages-by-role-for-admin' ), 'Access is denied' );
	}
}

add_filter( 'template_redirect', 'apbrfa_block_access_page' );

