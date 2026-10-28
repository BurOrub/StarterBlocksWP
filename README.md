# Starter Blocks

**English** · [Italiano](#italiano)

Content blocks for the [Starter Theme](https://github.com/BurOrub/StarterThemeWP), built as native WordPress blocks (React in the editor, PHP on the site). No other plugin needed.

- **Blocks**: *Elenco servizi* (side menu that follows the scroll + one card per service). More to come.
- **Editing**: side panel in the editor, or the pencil in the block toolbar: a wide window; changes are applied to the block only when you press *Save*.
- **Requires**: the Starter Theme (the blocks use its functions, styles and scripts).
- **Install**: download the latest [release](../../releases/latest) (`starter-blocks.zip`), upload it in *Plugins → Add New → Upload*, activate.
- **Build** (only to change the editor code): `sh build.sh` compiles `src/index.jsx` into `build/index.js` using the theme's `esbuild` (no `npm install` here).
- Block data keeps the format of the previous blocks, so existing pages keep working.

---

## Italiano

Blocchi di contenuto per lo [Starter Theme](https://github.com/BurOrub/StarterThemeWP), come blocchi nativi di WordPress (React nell'editor, PHP sul sito). Non serve nessun altro plugin.

- **Blocchi**: *Elenco servizi* (menu laterale che segue lo scroll + una scheda per servizio). Altri in arrivo.
- **Modifica**: pannello laterale nell'editor, oppure la matita nella barra del blocco: una finestra larga; le modifiche si applicano al blocco solo premendo *Salva*.
- **Richiede**: lo Starter Theme (i blocchi usano le sue funzioni, i suoi stili e i suoi script).
- **Installazione**: scarica l'ultima [release](../../releases/latest) (`starter-blocks.zip`), caricala da *Plugin → Aggiungi nuovo → Carica*, attiva.
- **Compilazione** (solo per cambiare il codice dell'editor): `sh build.sh` compila `src/index.jsx` in `build/index.js` con `esbuild` del tema (qui non serve `npm install`).
- I dati dei blocchi mantengono il formato dei blocchi precedenti, quindi le pagine già fatte continuano a funzionare.
