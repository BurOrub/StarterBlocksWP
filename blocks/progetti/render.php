<?php
/**
 * Blocco "Elenco progetti": griglia dei progetti del Portfolio con schede per categoria e paginazione.
 * Si mette in una pagina normale (es. "Portfolio"): titolo, testi e altri blocchi sono liberi. Filtro e pagine usano
 * ?categoria=slug e ?pagina=N sulla stessa pagina, quindi funzionano senza JavaScript.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! post_type_exists( 'portfolio' ) ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Elenco progetti: il Portfolio è spento (Opzioni → Portfolio).', 'slug-theme' ) );
	return;
}

// Interruttori: se il campo non è mai stato salvato valgono "acceso".
$starter_flag = static function ( $name ) {
	$value = starter_bf( $name );

	return null === $value || '' === $value ? true : (bool) $value;
};

// Visualizzazione e griglia (scheda Layout). I blocchi salvati prima valgono "classica", 3 colonne, 20px.
$starter_layout = in_array( (string) starter_bf( 'visualizzazione' ), array( 'classica', 'masonry', 'blocchi', 'sfalsata' ), true ) ? (string) starter_bf( 'visualizzazione' ) : 'classica';
$starter_px     = static function ( $name, $max = 120 ) {
	$value = starter_bf( $name );

	return null === $value || '' === $value ? 20 : max( 0, min( $max, (int) $value ) );
};
$starter_grid   = array(
	'cols'     => in_array( (string) starter_bf( 'colonne' ), array( '2', '3', '4', '5' ), true ) ? (int) starter_bf( 'colonne' ) : 3,
	'cols_t'   => in_array( (string) starter_bf( 'colonne_tablet' ), array( '1', '2', '3' ), true ) ? (int) starter_bf( 'colonne_tablet' ) : 2,
	'cols_m'   => in_array( (string) starter_bf( 'colonne_mobile' ), array( '1', '2' ), true ) ? (int) starter_bf( 'colonne_mobile' ) : 1,
	'gap_x'    => $starter_px( 'gap_orizzontale' ),
	'gap_y'    => $starter_px( 'gap_verticale', 200 ),
	'ratio'    => in_array( (string) starter_bf( 'proporzione' ), array( '16-9', '3-2', '4-3', '1-1', '4-5' ), true ) ? (string) starter_bf( 'proporzione' ) : '4-3',
	'fourth'   => (bool) starter_bf( 'quarto_elemento' ),
);
$starter_per_page = max( 1, min( 60, (int) starter_bf( 'per_pagina' ) ?: 12 ) );
$starter_tabs_on  = $starter_flag( 'schede' );
$starter_counts   = $starter_flag( 'conteggi' );
$starter_filter_style = in_array( (string) starter_bf( 'filtri_stile' ), array( 'linea', 'testo', 'pillole', 'frase' ), true ) ? (string) starter_bf( 'filtri_stile' ) : 'linea';
$starter_filter_text  = null === starter_bf( 'filtri_frase' ) ? __( 'Realizziamo progetti di', 'slug-theme' ) : (string) starter_bf( 'filtri_frase' );
$starter_same_page = 'nuova' !== starter_bf( 'filtri_modo' ); // Predefinito: filtri e pagine senza ricaricare (JS/scripts.js, initProjectFilters); i link restano normali e funzionano anche senza JavaScript.

// Opzioni delle card (categorie, riassunto e cliente, video, animazione logo, colori): le legge starter_portfolio_card_config(), la stessa funzione degli "Altri progetti".
$starter_card           = starter_portfolio_card_config( 'starter_bf' );
$starter_cat_style      = $starter_card['cats'];
$starter_cat_opts       = $starter_card['cats_opts'];
$starter_title_opts     = $starter_card['titles'];
$starter_video_hover    = $starter_card['video'];
$starter_reveal         = $starter_card['reveal'];
$starter_cat_style_attr = $starter_card['style'];

// Colori dei filtri (scheda Filtri): normale, hover, attivo. Per le pillole è lo sfondo e il testo si sceglie da solo (chiaro o scuro).
$starter_flt_vars = '';

foreach ( array( 'filtri_colore' => array( 'c', 'ct' ), 'filtri_colore_hover' => array( 'ch', 'cht' ), 'filtri_colore_attivo' => array( 'ca', 'cat' ) ) as $starter_flt_name => $starter_flt_keys ) {
	$starter_flt_hex = starter_resolve_named_color( (string) starter_bf( $starter_flt_name ) );

	if ( $starter_flt_hex ) {
		$starter_flt_vars .= '--flt-' . $starter_flt_keys[0] . ':' . $starter_flt_hex . ';--flt-' . $starter_flt_keys[1] . ':' . ( starter_color_is_dark( $starter_flt_hex ) ? '#fff' : '#111' ) . ';';
	}
}
$starter_only     = array_values( array_filter( array_map( 'intval', (array) starter_bf( 'solo_categorie' ) ) ) );

$starter_term_args = array(
	'taxonomy'   => 'portfolio_category',
	'hide_empty' => true,
);

if ( $starter_only ) {
	$starter_term_args['include'] = $starter_only;
}

$starter_terms = get_terms( $starter_term_args );
$starter_terms = is_wp_error( $starter_terms ) ? array() : $starter_terms;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- filtro e pagina in sola lettura dell'URL.
$starter_slug  = isset( $_GET['categoria'] ) ? sanitize_title( wp_unslash( $_GET['categoria'] ) ) : '';
$starter_paged = isset( $_GET['pagina'] ) ? max( 1, (int) $_GET['pagina'] ) : 1;
// phpcs:enable

if ( ! in_array( $starter_slug, wp_list_pluck( $starter_terms, 'slug' ), true ) ) {
	$starter_slug = '';
}

$starter_base = ! empty( $is_preview ) ? '#' : (string) get_permalink( get_queried_object_id() );
$starter_base = $starter_base ? $starter_base : home_url( '/' );

$starter_tax = array();

if ( $starter_slug ) {
	$starter_tax[] = array(
		'taxonomy' => 'portfolio_category',
		'field'    => 'slug',
		'terms'    => $starter_slug,
	);
} elseif ( $starter_only ) {
	$starter_tax[] = array(
		'taxonomy' => 'portfolio_category',
		'terms'    => $starter_only,
	);
}

$starter_query = new WP_Query(
	array(
		'post_type'           => 'portfolio',
		'post_status'         => 'publish',
		'posts_per_page'      => $starter_per_page,
		'paged'               => $starter_paged,
		'ignore_sticky_posts' => true,
		'tax_query'           => $starter_tax, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	)
);

$starter_total = $starter_slug ? ( new WP_Query(
	array(
		'post_type'      => 'portfolio',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'tax_query'      => $starter_only ? array( array( 'taxonomy' => 'portfolio_category', 'terms' => $starter_only ) ) : array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	)
) )->found_posts : $starter_query->found_posts;

$starter_wrapper = get_block_wrapper_attributes( array(
	'class'            => 'alignfull content-block project-list',
	'data-filter-mode' => $starter_same_page ? 'stessa' : 'nuova',
	'style' => $starter_cat_style_attr . $starter_flt_vars,
) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap">

		<?php
		if ( $starter_tabs_on && count( $starter_terms ) >= 2 ) {
			starter_portfolio_tabs( $starter_terms, $starter_base, $starter_slug, $starter_total, $starter_counts, $starter_filter_style, $starter_filter_text );
		}
		?>

		<?php if ( $starter_query->have_posts() ) : ?>

			<?php
			$starter_cards  = array();

			while ( $starter_query->have_posts() ) :
				$starter_query->the_post();
				ob_start();
				get_template_part(
					'template-parts/portfolio/card',
					null,
					array(
						'cats'      => $starter_cat_style,
						'cats_opts' => $starter_cat_opts,
						'titles'    => $starter_title_opts,
						'video'     => $starter_video_hover,
						'reveal'    => $starter_reveal,
						'img_mb'    => $starter_card['img_mb'],
					)
				);
				$starter_cards[] = ob_get_clean();
			endwhile;

			wp_reset_postdata();

			echo starter_portfolio_grid_html( $starter_cards, $starter_layout, $starter_grid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML delle card già scappato nel template.
			?>

			<?php
			if ( $starter_query->max_num_pages > 1 ) {
				$starter_page_base = $starter_slug ? add_query_arg( 'categoria', $starter_slug, $starter_base ) : $starter_base;
				$starter_links     = paginate_links(
					array(
						'base'      => $starter_page_base . ( false !== strpos( $starter_page_base, '?' ) ? '&' : '?' ) . 'pagina=%#%',
						'format'    => '',
						'current'   => $starter_paged,
						'total'     => $starter_query->max_num_pages,
						'mid_size'  => 2,
						'prev_text' => __( '← Precedenti', 'slug-theme' ),
						'next_text' => __( 'Successivi →', 'slug-theme' ),
					)
				);

				if ( $starter_links ) {
					echo '<nav class="navigation pagination" aria-label="' . esc_attr__( 'Paginazione', 'slug-theme' ) . '"><div class="nav-links">' . $starter_links . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			?>

		<?php else : ?>

			<p class="txt"><?php esc_html_e( 'Nessun progetto da mostrare.', 'slug-theme' ); ?></p>

		<?php endif; ?>

	</div>
</section>
