(() => {
  // src/index.jsx
  var { registerBlockType } = wp.blocks;
  var { InspectorControls, BlockControls, useBlockProps, MediaUpload, MediaUploadCheck, LinkControl } = wp.blockEditor;
  var { PanelBody, TextControl, TextareaControl, SelectControl, Button, Popover, SearchControl, Modal, ToolbarGroup, ToolbarButton } = wp.components;
  var { useState, useEffect, Fragment } = wp.element;
  var { useSelect } = wp.data;
  var ServerSideRender = wp.serverSideRender;
  var apiFetch = wp.apiFetch;
  var BLOCK = "starter-theme/elenco-servizi";
  var FIELDS = ["id", "icona", "occhiello", "titolo", "etichetta", "testo", "immagine", "elenco", "cta", "cta_stile"];
  var KEYS = {
    voci: "field_ses_voci",
    voci_id: "field_ses_id",
    voci_icona: "field_ses_icona",
    voci_occhiello: "field_ses_occhiello",
    voci_titolo: "field_ses_titolo",
    voci_etichetta: "field_ses_etichetta",
    voci_testo: "field_ses_testo",
    voci_immagine: "field_ses_immagine",
    voci_elenco: "field_ses_elenco",
    voci_cta: "field_ses_cta",
    voci_cta_stile: "field_btn_fe28e255b4de",
    sfondo_sezione: "field_group_starter_elenco_servizi_adv_bg",
    classi_custom: "field_group_starter_elenco_servizi_adv_classes"
  };
  function readRows(data) {
    const count = parseInt(data.voci, 10) || 0;
    const rows = [];
    for (let i = 0; i < count; i++) {
      const row = {};
      FIELDS.forEach((f) => {
        const v = data[`voci_${i}_voci_${f}`];
        row[f] = void 0 === v || null === v ? "" : v;
      });
      rows.push(row);
    }
    return rows;
  }
  function writeRows(data, rows) {
    const next = {};
    Object.keys(data).forEach((k) => {
      if (!/^_?voci(_\d+_voci_[a-z_]+)?$/.test(k)) {
        next[k] = data[k];
      }
    });
    next.voci = rows.length;
    next._voci = KEYS.voci;
    rows.forEach((row, i) => {
      FIELDS.forEach((f) => {
        const key = `voci_${i}_voci_${f}`;
        next[key] = row[f];
        next["_" + key] = KEYS["voci_" + f];
      });
    });
    return next;
  }
  var toChoices = (obj) => Object.keys(obj || {}).map((value) => ({ value, label: obj[value] }));
  var iconLibrary = null;
  var iconRequest = null;
  function loadIcons() {
    if (iconLibrary) {
      return Promise.resolve(iconLibrary);
    }
    if (!iconRequest) {
      iconRequest = apiFetch({ path: "/starter/v1/icons" }).then((lib) => {
        iconLibrary = lib;
        return lib;
      });
    }
    return iconRequest;
  }
  function Icon({ name, size = 22 }) {
    const markup = iconLibrary && iconLibrary.icons ? iconLibrary.icons[name] : "";
    if (!markup || iconLibrary.svgs && iconLibrary.svgs[name]) {
      return /* @__PURE__ */ wp.element.createElement("span", { style: { fontSize: 11, opacity: 0.7 } }, name || "\u2014");
    }
    return /* @__PURE__ */ wp.element.createElement(
      "svg",
      {
        width: size,
        height: size,
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        strokeWidth: "1.8",
        strokeLinecap: "round",
        strokeLinejoin: "round",
        style: { fill: "none" },
        dangerouslySetInnerHTML: { __html: markup }
      }
    );
  }
  function IconField({ value, onChange }) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");
    const [, refresh] = useState(0);
    useEffect(() => {
      loadIcons().then(() => refresh((n) => n + 1));
    }, []);
    const names = iconLibrary ? Object.keys(iconLibrary.icons) : [];
    const q = search.trim().toLowerCase();
    const found = (q ? names.filter((n) => n.includes(q) || (iconLibrary.keywords[n] || "").toLowerCase().includes(q)) : names).slice(0, 48);
    return /* @__PURE__ */ wp.element.createElement("div", { className: "starter-poc-icon" }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, "Icona"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => setOpen(!open), style: { width: 44, height: 44, justifyContent: "center" } }, /* @__PURE__ */ wp.element.createElement(Icon, { name: value || "key" })), open && /* @__PURE__ */ wp.element.createElement(Popover, { onClose: () => setOpen(false), placement: "left-start", focusOnMount: "firstElement" }, /* @__PURE__ */ wp.element.createElement("div", { style: { padding: 12, width: 280 } }, /* @__PURE__ */ wp.element.createElement(SearchControl, { value: search, onChange: setSearch, label: "Cerca icona", __nextHasNoMarginBottom: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 4, marginTop: 8, maxHeight: 240, overflow: "auto" } }, found.map((n) => /* @__PURE__ */ wp.element.createElement(
      Button,
      {
        key: n,
        variant: n === value ? "primary" : "tertiary",
        label: n,
        showTooltip: true,
        onClick: () => {
          onChange(n);
          setOpen(false);
        },
        style: { justifyContent: "center", padding: 4 }
      },
      /* @__PURE__ */ wp.element.createElement(Icon, { name: n, size: 20 })
    ))))));
  }
  function ImageField({ value, onChange }) {
    const id = parseInt(value, 10) || 0;
    const media = useSelect((select) => id ? select("core").getMedia(id) : null, [id]);
    const thumb = media && media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail ? media.media_details.sizes.thumbnail.source_url : media && media.source_url;
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, "Foto"), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload,
      {
        allowedTypes: ["image"],
        value: id,
        onSelect: (m) => onChange(m.id),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center" } }, thumb && /* @__PURE__ */ wp.element.createElement("img", { src: thumb, alt: "", style: { width: 64, height: 64, objectFit: "cover", borderRadius: 4 } }), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: open }, id ? "Cambia" : "Scegli foto"), id ? /* @__PURE__ */ wp.element.createElement(Button, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null)
      }
    )));
  }
  function LinkField({ value, onChange }) {
    const [open, setOpen] = useState(false);
    const current = value && value.url ? { url: value.url, title: value.title, opensInNewTab: "_blank" === value.target } : null;
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, "Pulsante"), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => setOpen(!open) }, current ? current.title || current.url : "Aggiungi link"), current ? /* @__PURE__ */ wp.element.createElement(Button, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null), open && /* @__PURE__ */ wp.element.createElement(Popover, { onClose: () => setOpen(false), placement: "left-start", focusOnMount: "firstElement" }, /* @__PURE__ */ wp.element.createElement(
      LinkControl,
      {
        value: current,
        settings: [{ id: "opensInNewTab", title: "Apri in una nuova scheda" }],
        onChange: (v) => onChange(v && v.url ? { title: v.title || "", url: v.url, target: v.opensInNewTab ? "_blank" : "" } : ""),
        onRemove: () => {
          onChange("");
          setOpen(false);
        }
      }
    )));
  }
  var NEW_ROW = { id: "", icona: "key", occhiello: "", titolo: "", etichetta: "", testo: "", immagine: "", elenco: "", cta: "", cta_stile: "" };
  var buttonChoices = () => toChoices(window.starterPoc && window.starterPoc.buttonChoices);
  var bgChoices = () => toChoices(window.starterPoc && window.starterPoc.bgChoices);
  function ServiceFields({ row, set, wide }) {
    if (!wide) {
      return /* @__PURE__ */ wp.element.createElement(Fragment, null, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "44px 1fr", gap: 12, alignItems: "end" } }, /* @__PURE__ */ wp.element.createElement(IconField, { value: row.icona, onChange: set("icona") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Ancora", value: row.id, onChange: set("id"), placeholder: "vendita", __nextHasNoMarginBottom: true })), /* @__PURE__ */ wp.element.createElement("div", { style: { height: 12 } }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Occhiello", value: row.occhiello, onChange: set("occhiello") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Titolo", value: row.titolo, onChange: set("titolo") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Etichetta nel menu", value: row.etichetta, onChange: set("etichetta") }), /* @__PURE__ */ wp.element.createElement(TextareaControl, { label: "Testo", rows: 3, value: row.testo, onChange: set("testo") }), /* @__PURE__ */ wp.element.createElement(ImageField, { value: row.immagine, onChange: set("immagine") }), /* @__PURE__ */ wp.element.createElement(TextareaControl, { label: "Elenco con spunte", rows: 5, value: row.elenco, onChange: set("elenco") }), /* @__PURE__ */ wp.element.createElement(LinkField, { value: row.cta, onChange: set("cta") }), /* @__PURE__ */ wp.element.createElement(SelectControl, { label: "Stile pulsante", value: row.cta_stile, options: buttonChoices(), onChange: set("cta_stile") }));
    }
    const full = { gridColumn: "1 / -1" };
    return /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "1fr 1fr", gap: "4px 24px" } }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "44px 1fr", gap: 12, alignItems: "end", marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement(IconField, { value: row.icona, onChange: set("icona") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Ancora", value: row.id, onChange: set("id"), placeholder: "vendita", __nextHasNoMarginBottom: true })), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Occhiello", value: row.occhiello, onChange: set("occhiello") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Titolo", value: row.titolo, onChange: set("titolo") }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Etichetta nel menu", value: row.etichetta, onChange: set("etichetta") }), /* @__PURE__ */ wp.element.createElement("div", { style: full }, /* @__PURE__ */ wp.element.createElement(TextareaControl, { label: "Testo", rows: 4, value: row.testo, onChange: set("testo") })), /* @__PURE__ */ wp.element.createElement("div", { style: full }, /* @__PURE__ */ wp.element.createElement(TextareaControl, { label: "Elenco con spunte (una voce per riga)", rows: 6, value: row.elenco, onChange: set("elenco") })), /* @__PURE__ */ wp.element.createElement(ImageField, { value: row.immagine, onChange: set("immagine") }), /* @__PURE__ */ wp.element.createElement("div", null, /* @__PURE__ */ wp.element.createElement(LinkField, { value: row.cta, onChange: set("cta") }), /* @__PURE__ */ wp.element.createElement(SelectControl, { label: "Stile pulsante", value: row.cta_stile, options: buttonChoices(), onChange: set("cta_stile") })));
  }
  function ServiceRow({ row, index, total, onChange, onMove, onRemove }) {
    const set = (field) => (value) => onChange({ ...row, [field]: value });
    const title = row.titolo || row.etichetta || `Servizio ${index + 1}`;
    return /* @__PURE__ */ wp.element.createElement(PanelBody, { title, initialOpen: 0 === index }, /* @__PURE__ */ wp.element.createElement(ServiceFields, { row, set }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, marginTop: 8 } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", size: "small", disabled: 0 === index, onClick: () => onMove(index, -1) }, "\u2191 Su"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", size: "small", disabled: index === total - 1, onClick: () => onMove(index, 1) }, "\u2193 Gi\xF9"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "tertiary", size: "small", isDestructive: true, onClick: () => onRemove(index) }, "Elimina")));
  }
  function EditModal({ rows, data, onSave, onClose }) {
    const [draft, setDraft] = useState(rows);
    const [adv, setAdv] = useState({ sfondo_sezione: data.sfondo_sezione || "", classi_custom: data.classi_custom || "" });
    const [sel, setSel] = useState(0);
    const current = "adv" === sel ? null : draft[Math.min(sel, draft.length - 1)];
    const index = "adv" === sel ? -1 : Math.min(sel, draft.length - 1);
    const setRow = (field) => (value) => setDraft(draft.map((r, i) => i === index ? { ...r, [field]: value } : r));
    const labelOf = (r, i) => r.titolo || r.etichetta || `Servizio ${i + 1}`;
    const move = (dir) => {
      const next = draft.slice();
      [next[index], next[index + dir]] = [next[index + dir], next[index]];
      setDraft(next);
      setSel(index + dir);
    };
    return /* @__PURE__ */ wp.element.createElement(Modal, { title: "Elenco servizi", onRequestClose: onClose, size: "large", style: { width: 1100, maxWidth: "96vw" }, shouldCloseOnClickOutside: false }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "240px 1fr", gap: 28, minHeight: 440 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", flexDirection: "column", gap: 4, borderRight: "1px solid #e0e0e0", paddingRight: 16 } }, draft.map((r, i) => /* @__PURE__ */ wp.element.createElement(Button, { key: i, variant: i === index ? "primary" : "tertiary", onClick: () => setSel(i), style: { justifyContent: "flex-start", width: "100%", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" } }, labelOf(r, i))), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => {
      setDraft([...draft, { ...NEW_ROW }]);
      setSel(draft.length);
    }, style: { marginTop: 8, justifyContent: "center" } }, "+ Aggiungi servizio"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "adv" === sel ? "primary" : "tertiary", onClick: () => setSel("adv"), style: { marginTop: 16, justifyContent: "flex-start" } }, "Avanzate")), /* @__PURE__ */ wp.element.createElement("div", null, current && /* @__PURE__ */ wp.element.createElement(Fragment, null, /* @__PURE__ */ wp.element.createElement(ServiceFields, { row: current, set: setRow, wide: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, marginTop: 8 } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", size: "small", disabled: 0 === index, onClick: () => move(-1) }, "\u2191 Su"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", size: "small", disabled: index === draft.length - 1, onClick: () => move(1) }, "\u2193 Gi\xF9"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "tertiary", size: "small", isDestructive: true, onClick: () => {
      setDraft(draft.filter((_, i) => i !== index));
      setSel(Math.max(0, index - 1));
    } }, "Elimina"))), "adv" === sel && /* @__PURE__ */ wp.element.createElement("div", { style: { maxWidth: 420 } }, /* @__PURE__ */ wp.element.createElement(SelectControl, { label: "Sfondo della sezione", value: adv.sfondo_sezione, options: bgChoices(), onChange: (v) => setAdv({ ...adv, sfondo_sezione: v }) }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Classi CSS", value: adv.classi_custom, placeholder: "mt2 pb4 pb2-sma", onChange: (v) => setAdv({ ...adv, classi_custom: v }) })), !current && "adv" !== sel && /* @__PURE__ */ wp.element.createElement("p", null, "Nessun servizio: aggiungine uno a sinistra."))), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", justifyContent: "flex-end", gap: 8, marginTop: 24, paddingTop: 16, borderTop: "1px solid #e0e0e0" } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "tertiary", onClick: onClose }, "Annulla"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "primary", onClick: () => onSave(draft, adv) }, "Salva")));
  }
  function Edit({ attributes, setAttributes }) {
    const data = attributes.data && !Array.isArray(attributes.data) ? attributes.data : {};
    const rows = readRows(data);
    const blockProps = useBlockProps();
    const [modalOpen, setModalOpen] = useState(false);
    const save = (nextRows) => setAttributes({ data: writeRows(data, nextRows) });
    const saveField = (key, value) => setAttributes({ data: { ...data, [key]: value, ["_" + key]: KEYS[key] } });
    const saveAll = (nextRows, adv) => {
      const next = writeRows(data, nextRows);
      ["sfondo_sezione", "classi_custom"].forEach((key) => {
        next[key] = adv[key];
        next["_" + key] = KEYS[key];
      });
      setAttributes({ data: next });
      setModalOpen(false);
    };
    const move = (i, dir) => {
      const next = rows.slice();
      const j = i + dir;
      [next[i], next[j]] = [next[j], next[i]];
      save(next);
    };
    return /* @__PURE__ */ wp.element.createElement(Fragment, null, /* @__PURE__ */ wp.element.createElement(BlockControls, null, /* @__PURE__ */ wp.element.createElement(ToolbarGroup, null, /* @__PURE__ */ wp.element.createElement(ToolbarButton, { icon: "edit", label: "Modifica contenuti", onClick: () => setModalOpen(true) }))), modalOpen && /* @__PURE__ */ wp.element.createElement(EditModal, { rows, data, onSave: saveAll, onClose: () => setModalOpen(false) }), /* @__PURE__ */ wp.element.createElement(InspectorControls, null, /* @__PURE__ */ wp.element.createElement(PanelBody, { title: "Servizi", initialOpen: true }, /* @__PURE__ */ wp.element.createElement("p", { style: { margin: "0 0 12px", display: "flex", gap: 8, flexWrap: "wrap" } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "primary", onClick: () => setModalOpen(true) }, "Modifica contenuti"), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => save([...rows, { ...NEW_ROW }]) }, "Aggiungi servizio"))), rows.map((row, i) => /* @__PURE__ */ wp.element.createElement(
      ServiceRow,
      {
        key: i,
        row,
        index: i,
        total: rows.length,
        onChange: (r) => save(rows.map((x, k) => k === i ? r : x)),
        onMove: move,
        onRemove: (k) => save(rows.filter((_, n) => n !== k))
      }
    )), /* @__PURE__ */ wp.element.createElement(PanelBody, { title: "Avanzate", initialOpen: false }, /* @__PURE__ */ wp.element.createElement(SelectControl, { label: "Sfondo della sezione", value: data.sfondo_sezione || "", options: bgChoices(), onChange: (v) => saveField("sfondo_sezione", v) }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Classi CSS", value: data.classi_custom || "", placeholder: "mt2 pb4 pb2-sma", onChange: (v) => saveField("classi_custom", v) }))), /* @__PURE__ */ wp.element.createElement("div", { ...blockProps }, /* @__PURE__ */ wp.element.createElement(ServerSideRender, { block: BLOCK, attributes, httpMethod: "POST" })));
  }
  registerBlockType(BLOCK, {
    edit: Edit,
    save: () => null
    // Blocco dinamico: lo disegna il PHP.
  });
})();
