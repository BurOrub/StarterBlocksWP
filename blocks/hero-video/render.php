<?php
/**
 * Blocco "Hero con video": come la hero con slider ma con un video a tutta larghezza (muto, in ciclo, parte da solo)
 * al posto delle foto. Velo scuro, titolo H1 con parte in corsivo (<em>), testo e pulsante. La copertina è l'immagine
 * mostrata finché il video carica (e a chi ha attivato "riduci animazioni"). Colori e font dalle opzioni del tema.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_video  = starter_bf( 'video' );
$starter_poster = (int) starter_bf( 'copertina' );
$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title  = starter_bf( 'titolo' );
$starter_tags   = starter_section_heading_tags( (bool) $starter_eyebrow );
$starter_text   = starter_bf( 'testo' );
$starter_link   = starter_bf( 'link' );
$starter_link2  = starter_bf( 'link_2' );

$starter_video_url = is_array( $starter_video ) ? ( $starter_video['url'] ?? '' ) : (string) $starter_video;

if ( ! $starter_video_url && ! $starter_poster && ! $starter_title ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Hero con video: aggiungi il video e il titolo.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull hero-slider hero-video hero-section' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="hero-video__media" aria-hidden="true">
		<?php if ( $starter_video_url ) : ?>
			<video autoplay muted loop playsinline disablepictureinpicture controlslist="nodownload nofullscreen noremoteplayback" preload="auto"<?php echo $starter_poster ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $starter_poster, 'starter-hero' ) ) . '"' : ''; ?>>
				<?php echo starter_video_sources( $starter_video_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- starter_video_sources() scappa gli URL. ?>
			</video>
		<?php elseif ( $starter_poster ) : ?>
			<?php echo wp_get_attachment_image( $starter_poster, 'starter-hero', false, array( 'sizes' => '100vw', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>

	<div class="hero-slider__content">
		<?php if ( $starter_title ) : ?>
			<?php if ( $starter_eyebrow ) : ?>
				<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="hero-slider__eyebrow eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
			<?php endif; ?>
			<<?php echo esc_html( $starter_tags['title'] ); ?> class="hero-slider__title title-display"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
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
