<?php
/** Disegno del blocco (React) "starter-theme/testo-affiancato". */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_side      = starter_bf( 'lato_media' ) ? starter_bf( 'lato_media' ) : 'sinistra';
$starter_bg        = starter_bf( 'sfondo' ) ? starter_bf( 'sfondo' ) : 'nessuno';
$starter_eyebrow   = starter_bf( 'occhiello' );
$starter_title     = starter_bf( 'titolo' );
$starter_text      = starter_bf( 'testo' );
$starter_quote     = starter_bf( 'citazione' );
$starter_checklist = starter_bf( 'checklist' );
$starter_link      = starter_bf( 'link' );
$starter_images    = starter_photo_pair( 'immagine', 'immagine_2', 'immagini' ); // id: principale + seconda facoltativa (campi immagine singoli)

if ( ! $starter_title && ! $starter_text && ! ( is_array( $starter_images ) && $starter_images ) ) {
	if ( ! empty( $is_preview ) ) {
		echo '<p class="block-placeholder">' . esc_html__( 'Testo affiancato: compila i campi del blocco.', 'slug-theme' ) . '</p>';
	}
	return;
}

$starter_tags    = starter_section_heading_tags( (bool) $starter_eyebrow, 'h1' === starter_bf( 'livello' ) ? 1 : 2 );
$starter_classes = 'alignfull content-block split split--' . $starter_side . ( 'muted' === $starter_bg ? ' panel panel--muted' : '' );
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => $starter_classes ) );

ob_start();
?>
<div class="split__media" data-reveal>
	<?php if ( is_array( $starter_images ) && $starter_images ) : ?>
		<div class="split__photos<?php echo empty( $starter_images[1] ) ? ' split__photos--single' : ''; ?>">
			<div class="split__photo-main">
				<?php echo wp_get_attachment_image( $starter_images[0], 'large', false, array( 'loading' => 'lazy' ) ); ?>
			</div>
			<?php if ( ! empty( $starter_images[1] ) ) : ?>
				<div class="split__photo-round">
					<?php echo wp_get_attachment_image( $starter_images[1], 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
<?php
$starter_media_html = ob_get_clean();

ob_start();
?>
<div class="split__text" data-reveal>
	<?php if ( $starter_eyebrow ) : ?>
		<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
	<?php endif; ?>

	<?php if ( $starter_title ) : ?>
		<<?php echo esc_html( $starter_tags['title'] ); ?> class="split__title text-balance"><?php echo starter_kses( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
	<?php endif; ?>

	<?php if ( $starter_text ) : ?>
		<p class="split__lead"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<?php if ( is_array( $starter_checklist ) && $starter_checklist ) : ?>
		<ul class="checklist">
			<?php foreach ( $starter_checklist as $starter_item ) : ?>
				<li><span class="checklist__mark"><?php echo starter_icon( 'check', 14, 2.5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php echo starter_inline( $starter_item['voce'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $starter_quote ) : ?>
		<p class="split__quote"><?php echo starter_inline( $starter_quote ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	<?php endif; ?>

	<?php if ( $starter_link ) : ?>
		<a <?php echo starter_link_attrs( $starter_link, starter_button_class( starter_bf( 'link_stile' ), 'button split__button', 'split__button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo starter_flip_text( starter_link_title( $starter_link ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo starter_icon( 'arrow-right', 16, 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	<?php endif; ?>
</div>
<?php
$starter_text_html = ob_get_clean();

$starter_first  = 'sinistra' === $starter_side ? $starter_media_html : $starter_text_html;
$starter_second = 'sinistra' === $starter_side ? $starter_text_html : $starter_media_html;
?>

<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( 'muted' === $starter_bg ) : ?>
		<div class="panel__box"><div class="panel__inner split__grid">
	<?php else : ?>
		<div class="block-wrap split__grid">
	<?php endif; ?>

		<?php echo $starter_first; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo $starter_second; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

	<?php if ( 'muted' === $starter_bg ) : ?>
		</div></div>
	<?php else : ?>
		</div>
	<?php endif; ?>
</section>
