<?php
/**
 * Blocco "Progetto — media": immagini e video in 1, 2 o 3 colonne. Ogni elemento è un'immagine o un video (muto, in ciclo).
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_items = starter_bf( 'elementi' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Progetto — media: aggiungi almeno un\'immagine o un video.', 'slug-theme' ) );
	return;
}

$starter_cols    = in_array( (string) starter_bf( 'colonne' ), array( '1', '2', '3' ), true ) ? (int) starter_bf( 'colonne' ) : 1;
$starter_ratio   = starter_bf( 'proporzione' );
$starter_ratio   = in_array( $starter_ratio, array( '16-9', '4-3', '1-1', '4-5', '3-4' ), true ) ? $starter_ratio : 'originale';
$starter_sizes   = 1 === $starter_cols ? '(max-width: 1280px) 100vw, 1280px' : '(max-width: 640px) 100vw, ' . round( 100 / $starter_cols ) . 'vw';
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block project-media' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap">
		<div class="project-media__grid project-media__grid--cols-<?php echo (int) $starter_cols; ?> project-media__grid--ratio-<?php echo esc_attr( $starter_ratio ); ?>">
			<?php foreach ( $starter_items as $starter_item ) : ?>
				<?php
				$starter_is_video = 'video' === $starter_item['elementi_tipo'];
				$starter_video    = is_array( $starter_item['elementi_video'] ) && ! empty( $starter_item['elementi_video']['url'] ) ? $starter_item['elementi_video']['url'] : '';
				$starter_image    = (int) $starter_item['elementi_immagine'];
				$starter_poster   = (int) $starter_item['elementi_copertina'];
				$starter_caption  = (string) $starter_item['elementi_didascalia'];

				if ( $starter_is_video ? ! ( $starter_video || $starter_poster ) : ! $starter_image ) {
					continue;
				}
				?>
				<figure class="project-media__item" data-reveal>
					<div class="project-media__frame">
						<?php if ( $starter_is_video && $starter_video ) : ?>
							<video autoplay muted loop playsinline disablepictureinpicture controlslist="nodownload nofullscreen noremoteplayback" preload="metadata"<?php echo $starter_poster ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $starter_poster, 'large' ) ) . '"' : ''; ?>>
								<?php echo starter_video_sources( $starter_video ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- starter_video_sources() scappa gli URL. ?>
							</video>
						<?php elseif ( $starter_is_video ) : ?>
							<?php echo wp_get_attachment_image( $starter_poster, 'large', false, array( 'loading' => 'lazy', 'sizes' => $starter_sizes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<?php echo wp_get_attachment_image( $starter_image, 1 === $starter_cols ? 'full' : 'large', false, array( 'loading' => 'lazy', 'sizes' => $starter_sizes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</div>
					<?php if ( $starter_caption ) : ?>
						<figcaption class="project-media__caption"><?php echo starter_inline( $starter_caption ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
