<?php

/*
  Plugin Name: Sweet Captcha
  Plugin URI: http://www.sweetcaptcha.com
  Description: Adds SweetCaptcha anti-spam solution to WordPress on the comment form, registration form, and other forms. Is compatible with Contact Form 7 and BuddyPress plug-ins. Wordpress network is also supported.
  Version: 3.1.0
  Author: Sweet Captcha
  Author URI: http://www.sweetcaptcha.com
  License: GNU GPL2
 */

/*
  Copyright (C) 2010 SweetCaptcha.com ltd. (www.sweetcaptcha.com). All rights reserved.

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License,
  version 2, as published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

define('SWEETCAPTCHA_SITE_URL', 'www.sweetcaptcha.com');

// for backward compatibility - 2.0
defined('WP_PLUGIN_DIR') or define('WP_PLUGIN_DIR', ABSPATH . '/wp-content/plugins');

// define absolute path to plugin
define('SWEETCAPTCHA_DIR_NAME', basename(dirname(__FILE__)));
define('SWEETCAPTCHA_ROOT', WP_PLUGIN_DIR . '/' . SWEETCAPTCHA_DIR_NAME);
define('SWEETCAPTCHA_URL', WP_PLUGIN_URL . '/' . SWEETCAPTCHA_DIR_NAME);
// define absolute path to plugin  sweetcaptcha.php
define('SWEETCAPTCHA_PHP_PATH', SWEETCAPTCHA_URL . '/library/sweetcaptcha.php');
// define absolute path to plugin library
define('SWEETCAPTCHA_LIBRARY', SWEETCAPTCHA_ROOT . '/library');
// define absolute path to plugin templates
define('SWEETCAPTCHA_TEMPLATE', SWEETCAPTCHA_ROOT . '/template');
define('SWEETCAPTCHA_ERROR_MESSAGE', __('You chose the wrong image solution for the security challenge, please read the instructions and try again.', 'sweetcaptcha'));
define('SWEETCAPTCHA_ERROR_MESSAGE_BR', __('You chose the wrong image solution for the security challenge.<br>Please read the instructions and try again.', 'sweetcaptcha'));
define('SWEETCAPTCHA_CONNECT_ERROR', __("It seems that your site / server can't reach sweetcaptcha.com,<br> please ask your hosting provider to open your site to http://www.sweetcaptcha.com:80 for it to function correctly.<br>Thank you,<br>sweetCaptcha Support Team.", 'sweetcaptcha'));
define('SWEETCAPTCHA_LOGIN_STYLE', '<style type="text/css">body.login div.sweetcaptcha ul li {background: inherit;width: auto;height: auto;} body.login div.sweetcaptcha ul li img {width: 34px !important;height: auto !important;}</style>');

add_action('plugins_loaded', 'sweetcaptcha_load_textdomain');

/**
 * @return true if sweetCaptcha is properly registered.
 */
function sweetcaptcha_is_registered() {
  return ((get_option('sweetcaptcha_app_id', '')) && (get_option('sweetcaptcha_key', '')) && (get_option('sweetcaptcha_secret', '')));
}

