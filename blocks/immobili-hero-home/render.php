<?php
/** Disegno del blocco (React) "starter-theme/immobili-hero-home". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_cta1    = starter_bf( 'cta_primaria' );
$starter_cta2    = starter_bf( 'cta_secondaria' );

$starter_hero      = starter_imm_get_hero();
$starter_hero_post = $starter_hero['post'];
$starter_image_id  = $starter_hero['image_id'];
$starter_image_alt = ( $starter_hero_post && $starter_image_id ) ? starter_imm_immobile_image_alt( $starter_hero_post->ID, $starter_image_id ) : '';

// SEO
$starter_badge_tag = starter_heading_tag( 'occhiello', 'h1' );
$starter_title_tag = starter_heading_tag( 'titolo', $starter_eyebrow ? 'h2' : 'h1' );

if ( ! $starter_title && ! $starter_text && ! $starter_image_id ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Hero home: compila i campi del blocco.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block imm-hero' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap imm-hero__grid">

		<div class="imm-hero__text" data-reveal>
			<?php if ( $starter_eyebrow ) : ?>
				<<?php echo esc_html( $starter_badge_tag ); ?> class="imm-hero__badge"><span class="imm-hero__dot" aria-hidden="true"></span><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_badge_tag ); ?>>
			<?php endif; ?>

			<?php if ( $starter_title ) : ?>
				<<?php echo esc_html( $starter_title_tag ); ?> class="imm-hero__title text-balance has-xx-large-font-size"><?php echo starter_kses( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_title_tag ); ?>>
			<?php endif; ?>

			<?php if ( $starter_text ) : ?>
				<p class="imm-hero__lead"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>

			<?php if ( $starter_cta1 || $starter_cta2 ) : ?>
				<div class="imm-hero__actions">
					<?php if ( $starter_cta1 ) : ?>
						<a <?php echo starter_link_attrs( $starter_cta1, starter_button_class( starter_bf( 'cta_primaria_stile' ), 'button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php echo starter_flip_text( starter_link_title( $starter_cta1 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<?php if ( $starter_cta2 ) : ?>
						<a <?php echo starter_link_attrs( $starter_cta2, starter_button_class( starter_bf( 'cta_secondaria_stile' ), 'button button--outline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo starter_flip_text( starter_link_title( $starter_cta2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="imm-hero__media" data-reveal>
			<?php if ( $starter_image_id ) : ?>
				<div class="imm-hero__photo">
					<?php if ( $starter_hero_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $starter_hero_post ) ); ?>" tabindex="-1">
					<?php endif; ?>
					<?php
					echo wp_get_attachment_image(
						$starter_image_id,
						'large',
						false,
						array(
							'alt'           => $starter_image_alt,
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(max-width: 1023px) 100vw, 50vw',
						)
					);
					?>
					<?php if ( $starter_hero_post ) : ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $starter_hero_post ) : ?>
				<?php
				$starter_hero_zone  = starter_imm_get_zone_label( $starter_hero_post->ID );
				$starter_hero_thumb = get_post_thumbnail_id( $starter_hero_post );
				?>
				<a class="imm-hero__card" href="<?php echo esc_url( get_permalink( $starter_hero_post ) ); ?>">
					<?php
					if ( $starter_hero_thumb ) {
						echo wp_get_attachment_image( $starter_hero_thumb, 'starter-hero-thumb', false, array( 'alt' => '' ) );
					}
					?>
					<span class="imm-hero__card-body">
						<span class="imm-hero__card-kicker">
							<?php
							echo esc_html(
								$starter_hero_zone
									/* translators: %s: zona dell'immobile. */
									? sprintf( __( 'In evidenza · %s', 'slug-theme' ), $starter_hero_zone )
									: __( 'In evidenza', 'slug-theme' )
							);
							?>
						</span>
						<span class="imm-hero__card-title"><?php echo esc_html( get_the_title( $starter_hero_post ) ); ?></span>
						<span class="imm-hero__card-price"><?php echo esc_html( starter_imm_get_price_label( $starter_hero_post->ID ) ); ?></span>
					</span>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>
