<?php
/**
 * Blocco "Slider": slider singolo o carosello di immagini. Funzionamento in JS/scripts.js (initSliders), stile in CSS/starter.css (".slider-block").
 * Senza JS resta una fila di immagini scorrevole a dito/rotella.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_slides = array_filter( array_map( 'intval', (array) starter_bf( 'slide' ) ) );

if ( ! $starter_slides ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Slider: aggiungi almeno un\'immagine.', 'slug-theme' ) );
	return;
}

$starter_single  = 'singolo' === starter_bf( 'modalita' );
$starter_full    = 'piena' === starter_bf( 'larghezza' );
$starter_ratio   = (string) starter_bf( 'proporzione' );
$starter_ratio   = in_array( $starter_ratio, array( '16-9', '3-2', '4-3', '1-1', '4-5', '21-9' ), true ) ? $starter_ratio : ( '' === $starter_ratio ? '16-9' : 'originale' );
$starter_arrows  = starter_bf( 'frecce' );
$starter_arrows  = in_array( $starter_arrows, array( 'nessuna', 'sopra', 'sotto' ), true ) ? $starter_arrows : 'immagine';
$starter_pager   = starter_bf( 'paginazione' );
$starter_pager   = in_array( $starter_pager, array( 'nessuna', 'puntini', 'contatore', 'barra' ), true ) ? $starter_pager : 'puntini';
$starter_gap     = null === starter_bf( 'gap' ) || '' === starter_bf( 'gap' ) ? 16 : max( 0, min( 100, (int) starter_bf( 'gap' ) ) );
$starter_peek    = starter_bf( 'anteprima' ) ? max( 0, min( 200, (int) ( starter_bf( 'anteprima_px' ) ?: 50 ) ) ) : 0;
$starter_cols    = array();

foreach ( array( 'desktop' => 3, 'tablet' => 2, 'mobile' => 1 ) as $starter_device => $starter_default ) {
	$starter_value                = (int) starter_bf( 'colonne_' . $starter_device );
	$starter_cols[ $starter_device ] = $starter_single ? 1 : max( 1, min( 6, $starter_value ?: $starter_default ) );
}

$starter_autoplay = starter_bf( 'autoplay' ) ? max( 1, (int) ( starter_bf( 'autoplay_secondi' ) ?: 5 ) ) * 1000 : 0;
$starter_loop     = null === starter_bf( 'ricomincia' ) || starter_bf( 'ricomincia' );
$starter_captions = (bool) starter_bf( 'didascalie' );

$starter_classes = 'alignfull content-block slider-block slider-block--' . ( $starter_single ? 'singolo' : 'carosello' )
	. ( $starter_full ? ' slider-block--piena' : '' )
	. ( 'nessuno' === starter_bf( 'angoli' ) ? ' slider-block--square' : '' )
	. ' slider-block--arrows-' . $starter_arrows;
$starter_style   = sprintf(
	'--sld-d:%d;--sld-t:%d;--sld-m:%d;--sld-gap:%dpx;--sld-peek:%dpx;--sld-ratio:%s;',
	$starter_cols['desktop'],
	$starter_cols['tablet'],
	$starter_cols['mobile'],
	$starter_gap,
	$starter_peek,
	'originale' === $starter_ratio ? 'auto' : str_replace( '-', ' / ', $starter_ratio )
);
$starter_wrapper = get_block_wrapper_attributes(
	array(
		'class'       => $starter_classes,
		'style'       => $starter_style,
		'data-slider' => '',
		'data-pager'  => $starter_pager,
		'data-loop'   => $starter_loop ? '1' : '0',
		'data-autoplay' => (string) $starter_autoplay,
	)
);
$starter_sizes   = '(max-width: 640px) ' . round( 100 / $starter_cols['mobile'] ) . 'vw, (max-width: 1100px) ' . round( 100 / $starter_cols['tablet'] ) . 'vw, ' . ( $starter_full ? round( 100 / $starter_cols['desktop'] ) . 'vw' : round( 1280 / $starter_cols['desktop'] ) . 'px' );
$starter_arrow_class   = starter_button_class( starter_bf( 'frecce_stile' ), 'immagine' === $starter_arrows ? 'button' : 'button button--outline' ) . ' slider-block__arrow';
$starter_arrow_buttons = function () use ( $starter_arrow_class ) {
	return '<span class="slider-block__arrows">'
		. '<button type="button" class="' . esc_attr( $starter_arrow_class ) . '" data-slider-prev aria-label="' . esc_attr__( 'Precedente', 'slug-theme' ) . '">' . starter_icon( 'chevron-left', 20, 2 ) . '</button>'
		. '<button type="button" class="' . esc_attr( $starter_arrow_class ) . '" data-slider-next aria-label="' . esc_attr__( 'Successiva', 'slug-theme' ) . '">' . starter_icon( 'chevron-right', 20, 2 ) . '</button>'
		. '</span>';
};
$starter_controls = function ( $with_arrows ) use ( $starter_pager, $starter_arrow_buttons ) {
	$pager = '';

	if ( 'puntini' === $starter_pager ) {
		$pager = '<span class="slider-block__dots" data-slider-dots></span>';
	} elseif ( 'contatore' === $starter_pager ) {
		$pager = '<span class="slider-block__counter" data-slider-counter aria-live="polite"></span>';
	} elseif ( 'barra' === $starter_pager ) {
		$pager = '<span class="slider-block__progress" aria-hidden="true"><span data-slider-progress></span></span>';
	}

	return '<div class="slider-block__bar' . ( $with_arrows ? ' slider-block__bar--arrows' : '' ) . '">' . $pager . ( $with_arrows ? $starter_arrow_buttons() : '' ) . '</div>';
};
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="slider-block__wrap<?php echo $starter_full ? '' : ' block-wrap'; ?>">

		<?php if ( 'sopra' === $starter_arrows ) : ?>
			<div class="slider-block__bar slider-block__bar--top"><?php echo $starter_arrow_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>

		<div class="slider-block__viewport">
			<div class="slider-block__track" data-slider-track tabindex="0" role="group" aria-roledescription="<?php esc_attr_e( 'carosello', 'slug-theme' ); ?>">
				<?php foreach ( $starter_slides as $starter_image ) : ?>
					<?php $starter_caption = wp_get_attachment_caption( $starter_image ); ?>
					<figure class="slider-block__slide" role="group" aria-roledescription="slide">
						<div class="slider-block__frame">
							<?php if ( str_starts_with( (string) get_post_mime_type( $starter_image ), 'video/' ) ) : ?>
								<video autoplay muted loop playsinline disablepictureinpicture controlslist="nodownload nofullscreen noremoteplayback" preload="metadata">
									<?php echo starter_video_sources( (string) wp_get_attachment_url( $starter_image ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- starter_video_sources() scappa gli URL. ?>
								</video>
							<?php else : ?>
								<?php echo wp_get_attachment_image( $starter_image, 'large', false, array( 'loading' => 'lazy', 'sizes' => $starter_sizes, 'draggable' => 'false' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
						</div>
						<?php if ( $starter_captions && $starter_caption ) : ?>
							<figcaption class="slider-block__caption"><?php echo starter_inline( $starter_caption ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>

			<?php if ( 'immagine' === $starter_arrows ) : ?>
				<?php echo $starter_arrow_buttons(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>

		<?php if ( 'nessuna' !== $starter_pager || 'sotto' === $starter_arrows ) : ?>
			<?php echo $starter_controls( 'sotto' === $starter_arrows ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

	</div>
</section>
