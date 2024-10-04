<?php
/*
Plugin Name: Admin reCaptcha
Plugin URI: https://github.com/armujahid/Admin-reCaptcha.git
Description: This plugin enable reCapcha on Admin login screen
Version: 1.2
Author: Abdul Rauf
Author URI: http://armujahid.me/
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'pre_login_username_password', 'abdulrauf_adminreCaptcha_validatereCaptcha' );

// Validates reCaptcha
function abdulrauf_adminreCaptcha_validatereCaptcha()
{
	include('captcha.php'); 
	if ($resp != null && $resp->success) 
	{ 
		//reCaptcha validated
		return true;
	}
	else
	{
		yourls_do_action( 'login_failed' );
		yourls_login_screen( $error_msg = 'reCaptcha validation failed' );
		die();
		return false;
	}
}

// Register plugin on admin page
yourls_add_action( 'plugins_loaded', 'abdulrauf_adminreCaptcha_init' );
function abdulrauf_adminreCaptcha_init() {
    yourls_register_plugin_page( 'adminreCaptcha', 'Admin reCaptcha Settings', 'adminreCaptcha_config_page' );
}

// The function that will draw the config page
function adminreCaptcha_config_page() {
    	 if( isset( $_POST['abdulrauf_adminreCaptcha_public_key'] ) ) {
	        yourls_verify_nonce( 'abdulrauf_adminreCaptcha_nonce' );
	        abdulrauf_adminreCaptcha_save_admin();
	    }
    
    $nonce = yourls_create_nonce( 'abdulrauf_adminreCaptcha_nonce' );
    $pubkey = yourls_get_option( 'abdulrauf_adminreCaptcha_pub_key', "" );
    $privkey = yourls_get_option( 'abdulrauf_adminreCaptcha_priv_key', "" );
    echo '<h2>Admin reCaptcha plugin settings</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
    echo '<p><label for="abdulrauf_adminreCaptcha_public_key">reCaptcha site key: </label>';
    echo '<input type="text" id="abdulrauf_adminreCaptcha_public_key" name="abdulrauf_adminreCaptcha_public_key" value="' . $pubkey . '"></p>';  
    echo '<p><label for="abdulrauf_adminreCaptcha_private_key">reCaptcha secret key: </label>';
    echo '<input type="text" id="abdulrauf_adminreCaptcha_private_key" name="abdulrauf_adminreCaptcha_private_key" value="' . $privkey . '"></p>';
    echo '<input type="submit" value="Save"/>';
    echo '</form>';

}

// Save reCaptcha keys in database 
function abdulrauf_adminreCaptcha_save_admin()
{
	$pubkey = $_POST['abdulrauf_adminreCaptcha_public_key'];
	$privkey = $_POST['abdulrauf_adminreCaptcha_private_key'];
	if ( yourls_get_option( 'abdulrauf_adminreCaptcha_pub_key' ) !== false ) {
        yourls_update_option( 'abdulrauf_adminreCaptcha_pub_key', $pubkey );
    } 
	else {
        yourls_add_option( 'abdulrauf_adminreCaptcha_pub_key', $pubkey );
    }
	if ( yourls_get_option( 'abdulrauf_adminreCaptcha_priv_key' ) !== false ) {
        yourls_update_option( 'abdulrauf_adminreCaptcha_priv_key', $privkey );
    } 
	else {
        yourls_add_option( 'abdulrauf_adminreCaptcha_priv_key', $privkey );
    }
    echo "Saved";
}

// Add the JavaScript for reCaptcha widget
yourls_add_action( 'html_head', 'abdulrauf_adminreCaptcha_addjs' );
function abdulrauf_adminreCaptcha_addjs() {
	$siteKey = yourls_get_option( 'abdulrauf_adminreCaptcha_pub_key' );
	?>
	<script type="text/javascript">
	//JQuery function to add div for reCaptcha widget and load js only on login screen
	$(document).ready(function() {
		var logindiv = document.getElementById('login');
		if (logindiv != null) { //check if we are on login screen
			//getting reCaptcha script by jquery only on login screen
			$.getScript( "https://recaptcha.net/recaptcha/api.js?onload=loadCaptcha&render=explicit");
			var form = logindiv.innerHTML;
			var index = form.indexOf('<p style="text-align: right;">'); //finding tag before which reCaptcha widget should appear
			document.getElementById('login').innerHTML = form.slice(0, index) + '<div id="captcha_container"></div>' + form.slice(index);	    
		}
    });
	// JavaScript function to explicitly render the reCAPTCHA widget
	var loadCaptcha = function() {
	  captchaContainer = grecaptcha.render('captcha_container', {
		'sitekey' : '<?php echo $siteKey?>'
	  });
	};
	</script>
	<?php
}
?>
