<?php
/** Disegno del blocco (React) "starter-theme/mappa". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_address = trim( (string) starter_bf( 'indirizzo' ) );
$starter_button  = trim( (string) starter_bf( 'pulsante' ) );

if ( '' === $starter_address ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Mappa: inserisci l\'indirizzo.', 'slug-theme' ) . '</p>';
	}
	return;
}

if ( '' === $starter_button ) {
	$starter_button = __( 'Indicazioni stradali', 'slug-theme' );
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block map-block' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="map-block__box" data-reveal>
		<iframe src="<?php echo esc_url( 'https://maps.google.com/maps?q=' . rawurlencode( $starter_address ) . '&output=embed' ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: indirizzo. */ __( 'Mappa: %s', 'slug-theme' ), $starter_address ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
		<a class="button map-block__button" href="<?php echo esc_url( 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $starter_address ) ); ?>" target="_blank" rel="noopener">
			<?php echo starter_flip_text( $starter_button ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo starter_icon( 'arrow-up-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</section>
