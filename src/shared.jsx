/* Pezzi condivisi dai blocchi: icone, foto, link. */
const { InspectorControls, MediaUpload, MediaUploadCheck, LinkControl } = wp.blockEditor;
const { Button, Popover, SearchControl } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;
const apiFetch = wp.apiFetch;

/* Scelte di un menu: valore => etichetta. Le scelte raggruppate (gruppo => { valore => etichetta }) diventano un'intestazione non selezionabile seguita dalle sue voci. */
export const toChoices = ( obj ) =>
	Object.keys( obj || {} ).flatMap( ( value ) => {
		const label = obj[ value ];

		if ( label && 'object' === typeof label ) {
			return [ { value: '__group_' + value, label: value, disabled: true }, ...Object.keys( label ).map( ( v ) => ( { value: v, label: '\u00a0\u00a0' + label[ v ] } ) ) ];
		}

		return [ { value, label } ];
	} );

/* ------------------------------------------------------------------ icone (libreria del tema via REST) */

let iconLibrary = null;
let iconRequest = null;

export function loadIcons() {
	if ( iconLibrary ) {
		return Promise.resolve( iconLibrary );
	}

	if ( ! iconRequest ) {
		iconRequest = apiFetch( { path: '/starter/v1/icons' } ).then( ( lib ) => {
			iconLibrary = lib;
			return lib;
		} );
	}

	return iconRequest;
}

export function Icon( { name, size = 22 } ) {
	const markup = iconLibrary && iconLibrary.icons ? iconLibrary.icons[ name ] : '';

	if ( ! markup || ( iconLibrary.svgs && iconLibrary.svgs[ name ] ) ) {
		return <span style={ { fontSize: 11, opacity: 0.7 } }>{ name || '—' }</span>;
	}

	return (
		<svg
			width={ size }
			height={ size }
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.8"
			strokeLinecap="round"
			strokeLinejoin="round"
			style={ { fill: 'none' } } /* I pulsanti di WordPress riempiono gli svg: le icone sono a contorno. */
			dangerouslySetInnerHTML={ { __html: markup } }
		/>
	);
}

export function IconField( { value, onChange, label = 'Icona' } ) {
	const [ open, setOpen ] = useState( false );
	const [ search, setSearch ] = useState( '' );
	const [ , refresh ] = useState( 0 );

	useEffect( () => {
		loadIcons().then( () => refresh( ( n ) => n + 1 ) );
	}, [] );

	const names = iconLibrary ? Object.keys( iconLibrary.icons ) : [];
	const q = search.trim().toLowerCase();
	const found = ( q ? names.filter( ( n ) => n.includes( q ) || ( iconLibrary.keywords[ n ] || '' ).toLowerCase().includes( q ) ) : names ).slice( 0, 48 );

	return (
		<div className="starter-poc-icon">
			<div style={ { fontSize: 11, textTransform: 'uppercase', marginBottom: 4 } }>{ label }</div>
			<Button variant="secondary" onClick={ () => setOpen( ! open ) } style={ { width: 44, height: 44, justifyContent: 'center' } }>
				<Icon name={ value || 'key' } />
			</Button>
			{ open && (
				<Popover onClose={ () => setOpen( false ) } placement="left-start" focusOnMount="firstElement">
					<div style={ { padding: 12, width: 280 } }>
						<SearchControl value={ search } onChange={ setSearch } label="Cerca icona" __nextHasNoMarginBottom />
						<div style={ { display: 'grid', gridTemplateColumns: 'repeat(6, 1fr)', gap: 4, marginTop: 8, maxHeight: 240, overflow: 'auto' } }>
							{ found.map( ( n ) => (
								<Button
									key={ n }
									variant={ n === value ? 'primary' : 'tertiary' }
									label={ n }
									showTooltip
									onClick={ () => {
										onChange( n );
										setOpen( false );
									} }
									style={ { justifyContent: 'center', padding: 4 } }
								>
									<Icon name={ n } size={ 20 } />
								</Button>
							) ) }
						</div>
					</div>
				</Popover>
			) }
		</div>
	);
}

/* ------------------------------------------------------------------ foto e link */

export function ImageField( { value, onChange, label = 'Foto' } ) {
	const id = parseInt( value, 10 ) || 0;
	const media = useSelect( ( select ) => ( id ? select( 'core' ).getMedia( id ) : null ), [ id ] );
	const thumb = media && media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail ? media.media_details.sizes.thumbnail.source_url : media && media.source_url;

	return (
		<div style={ { marginBottom: 16 } }>
			<div style={ { fontSize: 11, textTransform: 'uppercase', marginBottom: 4 } }>{ label }</div>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ id }
					onSelect={ ( m ) => onChange( m.id ) }
					render={ ( { open } ) => (
						<div style={ { display: 'flex', gap: 8, alignItems: 'center' } }>
							{ thumb && <img src={ thumb } alt="" style={ { width: 64, height: 64, objectFit: 'cover', borderRadius: 4 } } /> }
							<Button variant="secondary" onClick={ open }>{ id ? 'Cambia' : 'Scegli foto' }</Button>
							{ id ? <Button variant="link" isDestructive onClick={ () => onChange( '' ) }>Togli</Button> : null }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

export function LinkField( { value, onChange, label = 'Pulsante' } ) {
	const [ open, setOpen ] = useState( false );
	const current = value && value.url ? { url: value.url, title: value.title, opensInNewTab: '_blank' === value.target } : null;

	return (
		<div style={ { marginBottom: 16 } }>
			<div style={ { fontSize: 11, textTransform: 'uppercase', marginBottom: 4 } }>{ label }</div>
			<div style={ { display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' } }>
				<Button variant="secondary" onClick={ () => setOpen( ! open ) }>
					{ current ? ( current.title || current.url ) : 'Aggiungi link' }
				</Button>
				{ current ? <Button variant="link" isDestructive onClick={ () => onChange( '' ) }>Togli</Button> : null }
			</div>
			{ open && (
				<Popover onClose={ () => setOpen( false ) } placement="left-start" focusOnMount="firstElement">
					<LinkControl
						value={ current }
						settings={ [ { id: 'opensInNewTab', title: 'Apri in una nuova scheda' } ] }
						onChange={ ( v ) => onChange( v && v.url ? { title: v.title || '', url: v.url, target: v.opensInNewTab ? '_blank' : '' } : '' ) }
						onRemove={ () => {
							onChange( '' );
							setOpen( false );
						} }
					/>
				</Popover>
			) }
		</div>
	);
}
