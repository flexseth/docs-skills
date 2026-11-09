<?php
/**
 * Server render for team-directory/list.
 *
 * @package TeamDirectory
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$team_directory_role     = isset( $attributes['role'] ) ? sanitize_text_field( $attributes['role'] ) : '';
$team_directory_per_page = isset( $attributes['perPage'] ) ? (int) $attributes['perPage'] : 10;
$team_directory_query    = team_directory_query_members( $team_directory_role, 1, $team_directory_per_page );
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>>
	<?php if ( $team_directory_query->have_posts() ) : ?>
		<ul class="wp-block-team-directory-list__items">
			<?php
			foreach ( $team_directory_query->posts as $team_directory_member ) :
				$team_directory_member_role = (string) get_post_meta( $team_directory_member->ID, 'role', true );
				$team_directory_start_year  = (int) get_post_meta( $team_directory_member->ID, 'start_year', true );
				?>
				<li class="wp-block-team-directory-list__item">
					<a class="wp-block-team-directory-list__name" href="<?php echo esc_url( get_permalink( $team_directory_member ) ); ?>">
						<?php echo esc_html( get_the_title( $team_directory_member ) ); ?>
					</a>
					<?php if ( '' !== $team_directory_member_role ) : ?>
						<span class="wp-block-team-directory-list__role"><?php echo esc_html( $team_directory_member_role ); ?></span>
					<?php endif; ?>
					<?php if ( $team_directory_start_year > 0 ) : ?>
						<span class="wp-block-team-directory-list__since">
							<?php
							/* translators: %d: start year. */
							echo esc_html( sprintf( __( 'Since %d', 'team-directory' ), $team_directory_start_year ) );
							?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="wp-block-team-directory-list__empty"><?php esc_html_e( 'No team members found.', 'team-directory' ); ?></p>
	<?php endif; ?>
</div>
