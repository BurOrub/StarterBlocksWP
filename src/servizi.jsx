/**
 * Blocco "Elenco servizi" (starter-theme/elenco-servizi), in React: ha preso il posto del blocco con lo stesso nome.
 *
 * Salva nell'attributo "data" lo stesso formato del blocco
 * (voci = numero di righe; voci_0_voci_titolo = valore; _voci_0_voci_titolo = chiave del campo),
 * quindi legge i contenuti già salvati e, volendo, si può tornare al blocco.
 * Il disegno (anteprima e sito) lo fa il PHP: templates/elenco-servizi.php.
 */
const { registerBlockType } = wp.blocks;
const { InspectorControls, BlockControls, useBlockProps, MediaUpload, MediaUploadCheck, LinkControl } = wp.blockEditor;
const { PanelBody, TextControl, TextareaControl, SelectControl, Button, Popover, SearchControl, Modal, ToolbarGroup, ToolbarButton } = wp.components;
const { useState, useEffect, Fragment } = wp.element;
const { useSelect } = wp.data;
const ServerSideRender = wp.serverSideRender;
import { toChoices, IconField, ImageField, LinkField } from './shared';

const BLOCK = 'starter-theme/elenco-servizi';
const FIELDS = [ 'id', 'icona', 'occhiello', 'titolo', 'etichetta', 'testo', 'immagine', 'elenco', 'cta', 'cta_stile' ];

/* Chiavi dei campi (si scrivono accanto ai valori, così il formato resta identico). */
const KEYS = {
	voci: 'field_ses_voci',
	voci_id: 'field_ses_id',
	voci_icona: 'field_ses_icona',
	voci_occhiello: 'field_ses_occhiello',
	voci_titolo: 'field_ses_titolo',
	voci_etichetta: 'field_ses_etichetta',
	voci_testo: 'field_ses_testo',
	voci_immagine: 'field_ses_immagine',
	voci_elenco: 'field_ses_elenco',
	voci_cta: 'field_ses_cta',
	voci_cta_stile: 'field_btn_fe28e255b4de',
	sfondo_sezione: 'field_group_starter_elenco_servizi_adv_bg',
	classi_custom: 'field_group_starter_elenco_servizi_adv_classes',
};

/* ------------------------------------------------------------------ dati: da "data" a righe e ritorno */

function readRows( data ) {
	const count = parseInt( data.voci, 10 ) || 0;
	const rows = [];

	for ( let i = 0; i < count; i++ ) {
		const row = {};
		FIELDS.forEach( ( f ) => {
			const v = data[ `voci_${ i }_voci_${ f }` ];
			row[ f ] = undefined === v || null === v ? '' : v;
		} );
		rows.push( row );
	}

	return rows;
}

function writeRows( data, rows ) {
	const next = {};

	// Tutto quello che non è una riga dei servizi resta com'è (sfondo, classi, chiavi sconosciute).
	Object.keys( data ).forEach( ( k ) => {
		if ( ! /^_?voci(_\d+_voci_[a-z_]+)?$/.test( k ) ) {
			next[ k ] = data[ k ];
		}
	} );

	next.voci = rows.length;
	next._voci = KEYS.voci;

	rows.forEach( ( row, i ) => {
		FIELDS.forEach( ( f ) => {
			const key = `voci_${ i }_voci_${ f }`;
			next[ key ] = row[ f ];
			next[ '_' + key ] = KEYS[ 'voci_' + f ];
		} );
	} );

	return next;
}


/* ------------------------------------------------------------------ campi di un servizio */

const NEW_ROW = { id: '', icona: 'key', occhiello: '', titolo: '', etichetta: '', testo: '', immagine: '', elenco: '', cta: '', cta_stile: '' };
const buttonChoices = () => toChoices( window.starterPoc && window.starterPoc.buttonChoices );
const bgChoices = () => toChoices( window.starterPoc && window.starterPoc.bgChoices );

