/**
 * Riquadro dei campi di un contenuto (Portfolio, Immobile) nella schermata di modifica: schede e campi disegnati dal motore, 
 * I valori vanno in un campo nascosto (JSON) che il PHP salva nel meta (inc/meta.php).
 */
import { readFields, mergeData, InlineFields } from './engine';

const { render } = wp.element;

const root = document.getElementById( 'starter-meta-root' );
const def = window.starterMeta;

if ( root && def ) {
	const input = document.getElementById( 'starter-meta-json' );
	let flat = {};

	try {
		flat = JSON.parse( input.value || '{}' ) || {};
	} catch ( e ) {
		flat = {};
	}

	render(
		<InlineFields
			fields={ def.fields }
			initial={ readFields( def.fields, flat ) }
			onChange={ ( values ) => {
				input.value = JSON.stringify( mergeData( def.fields, flat, values ) );
			} }
		/>,
		root
	);
}
