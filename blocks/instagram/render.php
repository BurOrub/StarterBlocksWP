<?php
/**
 * Blocco "Instagram": titolo, nome account, griglia di foto quadrate statiche e pulsante al profilo.
 * Nessun collegamento automatico (niente cookie di Instagram né rallentamenti): le foto si aggiornano a mano.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_photos  = (array) starter_bf( 'foto' );
$starter_title   = starter_bf( 'titolo' );
$starter_account = starter_bf( 'account' );
$starter_link    = starter_bf( 'link' );

if ( ! $starter_photos ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Instagram: aggiungi le foto.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull insta-grid content-block' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="starter-container">
		<?php starter_block_head( $starter_title, $starter_account ? '@' . ltrim( $starter_account, '@' ) : '', starter_bf( 'occhiello' ) ); ?>

		<ul class="insta-grid__list">
			<?php foreach ( $starter_photos as $starter_id ) : ?>
				<li class="insta-grid__item">
					<?php
					$starter_img = wp_get_attachment_image( $starter_id, 'medium_large', false, array( 'loading' => 'lazy' ) );

					if ( is_array( $starter_link ) && ! empty( $starter_link['url'] ) ) {
						echo '<a href="' . esc_url( $starter_link['url'] ) . '" target="_blank" rel="noopener noreferrer">' . $starter_img . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo $starter_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php starter_block_center_link( $starter_link, 'button button--outline', starter_bf( 'link_stile' ) ); ?>
	</div>
</section>