/* I campi di un servizio: in colonna nella barra laterale, su due colonne (più larghi) nella finestra. */
function ServiceFields( { row, set, wide } ) {
	if ( ! wide ) {
		return (
			<Fragment>
				<div style={ { display: 'grid', gridTemplateColumns: '44px 1fr', gap: 12, alignItems: 'end' } }>
					<IconField value={ row.icona } onChange={ set( 'icona' ) } />
					<TextControl label="Ancora" value={ row.id } onChange={ set( 'id' ) } placeholder="vendita" __nextHasNoMarginBottom />
				</div>
				<div style={ { height: 12 } } />
				<TextControl label="Occhiello" value={ row.occhiello } onChange={ set( 'occhiello' ) } />
				<TextControl label="Titolo" value={ row.titolo } onChange={ set( 'titolo' ) } />
				<TextControl label="Etichetta nel menu" value={ row.etichetta } onChange={ set( 'etichetta' ) } />
				<TextareaControl label="Testo" rows={ 3 } value={ row.testo } onChange={ set( 'testo' ) } />
				<ImageField value={ row.immagine } onChange={ set( 'immagine' ) } />
				<TextareaControl label="Elenco con spunte" rows={ 5 } value={ row.elenco } onChange={ set( 'elenco' ) } />
				<LinkField value={ row.cta } onChange={ set( 'cta' ) } />
				<SelectControl label="Stile pulsante" value={ row.cta_stile } options={ buttonChoices() } onChange={ set( 'cta_stile' ) } />
			</Fragment>
		);
	}

	const full = { gridColumn: '1 / -1' };

	return (
		<div style={ { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '4px 24px' } }>
			<div style={ { display: 'grid', gridTemplateColumns: '44px 1fr', gap: 12, alignItems: 'end', marginBottom: 16 } }>
				<IconField value={ row.icona } onChange={ set( 'icona' ) } />
				<TextControl label="Ancora" value={ row.id } onChange={ set( 'id' ) } placeholder="vendita" __nextHasNoMarginBottom />
			</div>
			<TextControl label="Occhiello" value={ row.occhiello } onChange={ set( 'occhiello' ) } />
			<TextControl label="Titolo" value={ row.titolo } onChange={ set( 'titolo' ) } />
			<TextControl label="Etichetta nel menu" value={ row.etichetta } onChange={ set( 'etichetta' ) } />
			<div style={ full }><TextareaControl label="Testo" rows={ 4 } value={ row.testo } onChange={ set( 'testo' ) } /></div>
			<div style={ full }><TextareaControl label="Elenco con spunte (una voce per riga)" rows={ 6 } value={ row.elenco } onChange={ set( 'elenco' ) } /></div>
			<ImageField value={ row.immagine } onChange={ set( 'immagine' ) } />
			<div>
				<LinkField value={ row.cta } onChange={ set( 'cta' ) } />
				<SelectControl label="Stile pulsante" value={ row.cta_stile } options={ buttonChoices() } onChange={ set( 'cta_stile' ) } />
			</div>
		</div>
	);
}

function ServiceRow( { row, index, total, onChange, onMove, onRemove } ) {
	const set = ( field ) => ( value ) => onChange( { ...row, [ field ]: value } );
	const title = row.titolo || row.etichetta || `Servizio ${ index + 1 }`;

	return (
		<PanelBody title={ title } initialOpen={ 0 === index }>
			<ServiceFields row={ row } set={ set } />
			<div style={ { display: 'flex', gap: 8, marginTop: 8 } }>
				<Button variant="secondary" size="small" disabled={ 0 === index } onClick={ () => onMove( index, -1 ) }>↑ Su</Button>
				<Button variant="secondary" size="small" disabled={ index === total - 1 } onClick={ () => onMove( index, 1 ) }>↓ Giù</Button>
				<Button variant="tertiary" size="small" isDestructive onClick={ () => onRemove( index ) }>Elimina</Button>
			</div>
		</PanelBody>
	);
}

/* ------------------------------------------------------------------ finestra larga (la "matita") */

/*
 * Si lavora su una copia: l'anteprima nell'editor si aggiorna una volta sola, premendo "Salva"; "Annulla" scarta tutto.
 * A sinistra l'elenco dei servizi (più "Avanzate"), a destra i campi del servizio scelto su due colonne.
 */
