<?php
/*
Plugin Name: sxss Privacy Protection
Plugin URI: http://sxss.nw.am
Description: Protects the privacy of user and admin
Author: sxss
Version: 0.4
*/

// I18n
load_plugin_textdomain('sxss_privacy', false, basename( dirname( __FILE__ ) ) . '/languages' );


function sxss_privacy_options()
{
	// Delete Comment IP Address
	$option[0]["key"] = "sxss_privacy_v_comment_ipaddress";
	$option[0]["available"] = 1;
	$option[0]["title"] = __("Comments: Don't save IP addresses", 'sxss_privacy');
	$option[0]["desc"] = __('IPs will be saved as 0.0.0.0 right after the comment was posted.', 'sxss_privacy');

	// Hash Comment IP Adresses
	$option[1]["key"] = "sxss_privacy_v_comment_ipaddress_hash";
	$option[1]["available"] = 1;
	$option[1]["title"] = __('Comments: Anonymize IP addresses', 'sxss_privacy');
	$option[1]["desc"] = __('IPs will be hashed with md5() and a salt. This is not 100% secure, but you can identify comment authors by their hash.', 'sxss_privacy');

	// Delete Comment E-Mail Addresses
	$option[2]["key"] = "sxss_privacy_v_comment_email";
	$option[2]["available"] = 1;
	$option[2]["title"] = __("Comments: Don't save Mail addresses", 'sxss_privacy');
	$option[2]["desc"] = __('Comment E-Mail addresses will be deleted. This may interfere with Gravatars and other plugins.', 'sxss_privacy');

	// Hash Comment E-Mail Addresses
	$option[3]["key"] = "sxss_privacy_v_comment_email_hash";
	$option[3]["available"] = 1;
	$option[3]["title"] = __('Comments: Anonymize Mail addresses', 'sxss_privacy');
	$option[3]["desc"] = __('E-Mail addresses will be hashed with md5(). This is not 100% secure, but Gravatars will work.', 'sxss_privacy');

	// Delete Comment Browser Agent
	$option[4]["key"] = "sxss_privacy_v_comment_browser_agent";
	$option[4]["available"] = 1;
	$option[4]["title"] = __("Comments: Don't save browser agents", 'sxss_privacy');
	$option[4]["desc"] = __('Delete the browster agent of the comment author. This information may be used by a statistics plugin.', 'sxss_privacy');

	// Force SSL Frontend
	$option[5]["key"] = "sxss_privacy_v_force_ssl";
	$option[5]["available"] = 1;
	$option[5]["title"] = __('Frontend: Use a secure connection', 'sxss_privacy');
	$option[5]["desc"] = __('Visitors will be redirected, if they try to use a http instead of a https (SSL) connection.', 'sxss_privacy');

	// Force SSL Backend
	$option[11]["key"] = "sxss_privacy_a_force_ssl";
	$option[11]["available"] = 1;
	$option[11]["title"] = __('Backend: Use a secure connection', 'sxss_privacy');
	$option[11]["desc"] = __('Users will be redirected, if they try to use a http instead of a https (SSL) connection.', 'sxss_privacy');

	// Hide Admin Username
	$option[6]["key"] = "sxss_privacy_a_username";
	$option[6]["available"] = 1;
	$option[6]["title"] = __('Hide usernames on the frontend', 'sxss_privacy');
	$option[6]["desc"] = __('Will remove the authors name from posts, pages, attachments, CPT and feeds.', 'sxss_privacy');

	// Hide Wordpress Version
	$option[7]["key"] = "sxss_privacy_a_wordpress_version";
	$option[7]["available"] = 1;
	$option[7]["title"] = __('Hide the Wordpress version number', 'sxss_privacy');
	$option[7]["desc"] = __('This will disable the Wordpress version number output. Note: There are still ways to find out your wp version!', 'sxss_privacy');

	// Limit Access to loggedin users
	$option[8]["key"] = "sxss_privacy_limit_blog_access";
	$option[8]["available"] = 1;
	$option[8]["title"] = __('Limit access to loggedin users', 'sxss_privacy');
	$option[8]["desc"] = __('Users will be redirected to the login page, if they are currently not logged in.', 'sxss_privacy');

	/* Plugins */

	// BBPress IP Adress
	$option[9]["key"] = "sxss_privacy_v_bbpress_ipadress";
	$option[9]["available"] = class_exists('bbPress');
	$option[9]["title"] = __('Delete IP addresses of bbPress users', 'sxss_privacy');
	$option[9]["desc"] = __('IPs will be saved as 0.0.0.0', 'sxss_privacy');

	// Grunion Contact Form IP Adress
	$option[10]["key"] = "sxss_privacy_p_grunioncontactform";
	$option[10]["available"] = function_exists('grunion_admin_menu');
	$option[10]["available"] = 0;
	$option[10]["title"] = __('Grunion Contact Form: Delete IPs', 'sxss_privacy');
	$option[10]["desc"] = __('Comming soon: Delete IPs from contact messages received with Grunion Contact Form', 'sxss_privacy');
	
	return $option;
}

