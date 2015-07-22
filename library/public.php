<?php

/**
 * Get SweetCaptcha values from POST data
 * @return array
 */
function sweetcaptcha_get_values() {
	return array(
		'sckey' => ( isset($_POST['sckey']) ? $_POST['sckey'] : '' ),
		'scvalue' => ( isset($_POST['scvalue']) ? $_POST['scvalue'] : '' )
	);
}

/**
 * Move submit button under SweetCaptcha field.
 * @return string
 */
function sweetcaptcha_move_submit_button() {
	return '
		<div id="sweetcaptcha-submit-button" class="form-submit"><br /></div>
		<script type="text/javascript">
		;(function(){
			try {
				var sub = document.getElementById("submit");
				if (! sub) { sub = document.getElementById("submitcomment"); }
				if (! sub) { sub = document.getElementById("wp-submit"); }
				if (! sub) { sub = document.getElementById("login"); }
				if (sub) {
					sub.parentNode.removeChild(sub);
					document.getElementById("sweetcaptcha-submit-button").appendChild(sub);
					document.getElementById("submit").tabIndex = 6;
				}
			}
			catch (e) {}
		})();
	  </script>';
}

/**
 * Add SweetCaptcha to comment form
 * @return boolean
 */
function sweetcaptcha_comment_form() {
	global $sweetcaptcha_instance, $user_ID, $wp_version;
	if (get_option('sweetcaptcha_form_omit_users') && isset($user_ID) && (int) $user_ID > 0) {
		return true;
	}

	$wp_versions = explode('.', $wp_version);
	echo $sweetcaptcha_instance->get_html();
	echo sweetcaptcha_move_submit_button();
	if ($wp_versions[0] >= 3 && $wp_versions[1] >= 0) {
		echo '
		<script type="text/javascript">
		;(function(){
			try {
				var respond = document.getElementById("respond");
			  if (respond) {
				  document.getElementById("respond").style.overflow="visible";
				}
			}
			catch (e) {}
		})();
		</script>';
	}
	remove_action('comment_form', 'sweetcaptcha_comment_form');
	return true;
}

/**
 * Add SweetCaptcha check submitted comment form
 * @return boolean
 */
