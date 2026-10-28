/* Registra nell'editor i blocchi del motore (blocks/<nome>/, passati dal PHP in starterBlocksDefs). */
import { makeEdit } from './engine';

const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;

const DEFS = window.starterBlocksDefs || {};

Object.keys( DEFS ).forEach( ( name ) => {
	registerBlockType( name, {
		edit: makeEdit( name, DEFS[ name ] ),
		save: DEFS[ name ].inner ? () => <InnerBlocks.Content /> : () => null, // Blocchi dinamici: li disegna il PHP (con i blocchi interni, se ci sono).
	} );
} );
