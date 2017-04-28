<?php

$template = \PetrovEgor\templates\Template::get_instance();
$params = $template->get_params();

$admin_url = admin_url();

if ( $params['is_wrong_api_key'] ) {
?>
	<div class="notice notice-warning">
		<p>
			Google API key is incorrect. Check key in developer console: <a href="https://console.developers.google.com/apis/credentials" target="_blank">https://console.developers.google.com/apis/credentials</a>
		</p>
	</div>
<?php
}
?>
<h1>Plugin settings</h1>
<form action="<?php echo esc_html( $admin_url ); ?>admin.php?page=youtube-checker-settings" method="post">
	<?php
	$value = isset( $params['apiKey'] ) ? $params['apiKey'] : '';
	$hour_checked = '';
	$twice_day_checked = '';
	$once_day_checked = '';
	if ( isset( $params['checkFreq'] ) ) {
		switch ( $params['checkFreq'] ) {
			case 'hourly':
				$hour_checked = 'checked';
				break;
			case 'twicedaily':
				$twice_day_checked = 'checked';
				break;
			case 'daily':
				$once_day_checked = 'checked';
				break;
		}
	}

	?>
	Google API key: <input type="text" placeholder="api key from developer console" class="regular-text" name="api_key" value="<?php echo esc_html( $value ); ?>"><br>
	<p>You can get API key here: <a href="https://console.developers.google.com/apis/credentials" target="_blank">https://console.developers.google.com/apis/credentials</a></p>

	<h2>Checking frequency</h2>
	<fieldset>
		<legend class="screen-reader-text"><span>input type="radio"</span></legend>
		<label title='g:i a'>
			<input type="radio" name="sync_frequency" value="hourly" <?php echo esc_html( $hour_checked ); ?> />
			<span>Every hour</span>
		</label><br>
		<label title='g:i a'>
			<input type="radio" name="sync_frequency" value="twicedaily" <?php echo esc_html( $twice_day_checked ); ?>/>
			<span>Twice a day</span>
		</label><br>
		<label title='g:i a'>
			<input type="radio" name="sync_frequency" value="daily" <?php echo esc_html( $once_day_checked ); ?>/>
			<span>Once a day</span>
		</label>
		<?php wp_nonce_field(); ?>
	</fieldset>
	<br>
	<input class="button-primary" type="submit" name="Save settings" value="Save settings">
</form>
