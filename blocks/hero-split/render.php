<?php
/**
 * Blocco "Hero testo + foto": prima sezione della pagina con occhiello, titolo grande (H1), testo, fino a due
 * pulsanti e una foto a lato. Ripreso dalla hero della home di Di Guida Real Estate, senza la parte degli immobili.
 * Se la pagina parte con questo blocco l'H1 è il suo titolo.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_cta1    = starter_bf( 'cta_primaria' );
$starter_cta2    = starter_bf( 'cta_secondaria' );
$starter_image   = starter_bf( 'foto' );
$starter_side    = starter_bf( 'lato_foto' ) ? starter_bf( 'lato_foto' ) : 'destra';

if ( ! $starter_title && ! $starter_text && ! $starter_image ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Hero testo + foto: compila i campi del blocco.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block hero-split hero-split--' . esc_attr( $starter_side ) ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap hero-split__grid">

		<div class="hero-split__text" data-reveal>
			<?php if ( $starter_eyebrow ) : ?>
				<?php $starter_eyebrow_tag = starter_heading_tag( 'occhiello', 'p' ); ?>
				<<?php echo esc_html( $starter_eyebrow_tag ); ?> class="hero-split__badge eyebrow"><span class="hero-split__dot" aria-hidden="true"></span><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_eyebrow_tag ); ?>>
			<?php endif; ?>

			<?php if ( $starter_title ) : ?>
				<?php $starter_title_tag = starter_heading_tag( 'titolo', 'h1' ); ?>
				<<?php echo esc_html( $starter_title_tag ); ?> class="hero-split__title title-display text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_title_tag ); ?>>
			<?php endif; ?>

			<?php if ( $starter_text ) : ?>
				<p class="hero-split__lead has-large-font-size"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>

			<?php if ( $starter_cta1 || $starter_cta2 ) : ?>
				<div class="hero-split__actions">
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

		<?php if ( $starter_image ) : ?>
			<div class="hero-split__media" data-reveal>
				<?php
				echo wp_get_attachment_image(
					(int) $starter_image,
					'large',
					false,
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '(max-width: 900px) 100vw, 50vw',
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		<?php endif; ?>

	</div>
</section>
