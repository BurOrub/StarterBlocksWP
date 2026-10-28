<?php
/**
 * Blocco "Progetto — testo": occhiello (es. "Contesto") e titolo a sinistra, paragrafi a destra (oppure tutto in una colonna).
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = (string) starter_bf( 'occhiello' );
$starter_title   = (string) starter_bf( 'titolo' );
$starter_text    = trim( (string) starter_bf( 'testo' ) );
$starter_layout  = 'centrato' === starter_bf( 'disposizione' ) ? 'centrato' : 'affiancato';

if ( ! $starter_eyebrow && ! $starter_title && ! $starter_text ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Progetto — testo: compila occhiello, titolo o testo.', 'slug-theme' ) );
	return;
}

$starter_tags    = starter_section_heading_tags( (bool) $starter_eyebrow );
$starter_paras   = $starter_text ? preg_split( '/\R\s*\R/', $starter_text ) : array();
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block project-text project-text--' . $starter_layout ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap project-text__grid" data-reveal>
		<?php if ( $starter_eyebrow || $starter_title ) : ?>
			<div class="project-text__head">
				<?php if ( $starter_eyebrow ) : ?>
					<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
				<?php endif; ?>
				<?php if ( $starter_title ) : ?>
					<<?php echo esc_html( $starter_tags['title'] ); ?> class="project-text__title title-h3 text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $starter_paras ) : ?>
			<div class="project-text__body txt">
				<?php foreach ( $starter_paras as $starter_para ) : ?>
					<p><?php echo starter_inline( nl2br( trim( $starter_para ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
