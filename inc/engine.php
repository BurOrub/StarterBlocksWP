<?php
/**
 * Motore dei blocchi: ogni cartella in blocks/ ha block.json, fields.json (l'elenco dei campi, da cui l'editor disegna il pannello)
 * e render.php (il disegno, che legge i campi con starter_bf( 'nome' )).
 *
 * I contenuti stanno nell'attributo "data" del blocco, nel formato che usava ACF: campo = valore, ripetitore = numero di righe + nome_0_sottocampo.
 * Per questo le pagine già scritte si leggono senza conversioni.
 *
 * @package Starter_Blocks_POC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Cartelle che contengono blocks/ e meta/: il plugin e, se attivo, il tema figlio. */
function starter_blocks_dirs() {
	$dirs = array( STARTER_POC_DIR );

	if ( get_stylesheet_directory() !== get_template_directory() ) {
		$dirs[] = trailingslashit( get_stylesheet_directory() );
	}

	return $dirs;
}

/* Blocchi del motore: slug => array( 'json' => block.json, 'fields' => campi ). */
function starter_blocks_registry() {
	static $registry = null;

	if ( null !== $registry ) {
		return $registry;
	}

	$registry = array();

	// I blocchi del plugin, poi quelli del tema figlio (cartella blocks/ del figlio): a parità di nome vince il figlio.
	foreach ( starter_blocks_dirs() as $base ) {
		foreach ( (array) glob( $base . 'blocks/*/block.json' ) as $file ) {
			$slug   = basename( dirname( $file ) );
			$json   = json_decode( (string) file_get_contents( $file ), true );
			$fields = json_decode( (string) @file_get_contents( dirname( $file ) . '/fields.json' ), true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- fields.json è facoltativo.

			if ( is_array( $json ) && ! empty( $json['name'] ) ) {
				$json['attributes'] = array_merge(
					array(
						'align' => array( 'type' => 'string', 'default' => 'full' ),
						'name'  => array( 'type' => 'string' ),
						'mode'  => array( 'type' => 'string' ),
						'id'    => array( 'type' => 'string' ),
						'data'  => array( 'type' => 'object', 'default' => new stdClass() ),
					),
					isset( $json['attributes'] ) ? (array) $json['attributes'] : array()
				);

				$registry[ $slug ] = array(
					'json'   => $json,
					'fields' => starter_blocks_live_choices( is_array( $fields ) ? $fields : array() ),
					'dir'    => dirname( $file ) . '/',
				);
			}
		}
	}

	return $registry;
}

/* Le scelte che dipendono dalle opzioni del tema (colori, stili dei pulsanti) si leggono al momento, non dal file. */
function starter_blocks_live_choices( array $fields ) {
	foreach ( $fields as $i => $field ) {
		if ( 'sfondo_sezione' === ( $field['name'] ?? '' ) && function_exists( 'starter_section_bg_choices' ) ) {
			$fields[ $i ]['choices'] = starter_section_bg_choices();
		} elseif ( 0 === strpos( (string) ( $field['key'] ?? '' ), 'field_btn_' ) && function_exists( 'starter_button_style_choices' ) ) {
			$fields[ $i ]['choices'] = starter_button_style_choices( isset( $field['choices']['chiaro'] ) );
		}

		if ( ! empty( $field['sub_fields'] ) ) {
			$fields[ $i ]['sub_fields'] = starter_blocks_live_choices( $field['sub_fields'] );
		}
	}

	return $fields;
}

/* Un allegato come lo restituiva ACF col formato "array". */
function starter_blocks_attachment( $id ) {
	$id   = (int) $id;
	$post = $id ? get_post( $id ) : null;

	if ( ! $post || 'attachment' !== $post->post_type ) {
		return false;
	}

	$mime = (string) $post->post_mime_type;
	$out  = array(
		'ID'          => $id,
		'id'          => $id,
		'title'       => $post->post_title,
		'filename'    => wp_basename( (string) get_attached_file( $id ) ),
		'url'         => (string) wp_get_attachment_url( $id ),
		'alt'         => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		'description' => $post->post_content,
		'caption'     => $post->post_excerpt,
		'name'        => $post->post_name,
		'mime_type'   => $mime,
		'type'        => strstr( $mime, '/', true ),
		'subtype'     => substr( strstr( $mime, '/' ), 1 ),
	);

	$meta = wp_get_attachment_metadata( $id );

	if ( is_array( $meta ) && isset( $meta['width'], $meta['height'] ) ) {
		$out['width']  = (int) $meta['width'];
		$out['height'] = (int) $meta['height'];
	}

	// Misure registrate: sizes[ 'large' ] = url, sizes[ 'large-width' ], sizes[ 'large-height' ] (come ACF).
	if ( 'image' === ( $out['type'] ?? '' ) ) {
		$out['sizes'] = array();

		foreach ( get_intermediate_image_sizes() as $size ) {
			$src = wp_get_attachment_image_src( $id, $size );

			if ( $src ) {
				$out['sizes'][ $size ]             = $src[0];
				$out['sizes'][ $size . '-width' ]  = $src[1];
				$out['sizes'][ $size . '-height' ] = $src[2];
			}
		}
	}

	return $out;
}

/* Il valore di un allegato nel formato richiesto dal campo (id, array o url). */
function starter_blocks_media_value( $raw, $format ) {
	$id = is_array( $raw ) ? (int) ( $raw['ID'] ?? $raw['id'] ?? 0 ) : (int) $raw;

	if ( ! $id ) {
		return '';
	}

	if ( 'array' === $format ) {
		return starter_blocks_attachment( $id );
	}

	if ( 'url' === $format ) {
		return (string) wp_get_attachment_url( $id );
	}

	return $id;
}

/* Un campo, letto dai dati e portato nella forma che aspetta il disegno del blocco. */
function starter_blocks_value( array $field, array $data, $prefix ) {
	$type = $field['type'] ?? 'text';
	$key  = $prefix . ( $field['name'] ?? '' );
	$raw  = array_key_exists( $key, $data ) ? $data[ $key ] : null;

	switch ( $type ) {
		case 'repeater':
			$rows  = array();
			$count = (int) $raw;

			for ( $i = 0; $i < $count; $i++ ) {
				$rows[] = starter_blocks_values( $field['sub_fields'] ?? array(), $data, $key . '_' . $i . '_' );
			}

			return $rows;

		case 'image':
		case 'file':
			return starter_blocks_media_value( $raw, $field['return_format'] ?? 'array' );

		case 'gallery':
			$items = array();

			foreach ( is_array( $raw ) ? $raw : array() as $item ) {
				$item_id = is_array( $item ) ? (int) ( $item['ID'] ?? $item['id'] ?? 0 ) : (int) $item;

				if ( ! $item_id || 'attachment' !== get_post_type( $item_id ) ) {
					continue; // Come ACF: le foto tolte dalla Libreria non compaiono.
				}

				$value = starter_blocks_media_value( $item, $field['return_format'] ?? 'array' );

				if ( $value ) {
					$items[] = $value;
				}
			}

			return $items;

		case 'relationship':
			return array_values( array_filter( array_map( 'intval', (array) $raw ) ) );

		case 'wysiwyg':
			if ( null === $raw || '' === $raw ) {
				return $raw;
			}

			// Come il testo di un articolo: paragrafi, shortcode e immagini ottimizzate; il tema può aggiungere i suoi passaggi.
			$html = wptexturize( (string) $raw );
			$html = convert_chars( $html );
			$html = wpautop( $html );
			$html = shortcode_unautop( $html );
			$html = do_shortcode( $html );
			$html = wp_filter_content_tags( $html );

			return apply_filters( 'starter_field_content', $html );

		case 'link':
			if ( ! is_array( $raw ) || empty( $raw['url'] ) ) {
				return '';
			}

			return array(
				'title'  => (string) ( $raw['title'] ?? '' ),
				'url'    => (string) $raw['url'],
				'target' => (string) ( $raw['target'] ?? '' ),
			);

		case 'true_false':
			return null === $raw ? null : ( ! empty( $raw ) && '0' !== $raw );

		case 'taxonomy':
			if ( 'multi_select' === ( $field['field_type'] ?? '' ) || 'checkbox' === ( $field['field_type'] ?? '' ) ) {
				return array_values( array_filter( array_map( 'intval', (array) $raw ) ) );
			}

			return $raw ? (int) ( is_array( $raw ) ? reset( $raw ) : $raw ) : '';

		case 'select':
			if ( ! empty( $field['multiple'] ) ) {
				return is_array( $raw ) ? array_values( $raw ) : array();
			}

			return $raw; // Mai salvato = null, come in ACF: certi disegni distinguono "non scelto" da "vuoto".

		default:
			return $raw;
	}
}

function starter_blocks_values( array $fields, array $data, $prefix = '' ) {
	$out = array();

	foreach ( $fields as $field ) {
		if ( empty( $field['name'] ) || in_array( $field['type'] ?? '', array( 'tab', 'message' ), true ) ) {
			continue;
		}

		$out[ $field['name'] ] = starter_blocks_value( $field, $data, $prefix );
	}

	return $out;
}

/* Vero mentre un blocco del motore è in disegno (il tema lo usa per scegliere da dove leggere i campi). */
function starter_bf_active() {
	return ! empty( $GLOBALS['starter_blocks_ctx'] );
}

/* Il valore di un campo del blocco che si sta disegnando (al posto di get_field() di ACF). $raw = valore salvato, senza trasformazioni. */
function starter_bf( $name, $raw = false ) {
	$ctx = $GLOBALS['starter_blocks_ctx'] ?? null;

	if ( ! $ctx ) {
		return '';
	}

	if ( $raw ) {
		return $ctx['data'][ $name ] ?? '';
	}

	return array_key_exists( $name, $ctx['values'] ) ? $ctx['values'][ $name ] : null;
}

/* Cartella (slug) di un blocco dal suo nome, qualunque sia il prefisso (starter-theme/…, starter-child/…). */
function starter_blocks_slug( $name ) {
	foreach ( starter_blocks_registry() as $slug => $block ) {
		if ( $block['json']['name'] === $name ) {
			return $slug;
		}
	}

	return '';
}

/* Id stabile per i blocchi senza id (dal contenuto); se due blocchi hanno gli stessi dati, il secondo riceve un numero in più. */
function starter_blocks_auto_id( array $data ) {
	static $seen = array();

	$id            = 'block_' . substr( md5( wp_json_encode( $data ) ), 0, 13 );
	$seen[ $id ]   = ( $seen[ $id ] ?? 0 ) + 1;

	return $seen[ $id ] > 1 ? $id . '_' . $seen[ $id ] : $id;
}

/* Disegno di un blocco del motore, sul sito e nell'anteprima dell'editor. */
function starter_blocks_render( $attributes, $content, $wp_block ) {
	$slug     = starter_blocks_slug( (string) $wp_block->name );
	$registry = starter_blocks_registry();

	$file     = isset( $registry[ $slug ] ) ? $registry[ $slug ]['dir'] . 'render.php' : '';

	if ( ! $file || ! is_file( $file ) || ! function_exists( 'starter_icon' ) ) {
		return ''; // Serve il tema Starter.
	}

	$data       = isset( $attributes['data'] ) && is_array( $attributes['data'] ) ? $attributes['data'] : array();
	$is_preview = defined( 'REST_REQUEST' ) && REST_REQUEST; // Richiesta dell'anteprima nell'editor.
	$previous   = $GLOBALS['starter_blocks_ctx'] ?? null;
	$block      = array( // Come lo passava ACF ai disegni: l'id serve a dare un nome unico agli elementi (es. lo scorrimento delle recensioni).
		'id'   => ! empty( $attributes['id'] ) ? $attributes['id'] : starter_blocks_auto_id( $data ),
		'name' => $wp_block->name,
		'data' => $data,
	);

	$GLOBALS['starter_blocks_ctx'] = array(
		'data'   => $data,
		'values' => starter_blocks_values( $registry[ $slug ]['fields'], $data ),
	);

	ob_start();
	include $file;
	$html = (string) ob_get_clean();

	$GLOBALS['starter_blocks_ctx'] = $previous;

	return $html;
}

/* Registrazione: i blocchi dei moduli spenti (Opzioni → Moduli) restano fuori, come prima. */
add_action(
	'init',
	function () {
		$skip = function_exists( 'starter_modules_inactive_blocks' ) ? starter_modules_inactive_blocks() : array();

		foreach ( starter_blocks_registry() as $slug => $block ) {
			if ( in_array( $slug, $skip, true ) ) {
				continue;
			}

			$json = $block['json'];
			unset( $json['$schema'] );
			$json['render_callback']       = 'starter_blocks_render';
			$json['editor_script_handles'] = array( 'starter-poc-elenco-servizi' );

			register_block_type( $json['name'], $json );
		}
	},
	9 // Prima della registrazione dei blocchi del tema (priorità 10): a parità di nome vince il plugin.
);
