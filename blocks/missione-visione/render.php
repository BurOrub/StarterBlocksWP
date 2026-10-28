<?php
/** Disegno del blocco (React) "starter-theme/missione-visione". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_items = starter_bf( 'voci' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Due riquadri: aggiungi almeno un riquadro.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block mission-vision panel panel--muted' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="panel__box">
		<div class="panel__inner mission-vision__grid">
			<?php foreach ( $starter_items as $starter_i => $starter_item ) : ?>
				<div class="mission-vision__card<?php echo 1 === $starter_i ? ' mission-vision__card--accent' : ''; ?>" data-reveal>
					<?php if ( $starter_item['occhiello'] ) : ?>
						<?php $starter_eyebrow_tag = starter_pick_tag( $starter_item['occhiello_tag'] ?? '', 'h2' ); ?>
						<<?php echo esc_html( $starter_eyebrow_tag ); ?> class="eyebrow"><?php echo starter_inline( $starter_item['occhiello'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_eyebrow_tag ); ?>>
					<?php endif; ?>
					<?php if ( $starter_item['titolo'] ) : ?>
						<?php $starter_title_tag = starter_pick_tag( $starter_item['titolo_tag'] ?? '', 'h3' ); ?>
						<<?php echo esc_html( $starter_title_tag ); ?> class="mission-vision__title text-balance"><?php echo starter_inline( $starter_item['titolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $starter_item['testo'] ) : ?>
						<p class="mission-vision__text"><?php echo starter_inline( $starter_item['testo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
