/**
 * Motore dei blocchi: dall'elenco dei campi di un blocco (blocks/<nome>/fields.json, passato dal PHP in starterBlocksDefs)
 * disegna il pannello di modifica. Un blocco in più non richiede codice JavaScript: basta la cartella in blocks/.
 *
 * I valori si salvano nell'attributo "data" nel formato di ACF (campo = valore; ripetitore = numero di righe + nome_0_sottocampo;
 * _campo = chiave del campo), così le pagine già scritte si leggono senza conversioni.
 */
import { toChoices, IconField, ImageField, LinkField } from './shared';

const { registerBlockType } = wp.blocks;
const { InspectorControls, BlockControls, InnerBlocks, useBlockProps, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl, CheckboxControl, Button, SearchControl, Modal, ToolbarGroup, ToolbarButton } = wp.components;
const { useState, useEffect, Fragment } = wp.element;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;
const ServerSideRender = wp.serverSideRender;

const DEFS = window.starterBlocksDefs || {};
const SKIP = [ 'tab', 'message' ];
const WIDE = [ 'textarea', 'repeater', 'gallery', 'message', 'relationship' ];
const LABEL_STYLE = { fontSize: 11, textTransform: 'uppercase', marginBottom: 4 };

/* ------------------------------------------------------------------ dati: da "data" a valori e ritorno */

const isOn = ( v ) => true === v || 1 === v || '1' === v;

function defaultOf( field ) {
	if ( 'repeater' === field.type ) {
		return [];
	}
	if ( 'true_false' === field.type ) {
		return isOn( field.default_value );
	}
	if ( 'gallery' === field.type || 'relationship' === field.type || ( 'select' === field.type && field.multiple ) ) {
		return [];
	}
	return undefined === field.default_value || null === field.default_value ? '' : field.default_value;
}

export function readFields( fields, data, prefix = '' ) {
	const values = {};

	fields.forEach( ( f ) => {
		if ( ! f.name || SKIP.includes( f.type ) ) {
			return;
		}

		const key = prefix + f.name;

		if ( 'repeater' === f.type ) {
			const count = parseInt( data[ key ], 10 ) || 0;
			values[ f.name ] = Array.from( { length: count }, ( _, i ) => readFields( f.sub_fields || [], data, `${ key }_${ i }_` ) );
			return;
		}

		const raw = data[ key ];

		if ( undefined === raw || null === raw ) {
			values[ f.name ] = defaultOf( f );
		} else if ( 'true_false' === f.type ) {
			values[ f.name ] = isOn( raw );
		} else if ( 'gallery' === f.type || 'relationship' === f.type ) {
			values[ f.name ] = Array.isArray( raw ) ? raw : [];
		} else if ( 'image' === f.type && Array.isArray( raw ) ) {
			values[ f.name ] = raw[ 0 ] || ''; // Contenuti vecchi, salvati quando il campo era una galleria.
		} else {
			values[ f.name ] = raw;
		}
	} );

	return values;
}

function writeFields( fields, values, out, prefix = '' ) {
	fields.forEach( ( f ) => {
		if ( ! f.name || SKIP.includes( f.type ) ) {
			return;
		}

		const key = prefix + f.name;
		const v = values[ f.name ];

		out[ key ] = v;
		out[ '_' + key ] = f.key;

		if ( 'repeater' === f.type ) {
			const rows = Array.isArray( v ) ? v : [];
			out[ key ] = rows.length;
			rows.forEach( ( row, i ) => writeFields( f.sub_fields || [], row, out, `${ key }_${ i }_` ) );
		} else if ( 'true_false' === f.type ) {
			out[ key ] = v ? 1 : 0;
		}
	} );

	return out;
}

/* Salva i valori dei campi di primo livello senza toccare le altre chiavi di "data" (chiavi sconosciute, campi di un'altra versione). */
export function mergeData( fields, data, values ) {
	const names = fields.filter( ( f ) => f.name && ! SKIP.includes( f.type ) ).map( ( f ) => f.name.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) );
	const own = new RegExp( `^_?(${ names.join( '|' ) })(_\\d+_.*)?$` );
	const next = {};

	Object.keys( data ).forEach( ( k ) => {
		if ( ! own.test( k ) ) {
			next[ k ] = data[ k ];
		}
	} );

	return writeFields( fields, values, next );
}

/* ------------------------------------------------------------------ condizioni ("mostra solo se…") */