function sxss_privacy_unique_salt()
{
	return md5( uniqid ( rand() ) );
}

function sxss_privacy_get_settings()
{
	$options = get_option('sxss_privacy');

	return unserialize( $options );
}

function sxss_privacy_save_settings( $options )
{
	foreach( sxss_privacy_options() as $option )
	{
		if( 1 == $option["available"] )
		{	
			$key = $option["key"];

			if( 1 == $options[$key] ) $save[$key] = 1;
			else $save[$key] = 0;
		}
	}

	if( 64 != strlen( $options["salt"] ) ) $save["salt"] = sxss_privacy_unique_salt();

	$save = serialize( $save );

	update_option( 'sxss_privacy', $save );
}

// settingspage
function sxss_privacy_settings() 
{

	// save settings
	if ($_POST['action'] == 'update')
	{
		if( $_POST["sxss_privacy_options"]["sxss_privacy_v_comment_ipaddress"] && 
			$_POST["sxss_privacy_options"]["sxss_privacy_v_comment_ipaddress_hash"] ) $sxss_privacy_error[] = __('You have to decide to <strong>delete</strong> or <strong>anonymize</strong> the IP addresses.', 'sxss_privacy');

		if( $_POST["sxss_privacy_options"]["sxss_privacy_v_comment_email"] && 
			$_POST["sxss_privacy_options"]["sxss_privacy_v_comment_email_hash"] ) $sxss_privacy_error[] = __('You have to decide to <strong>delete</strong> or <strong>anonymize</strong> the E-Mail addresses.', 'sxss_privacy');
		
		if( false == isset( $sxss_privacy_error ) )
		{
			sxss_privacy_save_settings( $_POST["sxss_privacy_options"] );

			$message = '<div id="message" class="updated fade"><p><strong>' . __('Privacy settings updated', 'sxss_privacy') . '</strong></p></div>'; 
		}
		else
		{
			echo '<div class="error">';

			foreach( $sxss_privacy_error as $error )
			{
				echo '<p>' . $error . '</p>';
			}

			echo '</div>';
		}
		
	} 


	echo '



	<!-- Style in Header einbinden als externe CSS Datei -->

	<div class="wrap">

		'.$message.'

		<div id="icon-options-general" class="icon32"><br /></div>

		<h2>' . __('Privacy Protection', 'sxss_privacy') . '</h2>

		<div id="message" class="updated">

			<p>

				' . __('This plugin is under developement. If you like it, <strong><a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/sxss-privacy">leave a review</a></strong> in the Plugin directory.', 'sxss_privacy') . '

			</p>
		
		</div>

		<form method="post" action="">

			<input type="hidden" name="action" value="update" />

			<div id="sxss-p-box">';

				$settings = sxss_privacy_get_settings();

				foreach( sxss_privacy_options() as $option )
				{
					$is_enabled = $settings[$option["key"]];
					$is_available = $option["available"];

					// mark checkboxes as checked
					if( 1 == $is_enabled ) $marker = ' checked';
					else $marker = '';

					// mark options as disabled
					if( 1 != $is_available )
					{
						$disabled["checkbox"] = ' disabled = "disabled"';
						$disabled["css"] = 'sxss-p-na ';
					}
					else
					{
						$disabled["checkbox"] = '';
						$disabled["css"] = '';
					}

					echo '<div class="' . $disabled["css"] . '">

							<input type="checkbox" name="sxss_privacy_options[' . $option["key"] . ']" value="1" ' . $marker . $disabled["checkbox"] . '>

							<strong>' . $option["title"] . '</strong>

							<span>' . $option["desc"] . '</span>

						</div>';
				}

			echo '</div>

			<input type="hidden" name="sxss_privacy_options[salt]" value="' . $settings["salt"] . '">

			<br style="clear: both;">

			<input type="submit" class="button-primary" value="' . __('Save privacy settings', 'sxss_privacy') . '" />

			<p align="right"><a target="_blank" title="sxss Plugins on wordpress.org" href="https://profiles.wordpress.org/sxss/"><img src="' . plugins_url( 'sxss-plugins.png' , __FILE__ ) . '"></a></p>

		</form>

	</div>';
}

// register settings page
function sxss_privacy_admin_menu()
{  
	add_options_page(__('sxss Privacy', 'sxss_privacy'), __('sxss Privacy', 'sxss_privacy'), 9, 'sxss_privacy', 'sxss_privacy_settings');  
}  

add_action("admin_menu", "sxss_privacy_admin_menu"); 






/* This is where the 'Privacy Magic' happens */

$sxss_privacy_settings = sxss_privacy_get_settings();

function sxss_privacy_ssl_redirect()
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
	exit();
}

function sxss_privacy_loggedin_user()
{
	$user = wp_get_current_user();

	if ( empty( $user->ID ) )
		return false;

	return true;
}

// Comment IPs
function sxss_privacy_ip( $ip )
{
	global $sxss_privacy_settings;

	if( 1 == $sxss_privacy_settings["sxss_privacy_v_comment_ipaddress"] )
	{
		return '0.0.0.0';
	}
	elseif( 1 == $sxss_privacy_settings["sxss_privacy_v_comment_ipaddress_hash"] )
	{
		return md5( $sxss_privacy_settings["salt"] . $ip );
	}

 	return $ip;
}

