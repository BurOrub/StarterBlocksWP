<?php
/**
 * Plugin Name: Starter Blocks
 * Description: Blocchi custom di Gutenberg per il tema Starter.
 * Version: 0.2.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: starter-blocks
 *
 * @package Starter_Blocks_POC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STARTER_POC_DIR', plugin_dir_path( __FILE__ ) );
define( 'STARTER_POC_URL', plugin_dir_url( __FILE__ ) );

require_once STARTER_POC_DIR . 'inc/engine.php';
require_once STARTER_POC_DIR . 'inc/meta.php';

/**
 * Righe del repeater "voci" dai dati salvati dal blocco (stesso formato di ACF: voci = numero di righe, voci_0_voci_titolo = valore).
 *
 * @param array $data Attributo "data" del blocco.
 * @return array[] Una riga per servizio, con le chiavi dei sottocampi (voci_id, voci_titolo…).
 */
function starter_poc_rows( $data ) {
	$data  = is_array( $data ) ? $data : array();
	$count = isset( $data['voci'] ) ? (int) $data['voci'] : 0;
	$names = array( 'voci_id', 'voci_icona', 'voci_occhiello', 'voci_titolo', 'voci_etichetta', 'voci_testo', 'voci_immagine', 'voci_elenco', 'voci_cta', 'voci_cta_stile' );
	$rows  = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$row = array();

		foreach ( $names as $name ) {
			$key         = 'voci_' . $i . '_' . $name;
			$row[ $name ] = isset( $data[ $key ] ) ? $data[ $key ] : ( '' );
		}

		$rows[] = $row;
	}

	return $rows;
}

/* Disegno del blocco sul sito e nell'anteprima dell'editor (stesso HTML del blocco). */
function starter_poc_render_servizi( $attributes, $content, $block ) {
	if ( ! function_exists( 'starter_icon' ) ) {
		return ''; // Serve il tema Starter.
	}

	$starter_items = starter_poc_rows( isset( $attributes['data'] ) ? $attributes['data'] : array() );
	$is_preview    = defined( 'REST_REQUEST' ) && REST_REQUEST; // Richiesta dell'anteprima nell'editor.

	ob_start();
	include STARTER_POC_DIR . 'templates/elenco-servizi.php';

	return (string) ob_get_clean();
}

/* Registrazione dei blocchi React. */
add_action(
	'init',
	function () {
		$json = json_decode( (string) file_get_contents( STARTER_POC_DIR . 'block.json' ), true );

		if ( ! is_array( $json ) ) {
			return;
		}

		$handle = 'starter-poc-elenco-servizi';

		wp_register_script(
			$handle,
			STARTER_POC_URL . 'build/index.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-server-side-render', 'wp-i18n' ),
			(string) filemtime( STARTER_POC_DIR . 'build/index.js' ),
			true
		);

		wp_localize_script(
			$handle,
			'starterPoc',
			array(
				'bgChoices'     => function_exists( 'starter_section_bg_choices' ) ? starter_section_bg_choices() : array( '' => '—' ),
				'buttonChoices' => function_exists( 'starter_button_style_choices' ) ? starter_button_style_choices() : array( '' => '—' ),
			)
		);

		$defs = array();

		foreach ( starter_blocks_registry() as $block ) {
			$defs[ $block['json']['name'] ] = array(
				'title'  => $block['json']['title'],
				'fields' => $block['fields'],
				'inner'  => $block['json']['starterInner'] ?? null,
			);
		}

		wp_localize_script( $handle, 'starterBlocksDefs', $defs );

		unset( $json['$schema'] );
		$json['render_callback']      = 'starter_poc_render_servizi';
		$json['editor_script_handles'] = array( $handle );

		register_block_type( $json['name'], $json );
	}
);