function sweetcaptcha_comment_form_check($comment) {
	global $sweetcaptcha_instance, $user_ID;
	if (get_option('sweetcaptcha_form_omit_users') && isset($user_ID) && (int) $user_ID > 0) {
		return $comment;
	}
	if (!empty($comment['comment_type']) && ( $comment['comment_type'] != 'comment' )) {
		return $comment;
	}
	$scValues = sweetcaptcha_get_values();
	if ($sweetcaptcha_instance->check($scValues) == 'true') {
		return $comment;
	} else { // since 2.0.4
		if (function_exists('wp_die')) {
			wp_die('<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		} else {
			die('<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		}
	}
}

/**
 * SweetCaptcha adjustments for login, registration, lost password,... form
 * @return boolean
 */
function sweetcaptcha_adjust_form() {
	return '';
}

/**
 * Add SweetCaptcha to login form
 * @return boolean
 */
function sweetcaptcha_login_form() {
	global $sweetcaptcha_instance;
	echo SWEETCAPTCHA_LOGIN_STYLE;
	echo $sweetcaptcha_instance->get_html();
	//echo sweetcaptcha_move_submit_button();
	return true;
}

/**
 * Add SweetCaptcha to registration form
 * @return boolean
 */
function sweetcaptcha_registration_form() {
	global $sweetcaptcha_instance;
	if (!get_option('sweetcaptcha_form_registration')) {
		return true;
	}
	echo SWEETCAPTCHA_LOGIN_STYLE;
	echo $sweetcaptcha_instance->get_html();
	//echo sweetcaptcha_move_submit_button();
	//echo sweetcaptcha_adjust_form();
	return true;
}

/**
 * Add SweetCaptcha authetificate check
 * @param $user
 * @return WP_Error
 */
function sweetcaptcha_authenticate($user) {
	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	if (!empty($_POST) && $sweetcaptcha_instance->check($scValues) != 'true') {
		$user = new WP_Error('captcha_wrong', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
	}
	return $user;
}

/**
 * Add SweetCaptcha lost password check
 * @param $user
 * @return mixed WP_Error or boolean
 */
function sweetcaptcha_lost_password_check($user) {
	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	if ($sweetcaptcha_instance->check($scValues) != 'true') {
		$user = new WP_Error('captcha_wrong', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		return $user;
	}
	return TRUE;
}

/**
 * Add SweetCaptcha registration form check
 * @param $errors
 * @return WP_Errors
 */
function sweetcaptcha_register_form_check($errors) {
	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	if ($sweetcaptcha_instance->check($scValues) != 'true') {
		$errors->add('captcha_wrong', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
	}
	return $errors;
}

/**
 * Add SweetCaptcha to BuddyPress registration form
 * @return boolean
 */
function sweetcaptcha_before_registration_submit_buttons() {
	global $sweetcaptcha_instance;
	echo
	'<div id="sweetcaptcha-wrapper">'
	. ( ( function_exists('sweetcaptcha_header') ) ? sweetcaptcha_header() : '' )
	. $sweetcaptcha_instance->get_html()
	. '</div>';
	return TRUE;
}

/**
 * Add SweetCaptcha to BuddyPress registration form validation
 * @return boolean
 */
function sweetcaptcha_signup_validate() {
	global $bp, $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	if ($sweetcaptcha_instance->check($scValues) != 'true') {
		$bp->signup->errors['signup_username'] = __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha');
	}
}

/**
 * Add SweetCaptcha to Wordpress Network sign-up form 
 * @param $errors
 * @return boolean
 */
function sweetcaptcha_signup_extra_fields($errors) {
	global $sweetcaptcha_instance;
	$error = ( isset($errors)) ? $errors->get_error_message('captcha_wrong') : '';
	echo $sweetcaptcha_instance->get_html();
	if (isset($error) && !empty($error)) {
		echo '<p class="error">' . $error . '</p>';
	}
	return true;
}

/**
 * Add SweetCaptcha validation to Wordpress Network sign-up form
 * @param $errors
 * @return mixed
 */
function sweetcaptcha_wpmu_validate_user_signup($errors) {
	global $sweetcaptcha_instance;
	if ($_POST['stage'] == 'validate-user-signup') {
		$scValues = sweetcaptcha_get_values();
		if ($sweetcaptcha_instance->check($scValues) != 'true') {
			$errors['errors']->add('captcha_wrong', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		}
	}
	return $errors;
}

/**
 * Add SweetCaptcha standard check (use for Contact Form 7)
 */
function sweetcaptcha_check($errors, $tag = '') {

	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();

	$tag = new WPCF7_Shortcode( $tag );

	if ($sweetcaptcha_instance->check($scValues) != 'true') {

		if ( method_exists($errors, 'invalidate' ) ) { // since CF7 4.1
			$errors->invalidate( $tag , __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha') );
		} else {
			$errors['valid'] = false;
			$errors['reason'] = '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha');
		}
	}
	return $errors;
}

/**
 * Add SweetCaptcha short code to Contact Form 7
 * @return a string with HTML code
 */
function sweetcaptcha_shortcode_cf7($tag) {

	$input = '<span class="wpcf7-form-control-wrap sweetcaptcha"><input type="text" name="sweetcaptcha" value="" size="1" class="wpcf7-form-control wpcf7-text" style="display: none;" /></span>';
	$sc = sweetcaptcha_shortcode().$input;

	return $sc;
}

/****************************************************************************************************************************
 * Add SweetCaptcha short code to Gravity Forms
 * @return a string with HTML code
 */

function sweetcaptcha_gform_submit_button ($button, $form) {
	//$input = '<span class="wpcf7-form-control-wrap your-sweetcaptcha"><input type="text" name="your-sweetcaptcha" value="" size="1" class="wpcf7-form-control wpcf7-text" style="display:none;" /></span>';
	return sweetcaptcha_shortcode().'<div class="sweetcaptcha-after"></div>'.$button;
}

function sweetcaptcha_gform_pre_submission($form) {
	//$_POST["input_3.3"] = "sweetcaptcha_gform_pre_submission";
	//die('sweetcaptcha_gform_pre_submission');
	return $form;
}

function sweetcaptcha_gform_validation($validation_result) {
	//	$validation_result["is_valid"] = true;
	//return $validation_result;
	$form = $validation_result["form"];
	//echo "sweetcaptcha_gform_validation(validation_result) = "; var_dump($validation_result)."<br>";die();
	//sweetcaptcha_before_registration_submit_buttons();
	//if ( empty($_POST['input_3.3']) ){
	//$validation_result["is_valid"] = false;
	//foreach ($form["fields"] as &$field) {
		//var_dump($field['id]']);
	//}
	
	//return $validation_result;
	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	echo __('ERROR', 'sweetcaptcha') . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha');
	if ($sweetcaptcha_instance->check($scValues) != 'true') {
		$validation_result["is_valid"] = false;
		//if (!empty($tag)) {
			//$errors['valid'] = false;
			//$errors['reason']['your-sweetcaptcha'] = __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha');
		//} else {
			//$errors['errors']->add('sweetcaptcha', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		//}
	}
	//return $errors;
	$validation_result["form"] = $form;
	return $validation_result;
}

/****************************************************************************************************************************
 * Add SweetCaptcha short code
 * @param $atts
 * @return string
 */
function sweetcaptcha_shortcode($atts = array()) {
	global $sweetcaptcha_instance;
	return ( ( function_exists('sweetcaptcha_header') ) ? sweetcaptcha_header() : '' ) . $sweetcaptcha_instance->get_html();
}

/**
 * Validate SweetCaptcha form
 * @param $errors
 * @param $tag
 * @return mixed array
 */
function sweetcaptcha_validate($errors, $tag = NULL) {
	global $sweetcaptcha_instance;
	$scValues = sweetcaptcha_get_values();
	if ($sweetcaptcha_instance->check($scValues) != 'true') {
		if (!empty($tag)) { // if Contact Form 7
			$errors['valid'] = false;
			$errors['reason']['your-message'] = __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha');
		} else {
			$errors['errors']->add('sweetcaptcha', '<strong>' . __('ERROR', 'sweetcaptcha') . '</strong>: ' . __(SWEETCAPTCHA_ERROR_MESSAGE, 'sweetcaptcha'));
		}
	}
	return $errors;
}
