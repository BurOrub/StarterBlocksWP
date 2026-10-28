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

  // src/engine.jsx
  var { registerBlockType } = wp.blocks;
  var { InspectorControls: InspectorControls2, BlockControls, InnerBlocks, useBlockProps, MediaUpload: MediaUpload2, MediaUploadCheck: MediaUploadCheck2 } = wp.blockEditor;
  var { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl, CheckboxControl, Button: Button2, SearchControl: SearchControl2, Modal, ToolbarGroup, ToolbarButton } = wp.components;
  var { useState: useState2, useEffect: useEffect2, Fragment } = wp.element;
  var { useSelect: useSelect2 } = wp.data;
  var apiFetch2 = wp.apiFetch;
  var ServerSideRender = wp.serverSideRender;
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
    const media = useSelect2((select) => ids.length ? select("core").getEntityRecords("postType", "attachment", { include: ids, per_page: 100 }) : [], [ids.join(",")]) || [];
    const thumbOf = (id) => {
      const m = media.find((x) => x.id === id);
      return m && m.media_details && m.media_details.sizes && m.media_details.sizes.thumbnail ? m.media_details.sizes.thumbnail.source_url : m && m.source_url;
    };
    const move = (i, dir) => {
      const next = ids.slice();
      [next[i], next[i + dir]] = [next[i + dir], next[i]];
      onChange(next);
    };
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", flexWrap: "wrap", gap: 8, marginBottom: 8 } }, ids.map((id, i) => /* @__PURE__ */ wp.element.createElement("div", { key: id + "-" + i, style: { width: 84, textAlign: "center" } }, thumbOf(id) ? /* @__PURE__ */ wp.element.createElement("img", { src: thumbOf(id), alt: "", style: { width: 84, height: 64, objectFit: "cover", borderRadius: 4, display: "block" } }) : /* @__PURE__ */ wp.element.createElement("div", { style: { width: 84, height: 64, background: "#eee", borderRadius: 4, display: "flex", alignItems: "center", justifyContent: "center", fontSize: 11 } }, media.find((x) => x.id === id) ? "video" : ""), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", justifyContent: "center" } }, /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Sposta prima" }, "\u2190"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", isDestructive: true, onClick: () => onChange(ids.filter((_, n) => n !== i)), label: "Togli" }, "\u2715"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: i === ids.length - 1, onClick: () => move(i, 1), label: "Sposta dopo" }, "\u2192"))))), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck2, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload2,
      {
        allowedTypes: "slide" === field.name ? ["image", "video"] : ["image"],
        multiple: "add",
        gallery: true,
        value: ids,
        onSelect: (items) => onChange(items.map((m) => m.id)),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", onClick: open }, ids.length ? "Modifica la selezione" : "Scegli le foto")
      }
    )));
  }
  function FileField({ value, onChange, label }) {
    const id = parseInt(value, 10) || 0;
    const media = useSelect2((select) => id ? select("core").getMedia(id) : null, [id]);
    const name = media && (media.source_url || "").split("/").pop();
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement(MediaUploadCheck2, null, /* @__PURE__ */ wp.element.createElement(
      MediaUpload2,
      {
        value: id,
        onSelect: (m) => onChange(m.id),
        render: ({ open }) => /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" } }, id ? /* @__PURE__ */ wp.element.createElement("span", { style: { fontSize: 12, wordBreak: "break-all" } }, name || `#${id}`) : null, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", onClick: open }, id ? "Cambia" : "Scegli il file"), id ? /* @__PURE__ */ wp.element.createElement(Button2, { variant: "link", isDestructive: true, onClick: () => onChange("") }, "Togli") : null)
      }
    )));
  }
  function TaxonomyField({ field, value, onChange }) {
    const terms = useSelect2((select) => select("core").getEntityRecords("taxonomy", field.taxonomy, { per_page: 100 }), [field.taxonomy]) || [];
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
      SelectControl,
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
    const [search, setSearch] = useState2("");
    const [found, setFound] = useState2([]);
    const [titles, setTitles] = useState2({});
    const postInput = document.getElementById("post_ID");
    const editor = wp.data.select("core/editor");
    const currentId = postInput && parseInt(postInput.value, 10) || editor && editor.getCurrentPostId && editor.getCurrentPostId() || 0;
    const rememberAll = (list) => setTitles((t) => ({ ...t, ...Object.fromEntries(list.map((p) => [p.id, p.title && p.title.rendered ? p.title.rendered : `#${p.id}`])) }));
    useEffect2(() => {
      if (ids.length) {
        apiFetch2({ path: `/wp/v2/${type}?include=${ids.join(",")}&per_page=100&_fields=id,title&context=edit` }).then(rememberAll).catch(() => {
        });
      }
    }, [ids.join(",")]);
    useEffect2(() => {
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
    return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, field.label), ids.map((id, i) => /* @__PURE__ */ wp.element.createElement("div", { key: id, style: { display: "flex", alignItems: "center", gap: 4, border: "1px solid #ddd", borderRadius: 4, padding: "2px 8px", marginBottom: 4 } }, /* @__PURE__ */ wp.element.createElement("span", { style: { flex: 1 }, dangerouslySetInnerHTML: { __html: titles[id] || `#${id}` } }), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Su" }, "\u2191"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: i === ids.length - 1, onClick: () => move(i, 1), label: "Gi\xF9" }, "\u2193"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", isDestructive: true, label: "Togli", onClick: () => onChange(ids.filter((x) => x !== id)) }, "\u2715"))), /* @__PURE__ */ wp.element.createElement(SearchControl2, { value: search, onChange: setSearch, label: "Cerca", __nextHasNoMarginBottom: true }), /* @__PURE__ */ wp.element.createElement("div", { style: { maxHeight: 160, overflow: "auto", marginTop: 4 } }, found.filter((p) => !ids.includes(p.id)).map((p) => /* @__PURE__ */ wp.element.createElement(Button2, { key: p.id, variant: "tertiary", style: { display: "block", width: "100%", textAlign: "left" }, onClick: () => onChange([...ids, p.id]) }, "+ ", /* @__PURE__ */ wp.element.createElement("span", { dangerouslySetInnerHTML: { __html: p.title && p.title.rendered ? p.title.rendered : `#${p.id}` } })))));
  }
  function Field({ field, value, onChange }) {
    const label = field.label;
    const options = toChoices(Array.isArray(field.choices) ? Object.fromEntries(field.choices.map((c) => [c, c])) : field.choices);
    switch (field.type) {
      case "text":
      case "url":
      case "email":
        return /* @__PURE__ */ wp.element.createElement(TextControl, { label, type: "text" === field.type ? "text" : field.type, value, placeholder: field.placeholder, onChange });
      case "textarea":
        return /* @__PURE__ */ wp.element.createElement(TextareaControl, { label, rows: parseInt(field.rows, 10) || 4, value, placeholder: field.placeholder, onChange });
      case "number":
        return /* @__PURE__ */ wp.element.createElement(TextControl, { label, type: "number", value, min: field.min, max: field.max, step: field.step, onChange });
      case "select":
        if (field.multiple) {
          const current = Array.isArray(value) ? value : [];
          return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), options.map((o) => /* @__PURE__ */ wp.element.createElement(CheckboxControl, { key: o.value, label: o.label, checked: current.includes(o.value), onChange: (on) => onChange(on ? [...current, o.value] : current.filter((x) => x !== o.value)), __nextHasNoMarginBottom: true })));
        }
        return /* @__PURE__ */ wp.element.createElement(SelectControl, { label, value: String(value), options, onChange });
      case "button_group":
        return /* @__PURE__ */ wp.element.createElement("div", { style: { marginBottom: 16 } }, /* @__PURE__ */ wp.element.createElement("div", { style: LABEL_STYLE }, label), /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 4, flexWrap: "wrap" } }, options.map((o) => /* @__PURE__ */ wp.element.createElement(Button2, { key: o.value, variant: String(value) === String(o.value) ? "primary" : "secondary", onClick: () => onChange(o.value) }, o.label))));
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
        return /* @__PURE__ */ wp.element.createElement(TextControl, { label, value, onChange });
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
    const [open, setOpen] = useState2(0);
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
    return /* @__PURE__ */ wp.element.createElement("div", { style: { margin: "8px 0 16px" } }, /* @__PURE__ */ wp.element.createElement("div", { style: { ...LABEL_STYLE, marginBottom: 8 } }, field.label), list.map((row, i) => /* @__PURE__ */ wp.element.createElement("div", { key: i, style: { border: "1px solid #ddd", borderRadius: 4, marginBottom: 8 } }, /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", alignItems: "center", gap: 4, padding: "4px 8px", background: i === open ? "#f0f0f1" : "transparent" } }, /* @__PURE__ */ wp.element.createElement(Button2, { variant: "tertiary", onClick: () => setOpen(i === open ? -1 : i), style: { flex: 1, justifyContent: "flex-start", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" } }, i === open ? "\u25BE " : "\u25B8 ", titleOf(row, i)), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: 0 === i, onClick: () => move(i, -1), label: "Su" }, "\u2191"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", disabled: i === list.length - 1, onClick: () => move(i, 1), label: "Gi\xF9" }, "\u2193"), /* @__PURE__ */ wp.element.createElement(Button2, { size: "small", isDestructive: true, label: "Elimina", onClick: () => {
      onChange(list.filter((_, n) => n !== i));
      setOpen(Math.max(0, i - 1));
    } }, "\u2715")), i === open && /* @__PURE__ */ wp.element.createElement("div", { style: { padding: "14px 14px 10px" } }, /* @__PURE__ */ wp.element.createElement(FieldGrid, { fields: sub, values: row, onChange: (r) => onChange(list.map((x, n) => n === i ? r : x)) })))), !(parseInt(field.max, 10) && list.length >= parseInt(field.max, 10)) && /* @__PURE__ */ wp.element.createElement(Button2, { variant: "secondary", onClick: () => {
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
  function InlineFields({ fields, initial, onChange }) {
    const [values, setValues] = useState2(initial);
    const tabs = splitTabs(fields);
    const [tab, setTab] = useState2(0);
    const current = tabs[Math.min(tab, tabs.length - 1)];
    const change = (next) => {
      setValues(next);
      onChange(next);
    };
    return /* @__PURE__ */ wp.element.createElement("div", null, tabs.length > 1 && /* @__PURE__ */ wp.element.createElement("div", { style: { display: "flex", gap: 4, flexWrap: "wrap", borderBottom: "1px solid #e0e0e0", marginBottom: 16, paddingBottom: 8 } }, tabs.map((t, i) => /* @__PURE__ */ wp.element.createElement(Button2, { key: i, variant: current === t ? "primary" : "tertiary", onClick: () => setTab(i) }, t.label))), current && /* @__PURE__ */ wp.element.createElement(FieldGrid, { fields: current.fields, scope: fields, values, onChange: change }));
  }

  // src/meta.jsx
  var { render } = wp.element;
  var root = document.getElementById("starter-meta-root");
  var def = window.starterMeta;
  if (root && def) {
    const input = document.getElementById("starter-meta-json");
    let flat = {};
    try {
      flat = JSON.parse(input.value || "{}") || {};
    } catch (e) {
      flat = {};
    }
    render(
      /* @__PURE__ */ wp.element.createElement(
        InlineFields,
        {
          fields: def.fields,
          initial: readFields(def.fields, flat),
          onChange: (values) => {
            input.value = JSON.stringify(mergeData(def.fields, flat, values));
          }
        }
      ),
      root
    );
  }
})();