/* Load plugin textdomain. */
function sweetcaptcha_load_textdomain() {
  load_plugin_textdomain('sweetcaptcha', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// prepare wordpress version for check
$wp_versions = explode('.', $wp_version);

add_action('wp_enqueue_scripts', 'sweetcaptcha_init', 1);
if (!function_exists('sweetcaptcha_init')) {

  function sweetcaptcha_init() {
    wp_enqueue_script('jquery');
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin_info = get_plugin_data(__FILE__);
    $ver = $plugin_info["Version"];
    $app_id = get_option('sweetcaptcha_app_id', '') ? get_option('sweetcaptcha_app_id', '') : '1';
    wp_enqueue_script('swtcptcf', plugins_url('js/swtcptcf.js', __FILE__), array(), $ver);
    wp_enqueue_script('sweetcaptcha-csrf', 'https://'.SWEETCAPTCHA_SITE_URL.'/api/v2/apps/csrf/'.$app_id, array(), $ver, true);
    wp_enqueue_style('sweetcaptcha_Stylesheet', plugins_url('css/style.css', __FILE__), array(), $ver);
  }

}

add_action('plugins_loaded', 'sweetcaptcha_plugins_loaded');
if (!function_exists('sweetcaptcha_plugins_loaded')) {

  function sweetcaptcha_plugins_loaded() {
    if (get_option('sweetcaptcha_form_contact_7')) { // add SweetCaptcha to Contact Form 7
      if (function_exists('wpcf7_add_shortcode')) {
        wpcf7_add_shortcode('sweetcaptcha', 'sweetcaptcha_shortcode_cf7', true);
        add_filter('wpcf7_validate_sweetcaptcha', 'sweetcaptcha_check', 10, 2);
      }
    }
    if (get_option('sweetcaptcha_form_gf')) { // add SweetCaptcha to Gravity Form
      add_filter("gform_submit_button", "sweetcaptcha_gform_submit_button", 10, 2);

      //add_filter("gform_validation", "sweetcaptcha_gform_validation");
      add_action("gform_pre_submission", "sweetcaptcha_gform_pre_submission");
    }
  }

}

require_once SWEETCAPTCHA_LIBRARY . '/sweetcaptcha.php';

// split action to admin and public part
if (is_admin()) {
  require_once SWEETCAPTCHA_LIBRARY . '/admin.php';
  // Add admin notices.
  add_action('admin_notices', 'sweetcaptcha_admin_notices');
  // add link to settings menu
  add_action('admin_menu', 'sweetcaptcha_admin_menu');

  // TODO: add activation hook -> default option values - XXX - doesn't work since wp 3.1 and below 2.8
  //register_activation_hook( __FILE__, 'sweetcaptcha_activate' );
  // because various problems with register activation hook trough Wordpress versions - check if Sweet Captcha is installed, otherwise set default values
  add_action('admin_menu', 'sweetcaptcha_activate');

  // add uninstall hook -> remove option values - only for wordpress version >= 2.7
  if (( $wp_versions[0] >= 2 ) && ( $wp_versions[1] >= 7 )) {
    register_uninstall_hook(__FILE__, 'sweetcaptcha_uninstall');
  }
} else {
  if (sweetcaptcha_is_registered()) {
    require_once SWEETCAPTCHA_LIBRARY . '/public.php';
    // add jquery to all public pages
    // add Sweet Captcha to comment form
    if (get_option('sweetcaptcha_form_comment')) {
      add_action('comment_form', 'sweetcaptcha_comment_form', 1);
      add_filter('preprocess_comment', 'sweetcaptcha_comment_form_check', 1);
    }

    // add Sweet Captcha to login form

    if (get_option('sweetcaptcha_form_login')) {
      if (( ( $wp_versions[0] >= 2 ) || ( $wp_versions[1] > 7 ))) {
        add_action('login_form', 'sweetcaptcha_login_form', 1);
        add_filter('authenticate', 'sweetcaptcha_authenticate', 40, 3);
      }
      // add SweetCaptcha to BuddyPress login form
      add_action('bp_sidebar_login_form', 'sweetcaptcha_login_form');
    }


    // add Sweet Captcha to lost password form
    if (get_option('sweetcaptcha_form_lost')) {
      add_action('lostpassword_form', 'sweetcaptcha_login_form', 1);
      add_filter('allow_password_reset', 'sweetcaptcha_lost_password_check', 1);
    }

    // add Sweet Captcha to registration form
    if (get_option('sweetcaptcha_form_registration')) {
      add_action('register_form', 'sweetcaptcha_registration_form');
      add_filter('registration_errors', 'sweetcaptcha_register_form_check', 1);

      add_action('bp_before_registration_submit_buttons', 'sweetcaptcha_before_registration_submit_buttons');
      add_action('bp_signup_validate', 'sweetcaptcha_signup_validate');

      // adding SweetCaptcha to Network Wordpress registration form - WP version >= 3
      if (( $wp_versions[0] > 2)) {
        add_action('signup_extra_fields', 'sweetcaptcha_signup_extra_fields');
        add_filter('wpmu_validate_user_signup', 'sweetcaptcha_wpmu_validate_user_signup');
      }
    }
  }
}

// SweetCaptcha Contact Form 
if (get_option('sweetcaptcha_form_contact')) {
  require_once 'sweetcaptcha-contact-form.php';
}

// load plugin text domain - i18n
if (function_exists('load_plugin_textdomain')) {
  load_plugin_textdomain('sweetcaptcha', true, 'sweetcaptcha/languages');
}

/* Sample of adding header to sweetcaptcha form */
/*
function sweetcaptcha_header() {
  return '<label>Captcha Anti-Spam Question (required)</label>';
}
*/
