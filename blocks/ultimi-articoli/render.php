<?php
/**
 * Blocco "Ultimi articoli": gli articoli più recenti (anche di una sola categoria) con la card del blog.
 * Cosa mostrare nelle card (data, autore, categorie) segue Opzioni → Blog.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_args = array(
	'post_type'           => 'post',
	'posts_per_page'      => starter_bf( 'numero' ) ? (int) starter_bf( 'numero' ) : 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

if ( starter_bf( 'categoria' ) ) {
	$starter_args['cat'] = (int) starter_bf( 'categoria' );
}

$starter_posts = new WP_Query( $starter_args );

if ( ! $starter_posts->have_posts() ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Ultimi articoli: nessun articolo pubblicato.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull latest-posts content-block' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="starter-container">
		<?php starter_block_head( starter_bf( 'titolo' ), starter_bf( 'testo' ), starter_bf( 'occhiello' ) ); ?>

		<div class="blog-grid latest-posts__grid">
			<?php
			while ( $starter_posts->have_posts() ) {
				$starter_posts->the_post();
				get_template_part( 'template-parts/content/card' );
			}
			wp_reset_postdata();
			?>
		</div>

		<?php starter_block_center_link( starter_bf( 'link' ), 'button button--outline', starter_bf( 'link_stile' ) ); ?>
	</div>
</section>
