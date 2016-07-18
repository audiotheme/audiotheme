<?php
/**
 * View to modules on the dashboard screen.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */
?>

<div class="audiotheme-dashboard-lead">
	<p>
		<?php _e( 'Gigs, Discography, and Videos are the backbone of AudioTheme. Explore each feature below or use the menu options to the left to get started.', 'audiotheme' ); ?>
	</p>
</div>

<div class="audiotheme-module-cards">

	<?php foreach ( $modules as $module ) :
		$classes   = array( 'audiotheme-module-card', 'audiotheme-module-card--' . $module->id );
		$classes[] = $module->is_active() ? 'is-active' : 'is-inactive';
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-module-id="<?php echo esc_attr( $module->id ); ?>">

			<div class="audiotheme-module-card-details">
				<h2 class="audiotheme-module-card-name"><?php echo esc_html( $module->name ); ?></h2>
				<div class="audiotheme-module-card-description">
					<?php echo wpautop( esc_html( $module->description ) ); ?>
				</div>
				<div class="audiotheme-module-card-overview">
					<?php if ( method_exists( $module, 'display_overview' ) ) : ?>
						<?php $module->display_overview(); ?>
					<?php else : ?>
						<?php do_action( 'audiotheme_module_card_overview', $module->id ); ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="audiotheme-module-card-actions">
				<div class="audiotheme-module-card-actions-primary">
					<?php if ( method_exists( $module, 'display_primary_button' ) ) : ?>
						<?php $module->display_primary_button(); ?>
					<?php else : ?>
						<?php do_action( 'audiotheme_module_card_primary_button', $module->id ); ?>
					<?php endif; ?>
				</div>

				<div class="audiotheme-module-card-actions-secondary">
					<a href=""><?php esc_html_e( 'Details', 'audiotheme' ); ?></a>
				</div>
			</div>

		</div>
	<?php endforeach; ?>

</div>
