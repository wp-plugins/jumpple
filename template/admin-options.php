<div class="wrap">

  <div class="icon32 icon32-bws" id="icon-options-sweetcaptcha"></div>
  <h2><?php _e('sweetCaptcha Settings', 'sweetcaptcha'); ?></h2>

  <?php if (!empty($message)): ?>
    <div class="updated" style="width:99%; float: left">
      <p><strong><?php echo $message; ?></strong></p>
    </div>
  <?php endif; ?>

  <!-- <div style="padding: 4px; background: #FAF6E5; width: 660px; border-radius: 5px; overflow: hidden;"></div> -->
  
	<div style='position: relative; clear: both;'>

		<div style="padding: 0px 0px 10px 0px; overflow: hidden; position: absolute; left: 300px;top: 65px;right: 200px; border-bottom: 1px solid #DDDDDD; width: 314px;">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="formDonate" id="formDonate" target="_blank" style="float: left">
			<input type="hidden" name="cmd" value="_s-xclick">
      <input type="hidden" name="hosted_button_id" value="KJ9FG7STBXQ76">
      <input type="image" style="float: left; padding: 3px 0px 3px 0px;" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" name="submitDonate" id="submitDonate" alt="PayPal - The safer, easier way to pay online!">
      <img style="width:1; height:1px; border:none; padding:0; margin:0;" alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif">
    </form>
    <div style="float: left; margin: 7px 0px 0px 10px; color: #777; font-style: italic">to help keep sweetCaptcha free</div>
  </div>
	
  <form name="form1" method="post" action="">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
    <table class="form-table">
      <tbody>
        <?php
        if (!empty($sweetcaptcha_options) && is_array($sweetcaptcha_options)):
          foreach ($sweetcaptcha_options as $opt_name => $opt):
        ?>

        <?php if ( ($opt_name == 'sweetcaptcha_additional_settings_top') || ($opt_name == 'sweetcaptcha_additional_settings_bottom') ) { ?>
          <tr>
						<th scope="row"><label>Language, themes, statistics</label></th>
            <td style="padding: 20px 10px;">
              <a href="http://www.sweetcaptcha.com/accounts/signin?ref=wordpress" target="_blank" style="font-weight: bold">Login</a> to your sweetCaptcha account for changing your language, design and additional settings
							<?php if ($opt_name == 'sweetcaptcha_additional_settings_top') { $margin = 'margin-bottom: 30px;'; } else { $margin = ''; } ?>
              <div style="color: #999; <?=$margin?>">Your password was sent to you in your welcome email</div>
            </td>
          </tr>
        <?php continue; } ?>

        <tr valign="top">
          <th scope="row" style="min-width: 15%"><label for="<?php echo $opt_name ?>"><?php echo $opt['title'] . ':'; ?></label></th>
          <?php
            if (!substr_count($opt_name, '_form_')) {
              $type = 'text';
              $checked = null;
              $class = ' class="regular-text"';
              $value = isset($options_values[$opt_name]) ? $options_values[$opt_name] : null;
            } else {
              $type = 'checkbox';
              $checked = isset($options_values[$opt_name]) && !empty($options_values[$opt_name]) ? ' checked="checked"' : null;
              $class = null;
              $value = 1;
            }
          ?>
          <td>
            <input<?php echo $class ?> id="<?php echo $opt_name ?>" type="<?php echo $type ?>" name="<?php echo $opt_name ?>" value="<?php echo $value ?>" size="50" <?php echo $checked ?> />
            <?php if (isset($sweetcaptcha_options[$opt_name]['description'])): ?>
              <span class="description"><?php echo $sweetcaptcha_options[$opt_name]['description']; ?></span>
						<?php endif; ?>
          </td>
        </tr>
        
        <?php
        if ($opt_name == 'sweetcaptcha_form_contact_7') {
        ?>
        <tr>
          <td colspan="2">
            <div style="margin-left: 220px; padding: 10px; background: #eee; width: 600px">
              <?php echo __('To integrate SweetCaptcha with Contact Form 7 please do the following:', 'sweetcaptcha') . '<br />'; ?>
              <?php echo __('A. Copy the following tag with square brackets [sweetcaptcha sweetcaptcha]', 'sweetcaptcha') . '<br />'; ?>
              <?php echo __('B. Open the page with settings of Contact Form 7', 'sweetcaptcha') . '<br />'; ?>
              <?php echo __('C. Paste the copied tag into "Form" section above the line which contains "&lt;p&gt;[submit "Send"]&lt;/p&gt;"', 'sweetcaptcha') . '<br />'; ?>
              <?php printf(__('D. Need more help ?  <a href="%s" title="Contact us" target="_blank">Contact us</a>.', 'sweetcaptcha'), 'http://'.SWEETCAPTCHA_SITE_URL.''); ?>
            </div>
          </td>
        </tr>
        <?php } ?>
        
        <?php if ($opt_name == 'sweetcaptcha_form_contact') { ?>
        <tr>
          <td colspan="2">
            <div style="margin-left: 220px; padding: 10px; background: #eee; width: 600px">
              <?php include 'admin-options-contactform.php'; ?>
            </div>
          </td>
        </tr>
        <?php } ?>

        <?php
          endforeach;
        endif;
        ?>
      </tbody>
    </table>

    <p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'sweetcaptcha') ?>" /></p>
  </form>
	</div>
</div>