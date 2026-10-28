<?php
/**
 * Campi dei contenuti (Portfolio, Immobili, dati del consulente): stesso motore dei blocchi, ma i valori stanno nel meta dell'articolo o
 * del termine. I nomi dei meta sono questi (campo = valore; ripetitore = numero di righe + nome_0_sottocampo; _campo = chiave),
 * quindi i contenuti già scritti si leggono senza conversioni.
 *
 * - meta/<nome>.json: elenco dei campi e dove compaiono (tipo di contenuto o tassonomia).
 * - Articoli: un riquadro nella schermata di modifica (src/meta.jsx); i valori vanno in un campo nascosto e si salvano con l'articolo.
 * - Termini: i campi sono nel modulo di modifica del termine.
 * - Il tema legge con starter_meta_get( 'nome', $articolo_o_termine ).
 *
 * @package Starter_Blocks_POC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Gruppi di campi dei contenuti: id => array( 'object' => post_type|taxonomy, 'name' => slug, 'title' => titolo, 'fields' => campi ). */
function starter_meta_registry() {
	static $registry = null;

	if ( null !== $registry ) {
		return $registry;
	}

	$registry = array();

	foreach ( starter_blocks_dirs() as $base ) {
		foreach ( (array) glob( $base . 'meta/*.json' ) as $file ) {
			$group = json_decode( (string) file_get_contents( $file ), true );

			if ( is_array( $group ) && ! empty( $group['name'] ) && ! empty( $group['fields'] ) ) {
				$registry[ basename( $file, '.json' ) ] = $group;
			}
		}
	}

	return $registry;
}

/* Definizione di un campo dalla sua chiave (blocchi e contenuti): lo usa Starter Live. */
function starter_field_def( $key ) {
	static $index = null;

	if ( null === $index ) {
		$index = array();
		$walk  = static function ( $fields ) use ( &$index, &$walk ) {
			foreach ( $fields as $field ) {
				if ( ! empty( $field['key'] ) ) {
					$index[ $field['key'] ] = $field;
				}

				if ( ! empty( $field['sub_fields'] ) ) {
					$walk( $field['sub_fields'] );
				}
			}
		};

		foreach ( starter_blocks_registry() as $block ) {
			$walk( $block['fields'] );
		}

		foreach ( starter_meta_registry() as $group ) {
			$walk( $group['fields'] );
		}
	}

	return isset( $index[ $key ] ) ? $index[ $key ] : null;
}

/* Cosa indica $ref: articolo (id), termine ( 'term_12', WP_Term ), opzioni ( 'option' ). Restituisce array( tipo, id ). */
function starter_meta_target( $ref ) {
	if ( $ref instanceof WP_Term ) {
		return array( 'term', (int) $ref->term_id, $ref->taxonomy );
	}

	if ( $ref instanceof WP_Post ) {
		return array( 'post', (int) $ref->ID, $ref->post_type );
	}

	// Termine nel formato: "tassonomia_12".
	if ( is_string( $ref ) && preg_match( '/^([a-z0-9_\-]+)_(\d+)$/i', $ref, $m ) && 'term' !== $m[1] && taxonomy_exists( $m[1] ) ) {
		return array( 'term', (int) $m[2], $m[1] );
	}

	if ( is_string( $ref ) && 0 === strpos( $ref, 'term_' ) ) {
		$term = get_term( (int) substr( $ref, 5 ) );

		return $term && ! is_wp_error( $term ) ? array( 'term', (int) $term->term_id, $term->taxonomy ) : null;
	}

	if ( is_array( $ref ) && isset( $ref['taxonomy'], $ref['term_id'] ) ) {
		return array( 'term', (int) $ref['term_id'], $ref['taxonomy'] );
	}

	$id = (int) $ref;

	return $id && get_post( $id ) ? array( 'post', $id, get_post_type( $id ) ) : null;
}

/* Campi del gruppo che riguarda un tipo di contenuto o una tassonomia. */
function starter_meta_fields_for( $object, $name ) {
	foreach ( starter_meta_registry() as $group ) {
		if ( $object === $group['object'] && $name === $group['name'] ) {
			return $group['fields'];
		}
	}

	return array();
}

/* Tutti i meta (già in cache) come elenco chiave => valore. */
function starter_meta_all( $kind, $id ) {
	$all  = 'term' === $kind ? get_term_meta( $id ) : get_post_meta( $id );
	$data = array();

	foreach ( (array) $all as $key => $values ) {
		$data[ $key ] = maybe_unserialize( is_array( $values ) ? reset( $values ) : $values );
	}

	return $data;
}

