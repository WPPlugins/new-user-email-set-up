<?php
/* 
Plugin Name: New User Email Setup
Plugin URI: http://www.epicalex.com/new-user-email-set-up/
Version: 0.5.1
Author: Alex Cragg
Author URI: http://epicalex.com/
Description: A Plugin to setup the registration email sent to new users. REQUIRES PHP 5
*/

/*
Copyright (C) 2007 Alex Cragg (email: alex AT epicalex DOT com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

// Added in case the path to plugin folder has been changed by user
if ( ! defined( 'WP_CONTENT_URL' ) )
	  define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	  define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	  define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	  
// Root to this plugin's dir
$nue_root = WP_PLUGIN_URL. "/newuseremail/";

if (!class_exists("NewUserEmailSetup")) { //Start Class
	class NewUserEmailSetup {
		var $adminOptionsName = "NewUserEmailAdminOptions";
		public $nue_options, $NewUserEmailAdminOptions;
		function NewUserEmailSetup() { //constructor
			
		}
		function init() {
			$this->getAdminOptions();
			//$plugin_dir = basename(dirname(__FILE__));
			//load_plugin_textdomain( 'NewUserEmail', 'wp-content/plugins/' . $plugin_dir, $plugin_dir );
		}
			 		
		// Added for 2.7 functionality
		function nue_admin_init(){
			if(function_exists('register_setting')) {
			register_setting('nue-options-group', $nue_options['html']);
			register_setting('nue-options-group', $nue_options['fromaddress']);
			register_setting('nue-options-group', $nue_options['from']);
			register_setting('nue-options-group', $nue_options['subject']);
			register_setting('nue-options-group', $nue_options['adminsubject']);
			register_setting('nue-options-group', $nue_options['text']);
			register_setting('nue-options-group', $nue_options['admintext']);
			}
		}
		//Returns an array of admin options
		public function getAdminOptions() {
			$NewUserEmailAdminOptions = array(
				'html' => 'text/HTML',
				'fromaddress' => 'Enter your admin email here',
				'from' => 'Enter the name you want your admin email sent from here. eg. Admin',
				'subject' => 'Welcome to %blogname%',
				'text' => 'Welcome %username% please find below your login details.<br /> I hope you enjoy our site.<br /> <strong>Username:</strong> %username%<br /> <strong>Password:</strong> %password%<br /> %loginurl%',
				'adminsubject' => '%blogname% - New User Registration',
				'admintext' => 'There is a new user registered on your blog:<br /> <strong>Username:</strong> %username%<br /> <strong>Email:</strong> %useremail%'
			);
			// If old style options are already in the table, take their value and transform it to the new style
			foreach( $NewUserEmailAdminOptions as $key => $value ) {
				if( $existing = get_option( 'newuseremail' . $key ) ) {
					$NewUserEmailAdminOptions[$key] = $existing;
					delete_option( 'newuseremail' . $key );
				}
			}
			// If new style options are already in the table, keep their values. Also, the from name value will be blank, so add the default value in
			$nue_options = get_option($this->adminOptionsName);
			if (!empty($nue_options)) {
				foreach ($nue_options as $key => $value)
					$NewUserEmailAdminOptions[$key] = $value;
			}				
			update_option($this->adminOptionsName, $NewUserEmailAdminOptions);
			return $NewUserEmailAdminOptions;
		}
			
######################################
//	Functions for printing out the admin screen and updating
//	the options
######################################

function printNewUserEmailAdminPage() {
	$nue_options = $this->getAdminOptions();
						
	if (isset($_POST['nue_submit'])) { 
		if (isset($_POST['newuseremailhtml'])) {
			$nue_options['html'] = $_POST['newuseremailhtml'];
		}
		if (isset($_POST['newuseremailfrom'])) {
			$nue_options['from'] = $_POST['newuseremailfrom'];
		}
		if (isset($_POST['newuseremailfromaddress'])) {
			$nue_options['fromaddress'] = $_POST['newuseremailfromaddress'];
		}
		if (isset($_POST['newuseremailsubject'])) {
			$nue_options['subject'] = $_POST['newuseremailsubject'];
		}
		if (isset($_POST['newuseremailtext'])) {
			$nue_options['text'] = $_POST['newuseremailtext'];
		}
		if (isset($_POST['newuseremailadminsubject'])) {
			$nue_options['adminsubject'] = $_POST['newuseremailadminsubject'];
		}
		if (isset($_POST['newuseremailadmintext'])) {
			$nue_options['admintext'] = $_POST['newuseremailadmintext'];
		}
		update_option($this->adminOptionsName, $nue_options);
		
		?>
		<div class="updated"><p><strong><?php _e("Settings Updated.", "NewUserEmail");?></strong></p></div>
	<?php
	} ?>

<div class="wrap">
	<div id="icon-newusermail" class="icon32">
	<br/>
	</div>
	<h2>New User Email</h2>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<?php 
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('update-options');
		if (function_exists(' settings_fields')) { settings_fields('nue-options-group'); } ?> <!-- Added for 2.7 functionality -->
		<div id="poststuff" class="meta-box-sortables">	
			<div class="postbox close-me nue-left">
				<h3 class="hndle"><span><?php _e('User Email Settings','NewUserEmail'); ?></span></h3>
					<div class="inside">
						<h4><?php _e('Allow HTML in Email Content?', 'NewUserEmail') ?></h4>
							<label for="newuseremailhtml_yes">
							<input id="newuseremailhtml_yes" type="radio" value="text/HTML" <?php if ($nue_options['html'] == "text/HTML") { _e('checked="checked"', "NewUserEmail"); }?> name="newuseremailhtml"/>
							<?php _e('Yes', 'NewUserEmail') ?>
							</label>
							<label for="newuseremailhtml_no">
							<input id="newuseremailhtml_no" type="radio" value="text/plain" <?php if ($nue_options['html'] == "text/plain") { _e('checked="checked"', "NewUserEmail"); }?> name="newuseremailhtml"/>
							<?php _e('No', 'NewUserEmail') ?>
							</label> 
							
						<h4><?php _e('Registration Email Subject', 'NewUserEmail') ?></h4>
							<input id="newuseremailsubject" type="text" size="40" style="font-size: 12px;" value="<?php _e(stripslashes($nue_options['subject']), 'NewUserEmail') ?>" name="newuseremailsubject"/>
							
						<h4><?php _e('Registration Email Text', 'NewUserEmail') ?></h4>
							<textarea id="newuseremailtext" style="width: 98%; font-size: 12px;" rows="4" cols="60" name="newuseremailtext"><?php _e(stripslashes($nue_options['text']), 'NewUserEmail') ?>
							</textarea>
							<p><?php _e('Use this to create a custom email that is sent to new users when they register. It overides the default text and you can write anything at all in here, but remember to use the following variables so that your users still know how to login!', 'NewUserEmail') ?></p>
							
							<p><?php _e('%username%, %useremail%, %password%, %siteurl%, %blogname%, and %loginurl%.', 'NewUserEmail') ?></p> 
							<p>
							<?php _e('Note the percentage signs (%), each variable must have percentage signs around them with <strong>no spaces</strong>.  For an example text, using some of the variables, please see above.', 'NewUserEmail') ?>
							</p>
							<p><?php _e('<strong>IMPORTANT:</strong> Make sure that you test out your new email by registering yourself so that you see what your users are receiving.', 'NewUserEmail') ?></p>

						<h4><?php _e('From Address', 'NewUserEmail') ?></h4>
							<input id="newuseremailfromaddress" type="text" size="40" style="font-size: 12px;" value="<?php _e(stripslashes($nue_options['fromaddress']), 'NewUserEmail') ?>" name="newuseremailfromaddress"/>
							<p><?php _e('NB You must have this email set up as a real email address, otherwise it will be sent from your host\'s mailbox, which looks ugly! This can be different from the address you have set up in your general WordPress options.', 'NewUserEmail') ?></p>

						<h4><?php _e('From Name', 'NewUserEmail') ?></h4>
							<input id="newuseremailfrom" type="text" size="40" style="font-size: 12px;" value="<?php _e(stripslashes($nue_options['from']), 'NewUserEmail') ?>" name="newuseremailfrom"/>
							<p><?php _e('This could be something like \'Epic Alex\', or \'Admin\'.', 'NewUserEmail') ?></p>
							<div class="submit">
								<input type="submit" name="nue_submit" value="<?php _e('Save', 'NewUserEmail') ?>" />
							</div>
					</div>
			</div>
			<div class="postbox close-me nue-right">
				<h3 class="hndle"><span><?php _e('Admin Email Settings','NewUserEmail'); ?></span></h3>
					<div class="inside">
						<h4><?php _e('Administration Notification Email Subject', 'NewUserEmail') ?></h4>
							<input id="newuseremailadminsubject" type="text" size="40" style="font-size: 12px;" value="<?php _e(stripslashes($nue_options['adminsubject']), 'NewUserEmail') ?>" name="newuseremailadminsubject"/>
							
						<h4><?php _e('Administration Notification Email Text', 'NewUserEmail') ?></h4>
							<textarea id="newuseremailadmintext" style="width: 98%; font-size: 12px;" rows="4" cols="60" name="newuseremailadmintext"><?php _e(stripslashes($nue_options['admintext']), 'NewUserEmail') ?>
							</textarea>
							<p>
							<?php _e('This is to define the email that is sent to the Blog Administrator when a new user registers, you can use the same variables as above.', 'NewUserEmail') ?>
							</p>
							<div class="submit">
								<input type="submit" name="nue_submit" value="<?php _e('Save', 'NewUserEmail') ?>" />
							</div>
					</div>
			</div>
			<div class="clear"></div>
			<div class="postbox close-me">
				<h3 class="hndle"><span><?php _e('Test','NewUserEmail'); ?></span></h3>
					<div class="inside">
					<p><?php _e('Once you have saved the above options, this will show you approx. how the email will look, without any of the variables replaced, but with line breaks and formating etc, so send a test one to see it fully in action!', 'NewUserEmail') ?></p>
					<h4><?php _e('New User Email', 'NewUserEmail') ?></h4>
					<?php 
					echo "<strong>Subject: </strong>" . $nue_options['subject'] . "<br />";
					echo "<strong>From: </strong>" . $nue_options['from'] . " - " . $nue_options['fromaddress'] . "<br/><br/>";
					echo $nue_options['text'];
					?>
					<h4><?php _e('Admin Email', 'NewUserEmail') ?></h4>
					<?php
					echo "<strong>Subject: </strong>" . $nue_options['adminsubject']. "<br /><br/>";
					echo $nue_options['admintext'];
					?>
					</div>
			</div>

			<div class="postbox close-me">
				<h3 class="hndle"><span><?php _e('Support','NewUserEmail'); ?></span></h3>
					<div class="inside">
						<h4><?php _e('Help Me!', 'NewUserEmail') ?></h4>
							<p><?php _e('If you need help with this plugin, or if you want to make a suggestion, then please email me at alex AT epicalex DOT com', 'NewUserEmail') ?><p>
						<h4><?php _e('Support This Plugin!', 'NewUserEmail') ?></h4>
							<p><?php _e('There are a few ways you can support me to say thanks for making this plugin, you can donate by paypal, or you can sign up to something through one of my affiliate links such as Bluehost or Dreamhost.', 'NewUserEmail') ?></p>
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_s-xclick">
								<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
								<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
								<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
							</form>
							<script src=http://www.bluehost.com/src/js/epicskitours/CODE15/88x31/3.gif></script>
							<p>when you visit www.dreamhost.com and sign up for hosting, be sure to use the promo code ALEXMYSOUTHAM to get $50 off your bill, PLUS 10% extra bandwidth!</p>
					</div>
			</div>
		</div>
	</form>
	    <script type="text/javascript">
			<!--
			jQuery('.postbox h3').before('<div class="handlediv" title="Click to toggle"><br /></div>');		
			jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
			jQuery('.postbox.close-me').each(function(){ jQuery(this).addClass("closed");
			});
			//-->
		</script> <!-- The arrow added for the dropdown currently doesn't work, the h3 header bar has to be clicked... -->
		
	<?php // Adds a link in the footer on the New User Email Setup page only with link to the plugin page and to my blog homepage. thanks to http://striderweb.com/
	add_action('in_admin_footer', 'nue_admin_footer' );
		function nue_admin_footer() {
			$plugin_data = get_plugin_data( __FILE__ );
			printf('%1$s | Version %2$s | by %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
		}
}//End function printNewUserEmailAdminPage()
	
	} //End Class NewUserEmailSetup

} //End  if Class !Exists for NewUserEmailSetup

######################################
//  	Setup mail function to send emails. based on built in mail
//	function, but modified to allow for HTML emails, and to
//	make sure that altering that doesn't affect any other use of
//	the mail function...better safe than sorry...
######################################

if ( !function_exists( 'newuser_mail' ) ) :
function newuser_mail($to, $subject, $message, $headers = '') {
$new = new NewUserEmailSetup();
$nue_options = $new->getAdminOptions();
	global $phpmailer;

	if ( !is_object( $phpmailer ) ) {
		require_once(ABSPATH . WPINC . '/class-phpmailer.php');
		require_once(ABSPATH . WPINC . '/class-smtp.php');
		$phpmailer = new PHPMailer();
	}

	$mail = compact('to', 'subject', 'message', 'headers');
	$mail = apply_filters('wp_mail', $mail);
	extract($mail, EXTR_SKIP);

	if ( $headers == '' ) {
		$headers = "MIME-Version: 1.0\n" .
			"From: " . apply_filters('wp_mail_from', "wordpress@" . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']))) . "\n" . 
			"Content-Type: text/HTML; charset=\"" . get_option('blog_charset') . "\"\n";
	}

	$phpmailer->ClearAddresses();
	$phpmailer->ClearCCs();
	$phpmailer->ClearBCCs();
	$phpmailer->ClearReplyTos();
	$phpmailer->ClearAllRecipients();
	$phpmailer->ClearCustomHeaders();

	$phpmailer->FromName = "WordPress";
	$phpmailer->AddAddress("$to", "");
	$phpmailer->Subject = $subject;
	$phpmailer->Body    = $message;
        if ($nue_options['html'] == 'text/HTML' ) {
	$phpmailer->IsHTML( true );
	    } else {
	$phpmailer->IsHTML( false );
	    }
	$phpmailer->IsMail(); // set mailer to use php mail()

	do_action_ref_array('phpmailer_init', array(&$phpmailer));

	$mailheaders = (array) explode( "\n", $headers );
	foreach ( $mailheaders as $line ) {
		$header = explode( ":", $line );
		switch ( trim( $header[0] ) ) {
			case "From":
				$from = trim( str_replace( '"', '', $header[1] ) );
				if ( strpos( $from, '<' ) ) {
					$phpmailer->FromName = str_replace( '"', '', substr( $header[1], 0, strpos( $header[1], '<' ) - 1 ) );
					$from = trim( substr( $from, strpos( $from, '<' ) + 1 ) );
					$from = str_replace( '>', '', $from );
				} else {
					$phpmailer->FromName = $from;
				}
				$phpmailer->From = trim( $from );
				break;
			default:
				if ( $line != '' && $header[0] != 'MIME-Version' && $header[0] != 'Content-Type' )
					$phpmailer->AddCustomHeader( $line );
				break;
		}
	}

	$result = @$phpmailer->Send();

	return $result;
}
endif;

######################################
//	This overwrites wp_new_user_notification that is defined
//	in pluggable.php
######################################

if ( !function_exists('wp_new_user_notification') ) {
function wp_new_user_notification($user_id, $plaintext_pass = '') {
$new = new NewUserEmailSetup();
$nue_options = $new->getAdminOptions();
	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);
		// These are the variables that can be used on the options page to be replaced in the email when sent. The variables are replaced here too. If you want to add more variables, make sure you add the replacement code too, otherwise nothing will happen...
    	$find = array('/%username%/i', '/%password%/i', '/%blogname%/i', '/%siteurl%/i', '/%loginurl%/i', '/%useremail%/i');
    	$replace = array($user_login, $plaintext_pass, get_option('blogname'), get_option('siteurl'), get_option('siteurl').'/wp-login.php', $user_email);
		
		// This is the email sent to the Blog Admin
    	$headers .= "MIME-Version: 1.0\n" .
	    "From: ". $user_email . "\n" . 
    	"Content-Type: ". $nue_options['html'] ." charset=\"" . get_option('blog_charset') . "\"\n";
        
    	$subject = stripslashes($nue_options['adminsubject']);
    	$subject = preg_replace($find, $replace, $subject);
    	$subject = preg_replace("/%.*%/", "", $subject);

    	
    	$message = stripslashes($nue_options['admintext']);
    	$message = preg_replace($find, $replace, $message);
    	$message = preg_replace("/%.*%/", "", $message);

	@newuser_mail($nue_options['fromaddress'], $subject, $message, $headers);

	if ( empty($plaintext_pass) )
		return;
		// This is the email sent to the New User
    	$headers .= "MIME-Version: 1.0\n" .
	    "From: ". $nue_options['from'] ."<". $nue_options['fromaddress'] . ">\n" . 
    	"Content-Type: ". $nue_options['html'] ." charset=\"" . get_option('blog_charset') . "\"\n";
        
    	$subject = stripslashes($nue_options['subject']);
    	$subject = preg_replace($find, $replace, $subject);
    	$subject = preg_replace("/%.*%/", "", $subject);	

    	$message = stripslashes($nue_options['text']);
    	$message = preg_replace($find, $replace, $message);
    	$message = preg_replace("/%.*%/", "", $message);
		
	newuser_mail($user_email, $subject, $message, $headers);
}
}
	

if (class_exists("NewUserEmailSetup")) {
	$new_user_email_setup = new NewUserEmailSetup();
}

//Initialize the admin panel
if (!function_exists("NewUserEmailSetupAdminPanel")) {
	function NewUserEmailSetupAdminPanel() {
		global $new_user_email_setup;
		if (!isset($new_user_email_setup)) {
			return;
		}
		if (function_exists('add_options_page')) {
			add_options_page( 
				'New User Email Setup',
				'New User Email',
				'manage_options',
				__FILE__,
				array(&$new_user_email_setup, 'printNewUserEmailAdminPage')
				);		
		}
	}	
}

if (isset($new_user_email_setup)) {
	//Actions
	add_action('admin_menu', 'NewUserEmailSetupAdminPanel');
	add_action('activate_'.basename(__FILE__),  array(&$new_user_email_setup, 'init'));
	add_action('admin_init', array(&$new_user_email_setup,'nue_admin_init' ));
}

?>