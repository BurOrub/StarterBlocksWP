<?php
/**
 * Blocco "Hero con slider": foto a tutta larghezza che cambiano in dissolvenza (CSS + poche righe in
 * JS/scripts.js, nessuna libreria), velo scuro, titolo H1 con parte in corsivo (<em>), testo e pulsante.
 * La prima foto è precaricata (fetchpriority high), le altre in lazy. Colori e font dalle opzioni del tema.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_images = (array) starter_bf( 'immagini' );
$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title  = starter_bf( 'titolo' );
$starter_text   = starter_bf( 'testo' );
$starter_link   = starter_bf( 'link' );
$starter_link2  = starter_bf( 'link_2' );
$starter_delay  = max( 3, (int) ( starter_bf( 'durata' ) ? starter_bf( 'durata' ) : 6 ) );

if ( ! $starter_images && ! $starter_title ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Hero con slider: aggiungi le foto e il titolo.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull hero-slider hero-section' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $starter_images ) : ?>
		<div class="hero-slider__slides" aria-hidden="true" data-hero-slider="<?php echo esc_attr( $starter_delay * 1000 ); ?>">
			<?php foreach ( array_values( $starter_images ) as $starter_i => $starter_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$starter_id,
					'starter-hero',
					false,
					array(
						'class'         => 0 === $starter_i ? 'is-active' : '',
						'sizes'         => '100vw',
						'loading'       => 0 === $starter_i ? 'eager' : 'lazy',
						'fetchpriority' => 0 === $starter_i ? 'high' : 'low',
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="hero-slider__content">
		<?php if ( $starter_title ) : ?>
			<?php if ( $starter_eyebrow ) : ?>
				<?php $starter_eyebrow_tag = starter_heading_tag( 'occhiello', 'h1' ); ?>
				<<?php echo esc_html( $starter_eyebrow_tag ); ?> class="hero-slider__eyebrow eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_eyebrow_tag ); ?>>
			<?php endif; ?>
			<?php $starter_title_tag = starter_heading_tag( 'titolo', $starter_eyebrow ? 'h2' : 'h1' ); ?>
			<<?php echo esc_html( $starter_title_tag ); ?> class="hero-slider__title title-display"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $starter_text ) : ?>
			<div class="hero-slider__lead has-large-font-size"><?php echo wp_kses_post( wpautop( $starter_text ) ); ?></div>
		<?php endif; ?>

		<?php
		$starter_has_link  = is_array( $starter_link ) && ! empty( $starter_link['url'] );
		$starter_has_link2 = is_array( $starter_link2 ) && ! empty( $starter_link2['url'] );
		?>
		<?php if ( $starter_has_link || $starter_has_link2 ) : ?>
			<div class="hero-slider__actions">
				<?php if ( $starter_has_link ) : ?>
					<a <?php echo starter_link_attrs( $starter_link, starter_button_class( starter_bf( 'link_stile' ), 'button button--light' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo starter_flip_text( starter_link_title( $starter_link ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
				<?php if ( $starter_has_link2 ) : ?>
					<a <?php echo starter_link_attrs( $starter_link2, starter_button_class( starter_bf( 'link_2_stile' ), 'button button--outline' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo starter_flip_text( starter_link_title( $starter_link2 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