export function isVisible( field, siblings, values ) {
	const logic = field.conditional_logic;

	if ( ! Array.isArray( logic ) || ! logic.length ) {
		return true;
	}

	return logic.some( ( group ) =>
		group.every( ( rule ) => {
			const target = siblings.find( ( s ) => s.key === rule.field );

			if ( ! target ) {
				return true; // Condizione su un campo che non c'è: non nasconde niente.
			}

			let current = values[ target.name ];

			if ( 'true_false' === target.type ) {
				current = isOn( current ) ? '1' : '0';
			}

			const empty = undefined === current || null === current || '' === current || '0' === current && 'true_false' === target.type || ( Array.isArray( current ) && ! current.length );
			const list = Array.isArray( current ) ? current.map( String ) : [ String( current ) ];

			switch ( rule.operator ) {
				case '==empty':
					return empty;
				case '!=empty':
					return ! empty;
				case '!=':
					return ! list.includes( String( rule.value ) );
				case '==contains':
					return list.some( ( v ) => v.includes( String( rule.value ) ) );
				case '!=contains':
					return ! list.some( ( v ) => v.includes( String( rule.value ) ) );
				default: // '=='
					return list.includes( String( rule.value ) );
			}
		} )
	);
}

/* ------------------------------------------------------------------ campi speciali: galleria, file, tassonomia */

function GalleryField( { field, value, onChange, label } ) {
	const ids = ( Array.isArray( value ) ? value : [] ).map( ( v ) => parseInt( v, 10 ) ).filter( Boolean );
	const media = useSelect( ( select ) => ( ids.length ? select( 'core' ).getEntityRecords( 'postType', 'attachment', { include: ids, per_page: 100 } ) : [] ), [ ids.join( ',' ) ] ) || [];
	const thumbOf = ( id ) => {
		const m = media.find( ( x ) => x.id === id );
		return m && m.media_details && m.media_details.sizes && m.media_details.sizes.thumbnail ? m.media_details.sizes.thumbnail.source_url : m && m.source_url;
	};
	const move = ( i, dir ) => {
		const next = ids.slice();
		[ next[ i ], next[ i + dir ] ] = [ next[ i + dir ], next[ i ] ];
		onChange( next );
	};

	return (
		<div style={ { marginBottom: 16 } }>
			<div style={ LABEL_STYLE }>{ label }</div>
			<div style={ { display: 'flex', flexWrap: 'wrap', gap: 8, marginBottom: 8 } }>
				{ ids.map( ( id, i ) => (
					<div key={ id + '-' + i } style={ { width: 84, textAlign: 'center' } }>
						{ thumbOf( id ) ? <img src={ thumbOf( id ) } alt="" style={ { width: 84, height: 64, objectFit: 'cover', borderRadius: 4, display: 'block' } } /> : <div style={ { width: 84, height: 64, background: '#eee', borderRadius: 4, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 11 } }>{ media.find( ( x ) => x.id === id ) ? 'video' : '' }</div> }
						<div style={ { display: 'flex', justifyContent: 'center' } }>
							<Button size="small" disabled={ 0 === i } onClick={ () => move( i, -1 ) } label="Sposta prima">←</Button>
							<Button size="small" isDestructive onClick={ () => onChange( ids.filter( ( _, n ) => n !== i ) ) } label="Togli">✕</Button>
							<Button size="small" disabled={ i === ids.length - 1 } onClick={ () => move( i, 1 ) } label="Sposta dopo">→</Button>
						</div>
					</div>
				) ) }
			</div>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ 'slide' === field.name ? [ 'image', 'video' ] : [ 'image' ] }
					multiple="add"
					gallery
					value={ ids }
					onSelect={ ( items ) => onChange( items.map( ( m ) => m.id ) ) }
					render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ ids.length ? 'Modifica la selezione' : 'Scegli le foto' }</Button> }
				/>
			</MediaUploadCheck>
		</div>
	);
}