/* Un campo di un articolo o di un termine. */
function starter_meta_get( $name, $ref = null ) {
	if ( null === $ref ) {
		$ref = get_the_ID();
	}

	if ( 'option' === $ref || 'options' === $ref ) {
		return get_option( 'options_' . $name, '' );
	}

	$target = starter_meta_target( $ref );

	if ( ! $target ) {
		return null;
	}

	foreach ( starter_meta_fields_for( 'term' === $target[0] ? 'taxonomy' : 'post_type', $target[2] ) as $field ) {
		if ( ( $field['name'] ?? '' ) !== $name ) {
			continue;
		}

		$value = starter_blocks_value( $field, starter_meta_all( $target[0], $target[1] ), '' );

		/**
		 * Valore di un campo dei contenuti, prima di restituirlo (es. per tradurre le scelte dei menu).
		 *
		 * @param mixed  $value  Valore.
		 * @param array  $field  Definizione del campo.
		 * @param array  $target Tipo (post|term), id e tipo di contenuto o tassonomia.
		 */
		return apply_filters( 'starter_meta_value', $value, $field, $target );
	}

	// Campo non dichiarato: il meta così com'è.
	return 'term' === $target[0] ? get_term_meta( $target[1], $name, true ) : get_post_meta( $target[1], $name, true );
}

/* ------------------------------------------------------------------ pulizia e scrittura */

/* Valore da salvare per un campo, ripulito secondo il suo tipo. Restituisce null se il valore non è ammesso. */
function starter_meta_clean_value( array $field, $raw ) {
	$type = $field['type'] ?? 'text';

	switch ( $type ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );

		case 'wysiwyg':
			return wp_kses_post( (string) $raw );

		case 'number':
			return '' === $raw || null === $raw ? '' : ( is_numeric( $raw ) ? $raw + 0 : '' );

		case 'url':
			return esc_url_raw( (string) $raw );

		case 'email':
			return sanitize_email( (string) $raw );

		case 'true_false':
			return ! empty( $raw ) && '0' !== $raw ? 1 : 0;

		case 'image':
		case 'file':
			return $raw ? (int) $raw : '';

		case 'gallery':
		case 'relationship':
			return array_values( array_filter( array_map( 'intval', (array) $raw ) ) );

		case 'taxonomy':
			return is_array( $raw ) ? array_values( array_filter( array_map( 'intval', $raw ) ) ) : ( $raw ? (int) $raw : '' );

		case 'link':
			if ( ! is_array( $raw ) || empty( $raw['url'] ) ) {
				return '';
			}

			return array(
				'title'  => sanitize_text_field( (string) ( $raw['title'] ?? '' ) ),
				'url'    => esc_url_raw( (string) $raw['url'] ),
				'target' => '_blank' === ( $raw['target'] ?? '' ) ? '_blank' : '',
			);

		case 'select':
		case 'button_group':
			$choices = isset( $field['choices'] ) ? array_map( 'strval', array_keys( (array) $field['choices'] ) ) : null;
			$check   = static function ( $value ) use ( $choices ) {
				$value = sanitize_text_field( (string) $value );

				return null === $choices || in_array( $value, $choices, true ) ? $value : '';
			};

			if ( ! empty( $field['multiple'] ) ) {
				return array_values( array_filter( array_map( $check, (array) $raw ), 'strlen' ) );
			}

			return $check( is_array( $raw ) ? '' : $raw );

		default:
			return sanitize_text_field( (string) $raw );
	}
}

/* Valori ripuliti di un elenco di campi dentro $data, nella forma piatta (nome, _nome, nome_0_sotto…). */
function starter_meta_clean_fields( array $fields, array $data, array &$out, $prefix = '' ) {
	foreach ( $fields as $field ) {
		if ( empty( $field['name'] ) || in_array( $field['type'] ?? '', array( 'tab', 'message' ), true ) ) {
			continue;
		}

		$key = $prefix . $field['name'];

		$out[ '_' . $key ] = $field['key'];

		if ( 'repeater' === ( $field['type'] ?? '' ) ) {
			$count = isset( $data[ $key ] ) ? max( 0, min( 200, (int) $data[ $key ] ) ) : 0;

			if ( ! empty( $field['max'] ) ) {
				$count = min( $count, (int) $field['max'] );
			}

			$out[ $key ] = $count;

			for ( $i = 0; $i < $count; $i++ ) {
				starter_meta_clean_fields( $field['sub_fields'] ?? array(), $data, $out, $key . '_' . $i . '_' );
			}

			continue;
		}

		$out[ $key ] = starter_meta_clean_value( $field, $data[ $key ] ?? '' );
	}
}

