<?php
/**
 * Blocco "Progetto — testata": anteprima nell'editor di copertina, titolo e dati del progetto.
 * Sul sito non stampa niente: la testata la scrive già single-portfolio.php.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $is_preview ) ) {
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull project-preview' ) );
?>
<div <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	get_template_part(
		'template-parts/portfolio/project-head',
		null,
		array(
			'post_id' => get_the_ID(), // L'editor passa l'articolo aperto (post_id della richiesta di anteprima).
			'preview' => true,
		)
	);
	?>
</div>
