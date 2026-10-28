<?php
/**
 * Blocco "Contatti": a sinistra i recapiti scritti nel blocco (icona + testo; email e telefoni diventano link da soli) oppure,
 * se il blocco non ne ha, quelli di Opzioni → Footer; a destra il modulo (shortcode di Contact Form 7 scelto nel blocco).
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_form    = trim( (string) starter_bf( 'shortcode_form' ) );
$starter_rows    = array();

// Recapiti scritti nel blocco (modificabili anche dal frontend con Starter Live).
foreach ( (array) starter_bf( 'recapiti' ) as $starter_entry ) {
	$starter_text = trim( (string) ( $starter_entry['testo'] ?? '' ) );

	if ( '' === trim( wp_strip_all_tags( $starter_text ) ) ) {
		continue;
	}

	$starter_href   = starter_contact_href( trim( wp_strip_all_tags( $starter_text ) ) ); // il link si riconosce dal testo, senza grassetto e simili
	$starter_rows[] = array(
		! empty( $starter_entry['icona'] ) ? $starter_entry['icona'] : starter_contact_icon( $starter_href ),
		array(
			'text' => $starter_text,
			'href' => $starter_href,
		),
	);
}

// Nessun recapito nel blocco: si usano quelli di Opzioni → Footer.
if ( ! $starter_rows ) {
	foreach ( starter_get_footer_contacts() as $starter_item ) {
		$starter_rows[] = array( starter_contact_icon( $starter_item['href'] ), $starter_item );
	}
}

if ( ! $starter_rows && '' === $starter_form ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Contatti: compila i recapiti del footer e inserisci lo shortcode del modulo.', 'slug-theme' ) );
	return;
}

$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block contacts-block' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="starter-container contacts-block__grid">
		<div class="contacts-block__info" data-reveal>
			<?php if ( $starter_eyebrow ) : ?>
				<?php $starter_eyebrow_tag = starter_heading_tag( 'occhiello', 'span' ); ?>
				<<?php echo esc_html( $starter_eyebrow_tag ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_eyebrow_tag ); ?>>
			<?php endif; ?>
			<?php if ( $starter_title ) : ?>
				<?php $starter_title_tag = starter_heading_tag( 'titolo', 'h2' ); ?>
				<<?php echo esc_html( $starter_title_tag ); ?> class="contacts-block__title"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_title_tag ); ?>>
			<?php endif; ?>

			<ul class="contacts-block__list">
				<?php foreach ( $starter_rows as $starter_row ) : ?>
					<li>
						<span class="icon-circle"><?php echo starter_icon( $starter_row[0], 19, 1.9 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php if ( $starter_row[1]['href'] ) : ?>
							<a href="<?php echo esc_attr( $starter_row[1]['href'] ); ?>"><?php echo starter_inline( $starter_row[1]['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php else : ?>
							<span><?php echo starter_inline( $starter_row[1]['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( '' !== $starter_form ) : ?>
			<div class="contacts-block__form" data-reveal data-live-shortcode="shortcode_form">
				<?php echo do_shortcode( $starter_form ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
	</div>
</section>
