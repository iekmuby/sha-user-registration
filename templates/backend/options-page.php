<div class="wrap">
	<h1><?php _e( 'User Registration Settings', 'sha-ureg' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'sha-ureg-settings-group' ); ?>
		<?php do_settings_sections( 'sha-ureg-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Allow username', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $allow_username == 1) ? 'checked ' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>allow_username" value="1" <?php echo $checked; ?>/>
					<span class="description"><?php _e( 'Allow to users set their own usernames. Otherwise username is equal to email address', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Allow password', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $allow_password == 1) ? 'checked ' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>allow_password" class="toggle-checkbox" data-group="password" value="1" <?php echo $checked; ?>/>
					<span class="description"><?php _e( 'Allow to users set their own passwords', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<?php $pass_class = ( $allow_password == 1 ) ? '' : ' hided'; ?>
			<tr valign="top" class="password<?php echo $pass_class; ?>">
				<th scope="row">
					<?php _e( 'Validate password', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $validate_password == 1) ? 'checked ' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>validate_password" value="1" <?php echo $checked; ?>/>
					<span class="description"><?php _e( 'Disallow weak passwords (less than 8 chars, lowercase only, no digits and punctuation)', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Need agreement', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $need_agreement == 1) ? 'checked ' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>need_agreement" class="toggle-checkbox" data-group="agreement"  value="1" <?php echo $checked; ?>/>
					<span class="description"><?php _e( 'Agreement checkbox, required before registration', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<?php $agreement_class = ( $need_agreement == 1 ) ? '' : ' hided'; ?>
			<tr valign="top" class="agreement<?php echo $agreement_class; ?>">
				<th scope="row">
					<?php _e( 'Agreement label', 'sha-ureg' ); ?>
				</th>
				<td>
					<input type="text" name="<?php echo $sha_ureg_prefix; ?>agreement_label" value="<?php echo $agreement_label; ?>" /><br />
					<span class="description"><?php _e( 'Agreement label data, next to checkbox', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Use reCaptcha', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $use_recaptcha == 1) ? ' checked' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>use_recaptcha" class="toggle-checkbox" data-group="recaptcha"  value="1"<?php echo $checked; ?> />
					<span class="description"><?php _e( 'Add Google reCaptcha to registration form', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<?php $recaptcha_class = ( $use_recaptcha == 1 ) ? '' : ' hided'; ?>
			<tr valign="top" class="recaptcha<?php echo $recaptcha_class; ?>">
				<th scope="row">
					<?php _e( 'reCaptcha site key', 'sha-ureg' ); ?>
				</th>
				<td>
					<input type="text" name="<?php echo $sha_ureg_prefix; ?>rec_site_key" value="<?php echo $site_key; ?>" />
				</td>
			</tr>
			<tr valign="top" class="recaptcha<?php echo $recaptcha_class; ?>">
				<th scope="row">
					<?php _e( 'reCaptcha secret key', 'sha-ureg' ); ?>
				</th>
				<td>
					<input type="password" name="<?php echo $sha_ureg_prefix; ?>rec_secret" value="<?php echo str_repeat( '*', strlen( $secret_key ) ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Need activation', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php $checked = ( $need_activation == 1) ? ' checked' : ''; ?>
					<input type="checkbox" name="<?php echo $sha_ureg_prefix; ?>need_activation" class="toggle-checkbox"  data-group="activation" value="1"<?php echo $checked; ?> />
					<span class="description"><?php _e( 'Sends activation link to user, if checked. Otherwise user already activated', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<?php $activation_class = ( $need_activation == 1 ) ? '' : ' hided'; ?>
			<tr valign="top" class="activation<?php echo $activation_class; ?>">
				<th scope="row">
					<?php _e( 'Activation success page contents', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php wp_dropdown_pages( array( 'selected' => (int)$activation_success, 'name' => $sha_ureg_prefix . 'activation_success', 'show_option_none' => __( 'No redirect', 'sha-ureg' ) ) ); ?><br />
					<span class="description"><?php _e( 'Page, which content shows after success activation', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<tr valign="top" class="activation<?php echo $activation_class; ?>">
				<th scope="row">
					<?php _e( 'Activation fail page contents', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php wp_dropdown_pages( array( 'selected' => (int)$activation_fail, 'name' => $sha_ureg_prefix . 'activation_fail', 'show_option_none' => __( 'No redirect', 'sha-ureg' ) ) ); ?><br />
					<span class="description"><?php _e( 'Page, which content shows after failed activation', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php _e( 'Registration redirect', 'sha-ureg' ); ?>
				</th>
				<td>
					<?php wp_dropdown_pages( array( 'selected' => (int)$registration_redirect, 'name' => $sha_ureg_prefix . 'registration_redirect', 'show_option_none' => __( 'No redirect', 'sha-ureg' ) ) ); ?><br />
					<span class="description"><?php _e( 'Page, where user will be redirected after activation', 'sha-ureg' ); ?>.</span>
				</td>
			</tr>
		</table>
		
		<?php submit_button(); ?>

	</form>
</div>