/* Sostituisce tutti i valori dei campi di un articolo o termine con quelli ricevuti (piatti), dopo averli ripuliti. */
function starter_meta_save( $kind, $id, array $fields, array $data ) {
	$out = array();

	starter_meta_clean_fields( $fields, $data, $out );

	$names = array();

	foreach ( $fields as $field ) {
		if ( ! empty( $field['name'] ) && ! in_array( $field['type'] ?? '', array( 'tab', 'message' ), true ) ) {
			$names[] = preg_quote( $field['name'], '/' );
		}
	}

	$own = '/^_?(' . implode( '|', $names ) . ')(_\d+_.*)?$/';

	// Via i meta di questi campi che non ci sono più (righe tolte da un ripetitore), poi si scrive il resto.
	foreach ( array_keys( starter_meta_all( $kind, $id ) ) as $key ) {
		if ( preg_match( $own, $key ) && ! array_key_exists( $key, $out ) ) {
			'term' === $kind ? delete_term_meta( $id, $key ) : delete_post_meta( $id, $key );
		}
	}

	foreach ( $out as $key => $value ) {
		'term' === $kind ? update_term_meta( $id, $key, wp_slash( $value ) ) : update_post_meta( $id, $key, wp_slash( $value ) );
	}
}

/* ------------------------------------------------------------------ articoli: riquadro nella schermata di modifica */

