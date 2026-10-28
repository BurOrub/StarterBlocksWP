<?php
/**
 * Blocco "Prodotti in evidenza": griglia di prodotti con la stessa card del negozio (woocommerce/content-product.php).
 * Serve WooCommerce. Quali prodotti: in evidenza (stellina), più venduti, più recenti, di una categoria, scelti a mano.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wc_get_products' ) ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Prodotti in evidenza: serve WooCommerce.', 'slug-theme' ) );
	return;
}

$starter_source = starter_bf( 'sorgente' ) ? starter_bf( 'sorgente' ) : 'evidenza';
$starter_limit  = starter_bf( 'numero' ) ? (int) starter_bf( 'numero' ) : 8;
$starter_query  = array(
	'status'     => 'publish',
	'visibility' => 'catalog',
	'limit'      => $starter_limit,
	'return'     => 'ids',
);

switch ( $starter_source ) {
	case 'venduti':
		$starter_query['orderby']  = 'meta_value_num';
		$starter_query['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$starter_query['order']    = 'DESC';
		break;
	case 'recenti':
		$starter_query['orderby'] = 'date';
		$starter_query['order']   = 'DESC';
		break;
	case 'categoria':
		$starter_term = get_term( (int) starter_bf( 'categoria' ), 'product_cat' );
		if ( $starter_term && ! is_wp_error( $starter_term ) ) {
			$starter_query['category'] = array( $starter_term->slug );
		}
		break;
	case 'scelti':
		$starter_query['include'] = array_map( 'intval', (array) starter_bf( 'prodotti' ) );
		$starter_query['orderby'] = 'post__in';
		break;
	default:
		$starter_query['featured'] = true;
}

$starter_ids = ( 'scelti' === $starter_source && empty( $starter_query['include'] ) ) ? array() : wc_get_products( $starter_query );

if ( ! $starter_ids ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Prodotti in evidenza: nessun prodotto trovato con questa scelta.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull featured-products content-block' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="starter-container">
		<?php starter_block_head( starter_bf( 'titolo' ), starter_bf( 'testo' ), starter_bf( 'occhiello' ) ); ?>
	</div>

	<div class="woocommerce starter-wide">
		<ul class="products featured-products__grid">
			<?php
			foreach ( $starter_ids as $starter_id ) {
				$GLOBALS['post'] = get_post( $starter_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				setup_postdata( $GLOBALS['post'] );
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>

	<div class="starter-container">
		<?php starter_block_center_link( starter_bf( 'link' ), 'button button--outline', starter_bf( 'link_stile' ) ); ?>
	</div>
</section>
