<?php
/** Disegno del blocco (React) "starter-theme/sezione-voci". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_layout  = starter_bf( 'layout' ) ? starter_bf( 'layout' ) : 'numerato';
$starter_bg      = starter_bf( 'sfondo' ) ? starter_bf( 'sfondo' ) : 'accent';
$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_link    = starter_bf( 'link' );
$starter_items   = starter_bf( 'voci' );
$starter_cta_tx  = starter_bf( 'cta_testo' );
$starter_cta_ln  = starter_bf( 'cta_link' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Sezione voci: aggiungi almeno una voce.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_is_card  = in_array( $starter_layout, array( 'card', 'icone', 'passi' ), true );
$starter_is_icons = 'icone' === $starter_layout;
$starter_tags     = starter_section_heading_tags( (bool) $starter_eyebrow, 'h1' === starter_bf( 'livello' ) ? 1 : 2 );
$starter_classes  = 'alignfull content-block list-block list-block--' . $starter_layout . ( $starter_is_card ? ' panel panel--' . $starter_bg : '' );
$starter_wrapper  = get_block_wrapper_attributes( array( 'class' => $starter_classes ) );

$starter_link_class = starter_button_class( starter_bf( 'link_stile' ), $starter_is_card && 'accent' === $starter_bg ? 'button button--on-accent' : 'button button--outline' );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

<?php if ( $starter_is_card ) : ?>
	<div class="panel__box">
		<div class="panel__inner list-block__stack">

			<div class="section-head" data-reveal>
				<div class="section-head__text">
					<?php if ( $starter_eyebrow ) : ?>
						<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
					<?php endif; ?>
					<?php if ( $starter_title ) : ?>
						<<?php echo esc_html( $starter_tags['title'] ); ?> class="text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
					<?php endif; ?>
				</div>
				<?php if ( $starter_link ) : ?>
					<a <?php echo starter_link_attrs( $starter_link, $starter_link_class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php echo starter_flip_text( starter_link_title( $starter_link ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( 'passi' === $starter_layout ) : ?>
			<ol class="list-block__steps">
				<?php foreach ( $starter_items as $starter_i => $starter_item ) : ?>
					<li data-reveal>
						<span class="list-block__step-num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $starter_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<<?php echo esc_html( $starter_tags['child'] ); ?> class="list-block__card-title"><?php echo starter_inline( $starter_item['titolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['child'] ); ?>>
						<?php if ( $starter_item['testo'] ) : ?>
							<p><?php echo starter_inline( $starter_item['testo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php else : ?>
			<div class="list-block__cards">
				<?php foreach ( $starter_items as $starter_item ) : ?>
					<?php $starter_item_link = ! empty( $starter_item['link'] ) ? $starter_item['link'] : null; ?>
					<<?php echo $starter_item_link ? 'a ' . starter_link_attrs( $starter_item_link, 'list-block__card list-block__card--link' ) : 'div class="list-block__card"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-reveal>
						<span class="icon-circle"><?php echo starter_icon( $starter_item['icona'] ? $starter_item['icona'] : 'key', 22, 1.9 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<<?php echo esc_html( $starter_tags['child'] ); ?> class="list-block__card-title"><?php echo starter_inline( $starter_item['titolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['child'] ); ?>>
						<?php if ( $starter_item['testo'] ) : ?>
							<p><?php echo starter_inline( $starter_item['testo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
						<?php if ( $starter_item_link && ! $starter_is_icons ) : ?>
							<span class="list-block__card-arrow"><?php echo starter_icon( 'arrow-up-right', 18, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
					</<?php echo $starter_item_link ? 'a' : 'div'; ?>>
				<?php endforeach; ?>

				<?php if ( $starter_cta_tx && $starter_cta_ln ) : ?>
					<a <?php echo starter_link_attrs( $starter_cta_ln, 'list-block__cta' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-reveal>
						<span class="list-block__cta-text"><?php echo starter_inline( $starter_cta_tx ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="list-block__cta-row">
							<?php echo esc_html( starter_link_title( $starter_cta_ln, __( 'Contattaci', 'slug-theme' ) ) ); ?>
							<span class="list-block__cta-arrow"><?php echo starter_icon( 'arrow-up-right', 18, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</span>
					</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div>
	</div>

<?php else : ?>
	<div class="block-wrap list-block__split">

		<div class="list-block__intro" data-reveal>
			<?php if ( $starter_eyebrow ) : ?>
				<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
			<?php endif; ?>
			<?php if ( $starter_title ) : ?>
				<<?php echo esc_html( $starter_tags['title'] ); ?> class="text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
			<?php endif; ?>
			<?php if ( $starter_text ) : ?>
				<p class="split__lead"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			<?php endif; ?>
			<?php if ( $starter_link ) : ?>
				<a <?php echo starter_link_attrs( $starter_link, starter_button_class( starter_bf( 'link_stile' ), 'button button--outline split__button', 'split__button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo starter_flip_text( starter_link_title( $starter_link ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			<?php endif; ?>
		</div>

		<ol class="list-block__list">
			<?php foreach ( $starter_items as $starter_i => $starter_item ) : ?>
				<li data-reveal>
					<span class="list-block__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $starter_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div>
						<<?php echo esc_html( $starter_tags['child'] ); ?> class="list-block__item-title"><?php echo starter_inline( $starter_item['titolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['child'] ); ?>>
						<p><?php echo starter_inline( $starter_item['testo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
<?php endif; ?>

</section>
