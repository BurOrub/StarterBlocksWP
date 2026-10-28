<?php
/**
 * Blocco "Team": griglia delle persone (4 colonne, 3 sotto 1280px, 2 sotto 1024px, 1 sotto 640px) con foto
 * quadrata, nome, ruolo e breve bio.
 *
 * @package Starter_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$starter_items = starter_bf( 'voci' );

if ( ! is_array( $starter_items ) || ! $starter_items ) {
	starter_block_placeholder( ! empty( $is_preview ), __( 'Team: aggiungi almeno una persona.', 'slug-theme' ) );
	return;
}

$starter_eyebrow = starter_bf( 'occhiello' );
$starter_title   = starter_bf( 'titolo' );
$starter_text    = starter_bf( 'testo' );
$starter_tags    = starter_section_heading_tags( (bool) $starter_eyebrow );
$starter_wrapper = get_block_wrapper_attributes( array( 'class' => 'alignfull content-block team-grid' ) );
?>
<section <?php echo $starter_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="starter-container">
		<?php if ( $starter_eyebrow || $starter_title || $starter_text ) : ?>
			<div class="section-head" data-reveal>
				<div class="section-head__text">
					<?php if ( $starter_eyebrow ) : ?>
						<<?php echo esc_html( $starter_tags['eyebrow'] ); ?> class="eyebrow"><?php echo starter_inline( $starter_eyebrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['eyebrow'] ); ?>>
					<?php endif; ?>
					<?php if ( $starter_title ) : ?>
						<<?php echo esc_html( $starter_tags['title'] ); ?> class="text-balance"><?php echo starter_inline( $starter_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['title'] ); ?>>
					<?php endif; ?>
					<?php if ( $starter_text ) : ?>
						<p class="txt"><?php echo starter_inline( $starter_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<ul class="team-grid__list">
			<?php foreach ( $starter_items as $starter_item ) : ?>
				<?php
				$starter_photo = (int) $starter_item['voci_foto'];
				$starter_name  = (string) $starter_item['voci_nome'];
				$starter_role  = (string) $starter_item['voci_ruolo'];
				$starter_alt   = trim( $starter_name . ( $starter_role ? ', ' . $starter_role : '' ) );
				?>
				<li class="team-grid__card" data-reveal>
					<?php if ( $starter_photo ) : ?>
						<div class="team-grid__photo">
							<?php echo wp_get_attachment_image( $starter_photo, 'large', false, array( 'alt' => $starter_alt, 'loading' => 'lazy', 'sizes' => '(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>
					<div class="team-grid__body">
						<?php if ( $starter_name ) : ?>
							<<?php echo esc_html( $starter_tags['child'] ); ?> class="team-grid__name title-h5"><?php echo starter_inline( $starter_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_html( $starter_tags['child'] ); ?>>
						<?php endif; ?>
						<?php if ( $starter_role ) : ?>
							<span class="eyebrow"><?php echo starter_inline( $starter_role ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php if ( $starter_item['voci_bio'] ) : ?>
							<p class="team-grid__bio has-small-font-size"><?php echo starter_inline( $starter_item['voci_bio'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
