<?php
/*
Plugin Name: Jumpple Security
Plugin URI: http://www.Jumpple.com
Description: Jumpple will keep an eye on your website , 24 hours a day seven days a week, whether you are in a meeting, on a trip or even when you're asleep. See also: <a href="http://www.jumpple.com" target="_blank">www.jumpple.com</a> | <a href="http://www.jumpple.com/faq/" target="_blank">FAQ</a> 
Author: jumpple
Version: 1.0.1
Author URI: http://www.Jumpple.com
*/

add_action( 'init', 'jumpple_init' );

function jumpple_init()
{
	add_filter( 'plugin_action_links', 'jumpple_actions', 10, 2 );
	
	add_action( 'wp_footer', 'jumpple_badge' );
	add_action( 'admin_init', 'jumpple_settings' );
	add_action( 'admin_menu', 'jumpple_menu' );
	add_action( 'admin_notices', 'jumpple_admin_notices' );
	
	if ( is_admin() == true ) {
		wp_enqueue_script("jquery");
	}
}

function jumpple_admin_notices() {
	//if the website is not registered yet
	if ( get_option( 'jumpple-registered', false ) == false ) {
		echo '<div class="error jmpl" style="text-align: center;"><p style="color: red; font-size: 14px; font-weight: bold;">' . __( 'Your Jumpple plugin is not setup yet' ) . '</p><p>' .  __( 'Click ' ) . '<a href="options-general.php?page=jumpple_options">' . __( 'here' ) . '</a> ' .  __( 'to finish setup.' ) . '</p></div>';
	}
}

function jumpple_menu(){
	add_options_page( 'Jumpple', 'Jumpple', 'administrator', 'jumpple_options', 'jumpple_options' );
}

