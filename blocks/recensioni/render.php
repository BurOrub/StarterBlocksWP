<?php
/** Disegno del blocco (React) "starter-theme/recensioni". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_link    = starter_bf( 'link' );
$starter_items   = starter_bf( 'voci' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Recensioni: aggiungi almeno una recensione.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_track_id = 'reviews-' . ( isset( $block['id'] ) ? sanitize_html_class( $block['id'] ) : wp_unique_id() );
$starter_tags     = starter_section_heading_tags( (bool) $starter_eyebrow );
$starter_wrapper  = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block reviews' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap">
		<div class="section-head" data-reveal>
			<div class="section-head__text">
				<?php if ( $starter_eyebrow ) : ?>
					<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
				<?php endif; ?>
				<?php if ( $starter_title ) : ?>
					<<?php echo esc_html( $starter_tags['title'] ); ?>><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
				<?php endif; ?>
			</div>

			<div class="reviews__controls">
				<?php if ( $starter_link ) : ?>
					<a <?php echo starter_link_attrs( $starter_link, 'reviews__all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( starter_link_title( $starter_link ) ); ?></a>
				<?php endif; ?>
				<button type="button" class="round-button" data-scroll-for="<?php echo esc_attr( $starter_track_id ); ?>" data-scroll-dir="-1" aria-label="<?php esc_attr_e( 'Recensione precedente', 'slug-theme' ); ?>"><?php echo starter_icon( 'chevron-left', 20, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<button type="button" class="round-button" data-scroll-for="<?php echo esc_attr( $starter_track_id ); ?>" data-scroll-dir="1" aria-label="<?php esc_attr_e( 'Recensione successiva', 'slug-theme' ); ?>"><?php echo starter_icon( 'chevron-right', 20, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
		</div>
	</div>

	<div class="reviews__track" id="<?php echo esc_attr( $starter_track_id ); ?>" data-drag-scroll tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Recensioni dei clienti', 'slug-theme' ); ?>">
		<?php foreach ( $starter_items as $starter_item ) : ?>
			<figure class="reviews__card">
				<span class="reviews__stars" role="img" aria-label="<?php esc_attr_e( '5 stelle su 5', 'slug-theme' ); ?>">
					<?php for ( $starter_s = 0; $starter_s < 5; $starter_s++ ) : ?>
						<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.2 1.3-6.6L2.5 9.3l6.6-.8z"/></svg>
					<?php endfor; ?>
				</span>
				<blockquote>“<?php echo starter_inline( $starter_item['testo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>”</blockquote>
				<figcaption>
					<span class="reviews__initial" aria-hidden="true"><?php echo esc_html( mb_strtoupper( mb_substr( trim( $starter_item['nome'] ), 0, 1 ) ) ); ?></span>
					<span class="reviews__who">
						<span class="reviews__name"><?php echo esc_html( $starter_item['nome'] ); ?></span>
						<?php if ( ! empty( $starter_item['data'] ) ) : ?>
							<span class="reviews__when"><?php echo esc_html( $starter_item['data'] ); ?></span>
						<?php endif; ?>
					</span>
				</figcaption>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
