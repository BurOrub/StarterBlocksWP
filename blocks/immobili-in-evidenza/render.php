<?php
/** Disegno del blocco (React) "starter-theme/immobili-in-evidenza". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_posts = starter_imm_get_featured_immobili( 'in-evidenza-home', 3 );

if ( ! $starter_posts ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Immobili in evidenza: nessun immobile con Evidenza “In evidenza home”, il blocco non stampa nulla sul sito.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_link    = starter_bf( 'link' );
$starter_btn     = starter_button_class( starter_bf( 'link_stile' ), 'button button--outline' );
$starter_link_at = $starter_link ? starter_link_attrs( $starter_link, $starter_btn ) : 'href="' . esc_url( starter_imm_get_immobili_page_url() ) . '" class="' . esc_attr( $starter_btn ) . '"';
$starter_link_tx = starter_link_title( $starter_link, __( 'Scopri gli immobili', 'slug-theme' ) );

$starter_tags    = starter_section_heading_tags( (bool) $starter_eyebrow );
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block imm-immobili' ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap">
		<div class="section-head" data-reveal>
			<div class="section-head__text">
				<?php if ( $starter_eyebrow ) : ?>
					<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
				<?php endif; ?>
				<?php if ( $starter_title ) : ?>
					<<?php echo esc_html( $starter_tags['title'] ); ?> class="text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
				<?php endif; ?>
				<?php if ( $starter_text ) : ?>
					<p class="txt"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
			</div>
			<a <?php echo $starter_link_at; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo starter_flip_text( $starter_link_tx ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="immobile-grid">
			<?php
			foreach ( $starter_posts as $starter_post ) {
				setup_postdata( $GLOBALS['post'] = $starter_post ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				get_template_part( 'template-parts/content/card-immobile', null, array( 'heading' => $starter_tags['child'] ) );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
