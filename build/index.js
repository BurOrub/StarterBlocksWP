(() => {
  // src/shared.jsx
  var { InspectorControls, MediaUpload, MediaUploadCheck, LinkControl } = wp.blockEditor;
  var { Button, Popover, SearchControl } = wp.components;
  var { useState, useEffect } = wp.element;
  var { useSelect } = wp.data;
  var apiFetch = wp.apiFetch;
  var toChoices = (obj) => Object.keys(obj || {}).flatMap((value) => {
    const label = obj[value];
    if (label && "object" === typeof label) {
      return [{ value: "__group_" + value, label: value, disabled: true }, ...Object.keys(label).map((v) => ({ value: v, label: "\xA0\xA0" + label[v] }))];
    }
    return [{ value, label }];
  });
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
  function IconField({ value, onChange, label = "Icona" }) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");
    const [, refresh] = useState(0);
    useEffect(() => {
      loadIcons().then(() => refresh((n) => n + 1));
    }, []);
    const names = iconLibrary ? Object.keys(iconLibrary.icons) : [];
    const q = search.trim().toLowerCase();
    const found = (q ? names.filter((n) => n.includes(q) || (iconLibrary.keywords[n] || "").toLowerCase().includes(q)) : names).slice(0, 48);
    return /* @__PURE__ */ wp.element.createElement("div", { className: "starter-poc-icon" }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, label), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => setOpen(!open), style: { width: 44, height: 44, justifyContent: "center" } }, /* @__PURE__ */ wp.element.createElement(Icon, { name: value || "key" })), open && /* @__PURE__ */ wp.element.createElement(Popover, { onClose: () => setOpen(false), placement: "left-start", focusOnMount: "firstElement" }, /* @__PURE__ */ wp.element.createElement("div", { style: { padding: 12, width: 280 } }, /* @__PURE__ */ wp.element.createElement(SearchControl, { value: search, onChange: setSearch, label: "Cerca icona", __nextHasNoMarginBottom: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "repeat(6, 1fr)", gap: 4, marginTop: 8, maxHeight: 240, overflow: "auto" } }, found.map((n) => /* @__PURE__ */ wp.element.createElement(
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
  function ImageField({ value, onChange, label = "Foto" }) {
    const id = parseInt(value, 10) || 0;
    const media = useSelect((select) => id ? select("core").getMedia(id) : null, [id]);
    const thumb = media && media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail ? media.media_details.sizes.thumbnail.source_url : media && media.source_url;
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, label), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload,
      {
        allowedTypes: ["image"],
        value: id,
        onSelect: (m) => onChange(m.id),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center" } }, thumb && /* @__PURE__ */ wp.element.createElement("img", { src: thumb, alt: "", style: { width: 64, height: 64, objectFit: "cover", borderRadius: 4 } }), /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: open }, id ? "Cambia" : "Scegli foto"), id ? /* @__PURE__ */ wp.element.createElement(Button, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null)
      }
    )));
  }
  function LinkField({ value, onChange, label = "Pulsante" }) {
    const [open, setOpen] = useState(false);
    const current = value && value.url ? { url: value.url, title: value.title, opensInNewTab: "_blank" === value.target } : null;
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { fontSize: 11, textTransform: "uppercase", marginBottom: 4 } }, label), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" } }, /* @__PURE__ */ wp.element.createElement(Button, { variant: "secondary", onClick: () => setOpen(!open) }, current ? current.title || current.url : "Aggiungi link"), current ? /* @__PURE__ */ wp.element.createElement(Button, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null), open && /* @__PURE__ */ wp.element.createElement(Popover, { onClose: () => setOpen(false), placement: "left-start", focusOnMount: "firstElement" }, /* @__PURE__ */ wp.element.createElement(
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

  // src/servizi.jsx
  var { registerBlockType } = wp.blocks;
  var { InspectorControls: InspectorControls2, BlockControls, useBlockProps, MediaUpload: MediaUpload2, MediaUploadCheck: MediaUploadCheck2, LinkControl: LinkControl2 } = wp.blockEditor;
  var { PanelBody, TextControl, TextareaControl, SelectControl, Button: Button2, Popover: Popover2, SearchControl: SearchControl2, Modal, ToolbarGroup, ToolbarButton } = wp.components;
  var { useState: useState2, useEffect: useEffect2, Fragment } = wp.element;
  var { useSelect: useSelect2 } = wp.data;
  var ServerSideRender = wp.serverSideRender;
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
    return /* @__PURE__ */ wp.element.createElement(PanelBody, { title, initialOpen: 0 === index }, /* @__PURE__ */ wp.element.createElement(ServiceFields, { row, set }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, marginTop: 8 } }, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", size: "small", disabled: 0 === index, onClick: () => onMove(index, -1) }, "\u2191 Su"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", size: "small", disabled: index === total - 1, onClick: () => onMove(index, 1) }, "\u2193 Gi\xF9"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "tertiary", size: "small", isDestructive: true, onClick: () => onRemove(index) }, "Elimina")));
  }
  function EditModal({ rows, data, onSave, onClose }) {
    const [draft, setDraft] = useState2(rows);
    const [adv, setAdv] = useState2({ sfondo_sezione: data.sfondo_sezione || "", classi_custom: data.classi_custom || "" });
    const [sel, setSel] = useState2(0);
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
    return /* @__PURE__ */ wp.element.createElement(Modal, { title: "Elenco servizi", onRequestClose: onClose, size: "large", style: { width: 1100, maxWidth: "96vw" }, shouldCloseOnClickOutside: false }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: "240px 1fr", gap: 28, minHeight: 440 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", flexDirection: "column", gap: 4, borderRight: "1px solid #e0e0e0", paddingRight: 16 } }, draft.map((r, i) => /* @__PURE__ */ wp.element.createElement(Button2, { key: i, variant: i === index ? "primary" : "tertiary", onClick: () => setSel(i), style: { justifyContent: "flex-start", width: "100%", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" } }, labelOf(r, i))), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", onClick: () => {
      setDraft([...draft, { ...NEW_ROW }]);
      setSel(draft.length);
    }, style: { marginTop: 8, justifyContent: "center" } }, "+ Aggiungi servizio"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "adv" === sel ? "primary" : "tertiary", onClick: () => setSel("adv"), style: { marginTop: 16, justifyContent: "flex-start" } }, "Avanzate")), /* @__PURE__ */ wp.element.createElement("div", null, current && /* @__PURE__ */ wp.element.createElement(Fragment, null, /* @__PURE__ */ wp.element.createElement(ServiceFields, { row: current, set: setRow, wide: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, marginTop: 8 } }, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", size: "small", disabled: 0 === index, onClick: () => move(-1) }, "\u2191 Su"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", size: "small", disabled: index === draft.length - 1, onClick: () => move(1) }, "\u2193 Gi\xF9"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "tertiary", size: "small", isDestructive: true, onClick: () => {
      setDraft(draft.filter((_, i) => i !== index));
      setSel(Math.max(0, index - 1));
    } }, "Elimina"))), "adv" === sel && /* @__PURE__ */ wp.element.createElement("div", { style: { maxWidth: 420 } }, /* @__PURE__ */ wp.element.createElement(SelectControl, { label: "Sfondo della sezione", value: adv.sfondo_sezione, options: bgChoices(), onChange: (v) => setAdv({ ...adv, sfondo_sezione: v }) }), /* @__PURE__ */ wp.element.createElement(TextControl, { label: "Classi CSS", value: adv.classi_custom, placeholder: "mt2 pb4 pb2-sma", onChange: (v) => setAdv({ ...adv, classi_custom: v }) })), !current && "adv" !== sel && /* @__PURE__ */ wp.element.createElement("p", null, "Nessun servizio: aggiungine uno a sinistra."))), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", justifyContent: "flex-end", gap: 8, marginTop: 24, paddingTop: 16, borderTop: "1px solid #e0e0e0" } }, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "tertiary", onClick: onClose }, "Annulla"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "primary", onClick: () => onSave(draft, adv) }, "Salva")));
  }
  function Edit({ attributes, setAttributes }) {
    const data = attributes.data && !Array.isArray(attributes.data) ? attributes.data : {};
    const rows = readRows(data);
    const blockProps = useBlockProps();
    const [modalOpen, setModalOpen] = useState2(false);
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
    return /* @__PURE__ */ wp.element.createElement(Fragment, null, /* @__PURE__ */ wp.element.createElement(BlockControls, null, /* @__PURE__ */ wp.element.createElement(ToolbarGroup, null, /* @__PURE__ */ wp.element.createElement(ToolbarButton, { icon: "edit", label: "Modifica contenuti", onClick: () => setModalOpen(true) }))), modalOpen && /* @__PURE__ */ wp.element.createElement(EditModal, { rows, data, onSave: saveAll, onClose: () => setModalOpen(false) }), /* @__PURE__ */ wp.element.createElement(InspectorControls2, null, /* @__PURE__ */ wp.element.createElement(PanelBody, { title: "Servizi", initialOpen: true }, /* @__PURE__ */ wp.element.createElement("p", { style: { margin: "0 0 12px", display: "flex", gap: 8, flexWrap: "wrap" } }, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "primary", onClick: () => setModalOpen(true) }, "Modifica contenuti"), /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", onClick: () => save([...rows, { ...NEW_ROW }]) }, "Aggiungi servizio"))), rows.map((row, i) => /* @__PURE__ */ wp.element.createElement(
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

  // src/engine.jsx
  var { registerBlockType: registerBlockType2 } = wp.blocks;
  var { InspectorControls: InspectorControls3, BlockControls: BlockControls2, InnerBlocks, useBlockProps: useBlockProps2, MediaUpload: MediaUpload3, MediaUploadCheck: MediaUploadCheck3 } = wp.blockEditor;
  var { PanelBody: PanelBody2, TextControl: TextControl2, TextareaControl: TextareaControl2, SelectControl: SelectControl2, ToggleControl, CheckboxControl, Button: Button3, SearchControl: SearchControl3, Modal: Modal2, ToolbarGroup: ToolbarGroup2, ToolbarButton: ToolbarButton2 } = wp.components;
  var { useState: useState3, useEffect: useEffect3, Fragment: Fragment2 } = wp.element;
  var { useSelect: useSelect3 } = wp.data;
  var apiFetch2 = wp.apiFetch;
  var ServerSideRender2 = wp.serverSideRender;
  var DEFS = window.starterBlocksDefs || {};
  var SKIP = ["tab", "message"];
  var WIDE = ["textarea", "repeater", "gallery", "message", "relationship"];
  var LABEL_STYLE = { fontSize: 11, textTransform: "uppercase", marginBottom: 4 };
  var isOn = (v) => true === v || 1 === v || "1" === v;
  function defaultOf(field) {
    if ("repeater" === field.type) {
      return [];
    }
    if ("true_false" === field.type) {
      return isOn(field.default_value);
    }
    if ("gallery" === field.type || "relationship" === field.type || "select" === field.type && field.multiple) {
      return [];
    }
    return void 0 === field.default_value || null === field.default_value ? "" : field.default_value;
  }
  function readFields(fields, data, prefix = "") {
    const values = {};
    fields.forEach((f) => {
      if (!f.name || SKIP.includes(f.type)) {
        return;
      }
      const key = prefix + f.name;
      if ("repeater" === f.type) {
        const count = parseInt(data[key], 10) || 0;
        values[f.name] = Array.from({ length: count }, (_, i) => readFields(f.sub_fields || [], data, `${key}_${i}_`));
        return;
      }
      const raw = data[key];
      if (void 0 === raw || null === raw) {
        values[f.name] = defaultOf(f);
      } else if ("true_false" === f.type) {
        values[f.name] = isOn(raw);
      } else if ("gallery" === f.type || "relationship" === f.type) {
        values[f.name] = Array.isArray(raw) ? raw : [];
      } else if ("image" === f.type && Array.isArray(raw)) {
        values[f.name] = raw[0] || "";
      } else {
        values[f.name] = raw;
      }
    });
    return values;
  }
  function writeFields(fields, values, out, prefix = "") {
    fields.forEach((f) => {
      if (!f.name || SKIP.includes(f.type)) {
        return;
      }
      const key = prefix + f.name;
      const v = values[f.name];
      out[key] = v;
      out["_" + key] = f.key;
      if ("repeater" === f.type) {
        const rows = Array.isArray(v) ? v : [];
        out[key] = rows.length;
        rows.forEach((row, i) => writeFields(f.sub_fields || [], row, out, `${key}_${i}_`));
      } else if ("true_false" === f.type) {
        out[key] = v ? 1 : 0;
      }
    });
    return out;
  }
  function mergeData(fields, data, values) {
    const names = fields.filter((f) => f.name && !SKIP.includes(f.type)).map((f) => f.name.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"));
    const own = new RegExp(`^_?(${names.join("|")})(_\\d+_.*)?$`);
    const next = {};
    Object.keys(data).forEach((k) => {
      if (!own.test(k)) {
        next[k] = data[k];
      }
    });
    return writeFields(fields, values, next);
  }
  function isVisible(field, siblings, values) {
    const logic = field.conditional_logic;
    if (!Array.isArray(logic) || !logic.length) {
      return true;
    }
    return logic.some(
      (group) => group.every((rule) => {
        const target = siblings.find((s) => s.key === rule.field);
        if (!target) {
          return true;
        }
        let current = values[target.name];
        if ("true_false" === target.type) {
          current = isOn(current) ? "1" : "0";
        }
        const empty = void 0 === current || null === current || "" === current || "0" === current && "true_false" === target.type || Array.isArray(current) && !current.length;
        const list = Array.isArray(current) ? current.map(String) : [String(current)];
        switch (rule.operator) {
          case "==empty":
            return empty;
          case "!=empty":
            return !empty;
          case "!=":
            return !list.includes(String(rule.value));
          case "==contains":
            return list.some((v) => v.includes(String(rule.value)));
          case "!=contains":
            return !list.some((v) => v.includes(String(rule.value)));
          default:
            return list.includes(String(rule.value));
        }
      })
    );
  }
  function GalleryField({ field, value, onChange, label }) {
    const ids = (Array.isArray(value) ? value : []).map((v) => parseInt(v, 10)).filter(Boolean);
    const media = useSelect3((select) => ids.length ? select("core").getEntityRecords("postType", "attachment", { include: ids, per_page: 100 }) : [], [ids.join(",")]) || [];
    const thumbOf = (id) => {
      const m = media.find((x) => x.id === id);
      return m && m.media_details && m.media_details.sizes && m.media_details.sizes.thumbnail ? m.media_details.sizes.thumbnail.source_url : m && m.source_url;
    };
    const move = (i, dir) => {
      const next = ids.slice();
      [next[i], next[i + dir]] = [next[i + dir], next[i]];
      onChange(next);
    };
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", flexWrap: "wrap", gap: 8, marginBottom: 8 } }, ids.map((id, i) => /* @__PURE__ */ wp.element.createElement("div", { key: id + "-" + i, style: { width: 84, textAlign: "center" } }, thumbOf(id) ? /* @__PURE__ */ wp.element.createElement("img", { src: thumbOf(id), alt: "", style: { width: 84, height: 64, objectFit: "cover", borderRadius: 4, display: "block" } }) : /* @__PURE__ */ wp.element.createElement("div", { style: { width: 84, height: 64, background: "#eee", borderRadius: 4, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 11 } }, media.find((x) => x.id === id) ? "video" : ""), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", justifyContent: "center" } }, /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Sposta prima" }, "\u2190"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", isDestructive: true, onClick: () => onChange(ids.filter((_, n) => n !== i)), label: "Togli" }, "\u2715"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: i === ids.length - 1, onClick: () => move(i, 1), label: "Sposta dopo" }, "\u2192"))))), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck3, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload3,
      {
        allowedTypes: "slide" === field.name ? ["image", "video"] : ["image"],
        multiple: "add",
        gallery: true,
        value: ids,
        onSelect: (items) => onChange(items.map((m) => m.id)),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement(Button3, { variant: "secondary", onClick: open }, ids.length ? "Modifica la selezione" : "Scegli le foto")
      }
    )));
  }
  function FileField({ value, onChange, label }) {
    const id = parseInt(value, 10) || 0;
    const media = useSelect3((select) => id ? select("core").getMedia(id) : null, [id]);
    const name = media && (media.source_url || "").split("/").pop();
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck3, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload3,
      {
        value: id,
        onSelect: (m) => onChange(m.id),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" } }, id ? /* @__PURE__ */ wp.element.createElement("span", { style: { fontSize: 12, wordBreak: "break-all" } }, name || `#${id}`) : null, /* @__PURE__ */ wp.element.createElement(Button3, { variant: "secondary", onClick: open }, id ? "Cambia" : "Scegli il file"), id ? /* @__PURE__ */ wp.element.createElement(Button3, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null)
      }
    )));
  }
  function TaxonomyField({ field, value, onChange }) {
    const terms = useSelect3((select) => select("core").getEntityRecords("taxonomy", field.taxonomy, { per_page: 100 }), [field.taxonomy]) || [];
    const multi = "multi_select" === field.field_type || "checkbox" === field.field_type;
    if (multi) {
      const current = (Array.isArray(value) ? value : []).map(Number);
      return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, field.label), terms.map((t) => /* @__PURE__ */ wp.element.createElement(
        CheckboxControl,
        {
          key: t.id,
          label: t.name,
          checked: current.includes(t.id),
          onChange: (on) => onChange(on ? [...current, t.id] : current.filter((x) => x !== t.id)),
          __nextHasNoMarginBottom: true
        }
      )));
    }
    return /* @__PURE__ */ wp.element.createElement(
      SelectControl2,
      {
        label: field.label,
        value: String(value || ""),
        options: [{ value: "", label: "\u2014" }, ...terms.map((t) => ({ value: String(t.id), label: t.name }))],
        onChange: (v) => onChange(v ? parseInt(v, 10) : "")
      }
    );
  }
  function RelationshipField({ field, value, onChange }) {
    const ids = (Array.isArray(value) ? value : []).map((v) => parseInt(v, 10)).filter(Boolean);
    const type = (Array.isArray(field.post_type) ? field.post_type[0] : field.post_type) || "post";
    const [search, setSearch] = useState3("");
    const [found, setFound] = useState3([]);
    const [titles, setTitles] = useState3({});
    const postInput = document.getElementById("post_ID");
    const editor = wp.data.select("core/editor");
    const currentId = postInput && parseInt(postInput.value, 10) || editor && editor.getCurrentPostId && editor.getCurrentPostId() || 0;
    const rememberAll = (list) => setTitles((t) => ({ ...t, ...Object.fromEntries(list.map((p) => [p.id, p.title && p.title.rendered ? p.title.rendered : `#${p.id}`])) }));
    useEffect3(() => {
      if (ids.length) {
        apiFetch2({ path: `/wp/v2/${type}?include=${ids.join(",")}&per_page=100&_fields=id,title&context=edit` }).then(rememberAll).catch(() => {
        });
      }
    }, [ids.join(",")]);
    useEffect3(() => {
      const t = setTimeout(() => {
        apiFetch2({ path: `/wp/v2/${type}?search=${encodeURIComponent(search)}&per_page=12&exclude=${currentId}&_fields=id,title&context=edit` }).then((list) => {
          rememberAll(list);
          setFound(list);
        }).catch(() => setFound([]));
      }, 250);
      return () => clearTimeout(t);
    }, [search]);
    const move = (i, dir) => {
      const next = ids.slice();
      [next[i], next[i + dir]] = [next[i + dir], next[i]];
      onChange(next);
    };
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, field.label), ids.map((id, i) => /* @__PURE__ */ wp.element.createElement("div", { key: id, style: { display: "flex", alignItems: "center", gap: 4, border: "1px solid #ddd", borderRadius: 4, padding: "2px 8px", marginBottom: 4 } }, /* @__PURE__ */ wp.element.createElement("span", { style: { flex: 1 }, dangerouslySetInnerHTML: { __html: titles[id] || `#${id}` } }), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Su" }, "\u2191"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: i === ids.length - 1, onClick: () => move(i, 1), label: "Gi\xF9" }, "\u2193"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", isDestructive: true, label: "Togli", onClick: () => onChange(ids.filter((x) => x !== id)) }, "\u2715"))), /* @__PURE__ */ wp.element.createElement(SearchControl3, { value: search, onChange: setSearch, label: "Cerca", __nextHasNoMarginBottom: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { maxHeight: 160, overflow: "auto", marginTop: 4 } }, found.filter((p) => !ids.includes(p.id)).map((p) => /* @__PURE__ */ wp.element.createElement(Button3, { key: p.id, variant: "tertiary", style: { display: "block", width: "100%", textAlign: "left" }, onClick: () => onChange([...ids, p.id]) }, "+ ", /* @__PURE__ */ wp.element.createElement("span", { dangerouslySetInnerHTML: { __html: p.title && p.title.rendered ? p.title.rendered : `#${p.id}` } })))));
  }
  function Field({ field, value, onChange }) {
    const label = field.label;
    const options = toChoices(Array.isArray(field.choices) ? Object.fromEntries(field.choices.map((c) => [c, c])) : field.choices);
    switch (field.type) {
      case "text":
      case "url":
      case "email":
        return /* @__PURE__ */ wp.element.createElement(TextControl2, { label, type: "text" === field.type ? "text" : field.type, value, placeholder: field.placeholder, onChange });
      case "textarea":
        return /* @__PURE__ */ wp.element.createElement(TextareaControl2, { label, rows: parseInt(field.rows, 10) || 4, value, placeholder: field.placeholder, onChange });
      case "number":
        return /* @__PURE__ */ wp.element.createElement(TextControl2, { label, type: "number", value, min: field.min, max: field.max, step: field.step, onChange });
      case "select":
        if (field.multiple) {
          const current = Array.isArray(value) ? value : [];
          return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), options.map((o) => /* @__PURE__ */ wp.element.createElement(CheckboxControl, { key: o.value, label: o.label, checked: current.includes(o.value), onChange: (on) => onChange(on ? [...current, o.value] : current.filter((x) => x !== o.value)), __nextHasNoMarginBottom: true })));
        }
        return /* @__PURE__ */ wp.element.createElement(SelectControl2, { label, value: String(value), options, onChange });
      case "button_group":
        return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 4, flexWrap: "wrap" } }, options.map((o) => /* @__PURE__ */ wp.element.createElement(Button3, { key: o.value, variant: String(value) === String(o.value) ? "primary" : "secondary", onClick: () => onChange(o.value) }, o.label))));
      case "true_false":
        return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement(ToggleControl, { label, checked: !!value, onChange, __nextHasNoMarginBottom: true }));
      case "link":
        return /* @__PURE__ */ wp.element.createElement(LinkField, { label, value, onChange });
      case "image":
        return /* @__PURE__ */ wp.element.createElement(ImageField, { label, value, onChange });
      case "file":
        return /* @__PURE__ */ wp.element.createElement(FileField, { label, value, onChange });
      case "gallery":
        return /* @__PURE__ */ wp.element.createElement(GalleryField, { field, label, value, onChange });
      case "starter_icon":
        return /* @__PURE__ */ wp.element.createElement(IconField, { label, value, onChange });
      case "relationship":
        return /* @__PURE__ */ wp.element.createElement(RelationshipField, { field, value, onChange });
      case "taxonomy":
        return /* @__PURE__ */ wp.element.createElement(TaxonomyField, { field, value, onChange });
      case "repeater":
        return /* @__PURE__ */ wp.element.createElement(RepeaterField, { field, rows: value, onChange });
      case "message":
        return /* @__PURE__ */ wp.element.createElement("p", { style: { margin: "0 0 16px", color: "#555" } }, field.message || label);
      default:
        return /* @__PURE__ */ wp.element.createElement(TextControl2, { label, value, onChange });
    }
  }
  function FieldGrid({ fields, values, onChange, columns = 2, scope }) {
    return /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))`, gap: "8px 24px" } }, fields.filter((f) => f.name && !SKIP.includes(f.type) || "message" === f.type).map(
      (f, i) => isVisible(f, scope || fields, values) ? /* @__PURE__ */ wp.element.createElement("div", { key: f.key || f.name || i, style: { paddingBottom: 8, ...WIDE.includes(f.type) || 1 === columns ? { gridColumn: "1 / -1" } : {} } }, /* @__PURE__ */ wp.element.createElement(Field, { field: f, value: values[f.name], onChange: (v) => onChange({ ...values, [f.name]: v }) })) : null
    ));
  }
  function RepeaterField({ field, rows, onChange }) {
    const list = Array.isArray(rows) ? rows : [];
    const sub = field.sub_fields || [];
    const [open, setOpen] = useState3(0);
    const emptyRow = () => readFields(sub, {});
    const titleOf = (row, i) => {
      const text = sub.filter((s) => ["text", "textarea"].includes(s.type) && !/_tag$/.test(s.name)).map((s) => String(row[s.name] || "").trim()).find(Boolean);
      return text ? text.slice(0, 60) : `${field.label} ${i + 1}`;
    };
    const move = (i, dir) => {
      const next = list.slice();
      [next[i], next[i + dir]] = [next[i + dir], next[i]];
      onChange(next);
      setOpen(i + dir);
    };
    return /* @__PURE__ */ wp.element.createElement("div", { style: { margin: "8px 0 16px" } }, /* @__PURE__ */ wp.element.createElement("div", { style: { ...LABEL_STYLE, marginBottom: 8 } }, field.label), list.map((row, i) => /* @__PURE__ */ wp.element.createElement("div", { key: i, style: { border: "1px solid #ddd", borderRadius: 4, marginBottom: 8 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", alignItems: "center", gap: 4, padding: "4px 8px", background: i === open ? "#f0f0f1" : "transparent" } }, /* @__PURE__ */ wp.element.createElement(Button3, { variant: "tertiary", onClick: () => setOpen(i === open ? -1 : i), style: { flex: 1, justifyContent: "flex-start", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" } }, i === open ? "\u25BE " : "\u25B8 ", titleOf(row, i)), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Su" }, "\u2191"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", disabled: i === list.length - 1, onClick: () => move(i, 1), label: "Gi\xF9" }, "\u2193"), /* @__PURE__ */ wp.element.createElement(Button3, { size: "small", isDestructive: true, label: "Elimina", onClick: () => {
      onChange(list.filter((_, n) => n !== i));
      setOpen(Math.max(0, i - 1));
    } }, "\u2715")), i === open && /* @__PURE__ */ wp.element.createElement("div", { style: { padding: "14px 14px 10px" } }, /* @__PURE__ */ wp.element.createElement(FieldGrid, { fields: sub, values: row, onChange: (r) => onChange(list.map((x, n) => n === i ? r : x)) })))), !(parseInt(field.max, 10) && list.length >= parseInt(field.max, 10)) && /* @__PURE__ */ wp.element.createElement(Button3, { variant: "secondary", onClick: () => {
      onChange([...list, emptyRow()]);
      setOpen(list.length);
    } }, "+ ", field.button_label || "Aggiungi"));
  }
  function splitTabs(fields) {
    const tabs = [];
    let current = null;
    fields.forEach((f) => {
      if ("tab" === f.type) {
        current = { label: f.label || "Contenuto", fields: [] };
        tabs.push(current);
        return;
      }
      if (!current) {
        current = { label: "Contenuto", fields: [] };
        tabs.push(current);
      }
      current.fields.push(f);
    });
    return tabs.filter((t) => t.fields.length);
  }
  function EditModal2({ title, fields, initial, onSave, onClose }) {
    const [values, setValues] = useState3(initial);
    const tabs = splitTabs(fields);
    const [tab, setTab] = useState3(0);
    const current = tabs[Math.min(tab, tabs.length - 1)];
    return /* @__PURE__ */ wp.element.createElement(Modal2, { title, onRequestClose: onClose, size: "large", style: { width: 1e3, maxWidth: "96vw" }, shouldCloseOnClickOutside: false }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "grid", gridTemplateColumns: tabs.length > 1 ? "160px 1fr" : "1fr", gap: 28, minHeight: 360 } }, tabs.length > 1 && /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", flexDirection: "column", gap: 4, borderRight: "1px solid #e0e0e0", paddingRight: 16 } }, tabs.map((t, i) => /* @__PURE__ */ wp.element.createElement(Button3, { key: i, variant: current === t ? "primary" : "tertiary", onClick: () => setTab(i), style: { justifyContent: "flex-start" } }, t.label))), /* @__PURE__ */ wp.element.createElement("div", null, current && /* @__PURE__ */ wp.element.createElement(FieldGrid, { fields: current.fields, scope: fields, values, onChange: setValues }))), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", justifyContent: "flex-end", gap: 8, marginTop: 24, paddingTop: 16, borderTop: "1px solid #e0e0e0" } }, /* @__PURE__ */ wp.element.createElement(Button3, { variant: "tertiary", onClick: onClose }, "Annulla"), /* @__PURE__ */ wp.element.createElement(Button3, { variant: "primary", onClick: () => onSave(values) }, "Salva")));
  }
  function makeEdit(name, def) {
    const fields = def.fields;
    const tabs = splitTabs(fields);
    const hasFields = fields.some((f) => f.name);
    const advanced = tabs.length > 1 && "Avanzate" === tabs[tabs.length - 1].label ? tabs[tabs.length - 1] : null;
    return function Edit2({ attributes, setAttributes }) {
      const data = attributes.data && !Array.isArray(attributes.data) ? attributes.data : {};
      const values = readFields(fields, data);
      const blockProps = useBlockProps2();
      const [modalOpen, setModalOpen] = useState3(false);
      const save = (next) => setAttributes({ data: mergeData(fields, data, next) });
      return /* @__PURE__ */ wp.element.createElement(Fragment2, null, hasFields && /* @__PURE__ */ wp.element.createElement(BlockControls2, null, /* @__PURE__ */ wp.element.createElement(ToolbarGroup2, null, /* @__PURE__ */ wp.element.createElement(ToolbarButton2, { icon: "edit", label: "Modifica contenuti", onClick: () => setModalOpen(true) }))), modalOpen && /* @__PURE__ */ wp.element.createElement(
        EditModal2,
        {
          title: def.title,
          fields,
          initial: values,
          onSave: (next) => {
            save(next);
            setModalOpen(false);
          },
          onClose: () => setModalOpen(false)
        }
      ), /* @__PURE__ */ wp.element.createElement(InspectorControls3, null, hasFields && /* @__PURE__ */ wp.element.createElement(PanelBody2, { title: def.title, initialOpen: true }, /* @__PURE__ */ wp.element.createElement(Button3, { variant: "primary", onClick: () => setModalOpen(true) }, "Modifica contenuti")), advanced && /* @__PURE__ */ wp.element.createElement(PanelBody2, { title: "Avanzate", initialOpen: false }, /* @__PURE__ */ wp.element.createElement(FieldGrid, { fields: advanced.fields, scope: fields, values, columns: 1, onChange: save }))), /* @__PURE__ */ wp.element.createElement("div", { ...blockProps }, /* @__PURE__ */ wp.element.createElement(ServerSideRender2, { block: name, attributes, httpMethod: "POST", urlQueryArgs: { post_id: wp.data.select("core/editor") && wp.data.select("core/editor").getCurrentPostId && wp.data.select("core/editor").getCurrentPostId() || 0 } }), def.inner && /* @__PURE__ */ wp.element.createElement("div", { style: { border: "1px dashed #c3c4c7", padding: 16, margin: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, "Testo della sezione"), /* @__PURE__ */ wp.element.createElement(InnerBlocks, { template: def.inner.template }))));
    };
  }

  // src/blocks.jsx
  var { registerBlockType: registerBlockType3 } = wp.blocks;
  var { InnerBlocks: InnerBlocks2 } = wp.blockEditor;
  var DEFS2 = window.starterBlocksDefs || {};
  Object.keys(DEFS2).forEach((name) => {
    registerBlockType3(name, {
      edit: makeEdit(name, DEFS2[name]),
      save: DEFS2[name].inner ? () => /* @__PURE__ */ wp.element.createElement(InnerBlocks2.Content, null) : () => null
      // Blocchi dinamici: li disegna il PHP (con i blocchi interni, se ci sono).
    });
  });
})();
