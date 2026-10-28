<?php
/**
 * Blocco "Progetto — crediti": titolo e coppie ruolo / nome dei collaboratori.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_items = starter_bf( 'voci' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Progetto — crediti: aggiungi almeno un credito.', 'slug-theme' ) );
	return;
}

$starter_title   = (string) starter_bf( 'titolo' );
$starter_tags    = starter_section_heading_tags( false );
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block project-credits' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap project-credits__grid" data-reveal>
		<?php if ( $starter_title ) : ?>
			<<?php echo esc_html( $starter_tags['title'] ); ?> class="project-credits__title eyebrow"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
		<?php endif; ?>
		<dl class="project-credits__list">
			<?php foreach ( $starter_items as $starter_item ) : ?>
				<div class="project-credits__row">
					<dt><?php echo starter_inline( $starter_item['voci_ruolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dt>
					<dd>
						<?php if ( ! empty( $starter_item['voci_link'] ) ) : // Il sito del collaboratore (es. il fotografo). ?>
							<a href="<?php echo esc_url( $starter_item['voci_link'] ); ?>" target="_blank" rel="noopener"><?php echo starter_inline( $starter_item['voci_nome'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php else : ?>
							<?php echo starter_inline( $starter_item['voci_nome'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
