<?php
$template = \PetrovEgor\templates\Template::get_instance();
$params = $template->get_params();

?>
	<h1>Available Videos</h1>
	<table class="widefat">
		<thead>
		<tr>
			<th class="row-title">Posts</th>
			<th>Type</th>
			<th>Videos</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$counter = 0;
		foreach ( $params['posts'] as $post ) {
			$counter++;
			$class = ( $counter % 2 ) === 0 ? ' class="alternate"' : '';
			$wp_post = get_post( (int) $post['post_id'] );
			?>
			<tr<?php echo esc_html( $class ); ?>>
				<td class="row-title"><label for="tablecell">
						<div alt="f135" class="dashicons dashicons-align-left"></div>
						<a href="<?php echo esc_html( $wp_post->guid ); ?>" target="_blank">
							<?php echo esc_html( $wp_post->post_title ); ?>
						</a>
					</label></td>
				<td><?php echo esc_html( $wp_post->post_type ); ?></td>
				<?php
				$ids = \PetrovEgor\common::get_available_video_list( $wp_post );
				?>
				<td>
					<?php
					foreach ( $ids as $id ) {
						?><div alt="f236" class="dashicons dashicons-video-alt3"></div>
								<a href='https://www.youtube.com/watch?v=<?php echo esc_html( $id ) ?>' target='_blank'>
								https://www.youtube.com/watch?v=<?php echo esc_html( $id ) ?>
								</a>
								<br>
						<?php
					}
					?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<br>
	<span style="font-size: larger;"><?php esc_html( $params['paginationLinks'] ); ?></span>
<?php
