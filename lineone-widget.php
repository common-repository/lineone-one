<?php
/**
 * Plugin Name: LineOne.One
 * Description: LineOne - your websites greatest agent. LineOne connects you to your customers straight away on the phone so you can talk and help your users decide what to buy or learn about next. LineOne increases conversions and gives your website the WOW factor. Free to install and you only pay for calls you receive, calls cost from 20 cents. Cheaper than any cpc or buying a lead elsewhere.
 * Author: LineOne
 * Version: 1.0.0
 */

/**
 * Add the top level menu page.
 */
function wplo_create_plugin_settings_page(){
	// Add the menu item and page
	$page_title = 'LineOne Settings Page';
	$menu_title = 'LineOne Widget ';
	$capability = 'manage_options';
	$slug = 'lineone-widget';
	$callback = 'wplo_plugin_settings';
	$icon = 'dashicons-admin-plugins';
	$position = 100;

	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );	
}
add_action( 'admin_menu', 'wplo_create_plugin_settings_page' );

/**
 * Top level menu callback function
 */
function wplo_plugin_settings(){
	if( isset($_POST['updated']) && $_POST['updated'] === 'true' ){
		wplo_handle_form();
	}
	echo '<div class="wrap">
		<h2>LineOne Widget Settings</h2>
		<form method="POST">
			<input type="hidden" name="updated" value="true" />';
			wp_nonce_field( 'lineone_update', 'lineone_form' );
			echo '<table class="form-table">
				<tbody>
					<tr>
						<th width="13%"><label for="lineone_widget_id">LineOne Customer Id</label></th>
						<td width="87%"><input name="lineone_widget_id" id="lineone_widget_id" type="text" value="'. get_option('lineone_widget_id').'" class="regular-text" />
						<br><small>Please enter your LineOne details to enable widget on your website.</small></td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save">
			</p>
		</form>
	</div>';
}

/**
 * Callback function while install the plugin
 */
/*function wplo_install(){
	//echo "install"; exit;
	//add_action('wp_footer', 'wplo_widget_scripts');
}*/
register_activation_hook(__FILE__, 'wplo_widget_scripts');

/**
 * Callback function while deactivate the plugin
 */
function wplo_deactivation(){
	delete_option( 'lineone_widget_id' );
	add_action( 'wp_print_scripts', 'wpdocs_dequeue_script', 100 );
}
register_deactivation_hook(__FILE__, 'wplo_deactivation');

/**
 * Callback function while uninstall the plugin
 */
function wplo_uninstall(){
	remove_action('wp_footer', 'wplo_widget_scripts');
	delete_option( 'lineone_widget_id' );
	add_action( 'wp_print_scripts', 'wpdocs_dequeue_script', 100 );
}
register_uninstall_hook(__FILE__, 'wplo_uninstall');

/**
 * Callback function check version before activation
 */
function wplo_update_db_check(){
	global $wpbc_db_version;
	if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
		wplo_install();
	}
}
add_action('plugins_loaded', 'wplo_update_db_check');

/**
 * function to generate the HTML for settings page
 */
function wplo_handle_form(){
	if( ! isset( $_POST['lineone_form'] ) || ! wp_verify_nonce( $_POST['lineone_form'], 'lineone_update' ) ){
		echo '<div class="error"><p>Sorry, your nonce was not correct. Please try again.</p></div>';
		exit;
	} else {
		
		$lineone_widget_id = sanitize_text_field( $_POST['lineone_widget_id'] );
		
		if( $lineone_widget_id != '' ){
			update_option( 'lineone_widget_id', $lineone_widget_id );			
			echo '<div class="updated"><p>Your LineOne Customer id is saved.</p></div>';
		} else { 
			echo '<div class="error"><p>Enter valid LineOne Customer id.</p></div>';
		}
	}
}

/**
 * Callback function to add the Lineone widget scripts
 */
function wplo_widget_scripts(){
	wp_enqueue_script( 'widget-pop-up', 'https://lineone.one/widget-pop-up/widget-pop-up.js', array(), '', true );
}
add_action( 'wp_enqueue_scripts', 'wplo_widget_scripts' );

/**
 * Append script tags in lineone script
 */
function add_data_attribute($tag, $handle) {
	if ( 'widget-pop-up' !== $handle )
		return $tag;

	return str_replace( ' src', ' data-widgetbaseurl="https://lineone.one/call" data-widgetcustomerid="'.get_option('lineone_widget_id').'" id="lineone_widget_script" src', $tag );
}
add_filter('script_loader_tag', 'add_data_attribute', 10, 2);


/**
 * Remove scripts from footer
 */
function wpdocs_dequeue_script() {
    wp_dequeue_script( 'widget-pop-up' );
}
