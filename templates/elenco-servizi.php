<?php
/** Disegno del blocco "starter-theme/elenco-servizi" (React): lo stesso HTML che stampava il blocco. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $starter_items e $is_preview arrivano da starter_poc_render_servizi() (starter-blocks.php).

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Elenco servizi: aggiungi almeno un servizio.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block servizi' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap servizi__grid">

		<nav class="servizi__nav" aria-label="<?php esc_attr_e( 'Servizi', 'slug-theme' ); ?>">
			<?php foreach ( $starter_items as $starter_item ) : ?>
				<?php
				$starter_id    = sanitize_title( (string) $starter_item['voci_id'] );
				$starter_label = '' !== trim( (string) $starter_item['voci_etichetta'] ) ? $starter_item['voci_etichetta'] : $starter_item['voci_titolo'];
				?>
				<?php if ( $starter_id ) : ?>
					<a href="#<?php echo esc_attr( $starter_id ); ?>" data-servizio="<?php echo esc_attr( $starter_id ); ?>">
						<span class="icon-circle"><?php echo starter_icon( $starter_item['voci_icona'] ? $starter_item['voci_icona'] : 'key', 18, 1.9 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="servizi__label-wrap"><span class="servizi__label"><?php echo esc_html( $starter_label ); ?></span></span>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>

		<div class="servizi__list">
			<?php foreach ( $starter_items as $starter_row => $starter_item ) : ?>
				<?php
				$starter_id    = sanitize_title( (string) $starter_item['voci_id'] );
				$starter_lines = preg_split( '/\r\n|\r|\n/', (string) $starter_item['voci_elenco'], -1, PREG_SPLIT_NO_EMPTY );
				$starter_cta   = ! empty( $starter_item['voci_cta'] ) ? $starter_item['voci_cta'] : null;
				?>
				<article class="servizi__item" <?php echo $starter_id ? 'id="' . esc_attr( $starter_id ) . '"' : ''; ?> data-reveal>
					<?php if ( $starter_item['voci_immagine'] ) : ?>
						<div class="servizi__photo">
							<?php echo wp_get_attachment_image( (int) $starter_item['voci_immagine'], 'large', false, array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>

					<div class="servizi__body">
						<div class="servizi__head">
							<span class="icon-circle"><?php echo starter_icon( $starter_item['voci_icona'] ? $starter_item['voci_icona'] : 'key', 22, 1.9 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php if ( $starter_item['voci_occhiello'] ) : ?>
								<span class="eyebrow"><?php echo esc_html( $starter_item['voci_occhiello'] ); ?></span>
							<?php endif; ?>
						</div>
						<h2 class="title-h3 text-balance servizi__title"><?php echo esc_html( $starter_item['voci_titolo'] ); ?></h2>
						<?php if ( $starter_item['voci_testo'] ) : ?>
							<p class="servizi__text"><?php echo esc_html( $starter_item['voci_testo'] ); ?></p>
						<?php endif; ?>

						<?php if ( $starter_lines ) : ?>
							<ul class="servizi__checks"<?php echo starter_live_attrs( array( 'src' => 'acflines', 'key' => 'voci_' . (int) $starter_row . '_voci_elenco' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php foreach ( $starter_lines as $starter_line ) : ?>
									<li><span class="checklist__mark"><?php echo starter_icon( 'check', 14, 2.5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo esc_html( trim( $starter_line ) ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( $starter_cta ) : ?>
							<a <?php echo starter_link_attrs( $starter_cta, starter_button_class( isset( $starter_item['voci_cta_stile'] ) ? $starter_item['voci_cta_stile'] : '', 'button button--outline servizi__button', 'servizi__button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<?php echo starter_flip_text( starter_link_title( $starter_cta ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
