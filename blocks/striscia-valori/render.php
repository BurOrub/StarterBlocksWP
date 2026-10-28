<?php
/**
 * Blocco "Striscia valori": riga di voci con icona sopra e titolo (4 su desktop, 2 su tablet, 1 su mobile).
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_items = (array) starter_bf( 'voci' );

if ( ! $starter_items ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Striscia valori: aggiungi le voci.', 'slug-theme' ) );
	return;
}

// Layout: i valori di prima (bordi a tutta larghezza, voci nel contenuto, 4 / 2 / 2 colonne) non cambiano l'HTML; solo una scelta diversa aggiunge classe e variabili.
$starter_full = 'full' === starter_bf( 'larghezza' ); // Full width: anche le voci si distribuiscono su tutto lo schermo; altrimenti restano nel contenuto e solo i bordi sono a tutta larghezza (com'era).
$starter_cols      = array(
	'--vs-cols'     => min( 6, max( 2, (int) starter_bf( 'colonne' ) ?: 4 ) ),
	'--vs-cols-tab' => min( 4, max( 1, (int) starter_bf( 'colonne_tablet' ) ?: 2 ) ),
	'--vs-cols-sma' => min( 2, max( 1, (int) starter_bf( 'colonne_mobile' ) ?: 2 ) ),
);
$starter_custom    = 4 !== $starter_cols['--vs-cols'] || 2 !== $starter_cols['--vs-cols-tab'] || 2 !== $starter_cols['--vs-cols-sma'];
$starter_style     = '';

if ( $starter_custom ) {
	foreach ( $starter_cols as $starter_var => $starter_num ) {
		$starter_style .= $starter_var . ':' . $starter_num . ';';
	}
}

$starter_class   = 'values-strip alignfull' . ( $starter_custom ? ' values-strip--custom' : '' ) . ( $starter_full ? ' values-strip--full' : '' );
$starter_wrapper = get_block_wrapper_attributes( array_filter( array( 'class' => $starter_class, 'style' => $starter_style ) ) );
?>
<div <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<ul class="values-strip__list<?php echo $starter_full ? '' : ' starter-container'; ?>">
		<?php foreach ( $starter_items as $starter_item ) : ?>
			<li class="values-strip__item">
				<?php echo starter_icon( $starter_item['icona'], 34, 1.3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="values-strip__title title-h6"><?php echo starter_inline( $starter_item['titolo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
