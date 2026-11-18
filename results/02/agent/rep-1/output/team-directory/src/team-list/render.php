<?php
/**
 * Server-side render for the Team List block.
 *
 * @package TeamDirectory
 *
 * @var array     $attributes Block attributes.
 * @var string    $content    Block inner content.
 * @var \WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$team_directory_query = Team_Directory\query_members(
	array(
		'role'     => isset( $attributes['role'] ) ? (string) $attributes['role'] : '',
		'per_page' => isset( $attributes['perPage'] ) ? (int) $attributes['perPage'] : 10,
		'page'     => 1,
	)
);

$team_directory_show_photo = ! isset( $attributes['showPhoto'] ) || (bool) $attributes['showPhoto'];
$team_directory_show_bio   = ! isset( $attributes['showBio'] ) || (bool) $attributes['showBio'];
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( ! $team_directory_query->have_posts() ) : ?>
		<p class="wp-block-team-directory-team-list__empty">
			<?php esc_html_e( 'No team members found.', 'team-directory' ); ?>
		</p>
	<?php else : ?>
		<ul class="wp-block-team-directory-team-list__list">
			<?php
			foreach ( $team_directory_query->posts as $team_directory_post ) :
				$team_directory_member = Team_Directory\format_member( $team_directory_post );
				$team_directory_thumb  = (int) get_post_thumbnail_id( $team_directory_post );
				?>
				<li class="wp-block-team-directory-team-list__item">
					<?php
					if ( $team_directory_show_photo && $team_directory_thumb ) {
						echo wp_get_attachment_image(
							$team_directory_thumb,
							'medium',
							false,
							array(
								'class'   => 'wp-block-team-directory-team-list__photo',
								'loading' => 'lazy',
							)
						);
					}
					?>
					<div class="wp-block-team-directory-team-list__details">
						<h3 class="wp-block-team-directory-team-list__name">
							<a href="<?php echo esc_url( $team_directory_member['link'] ); ?>">
								<?php echo esc_html( $team_directory_member['name'] ); ?>
							</a>
						</h3>
						<?php if ( '' !== $team_directory_member['role'] ) : ?>
							<p class="wp-block-team-directory-team-list__role">
								<?php echo esc_html( $team_directory_member['role'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( $team_directory_member['start_year'] ) : ?>
							<p class="wp-block-team-directory-team-list__since">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %d: start year. */
										__( 'Since %d', 'team-directory' ),
										$team_directory_member['start_year']
									)
								);
								?>
							</p>
						<?php endif; ?>
						<?php if ( $team_directory_show_bio && '' !== $team_directory_member['bio'] ) : ?>
							<p class="wp-block-team-directory-team-list__bio">
								<?php echo esc_html( $team_directory_member['bio'] ); ?>
							</p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