function FileField( { value, onChange, label } ) {
	const id = parseInt( value, 10 ) || 0;
	const media = useSelect( ( select ) => ( id ? select( 'core' ).getMedia( id ) : null ), [ id ] );
	const name = media && ( media.source_url || '' ).split( '/' ).pop();

	return (
		<div style={ { marginBottom: 16 } }>
			<div style={ LABEL_STYLE }>{ label }</div>
			<MediaUploadCheck>
				<MediaUpload
					value={ id }
					onSelect={ ( m ) => onChange( m.id ) }
					render={ ( { open } ) => (
						<div style={ { display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' } }>
							{ id ? <span style={ { fontSize: 12, wordBreak: 'break-all' } }>{ name || `#${ id }` }</span> : null }
							<Button variant="secondary" onClick={ open }>{ id ? 'Cambia' : 'Scegli il file' }</Button>
							{ id ? <Button variant="link" isDestructive onClick={ () => onChange( '' ) }>Togli</Button> : null }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

function TaxonomyField( { field, value, onChange } ) {
	const terms = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'taxonomy', field.taxonomy, { per_page: 100 } ), [ field.taxonomy ] ) || [];
	const multi = 'multi_select' === field.field_type || 'checkbox' === field.field_type;

	if ( multi ) {
		const current = ( Array.isArray( value ) ? value : [] ).map( Number );

		return (
			<div style={ { marginBottom: 16 } }>
				<div style={ LABEL_STYLE }>{ field.label }</div>
				{ terms.map( ( t ) => (
					<CheckboxControl
						key={ t.id }
						label={ t.name }
						checked={ current.includes( t.id ) }
						onChange={ ( on ) => onChange( on ? [ ...current, t.id ] : current.filter( ( x ) => x !== t.id ) ) }
						__nextHasNoMarginBottom
					/>
				) ) }
			</div>
		);
	}

	return (
		<SelectControl
			label={ field.label }
			value={ String( value || '' ) }
			options={ [ { value: '', label: '—' }, ...terms.map( ( t ) => ( { value: String( t.id ), label: t.name } ) ) ] }
			onChange={ ( v ) => onChange( v ? parseInt( v, 10 ) : '' ) }
		/>
	);
}

/* Elenco di contenuti scelti a mano (es. prodotti): si cerca per titolo e si aggiunge; l'ordine si cambia con le frecce. */
function RelationshipField( { field, value, onChange } ) {
	const ids = ( Array.isArray( value ) ? value : [] ).map( ( v ) => parseInt( v, 10 ) ).filter( Boolean );
	const type = ( Array.isArray( field.post_type ) ? field.post_type[ 0 ] : field.post_type ) || 'post';
	const [ search, setSearch ] = useState( '' );
	const [ found, setFound ] = useState( [] );
	const [ titles, setTitles ] = useState( {} );
	const postInput = document.getElementById( 'post_ID' );
	const editor = wp.data.select( 'core/editor' );
	const currentId = ( postInput && parseInt( postInput.value, 10 ) ) || ( editor && editor.getCurrentPostId && editor.getCurrentPostId() ) || 0;

	const rememberAll = ( list ) => setTitles( ( t ) => ( { ...t, ...Object.fromEntries( list.map( ( p ) => [ p.id, p.title && p.title.rendered ? p.title.rendered : `#${ p.id }` ] ) ) } ) );

	useEffect( () => {
		if ( ids.length ) {
			apiFetch( { path: `/wp/v2/${ type }?include=${ ids.join( ',' ) }&per_page=100&_fields=id,title&context=edit` } ).then( rememberAll ).catch( () => {} );
		}
	}, [ ids.join( ',' ) ] );

	useEffect( () => {
		const t = setTimeout( () => {
			apiFetch( { path: `/wp/v2/${ type }?search=${ encodeURIComponent( search ) }&per_page=12&exclude=${ currentId }&_fields=id,title&context=edit` } ).then( ( list ) => { rememberAll( list ); setFound( list ); } ).catch( () => setFound( [] ) );
		}, 250 );
		return () => clearTimeout( t );
	}, [ search ] );

	const move = ( i, dir ) => {
		const next = ids.slice();
		[ next[ i ], next[ i + dir ] ] = [ next[ i + dir ], next[ i ] ];
		onChange( next );
	};

	return (
		<div style={ { marginBottom: 16 } }>
			<div style={ LABEL_STYLE }>{ field.label }</div>
			{ ids.map( ( id, i ) => (
				<div key={ id } style={ { display: 'flex', alignItems: 'center', gap: 4, border: '1px solid #ddd', borderRadius: 4, padding: '2px 8px', marginBottom: 4 } }>
					<span style={ { flex: 1 } } dangerouslySetInnerHTML={ { __html: titles[ id ] || `#${ id }` } } />
					<Button size="small" disabled={ 0 === i } onClick={ () => move( i, -1 ) } label="Su">↑</Button>
					<Button size="small" disabled={ i === ids.length - 1 } onClick={ () => move( i, 1 ) } label="Giù">↓</Button>
					<Button size="small" isDestructive label="Togli" onClick={ () => onChange( ids.filter( ( x ) => x !== id ) ) }>✕</Button>
				</div>
			) ) }
			<SearchControl value={ search } onChange={ setSearch } label="Cerca" __nextHasNoMarginBottom />
			<div style={ { maxHeight: 160, overflow: 'auto', marginTop: 4 } }>
				{ found.filter( ( p ) => ! ids.includes( p.id ) ).map( ( p ) => (
					<Button key={ p.id } variant="tertiary" style={ { display: 'block', width: '100%', textAlign: 'left' } } onClick={ () => onChange( [ ...ids, p.id ] ) }>
						+ <span dangerouslySetInnerHTML={ { __html: p.title && p.title.rendered ? p.title.rendered : `#${ p.id }` } } />
					</Button>
				) ) }
			</div>
		</div>
	);
}

/* ------------------------------------------------------------------ un campo */

function Field( { field, value, onChange } ) {
	const label = field.label;
	const options = toChoices( Array.isArray( field.choices ) ? Object.fromEntries( field.choices.map( ( c ) => [ c, c ] ) ) : field.choices );

	switch ( field.type ) {
		case 'text':
		case 'url':
		case 'email':
			return <TextControl label={ label } type={ 'text' === field.type ? 'text' : field.type } value={ value } placeholder={ field.placeholder } onChange={ onChange } />;
		case 'textarea':
			return <TextareaControl label={ label } rows={ parseInt( field.rows, 10 ) || 4 } value={ value } placeholder={ field.placeholder } onChange={ onChange } />;
		case 'number':
			return <TextControl label={ label } type="number" value={ value } min={ field.min } max={ field.max } step={ field.step } onChange={ onChange } />;
		case 'select':
			if ( field.multiple ) {
				const current = Array.isArray( value ) ? value : [];
				return (
					<div style={ { marginBottom: 16 } }>
						<div style={ LABEL_STYLE }>{ label }</div>
						{ options.map( ( o ) => (
							<CheckboxControl key={ o.value } label={ o.label } checked={ current.includes( o.value ) } onChange={ ( on ) => onChange( on ? [ ...current, o.value ] : current.filter( ( x ) => x !== o.value ) ) } __nextHasNoMarginBottom />
						) ) }
					</div>
				);
			}
			return <SelectControl label={ label } value={ String( value ) } options={ options } onChange={ onChange } />;
		case 'button_group':
			return (
				<div style={ { marginBottom: 16 } }>
					<div style={ LABEL_STYLE }>{ label }</div>
					<div style={ { display: 'flex', gap: 4, flexWrap: 'wrap' } }>
						{ options.map( ( o ) => (
							<Button key={ o.value } variant={ String( value ) === String( o.value ) ? 'primary' : 'secondary' } onClick={ () => onChange( o.value ) }>{ o.label }</Button>
						) ) }
					</div>
				</div>
			);
		case 'true_false':
			return <div style={ { marginBottom: 16 } }><ToggleControl label={ label } checked={ !! value } onChange={ onChange } __nextHasNoMarginBottom /></div>;
		case 'link':
			return <LinkField label={ label } value={ value } onChange={ onChange } />;
		case 'image':
			return <ImageField label={ label } value={ value } onChange={ onChange } />;
		case 'file':
			return <FileField label={ label } value={ value } onChange={ onChange } />;
		case 'gallery':
			return <GalleryField field={ field } label={ label } value={ value } onChange={ onChange } />;
		case 'starter_icon':
			return <IconField label={ label } value={ value } onChange={ onChange } />;
		case 'relationship':
			return <RelationshipField field={ field } value={ value } onChange={ onChange } />;
		case 'taxonomy':
			return <TaxonomyField field={ field } value={ value } onChange={ onChange } />;
		case 'repeater':
			return <RepeaterField field={ field } rows={ value } onChange={ onChange } />;
		case 'message':
			return <p style={ { margin: '0 0 16px', color: '#555' } }>{ field.message || label }</p>;
		default:
			return <TextControl label={ label } value={ value } onChange={ onChange } />;
	}
}

/* Elenco di campi su due colonne (i lunghi occupano tutta la riga). */
export function FieldGrid( { fields, values, onChange, columns = 2, scope } ) {
	return (
		<div style={ { display: 'grid', gridTemplateColumns: `repeat(${ columns }, minmax(0, 1fr))`, gap: '8px 24px' } }>
			{ fields.filter( ( f ) => f.name && ! SKIP.includes( f.type ) || 'message' === f.type ).map( ( f, i ) =>
				isVisible( f, scope || fields, values ) ? (
					<div key={ f.key || f.name || i } style={ { paddingBottom: 8, ...( WIDE.includes( f.type ) || 1 === columns ? { gridColumn: '1 / -1' } : {} ) } }>
						<Field field={ f } value={ values[ f.name ] } onChange={ ( v ) => onChange( { ...values, [ f.name ]: v } ) } />
					</div>
				) : null
			) }
		</div>
	);
}

/* Ripetitore: righe che si aprono una alla volta, con su/giù/elimina e "+ Aggiungi". */
function RepeaterField( { field, rows, onChange } ) {
	const list = Array.isArray( rows ) ? rows : [];
	const sub = field.sub_fields || [];
	const [ open, setOpen ] = useState( 0 );
	const emptyRow = () => readFields( sub, {} );
	const titleOf = ( row, i ) => {
		const text = sub.filter( ( s ) => [ 'text', 'textarea' ].includes( s.type ) && ! /_tag$/.test( s.name ) ).map( ( s ) => String( row[ s.name ] || '' ).trim() ).find( Boolean );
		return text ? text.slice( 0, 60 ) : `${ field.label } ${ i + 1 }`;
	};
	const move = ( i, dir ) => {
		const next = list.slice();
		[ next[ i ], next[ i + dir ] ] = [ next[ i + dir ], next[ i ] ];
		onChange( next );
		setOpen( i + dir );
	};

	return (
		<div style={ { margin: '8px 0 16px' } }>
			<div style={ { ...LABEL_STYLE, marginBottom: 8 } }>{ field.label }</div>
			{ list.map( ( row, i ) => (
				<div key={ i } style={ { border: '1px solid #ddd', borderRadius: 4, marginBottom: 8 } }>
					<div style={ { display: 'flex', alignItems: 'center', gap: 4, padding: '4px 8px', background: i === open ? '#f0f0f1' : 'transparent' } }>
						<Button variant="tertiary" onClick={ () => setOpen( i === open ? -1 : i ) } style={ { flex: 1, justifyContent: 'flex-start', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } }>
							{ i === open ? '▾ ' : '▸ ' }{ titleOf( row, i ) }
						</Button>
						<Button size="small" disabled={ 0 === i } onClick={ () => move( i, -1 ) } label="Su">↑</Button>
						<Button size="small" disabled={ i === list.length - 1 } onClick={ () => move( i, 1 ) } label="Giù">↓</Button>
						<Button size="small" isDestructive label="Elimina" onClick={ () => { onChange( list.filter( ( _, n ) => n !== i ) ); setOpen( Math.max( 0, i - 1 ) ); } }>✕</Button>
					</div>
					{ i === open && (
						<div style={ { padding: '14px 14px 10px' } }>
							<FieldGrid fields={ sub } values={ row } onChange={ ( r ) => onChange( list.map( ( x, n ) => ( n === i ? r : x ) ) ) } />
						</div>
					) }
				</div>
			) ) }
			{ ! ( parseInt( field.max, 10 ) && list.length >= parseInt( field.max, 10 ) ) && (
				<Button variant="secondary" onClick={ () => { onChange( [ ...list, emptyRow() ] ); setOpen( list.length ); } }>+ { field.button_label || 'Aggiungi' }</Button>
			) }
		</div>
	);
}

/* ------------------------------------------------------------------ schede ("tab") e finestra larga */

/* Divide i campi nelle schede: il primo gruppo prima di un "tab" si chiama Contenuto. */
export function splitTabs( fields ) {
	const tabs = [];
	let current = null;

	fields.forEach( ( f ) => {
		if ( 'tab' === f.type ) {
			current = { label: f.label || 'Contenuto', fields: [] };
			tabs.push( current );
			return;
		}
		if ( ! current ) {
			current = { label: 'Contenuto', fields: [] };
			tabs.push( current );
		}
		current.fields.push( f );
	} );

	return tabs.filter( ( t ) => t.fields.length );
}

/* Si lavora su una copia: l'anteprima si aggiorna una volta sola, premendo "Salva"; "Annulla" scarta tutto. */
export function EditModal( { title, fields, initial, onSave, onClose } ) {
	const [ values, setValues ] = useState( initial );
	const tabs = splitTabs( fields );
	const [ tab, setTab ] = useState( 0 );
	const current = tabs[ Math.min( tab, tabs.length - 1 ) ];

	return (
		<Modal title={ title } onRequestClose={ onClose } size="large" style={ { width: 1000, maxWidth: '96vw' } } shouldCloseOnClickOutside={ false }>
			<div style={ { display: 'grid', gridTemplateColumns: tabs.length > 1 ? '160px 1fr' : '1fr', gap: 28, minHeight: 360 } }>
				{ tabs.length > 1 && (
					<div style={ { display: 'flex', flexDirection: 'column', gap: 4, borderRight: '1px solid #e0e0e0', paddingRight: 16 } }>
						{ tabs.map( ( t, i ) => (
							<Button key={ i } variant={ current === t ? 'primary' : 'tertiary' } onClick={ () => setTab( i ) } style={ { justifyContent: 'flex-start' } }>{ t.label }</Button>
						) ) }
					</div>
				) }
				<div>{ current && <FieldGrid fields={ current.fields } scope={ fields } values={ values } onChange={ setValues } /> }</div>
			</div>
			<div style={ { display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 24, paddingTop: 16, borderTop: '1px solid #e0e0e0' } }>
				<Button variant="tertiary" onClick={ onClose }>Annulla</Button>
				<Button variant="primary" onClick={ () => onSave( values ) }>Salva</Button>
			</div>
		</Modal>
	);
}

/* ------------------------------------------------------------------ blocco */

export function makeEdit( name, def ) {
	const fields = def.fields;
	const tabs = splitTabs( fields );
	const advanced = tabs.length > 1 && 'Avanzate' === tabs[ tabs.length - 1 ].label ? tabs[ tabs.length - 1 ] : null;

	return function Edit( { attributes, setAttributes } ) {
		const data = attributes.data && ! Array.isArray( attributes.data ) ? attributes.data : {};
		const values = readFields( fields, data );
		const blockProps = useBlockProps();
		const [ modalOpen, setModalOpen ] = useState( false );
		const save = ( next ) => setAttributes( { data: mergeData( fields, data, next ) } );

		return (
			<Fragment>
				<BlockControls>
					<ToolbarGroup>
						<ToolbarButton icon="edit" label="Modifica contenuti" onClick={ () => setModalOpen( true ) } />
					</ToolbarGroup>
				</BlockControls>
				{ modalOpen && (
					<EditModal
						title={ def.title }
						fields={ fields }
						initial={ values }
						onSave={ ( next ) => { save( next ); setModalOpen( false ); } }
						onClose={ () => setModalOpen( false ) }
					/>
				) }
				<InspectorControls>
					<PanelBody title={ def.title } initialOpen>
						<Button variant="primary" onClick={ () => setModalOpen( true ) }>Modifica contenuti</Button>
					</PanelBody>
					{ advanced && (
						<PanelBody title="Avanzate" initialOpen={ false }>
							<FieldGrid fields={ advanced.fields } scope={ fields } values={ values } columns={ 1 } onChange={ save } />
						</PanelBody>
					) }
				</InspectorControls>
				<div { ...blockProps }>
					<ServerSideRender block={ name } attributes={ attributes } httpMethod="POST" />
					{ def.inner && (
						<div style={ { border: '1px dashed #c3c4c7', padding: 16, margin: 16 } }>
							<div style={ LABEL_STYLE }>Testo della sezione</div>
							<InnerBlocks template={ def.inner.template } />
						</div>
					) }
				</div>
			</Fragment>
		);
	};
}

/* Campi in una pagina (senza finestra): schede in alto e griglia dei campi. Usato dai riquadri degli articoli (src/meta.jsx). */
export function InlineFields( { fields, initial, onChange } ) {
	const [ values, setValues ] = useState( initial );
	const tabs = splitTabs( fields );
	const [ tab, setTab ] = useState( 0 );
	const current = tabs[ Math.min( tab, tabs.length - 1 ) ];
	const change = ( next ) => {
		setValues( next );
		onChange( next );
	};

	return (
		<div>
			{ tabs.length > 1 && (
				<div style={ { display: 'flex', gap: 4, flexWrap: 'wrap', borderBottom: '1px solid #e0e0e0', marginBottom: 16, paddingBottom: 8 } }>
					{ tabs.map( ( t, i ) => (
						<Button key={ i } variant={ current === t ? 'primary' : 'tertiary' } onClick={ () => setTab( i ) }>{ t.label }</Button>
					) ) }
				</div>
			) }
			{ current && <FieldGrid fields={ current.fields } scope={ fields } values={ values } onChange={ change } /> }
		</div>
	);
}
