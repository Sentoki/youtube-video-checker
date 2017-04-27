<?php

$template = \PetrovEgor\templates\Template::get_instance();
$params = $template->get_params();

$admin_url = admin_url();

?>
<h1>Main info</h1>
<div class="notice notice-info inline">
	<p>
		Last check: <?php echo esc_html( $params['lastCheck'] ); ?>
	</p>
</div>
<div class="notice notice-info inline">
	<p>
		Next check: <?php
		if ( is_null( $params['nextScheduled'] ) ) {
			echo 'not planned';
		} else {
			echo esc_html( $params['nextScheduled'] );
		}
		?>
	</p>
</div>
<div class="notice notice-success">
	<p>
		Available:  <?php echo esc_html( $params['availableCounter'] ); ?> videos
	</p>
</div>
<div class="notice notice-error">
	<p>
		Unavailable: <?php echo esc_html( $params['unavailableCounter'] ); ?> videos
	</p>
</div>


<?php
if ( \PetrovEgor\common::is_develop_mode() ) {
?>
<br>
<a class="button-secondary" href='<?php echo esc_html( $admin_url ); ?>admin.php?page=youtube-checker&youtube_checker_action=search-videos-in-posts' title="Search videos in posts">Search videos in posts</a>
	<a class="button-secondary" href='<?php echo esc_html( $admin_url ); ?>admin.php?page=youtube-checker&youtube_checker_action=check-by-api' title="Check videos by API">Check videos by API</a>
<?php } ?>
