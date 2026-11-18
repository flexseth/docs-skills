<?php
/**
 * Server-side rendering of the Team List block.
 *
 * @package TeamDirectory
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$team_directory_query = Team_Directory_Members::query(
	array(
		'page'     => 1,
		'per_page' => isset( $attributes['perPage'] ) ? (int) $attributes['perPage'] : Team_Directory_Members::DEFAULT_PER_PAGE,
		'role'     => isset( $attributes['role'] ) ? (string) $attributes['role'] : '',
	)
);

$team_directory_show_photo = ! isset( $attributes['showPhoto'] ) || (bool) $attributes['showPhoto'];
$team_directory_show_bio   = ! isset( $attributes['showBio'] ) || (bool) $attributes['showBio'];
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( empty( $team_directory_query->posts ) ) : ?>
		<p class="wp-block-team-directory-team-list__empty">
			<?php esc_html_e( 'No team members found.', 'team-directory' ); ?>
		</p>
	<?php else : ?>
		<ul class="wp-block-team-directory-team-list__list">
			<?php
			foreach ( $team_directory_query->posts as $team_directory_post ) :
				$team_directory_member = Team_Directory_Members::prepare( $team_directory_post );
				?>
				<li class="wp-block-team-directory-team-list__member">
					<?php
					if ( $team_directory_show_photo && $team_directory_member['photo'] ) {
						echo wp_get_attachment_image(
							$team_directory_member['photo']['id'],
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
						<?php if ( '' !== $team_directory_member['role'] || $team_directory_member['start_year'] ) : ?>
							<p class="wp-block-team-directory-team-list__meta">
								<?php if ( '' !== $team_directory_member['role'] ) : ?>
									<span class="wp-block-team-directory-team-list__role"><?php echo esc_html( $team_directory_member['role'] ); ?></span>
								<?php endif; ?>
								<?php if ( $team_directory_member['start_year'] ) : ?>
									<span class="wp-block-team-directory-team-list__since">
										<?php
										/* translators: %d: Year the member started. */
										echo esc_html( sprintf( __( 'Since %d', 'team-directory' ), $team_directory_member['start_year'] ) );
										?>
									</span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $team_directory_show_bio && '' !== $team_directory_member['bio'] ) : ?>
							<div class="wp-block-team-directory-team-list__bio">
								<?php echo wp_kses_post( wpautop( esc_html( $team_directory_member['bio'] ) ) ); ?>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