add_action(
	'add_meta_boxes',
	function ( $post_type ) {
		foreach ( starter_meta_registry() as $id => $group ) {
			if ( 'post_type' !== $group['object'] || $post_type !== $group['name'] ) {
				continue;
			}

			add_meta_box(
				'starter-meta-' . $id,
				$group['title'],
				static function ( $post ) use ( $group ) {
					$flat = array();

					foreach ( starter_meta_all( 'post', $post->ID ) as $key => $value ) {
						$flat[ $key ] = $value;
					}

					/**
					 * Valori iniziali dei campi di un contenuto appena creato (es. righe predefinite di un ripetitore).
					 *
					 * @param array $flat Valori piatti.
					 * @param int   $id   Id dell'articolo.
					 */
					$flat = apply_filters( 'starter_meta_initial', $flat, $post->ID );

					wp_nonce_field( 'starter_meta_save', 'starter_meta_nonce' );
					echo '<input type="hidden" id="starter-meta-json" name="starter_meta_json" value="' . esc_attr( wp_json_encode( (object) $flat ) ) . '">';
					echo '<div id="starter-meta-root" class="starter-meta-root"></div>';
				},
				$group['name'],
				'normal',
				'high'
			);
		}
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$type = get_post_type() ? get_post_type() : ( isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		foreach ( starter_meta_registry() as $group ) {
			if ( 'post_type' !== $group['object'] || $type !== $group['name'] || ! post_type_exists( $type ) ) {
				continue;
			}

			wp_enqueue_media();
			wp_enqueue_style( 'wp-components' );
			wp_enqueue_style( 'wp-block-editor' );
			wp_enqueue_script(
				'starter-poc-meta',
				STARTER_POC_URL . 'build/meta.js',
				array( 'wp-element', 'wp-components', 'wp-block-editor', 'wp-data', 'wp-core-data', 'wp-api-fetch' ),
				(string) filemtime( STARTER_POC_DIR . 'build/meta.js' ),
				true
			);
			wp_localize_script(
				'starter-poc-meta',
				'starterMeta',
				array(
					'title'  => $group['title'],
					'fields' => starter_blocks_live_choices( $group['fields'] ),
				)
			);
			break;
		}
	}
);

add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( ! isset( $_POST['starter_meta_nonce'], $_POST['starter_meta_json'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['starter_meta_nonce'] ) ), 'starter_meta_save' ) ) {
			return;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$data = json_decode( wp_unslash( $_POST['starter_meta_json'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ripulito campo per campo da starter_meta_save().

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( starter_meta_registry() as $group ) {
			if ( 'post_type' === $group['object'] && $post->post_type === $group['name'] ) {
				starter_meta_save( 'post', (int) $post_id, $group['fields'], $data );
			}
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------ termini: campi nel modulo di modifica */

/* Un campo semplice nel modulo del termine (testo, email, numero, area di testo, scelta, foto). */
function starter_meta_term_input( array $field, $value ) {
	$name = 'starter_term[' . $field['name'] . ']';
	$id   = 'starter-term-' . $field['name'];
	$type = $field['type'] ?? 'text';

	if ( 'image' === $type ) {
		$src = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
		?>
		<div class="starter-term-image" data-target="<?php echo esc_attr( $id ); ?>">
			<img src="<?php echo esc_url( (string) $src ); ?>" alt="" style="max-width:96px;height:auto;display:<?php echo $src ? 'block' : 'none'; ?>;margin-bottom:8px">
			<input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
			<button type="button" class="button starter-term-image__pick"><?php esc_html_e( 'Scegli la foto', 'slug-theme' ); ?></button>
			<button type="button" class="button-link starter-term-image__clear"><?php esc_html_e( 'Togli', 'slug-theme' ); ?></button>
		</div>
		<?php
		return;
	}

	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4" class="large-text">' . esc_textarea( (string) $value ) . '</textarea>';
		return;
	}

	if ( 'select' === $type ) {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';

		foreach ( (array) ( $field['choices'] ?? array() ) as $key => $label ) {
			echo '<option value="' . esc_attr( (string) $key ) . '"' . selected( (string) $value, (string) $key, false ) . '>' . esc_html( (string) $label ) . '</option>';
		}

		echo '</select>';
		return;
	}

	$html_type = in_array( $type, array( 'email', 'number', 'url' ), true ) ? $type : 'text';

	echo '<input type="' . esc_attr( $html_type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text">';
}

add_action(
	'init',
	function () {
		foreach ( starter_meta_registry() as $group ) {
			if ( 'taxonomy' !== $group['object'] || ! taxonomy_exists( $group['name'] ) ) {
				continue;
			}

			$fields = $group['fields'];
			$tax    = $group['name'];

			add_action(
				$tax . '_add_form_fields',
				static function () use ( $fields, $group ) {
					wp_nonce_field( 'starter_term_fields', 'starter_term_nonce' );
					echo '<h3>' . esc_html( $group['title'] ) . '</h3>';

					foreach ( $fields as $field ) {
						if ( empty( $field['name'] ) ) {
							continue;
						}

						echo '<div class="form-field"><label for="starter-term-' . esc_attr( $field['name'] ) . '">' . esc_html( $field['label'] ?? $field['name'] ) . '</label>';
						starter_meta_term_input( $field, '' );
						echo '</div>';
					}
				}
			);

			add_action(
				$tax . '_edit_form_fields',
				static function ( $term ) use ( $fields, $group ) {
					wp_nonce_field( 'starter_term_fields', 'starter_term_nonce' );
					echo '<tr class="form-field"><th colspan="2"><h3 style="margin:0">' . esc_html( $group['title'] ) . '</h3></th></tr>';

					foreach ( $fields as $field ) {
						if ( empty( $field['name'] ) ) {
							continue;
						}

						echo '<tr class="form-field"><th scope="row"><label for="starter-term-' . esc_attr( $field['name'] ) . '">' . esc_html( $field['label'] ?? $field['name'] ) . '</label></th><td>';
						starter_meta_term_input( $field, get_term_meta( $term->term_id, $field['name'], true ) );
						echo '</td></tr>';
					}
				}
			);

			$save = static function ( $term_id ) use ( $fields, $tax ) {
				if ( ! isset( $_POST['starter_term_nonce'], $_POST['starter_term'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['starter_term_nonce'] ) ), 'starter_term_fields' ) ) {
					return;
				}

				$taxonomy = get_taxonomy( $tax );

				if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->edit_terms ) ) {
					return;
				}

				starter_meta_save( 'term', (int) $term_id, $fields, (array) wp_unslash( $_POST['starter_term'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ripulito campo per campo da starter_meta_save().
			};

			add_action( 'created_' . $tax, $save );
			add_action( 'edited_' . $tax, $save );
		}
	},
	20
);

/* Scelta della foto nel modulo dei termini (libreria media di WordPress). */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'term.php', 'edit-tags.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_add_inline_script(
			'media-editor',
			"jQuery(function($){\$(document).on('click','.starter-term-image__pick',function(e){e.preventDefault();var box=\$(this).closest('.starter-term-image');var frame=wp.media({title:'Foto',multiple:false,library:{type:'image'}});frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();box.find('input').val(a.id);box.find('img').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)).show();});frame.open();});\$(document).on('click','.starter-term-image__clear',function(e){e.preventDefault();var box=\$(this).closest('.starter-term-image');box.find('input').val('');box.find('img').hide();});});"
		);
	}
);