function EditModal( { rows, data, onSave, onClose } ) {
	const [ draft, setDraft ] = useState( rows );
	const [ adv, setAdv ] = useState( { sfondo_sezione: data.sfondo_sezione || '', classi_custom: data.classi_custom || '' } );
	const [ sel, setSel ] = useState( 0 ); // Numero di riga, oppure 'adv'.

	const current = 'adv' === sel ? null : draft[ Math.min( sel, draft.length - 1 ) ];
	const index = 'adv' === sel ? -1 : Math.min( sel, draft.length - 1 );
	const setRow = ( field ) => ( value ) => setDraft( draft.map( ( r, i ) => ( i === index ? { ...r, [ field ]: value } : r ) ) );
	const labelOf = ( r, i ) => r.titolo || r.etichetta || `Servizio ${ i + 1 }`;

	const move = ( dir ) => {
		const next = draft.slice();
		[ next[ index ], next[ index + dir ] ] = [ next[ index + dir ], next[ index ] ];
		setDraft( next );
		setSel( index + dir );
	};

	return (
		<Modal title="Elenco servizi" onRequestClose={ onClose } size="large" style={ { width: 1100, maxWidth: '96vw' } } shouldCloseOnClickOutside={ false }>
			<div style={ { display: 'grid', gridTemplateColumns: '240px 1fr', gap: 28, minHeight: 440 } }>
				<div style={ { display: 'flex', flexDirection: 'column', gap: 4, borderRight: '1px solid #e0e0e0', paddingRight: 16 } }>
					{ draft.map( ( r, i ) => (
						<Button key={ i } variant={ i === index ? 'primary' : 'tertiary' } onClick={ () => setSel( i ) } style={ { justifyContent: 'flex-start', width: '100%', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' } }>
							{ labelOf( r, i ) }
						</Button>
					) ) }
					<Button variant="secondary" onClick={ () => { setDraft( [ ...draft, { ...NEW_ROW } ] ); setSel( draft.length ); } } style={ { marginTop: 8, justifyContent: 'center' } }>
						+ Aggiungi servizio
					</Button>
					<Button variant={ 'adv' === sel ? 'primary' : 'tertiary' } onClick={ () => setSel( 'adv' ) } style={ { marginTop: 16, justifyContent: 'flex-start' } }>Avanzate</Button>
				</div>
				<div>
					{ current && (
						<Fragment>
							<ServiceFields row={ current } set={ setRow } wide />
							<div style={ { display: 'flex', gap: 8, marginTop: 8 } }>
								<Button variant="secondary" size="small" disabled={ 0 === index } onClick={ () => move( -1 ) }>↑ Su</Button>
								<Button variant="secondary" size="small" disabled={ index === draft.length - 1 } onClick={ () => move( 1 ) }>↓ Giù</Button>
								<Button variant="tertiary" size="small" isDestructive onClick={ () => { setDraft( draft.filter( ( _, i ) => i !== index ) ); setSel( Math.max( 0, index - 1 ) ); } }>Elimina</Button>
							</div>
						</Fragment>
					) }
					{ 'adv' === sel && (
						<div style={ { maxWidth: 420 } }>
							<SelectControl label="Sfondo della sezione" value={ adv.sfondo_sezione } options={ bgChoices() } onChange={ ( v ) => setAdv( { ...adv, sfondo_sezione: v } ) } />
							<TextControl label="Classi CSS" value={ adv.classi_custom } placeholder="mt2 pb4 pb2-sma" onChange={ ( v ) => setAdv( { ...adv, classi_custom: v } ) } />
						</div>
					) }
					{ ! current && 'adv' !== sel && <p>Nessun servizio: aggiungine uno a sinistra.</p> }
				</div>
			</div>
			<div style={ { display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 24, paddingTop: 16, borderTop: '1px solid #e0e0e0' } }>
				<Button variant="tertiary" onClick={ onClose }>Annulla</Button>
				<Button variant="primary" onClick={ () => onSave( draft, adv ) }>Salva</Button>
			</div>
		</Modal>
	);
}

/* ------------------------------------------------------------------ blocco */

function Edit( { attributes, setAttributes } ) {
	const data = attributes.data && ! Array.isArray( attributes.data ) ? attributes.data : {};
	const rows = readRows( data );
	const blockProps = useBlockProps();
	const [ modalOpen, setModalOpen ] = useState( false );

	const save = ( nextRows ) => setAttributes( { data: writeRows( data, nextRows ) } );
	const saveField = ( key, value ) => setAttributes( { data: { ...data, [ key ]: value, [ '_' + key ]: KEYS[ key ] } } );

	const saveAll = ( nextRows, adv ) => {
		const next = writeRows( data, nextRows );
		[ 'sfondo_sezione', 'classi_custom' ].forEach( ( key ) => {
			next[ key ] = adv[ key ];
			next[ '_' + key ] = KEYS[ key ];
		} );
		setAttributes( { data: next } );
		setModalOpen( false );
	};

	const move = ( i, dir ) => {
		const next = rows.slice();
		const j = i + dir;
		[ next[ i ], next[ j ] ] = [ next[ j ], next[ i ] ];
		save( next );
	};

	return (
		<Fragment>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton icon="edit" label="Modifica contenuti" onClick={ () => setModalOpen( true ) } />
				</ToolbarGroup>
			</BlockControls>
			{ modalOpen && <EditModal rows={ rows } data={ data } onSave={ saveAll } onClose={ () => setModalOpen( false ) } /> }
			<InspectorControls>
				<PanelBody title="Servizi" initialOpen>
					<p style={ { margin: '0 0 12px', display: 'flex', gap: 8, flexWrap: 'wrap' } }>
						<Button variant="primary" onClick={ () => setModalOpen( true ) }>Modifica contenuti</Button>
						<Button variant="secondary" onClick={ () => save( [ ...rows, { ...NEW_ROW } ] ) }>Aggiungi servizio</Button>
					</p>
				</PanelBody>
				{ rows.map( ( row, i ) => (
					<ServiceRow
						key={ i }
						row={ row }
						index={ i }
						total={ rows.length }
						onChange={ ( r ) => save( rows.map( ( x, k ) => ( k === i ? r : x ) ) ) }
						onMove={ move }
						onRemove={ ( k ) => save( rows.filter( ( _, n ) => n !== k ) ) }
					/>
				) ) }
				<PanelBody title="Avanzate" initialOpen={ false }>
					<SelectControl label="Sfondo della sezione" value={ data.sfondo_sezione || '' } options={ bgChoices() } onChange={ ( v ) => saveField( 'sfondo_sezione', v ) } />
					<TextControl label="Classi CSS" value={ data.classi_custom || '' } placeholder="mt2 pb4 pb2-sma" onChange={ ( v ) => saveField( 'classi_custom', v ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender block={ BLOCK } attributes={ attributes } httpMethod="POST" />
			</div>
		</Fragment>
	);
}

registerBlockType( BLOCK, {
	edit: Edit,
	save: () => null, // Blocco dinamico: lo disegna il PHP.
} );