function jumpple_actions( $links, $file ){
	$this_plugin = plugin_basename( __FILE__ );
	
	if ( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=jumpple_options">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

function jumpple_badge() {
	if ( get_option( 'jumpple-registered', false ) == true && get_option( 'jumpple-badge', 'true' ) == 'true' ) {
		$pos = array(	'topright' => 'right: 0; top: 100px;',
			'downright' => 'right: 0; bottom: 100px;',
			'bottomright' => 'right: 100px; bottom: 0px;',
			'bottomleft' => 'left: 100px; bottom: 0px;'
			);

		$style = $pos[ get_option( 'jumpple-position',  'topright' ) ];

		echo '<a href="http://www.jumpple.com/" target="_blank"><img src="' . plugins_url( 'seal.png', __FILE__ ) . '" style="position: fixed; ' . $style . '" alt="" /></a>';
	}
}

function jumpple_footer() {
?>

<div id="share">
<a name="fb_share" class="fb-share" type="button_count" href="#" onclick="window.open( 'http://www.facebook.com/sharer.php?u=http%3A%2F%2Fwww.jumpple.com&amp;t=Check%20this%20cool%20service%20out!', 'sharer', 'toolbar=0, status=0, width=626, height=436' ); return false;"><img src="<?php echo plugins_url( 'fbshare.jpg', __FILE__ ); ?>" alt="Share" style="vertical-align: middle;" /></a>

<a href="http://twitter.com/share" class="twitter-share-button" data-count="none" data-text="Check out this cool service!" data-via="jumpple">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
</div>

<p>
<strong>Do you need to protect your other websites?<br />
<a href="https://jumpple.com/upgrade" target="_blank">Jumpple on!</a> - Time to upgrade.</strong>
</p>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<p>
<strong>Need to block SPAM? Check out the sweetest designed CAPTCHA service ever! <br />
<a href="http://wordpress.org/extend/plugins/sweetcaptcha-revolutionary-free-captcha-service/" target="_blank">SweetCaptcha</a> - Free designed captcha service</strong>
</p>

<style type="text/css">
#share { text-align: left; padding-bottom: 2px; }

.twitter-share-button, fb-share { vertical-align: middle }
</style>

<?php
}

function jumpple_settings() {
	register_setting( 'jumpple', 'jumpple-badge' );
	register_setting( 'jumpple', 'jumpple-position' );
}

function jumpple_options() {
	if ( get_option( 'jumpple-registered', false ) == false ) {
?>
<div class="wrap">
<h2>Jumpple</h2>

<?
if ( isset( $_POST['send'] ) ) {
	$api_app_id = '10004';
	$api_app_secret = 'e5c5acaf5a6720b137e80a496e7934bd';
	$api_url = 'https://www.jumpple.com/api/user/quickSignup';

	$params = array (
		'_app_id' => $api_app_id,
		'_app_secret' => $api_app_secret,
		'_response_format' => 'json',
		'user_email' => $_POST['email'],
		'user_domain' => $_POST['url'],
	);

	$data_sent = '';
	foreach($params as $name => $value) {
		$data_sent .= (empty($data_sent) ? '' : '&') . $name . '=' . urlencode($value);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_sent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec( $ch );
	curl_close( $ch );

	$response_json = json_decode( $result );


	if ( $response_json && $response_json->status == true ) {
		update_option( 'jumpple-registered', true );
?>
			<div id="error" class="valid">
				<ul>
					<li>You have successfully registered for the Jumpple monitoring service!</li>
					<li>We have sent you an email with your account activation details, please follow the instructions.</li>
					<li>Don't forget to manage your account, continue to <a href="http://www.jumpple.com/" target="_blank">http://www.Jumpple.com</a></li>
				</ul>
			</div>
			<script type="text/javascript" language="javascript">
				jQuery( 'div.error.jmpl' ).hide();
			</script>
<?php
		jumpple_badge_option();
		return false;
	} else {
?>
		<div id="error">
			<ul>
<?php
		if ( is_array( $response_json->error ) ) {
			foreach ( $response_json->error as $error ) {
				echo '<li>' . $error . '</li>';
			}
		} elseif ( $response_json->error ) {
			echo '<li>' . $response_json->error . '</li>';
		}
?>
			</ul>
		</div>
<?	}
}
?>

<p>Thank you for using Jumpple! protect your website in 60 seconds, register now!</p>

<form method="post" id="register">
	<input type="hidden" name="send" value="1" />
	<div>
		<label for="email">E-mail</label>
		<input id="email" name="email" type="text" />
		<span id="emailInfo"></span>
	</div>
	
	<div>
		<label for="url">URL</label>
		<input id="url" name="url" type="text" value="<?php echo site_url(); ?>" readonly="readonly" />
		<span id="urlInfo"></span>
	</div>
    
    <p class="submit">
		<input type="submit" id="register_submit" class="button-primary" name="send" value="<?php _e('Register') ?>" />
    </p>

</form>

<?php jumpple_footer(); ?>

</div>
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function($) {
	//global vars
	var form = $("#register");
	var url = $("#url");
	var urlInfo = $("#urlInfo");
	var email = $("#email");
	var emailInfo = $("#emailInfo");
	
	//On blur
	url.blur(validateurl);
	email.blur(validateEmail);

	// On Submitting
	form.submit(function(){
		$("#register_submit").attr('disabled', 'disabled');
	//	if( validateurl() & validateEmail() )
	//		return true
	//	else
	//		return false;
	});
	
	//validation functions
	function validateEmail(){
		//testing regular expression
		var a = $("#email").val();
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		//if it's valid email
		if(filter.test(a)){
			email.removeClass("error");
			emailInfo.text("");
			emailInfo.removeClass("error");
			return true;
		}
		//if it's NOT valid
		else{
			email.addClass("error");
			emailInfo.text("Please enter a valid E-mail.");
			emailInfo.addClass("error");
			return false;
		}
	}
	function validateurl(){
		//testing regular expression
		var a = $("#url").val();
		var filter = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
		//if it's valid url
		if(filter.test(a)){
			url.removeClass("error");
			urlInfo.text("");
			urlInfo.removeClass("error");
			return true;
		}
		//if it's NOT valid
		else{
			url.addClass("error");
			urlInfo.text("Please enter a valid url.");
			urlInfo.addClass("error");
			return false;
		}
	}
});
</script>

<style type="text/css">
#register{
	padding: 0 10px 10px;
}
#register label{
	display: block;
	color: #797979;
	font-weight: 700;
	line-height: 1.4em;
}
#register input{
	width: 220px;
	padding: 6px;
	font-family: Arial,  Verdana, Helvetica, sans-serif;
	font-size: 11px;
	border: 1px solid #cecece;
}
#register input.error{
	background: #f8dbdb;
	border-color: #e77776;
}
#register div{
	margin-bottom: 15px;
}
#register div span{
	margin-left: 10px;
	color: #b1b1b1;
	font-size: 11px;
	font-style: italic;
}
#register div span.error{
	color: #e46c6e;
}
#error{
	margin-bottom: 20px;
	border: 1px solid #efefef;
}
#error ul{
	list-style: square;
	padding: 5px;
	font-size: 11px;
}
#error ul li{
	list-style-position: inside;
	line-height: 1.6em;
}
</style>
<?php
	} else {
?>
<div class="wrap">
<h2>Jumpple</h2>

<?php jumpple_badge_option(); ?>

</div>
<?php
	}
}

function jumpple_badge_option() {
?>
<form method="post" action="options.php">
    <?php settings_fields( 'jumpple' ); ?>
    <table class="form-table"> 
        <tr valign="top">
        <th scope="row">Show Seal:</th>
        <td>	<select name="jumpple-badge">
			<option value="true">Show</option>
			<option value="false"<?php if( get_option( 'jumpple-badge',  'true' ) == 'false' ) echo ' selected="selected"'; ?>>Don't Show</option>
		</select>
	</td>
        <td><strong>Why do I need the Jumpple Seal?</strong><br />
The Jumpple Seal displays a small seal on your website letting your users know<br />
your website is monitored and protected by Jumpple!, This adds a proven sense of security and trust to your website.
        </td>
        </tr>

<tr valign="top">
        <th scope="row">Please select the location of the Jumpple Seal:</th>
        <td>	<select name="jumpple-position">
			<option value="topright">Top right</option>
			<option value="downright"<?php if( get_option( 'jumpple-position',  'topright' ) == 'downright' ) echo ' selected="selected"'; ?>>Down right</option>
			<option value="bottomright"<?php if( get_option( 'jumpple-position',  'topright' ) == 'bottomright' ) echo ' selected="selected"'; ?>>Bottom right</option>
			<option value="bottomleft"<?php if( get_option( 'jumpple-position',  'topright' ) == 'bottomleft' ) echo ' selected="selected"'; ?>>Bottom left</option>
		</select>
	</td>
        </tr>
	</table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>

<p>

</p>

<?php
	jumpple_footer();
}