add_filter( 'pre_comment_user_ip', 'sxss_privacy_ip' );


// Comment E-Mail
function sxss_privacy_mail( $mail )
{
	global $sxss_privacy_settings;

	if( 1 == $sxss_privacy_settings["sxss_privacy_v_comment_email"] )
	{
		return '';
	}
	elseif( 1 == $sxss_privacy_settings["sxss_privacy_v_comment_email_hash"] )
	{
		return md5( $email );
	}

 	return $email;
}

add_filter( 'pre_comment_author_email', 'sxss_privacy_mail');


// Comment Browser Agent
if( 1 == $sxss_privacy_settings["sxss_privacy_v_comment_browser_agent"] )
{
	function sxss_privacy_browser()
	{
		return '';
	}

	add_filter( 'pre_comment_user_agent', 'sxss_privacy_browser' );
}


// Delete BBPress IPs
if( 1 == $sxss_privacy_settings["sxss_privacy_v_bbpress_ipadress"] )
{
	function sxss_privacy_bbpress_ip_delete()
	{
		$query = $wpdb->query( $wpdb->prepare("
			UPDATE $wpdb->postmeta
			SET		_bbp_author_ip = %s
	    	WHERE	_bbp_author_ip != %s
  		", '0.0.0.0', '0.0.0.0' ));
	}

	add_action( 'bbp_new_reply', 'sxss_privacy_bbpress_ip_delete' );
}


// Force SSL
if( 1 == $sxss_privacy_settings["sxss_privacy_v_force_ssl"] && $_SERVER["HTTPS"] != "on")
{
	if( false == is_admin() ) add_action('init', 'sxss_privacy_ssl_redirect');
}
if( 1 == $sxss_privacy_settings["sxss_privacy_a_force_ssl"] && $_SERVER["HTTPS"] != "on")
{
	if( true == is_admin() ) add_action('init', 'sxss_privacy_ssl_redirect');
}



if( 1 == $sxss_privacy_settings["sxss_privacy_limit_blog_access"] )
{

	function login_form_message() {
	    echo '<div style="text-align: center; font-weight: bold; margin: 10px 0 20px 0;">' . __('Only loggedin users can view this blog.', 'sxss_privacy') . '</div>';
	}

	add_action('login_form', 'login_form_message');

	function sxss_privacy_is_login_page()
	{
	    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}

	function sxss_privacy_limit_blog_access()
	{
		if( false == is_user_logged_in() && false == sxss_privacy_is_login_page() )
		{
			$url = wp_login_url( "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] );
		
			wp_redirect( $url );
		
			exit();
		}
	}
	
	add_action('init', 'sxss_privacy_limit_blog_access');
}


// Wordpress Version
if( 1 == $sxss_privacy_settings["sxss_privacy_a_wordpress_version"] )
{
	function sxss_privacy_remove_wp_version()
	{
		return '';
	}

	add_filter('the_generator', 'sxss_privacy_remove_wp_version');
}


# PLUGIN SUPPORT

# Delete Grunion Contact Form IPs
if( 1 == $sxss_privacy_settings["sxss_privacy_p_grunioncontactform"] )
{
	function sxss_privacy_grunioncontactform_ip_delete()
	{
		$query = $wpdb->query( $wpdb->prepare("
			UPDATE $wpdb->postmeta
			SET		_feedback_ip = %s
	    	WHERE	_feedback_ip != %s
  		", '0.0.0.0', '0.0.0.0'));
	}
}

if( 1 == $sxss_privacy_settings["sxss_privacy_a_username"] )
{
	function sxss_privacy_name( $author )
	{
		if( false == is_admin() ) return '';
		else return $author;
	}

	add_filter( 'the_author', 'sxss_privacy_name' );
    add_filter( 'get_the_author_display_name', 'sxss_privacy_name' );
}

// Privacy Shortcode
function sxss_privacy_shortcode( $atts, $content = null ) 
{
	$url = wp_login_url( "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] );

	extract( shortcode_atts( array(
		'info' => 'This content is only visible to <a href="' . $url  . '">loggedin users</a>.',
	), $atts ) );

	if( false == is_user_logged_in() )
	{
		$output = $info;
	}
	else
	{
		$output = $content;
	}

	return '<span class="sxss-privacy-shortcode_">' . $output . '</span>';
}

add_shortcode( 'privacy', 'sxss_privacy_shortcode');
add_filter('widget_text', 'do_shortcode');
add_filter('the_excerpt', 'do_shortcode');


// Register Style
function sxss_privacy_style() {

	wp_register_style( 'sxss-privacy-style', plugins_url('privacy.css', __FILE__), false, false );
	wp_enqueue_style( 'sxss-privacy-style' );

}

// Hook into the 'admin_enqueue_scripts' action
add_action( 'admin_enqueue_scripts', 'sxss_privacy_style' );

?>