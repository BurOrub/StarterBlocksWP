<?php
/**
 * Blocco "Hero con foto e video": apertura di una pagina (es. Chi siamo). A sinistra occhiello (H1 piccolo, con la parola
 * chiave), titolo (H2 grande), testo, due pulsanti e una fila di piccole foto di clienti (max 6) con una frase; a destra
 * una foto e un video verticale, ognuno con la sua etichetta (parole chiave). Il video è facoltativo: senza, resta la
 * copertina.
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
$starter_clients = array_slice( array_values( (array) starter_bf( 'clienti' ) ), 0, 6 );
$starter_proof   = starter_bf( 'clienti_testo' );
$starter_photo   = (int) starter_bf( 'foto' );
$starter_tag1    = starter_bf( 'etichetta_foto' );
$starter_video   = starter_bf( 'video' );
$starter_poster  = (int) starter_bf( 'copertina_video' );
$starter_tag2    = starter_bf( 'etichetta_video' );
$starter_col1    = starter_bf( 'colore_etichetta_foto' ) ? starter_bf( 'colore_etichetta_foto' ) : 'cta';
$starter_col2    = starter_bf( 'colore_etichetta_video' ) ? starter_bf( 'colore_etichetta_video' ) : 'social';

if ( ! $starter_title && ! $starter_photo ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Hero con foto e video: compila i campi del blocco.', 'slug-theme' ) );
	return;
}

$starter_video_url = is_array( $starter_video ) ? ( $starter_video['url'] ?? '' ) : (string) $starter_video;
$starter_wrapper   = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block hero-media' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap hero-media__grid">

		<div class="hero-media__text" data-reveal>
			<div class="page-header__main hero-media__titles">
				<?php if ( empty( $is_preview ) && get_theme_mod( 'starter_breadcrumbs', false ) ) : ?>
					<?php starter_page_breadcrumb(); ?>
				<?php endif; ?>
				<?php starter_page_header_titles( $starter_eyebrow, $starter_title, true, true ); ?>
			</div>

			<?php if ( $starter_text ) : ?>
				<p class="hero-media__lead has-large-font-size"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>

			<?php if ( $starter_cta1 || $starter_cta2 ) : ?>
				<div class="hero-media__actions">
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

			<?php if ( $starter_clients || $starter_proof ) : ?>
				<div class="hero-media__proof">
					<?php if ( $starter_clients ) : ?>
						<ul class="hero-media__clients">
							<?php foreach ( $starter_clients as $starter_client ) : ?>
								<li><?php echo wp_get_attachment_image( (int) $starter_client, 'thumbnail', false, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $starter_proof ) : ?>
						<p class="hero-media__proof-text"><?php echo starter_inline( $starter_proof ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="hero-media__media" data-reveal>
			<?php if ( $starter_photo ) : ?>
				<figure class="hero-media__photo">
					<div class="hero-media__frame">
						<?php echo wp_get_attachment_image( $starter_photo, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 900px) 50vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<?php if ( $starter_tag1 ) : ?>
						<figcaption class="hero-media__tag hero-media__tag--<?php echo esc_attr( $starter_col1 ); ?>"><?php echo starter_inline( $starter_tag1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<?php if ( $starter_video_url || $starter_poster ) : ?>
				<figure class="hero-media__video">
					<div class="hero-media__frame">
						<?php if ( $starter_video_url ) : ?>
							<video autoplay muted loop playsinline disablepictureinpicture controlslist="nodownload nofullscreen noremoteplayback" preload="auto"<?php echo $starter_poster ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $starter_poster, 'large' ) ) . '"' : ''; ?>>
								<?php echo starter_video_sources( $starter_video_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- starter_video_sources() scappa gli URL. ?>
							</video>
						<?php else : ?>
							<?php echo wp_get_attachment_image( $starter_poster, 'large', false, array( 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</div>
					<?php if ( $starter_tag2 ) : ?>
						<figcaption class="hero-media__tag hero-media__tag--<?php echo esc_attr( $starter_col2 ); ?>"><?php echo starter_inline( $starter_tag2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>

	</div>
</section>
