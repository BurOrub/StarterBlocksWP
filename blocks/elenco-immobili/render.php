<?php
/** Disegno del blocco (React) "starter-theme/elenco-immobili". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );

$starter_values = starter_imm_filters_get_values();
$starter_paged  = starter_imm_filters_current_page();
$starter_query  = new WP_Query( starter_imm_filters_query_args( $starter_values, $starter_paged ) );
$starter_action = get_permalink();

if ( ! $starter_action ) {
	$starter_action = starter_imm_get_immobili_page_url();
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block imm-elenco' ) );

// Intestazione della pagina: la stessa di Blog, Portfolio e delle altre pagine (occhiello = titolo SEO, titolo grande, testo).
if ( $starter_eyebrow || $starter_title || $starter_text ) {
	starter_page_header(
		array(
			'eyebrow'    => (string) $starter_eyebrow,
			'title'      => (string) $starter_title,
			'lead_html'  => $starter_text ? wpautop( esc_html( $starter_text ) ) : '',
			'eyebrow_h1' => (bool) $starter_eyebrow,
		)
	);
}

$starter_child_tag = 'h3';
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="block-wrap">

		<?php starter_imm_filters_render_form( $starter_values, $starter_action, $starter_query->found_posts ); ?>

		<?php if ( $starter_query->have_posts() ) : ?>

			<div class="immobile-grid">
				<?php
				while ( $starter_query->have_posts() ) {
					$starter_query->the_post();
					get_template_part( 'template-parts/content/card-immobile', null, array( 'heading' => $starter_child_tag ) );
				}
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $starter_query->max_num_pages > 1 ) : ?>
				<?php
				$starter_base = remove_query_arg( starter_imm_filter_param( 'pagina' ) );
				$starter_base = $starter_base ? $starter_base : $starter_action;
				?>
				<nav class="pagination imm-elenco__pagination" aria-label="<?php esc_attr_e( 'Paginazione immobili', 'slug-theme' ); ?>">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'base'      => esc_url_raw( add_query_arg( starter_imm_filter_param( 'pagina' ), '%#%', $starter_base ) ),
								'format'    => '',
								'current'   => $starter_paged,
								'total'     => $starter_query->max_num_pages,
								'mid_size'  => 2,
								'prev_text' => __( '← Precedenti', 'slug-theme' ),
								'next_text' => __( 'Successivi →', 'slug-theme' ),
							)
						)
					);
					?>
				</nav>
			<?php endif; ?>

		<?php else : ?>

			<div class="imm-elenco__empty">
				<p class="imm-elenco__empty-title"><?php esc_html_e( 'Nessun immobile corrisponde alla tua ricerca', 'slug-theme' ); ?></p>
				<p><?php esc_html_e( 'Prova a modificare o togliere qualche filtro.', 'slug-theme' ); ?></p>
				<a class="button button--outline" href="<?php echo esc_url( $starter_action ); ?>"><?php echo starter_flip_text( __( 'Azzera i filtri', 'slug-theme' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>

		<?php endif; ?>

	</div>
</section>
