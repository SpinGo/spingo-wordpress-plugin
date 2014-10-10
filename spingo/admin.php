<?php
defined('ABSPATH') or die();
/** Settings **/

wp_enqueue_style(
	'spingo_style',
	plugin_dir_url(__FILE__).'css/admin.css'
);

add_action( 'admin_menu', 'spingo_plugin_menu' );

function spingo_plugin_menu() {
	add_options_page( 'SpinGo Settings', 'SpinGo', 'manage_options', 'spingo', 'spingo_plugin_options' );
}

function spingo_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>
	<form action='options.php' method='post'>
		<h2>SpinGo&reg; Settings</h2>
		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>
	</form>
	<?php
}

add_action( 'admin_init', 'spingo__settings_init' );
function spingo__settings_exist(  ) { 
	if( false == get_option( 'spingo_settings' ) ) { 
		add_option( 'spingo_settings' );
	}
}

function spingo__settings_init() { 
	register_setting( 'pluginPage', 'spingo__settings' );

	add_settings_section(
		'spingo__pluginPage_section', 
		__( 'Calendar Settings', 'spingo' ), 
		'spingo__settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'spingo__calendar', 
		__( 'Subdomain', 'spingo' ), 
		'spingo__text_field_calendar_render', 
		'pluginPage', 
		'spingo__pluginPage_section' 
	);

	add_settings_field( 
		'spingo__authToken', 
		__( 'AuthToken', 'spingo' ), 
		'spingo__text_field_authtoken_render', 
		'pluginPage', 
		'spingo__pluginPage_section' 
	);
}


function spingo__text_field_authtoken_render(  ) { 
	$options = get_option( 'spingo__settings' ); ?>
	<input type='text' name='spingo__settings[spingo__authToken]' value='<?php echo $options['spingo__authToken']; ?>'>
	<?php
}

function spingo__text_field_calendar_render(  ) { 
	$options = get_option( 'spingo__settings' ); ?>
	<input type='text' name='spingo__settings[spingo__subdomain]' value='<?php echo $options['spingo__subdomain']; ?>'>
	<?php
}


function spingo__settings_section_callback(  ) { 
	echo __( 'Please enter the subdomain of the your SpinGo&reg; calendar below.', 'spingo' );
}

function spingo_action_links($links) {
	$links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=spingo') .'">Settings</a>';
	return $links;
}

add_filter( 'plugin_action_links', 'spingo_action_links' );

/* Posts/Pages Shortcode */

add_action('media_buttons', 'insert_calendar_button', 100);

function insert_calendar_button() {
	echo '<a href="#" id="insert-calendar-button" class="button add-calendar" data-editor="content" title="Add Media"><span class="wp-calendar-buttons-icon"></span> Add Calendar</a>';
}

//if (get_user_option('rich_editing') == 'true') {
    add_filter("mce_external_plugins", "add_spingo_tinymce_plugin");
//}

function add_spingo_tinymce_plugin($plugin_array) {
    $plugin_array['spingo_calendar_editor'] = plugin_dir_url(__FILE__).'js/tinymce_plugin.js';
    return $plugin_array;
}

function my_theme_add_editor_styles() {
    add_editor_style( plugin_dir_url(__FILE__).'css/tinymce_editor.css' );
}
add_action( 'after_setup_theme', 'my_theme_add_editor_styles' );