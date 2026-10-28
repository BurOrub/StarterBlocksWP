<?php
/** Disegno del blocco (React) "starter-theme/intestazione-pagina". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_pos     = 'destra' === starter_bf( 'posizione_testo' ) ? 'destra' : 'sotto';

if ( ! $starter_title ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Intestazione pagina: compila il titolo.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block page-header' . ( 'destra' === $starter_pos ? ' page-header--lead-right' : '' ) ) );
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap page-header__grid">
		<div class="page-header__main" data-reveal>
			<?php if ( empty( $is_preview ) && starter_breadcrumbs_inline_on() ) : ?>
				<?php starter_page_breadcrumb(); ?>
			<?php endif; ?>
			<?php starter_page_header_titles( $starter_eyebrow, $starter_title, true, true ); ?>
		</div>
		<?php if ( $starter_text ) : ?>
			<p class="page-header__lead" data-reveal><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>
</section>
