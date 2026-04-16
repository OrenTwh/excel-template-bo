<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>FX Spreadsheet</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Segoe UI',Arial,sans-serif;font-size:12px;background:#f0f0f0;overflow:hidden;user-select:none}

/* ── Layout ─────────────────────────────────────────────────────────────── */
#app{display:flex;flex-direction:column;height:100vh}
#toolbar{background:#217346;color:#fff;padding:0 10px;display:flex;align-items:center;gap:8px;flex-shrink:0;height:38px}
#toolbar .tb-title{font-weight:700;font-size:13px;margin-right:6px}
#toolbar select,#toolbar input[type=number]{background:#1a5c38;color:#fff;border:1px solid #2e9e60;padding:1px 5px;font-size:12px;height:24px;border-radius:2px;outline:none}
#toolbar button{background:#2e9e60;color:#fff;border:none;padding:0 10px;font-size:12px;cursor:pointer;height:24px;border-radius:2px}
#toolbar button:hover{background:#25834f}
#toolbar .sep{width:1px;height:18px;background:rgba(255,255,255,.25);margin:0 2px}
#tb-status{font-size:11px;opacity:.7;margin-left:auto}

/* ── Formula bar ─────────────────────────────────────────────────────────── */
#fbar{background:#fff;border-bottom:2px solid #c0c0c0;padding:2px 6px;display:flex;align-items:center;gap:6px;flex-shrink:0;height:26px}
#fbar-ref{width:64px;border:1px solid #bbb;padding:1px 4px;font-size:11px;text-align:center;background:#f5f5f5;outline:none;font-family:monospace}
#fbar-sep{color:#bbb;font-weight:300;font-size:16px;line-height:1}
#fbar-val{flex:1;border:none;outline:none;font-size:12px;font-family:Consolas,monospace;color:#333;background:transparent;pointer-events:none}

/* ── Sheet area ──────────────────────────────────────────────────────────── */
#sheet-area{flex:1;position:relative;overflow:hidden}
.sv{display:none;position:absolute;inset:0;overflow:auto}
.sv.active{display:block}

/* ── Tab bar ─────────────────────────────────────────────────────────────── */
#tab-bar{background:#d4d4d4;border-top:2px solid #217346;display:flex;align-items:flex-end;padding:0 4px;flex-shrink:0;height:26px;overflow-x:auto;gap:1px}
.tab{padding:3px 14px;background:#bbb;border:1px solid #999;border-bottom:none;cursor:pointer;font-size:11px;border-radius:3px 3px 0 0;white-space:nowrap;color:#444}
.tab:hover{background:#ccc}
.tab.active{background:#fff;color:#000;font-weight:600;border-color:#aaa}
.tab-add{padding:2px 10px;background:none;border:none;cursor:pointer;font-size:15px;color:#555;line-height:1;flex-shrink:0}

/* ── Excel table ─────────────────────────────────────────────────────────── */
.xl{border-collapse:collapse;table-layout:fixed;border-spacing:0}
.xl th,.xl td{border:1px solid #d0d0d0;height:20px;padding:0;position:relative;white-space:nowrap}

/* column/row headers */
.rh,.ch{background:#f2f2f2;color:#666;font-weight:400;font-size:11px;text-align:center;position:sticky;z-index:3}
.rh{left:0;width:36px;border-right:2px solid #bbb;z-index:2}
.ch{top:0;height:18px;border-bottom:2px solid #bbb}
.rh.ch{z-index:4}

/* cell content */
.xc{display:block;width:100%;height:100%;padding:0 3px;line-height:20px;overflow:hidden;text-overflow:ellipsis;outline:none;white-space:pre;cursor:cell}
.xc[contenteditable=true]{cursor:text;user-select:text;white-space:pre}
.xc.ro{cursor:default;color:#1a1a1a}
.xc.formula-cell{cursor:default;color:#1a1a1a;font-style:normal}

/* selection */
td.xl-sel{outline:2px solid #1155cc;outline-offset:-1px;z-index:10}
td.xl-sel-range{background:#d6e4f7!important}

/* editing */
td.xl-editing{outline:2px solid #1155cc;outline-offset:-1px;z-index:11}
td.xl-editing .xc{background:#fff;white-space:pre}

/* cell colours */
.bg-green {background:#1a9850!important;color:#fff!important;font-weight:600;text-align:center}
.bg-dgreen{background:#145a32!important;color:#fff!important;font-weight:600;text-align:center}
.bg-blue  {background:#1565c0!important;color:#fff!important;font-weight:600;text-align:center}
.bg-teal  {background:#00796b!important;color:#fff!important;font-weight:600;text-align:center}
.bg-dark  {background:#37474f!important;color:#fff!important;font-weight:600;text-align:center}
.bg-gray  {background:#78909c!important;color:#fff!important;font-weight:600;text-align:center}
.bg-yellow{background:#fffde7}
.bg-lgreen{background:#e8f5e9}
.bg-lblue {background:#e3f2fd}
.bg-total {background:#fef9c3;font-weight:600}
.bg-calc  {background:#f0fdf4}  /* calculated cells - light mint */
.num{text-align:right}
.ctr{text-align:center}
.bold{font-weight:600}
.dim{color:#999;font-style:italic}

/* saving indicator */
td.saving::after{content:'💾';position:absolute;top:0;right:1px;font-size:9px;opacity:.6}
td.save-err{outline:2px solid #c00!important}

/* ── Column resize handle ────────────────────────────────────────────────── */
th.resizable{position:relative}
th.resizable .col-rz{position:absolute;right:0;top:0;width:5px;height:100%;cursor:col-resize;z-index:6;user-select:none}
th.resizable .col-rz:hover,th.resizable .col-rz.rz-active{background:rgba(33,115,70,.55)}

/* ── Context menu ────────────────────────────────────────────────────────── */
#ctx{display:none;position:fixed;background:#fff;border:1px solid #bbb;box-shadow:3px 3px 8px rgba(0,0,0,.2);z-index:9999;min-width:160px;border-radius:2px}
.ctx-item{padding:6px 16px;cursor:pointer;font-size:12px;color:#333}
.ctx-item:hover{background:#e8f0fe}
.ctx-sep{border-top:1px solid #eee;margin:2px 0}
.ctx-danger{color:#c00}

/* ── Modals ──────────────────────────────────────────────────────────────── */
.mo{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:8000;align-items:center;justify-content:center}
.mo.open{display:flex}
.mb{background:#fff;width:440px;max-width:96vw;border-radius:2px;box-shadow:0 8px 32px rgba(0,0,0,.3)}
.mh{background:#217346;color:#fff;padding:7px 12px;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-size:12px}
.mc-btn{background:none;border:none;color:#fff;font-size:17px;cursor:pointer;line-height:1}
.mbody{padding:12px}
.mf{margin-bottom:8px}
.mf label{display:block;font-size:11px;color:#555;margin-bottom:2px}
.mf input,.mf select{width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;height:27px;outline:none}
.mf input:focus,.mf select:focus{border-color:#217346}
.mfoot{padding:8px 12px;border-top:1px solid #eee;display:flex;gap:6px;justify-content:flex-end}
.btn-save  {background:#217346;color:#fff;border:none;padding:4px 16px;font-size:12px;cursor:pointer;border-radius:2px}
.btn-cancel{background:#e0e0e0;border:none;padding:4px 12px;font-size:12px;cursor:pointer;border-radius:2px}
.btn-del   {background:#c00;color:#fff;border:none;padding:4px 12px;font-size:12px;cursor:pointer;border-radius:2px;margin-right:auto}

/* ── Loading ─────────────────────────────────────────────────────────────── */
.ldo{display:none;position:absolute;inset:0;background:rgba(255,255,255,.65);z-index:100;align-items:center;justify-content:center}
.ldo.show{display:flex}
.spin{width:28px;height:28px;border:3px solid #ccc;border-top-color:#217346;border-radius:50%;animation:sp .7s linear infinite;margin-right:8px}
@keyframes sp{to{transform:rotate(360deg)}}

@media print{#toolbar,#tab-bar,#ctx,#fbar{display:none}.sv{display:block!important;position:static;overflow:visible}#app{height:auto}}
</style>
</head>
<body>
<div id="app">

<!-- toolbar -->
<div id="toolbar">
    <span class="tb-title">📊 FX</span>
    <div class="sep"></div>
    <label style="font-size:11px">Year</label>
    <input type="number" id="tb-year" value="{{ date('Y') }}" min="2020" max="2099" style="width:64px">
    <label style="font-size:11px">Month</label>
    <select id="tb-month">
        @for($m=1;$m<=12;$m++)
        <option value="{{ $m }}" {{ $m==date('n')?'selected':'' }}>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
        @endfor
    </select>
    <button id="tb-go">↻ Load</button>
    <div class="sep"></div>
    <button id="tb-add-cust">+ Customer</button>
    <span id="tb-status">Ready</span>
</div>

<!-- formula bar -->
<div id="fbar">
    <input id="fbar-ref" readonly value="">
    <span id="fbar-sep">ƒx</span>
    <input id="fbar-val" readonly placeholder="Select a cell…">
</div>

<!-- sheet area -->
<div id="sheet-area">
    <div class="sv active" id="sv-master">
        <div class="ldo" id="ld-master"><div class="spin"></div>Loading…</div>
        <div id="ct-master"></div>
    </div>
    <div class="sv" id="sv-detail">
        <div class="ldo" id="ld-detail"><div class="spin"></div>Loading…</div>
        <div id="ct-detail"></div>
    </div>
    @foreach($customers as $c)
    <div class="sv" id="sv-cust-{{ $c->id }}">
        <div class="ldo" id="ld-cust-{{ $c->id }}"><div class="spin"></div>Loading…</div>
        <div id="ct-cust-{{ $c->id }}"></div>
    </div>
    @endforeach
</div>

<!-- tab bar -->
<div id="tab-bar">
    <div class="tab active" data-sv="master">总表</div>
    <div class="tab" data-sv="detail">总表详情</div>
    @foreach($customers as $c)
    <div class="tab" data-sv="cust-{{ $c->id }}" data-cid="{{ $c->id }}">{{ $c->name }}</div>
    @endforeach
    <button class="tab-add" id="btn-add-tab" title="Add customer">+</button>
</div>
</div>

<!-- context menu -->
<div id="ctx">
    <div class="ctx-item" id="ctx-ins-above">Insert row above</div>
    <div class="ctx-item" id="ctx-ins-below">Insert row below</div>
    <div class="ctx-sep"></div>
    <div class="ctx-item ctx-danger" id="ctx-del-row">Delete row</div>
</div>

<!-- add customer modal -->
<div class="mo" id="mo-cust">
    <div class="mb">
        <div class="mh"><span>New Customer Sheet</span><button class="mc-btn" data-close="mo-cust">&times;</button></div>
        <div class="mbody">
            <div class="mf"><label>Name *</label><input id="mc-name" type="text"></div>
            <div class="mf"><label>Opening Balance (MYR)</label><input id="mc-bal" type="number" value="0" step="0.01"></div>
        </div>
        <div class="mfoot"><button class="btn-cancel" data-close="mo-cust">Cancel</button><button class="btn-save" id="mc-save">Create</button></div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════════════════════════
// CONFIG & HELPERS
// ═══════════════════════════════════════════════════════════════════════════════
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// Named route URLs — generated server-side so path changes never break JS
const ROUTES = {
    master:       '{{ route("fx.master") }}',
    masterDaily:  '{{ route("fx.master_daily.update") }}',
    detail:       '{{ route("fx.detail") }}',
    customer:     '{{ route("fx.customer",      ["id" => ":id"]) }}',
    customers:    '{{ route("fx.customers") }}',
    custStore:    '{{ route("fx.customers.store") }}',
    toggleToday:  '{{ route("fx.customers.toggle", ["id" => ":id"]) }}',
    txStore:      '{{ route("fx.transactions.store") }}',
    txUpdate:     '{{ route("fx.transactions.update", ["id" => ":id"]) }}',
    txDelete:     '{{ route("fx.transactions.delete", ["id" => ":id"]) }}',
    arrStore:     '{{ route("fx.arrangements.store") }}',
    arrUpdate:    '{{ route("fx.arrangements.update", ["id" => ":id"]) }}',
    arrDelete:    '{{ route("fx.arrangements.delete", ["id" => ":id"]) }}',
};

// Build a URL from a named route, substituting :id when needed
function r(name, id = null) {
    const url = ROUTES[name];
    return id !== null ? url.replace(':id', id) : url;
}

async function api(method, url, data) {
    const opts = { method, headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' } };
    if (method === 'GET' && data) return fetch(`${url}?${new URLSearchParams(data)}`, opts).then(res => res.ok ? res.json() : res.json().then(e => { throw new Error(e.message || res.status) }));
    if (data) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(data); }
    const res = await fetch(url, opts);
    if (!res.ok) { const e = await res.json().catch(() => ({})); throw new Error(e.message || res.status); }
    return res.json();
}

const ld = (id, show) => document.getElementById(id)?.classList.toggle('show', show);
const st = msg => { document.getElementById('tb-status').textContent = msg; };
const fmt = (v, d=2) => (parseFloat(v)||0).toLocaleString('en-MY',{minimumFractionDigits:d,maximumFractionDigits:d});
const fmtR = (v, d=4) => (parseFloat(v)||0).toFixed(d);

// modals
document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => document.getElementById(b.dataset.close).classList.remove('open')));
document.querySelectorAll('.mo').forEach(o => o.addEventListener('click', e => { if(e.target===o) o.classList.remove('open'); }));
const openMo = id => document.getElementById(id).classList.add('open');
const closeMo = id => document.getElementById(id).classList.remove('open');

// ═══════════════════════════════════════════════════════════════════════════════
// EXCEL GRID ENGINE
// ═══════════════════════════════════════════════════════════════════════════════
class ExcelGrid {
    constructor(container) {
        this.el       = container;
        this.sel      = null;   // currently selected td
        this.editing  = false;
        this.origVal  = '';
        this._saveTimer = null;
        this._bound   = {};

        this._bound.keydown  = this._onKeydown.bind(this);
        this._bound.mousedown= this._onMousedown.bind(this);

        container.addEventListener('mousedown',  this._bound.mousedown);
        container.addEventListener('dblclick',   e => { const td = e.target.closest('td[data-r]'); if(td) this._startEdit(td); });
        container.addEventListener('contextmenu',e => { const td = e.target.closest('td[data-r]'); if(td){ e.preventDefault(); this._select(td); showCtx(e, td); } });
        document.addEventListener('keydown',     this._bound.keydown);
    }

    destroy() {
        document.removeEventListener('keydown', this._bound.keydown);
    }

    _onMousedown(e) {
        const td = e.target.closest('td[data-r]');
        if (!td) return;
        if (this.editing && td !== this.sel) this._commitEdit();
        this._select(td);
        if (e.detail === 1 && td === this.sel && td.dataset.editable) {
            // single click on already selected → start edit on next mouseup
            this._clickTimer = setTimeout(() => this._startEdit(td), 200);
        }
    }

    _select(td) {
        clearTimeout(this._clickTimer);
        if (this.sel) this.sel.classList.remove('xl-sel');
        this.sel = td;
        td.classList.add('xl-sel');
        this._updateFbar(td);
    }

    _updateFbar(td) {
        const col = td.dataset.col || '';
        const row = td.dataset.r   || '';
        document.getElementById('fbar-ref').value = col + row;
        const xc = td.querySelector('.xc');
        const val = td.dataset.formula ? `= ${td.dataset.formula}` : (xc ? xc.textContent : '');
        document.getElementById('fbar-val').value = val;
    }

    _startEdit(td) {
        if (!td.dataset.editable) return;
        if (this.editing && this.sel === td) return;
        if (this.editing) this._commitEdit();
        this.editing = true;
        this.sel = td;
        td.classList.add('xl-editing');
        const xc = td.querySelector('.xc');
        this.origVal = xc.textContent;
        xc.contentEditable = 'true';
        xc.focus();
        // cursor to end
        const r = document.createRange(); r.selectNodeContents(xc); r.collapse(false);
        getSelection().removeAllRanges(); getSelection().addRange(r);
    }

    _commitEdit() {
        if (!this.editing) return;
        this.editing = false;
        const td  = this.sel;
        const xc  = td.querySelector('.xc');
        xc.contentEditable = 'false';
        td.classList.remove('xl-editing');
        const newVal = xc.textContent.trim();
        if (newVal !== this.origVal) {
            this._save(td, xc, newVal);
        }
    }

    _cancelEdit() {
        if (!this.editing) return;
        this.editing = false;
        const td = this.sel;
        const xc = td.querySelector('.xc');
        xc.contentEditable = 'false';
        td.classList.remove('xl-editing');
        xc.textContent = this.origVal;
    }

    async _save(td, xc, value) {
        const { savePath, saveMethod, saveField } = td.dataset;
        if (!savePath) return;
        td.classList.add('saving');
        td.classList.remove('save-err');
        try {
            const resp = await api(saveMethod || 'PATCH', savePath, { [saveField]: value });
            td.dataset.val = value;
            // trigger row-level recalc if defined
            if (td.dataset.recalcRow) recalcRow(td.closest('tr'), resp);
            // update formula bar
            this._updateFbar(td);
        } catch(e) {
            xc.textContent = this.origVal;
            td.classList.add('save-err');
            st('Save error: ' + e.message);
        }
        td.classList.remove('saving');
    }

    _onKeydown(e) {
        if (!this.sel) return;

        // editing mode
        if (this.editing) {
            if (e.key === 'Escape') { e.preventDefault(); this._cancelEdit(); return; }
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this._commitEdit(); this._move('down'); return; }
            if (e.key === 'Tab') { e.preventDefault(); this._commitEdit(); this._move(e.shiftKey ? 'left' : 'right'); return; }
            return; // let all other keys type normally
        }

        // navigation mode
        const navKeys = { ArrowUp:'up', ArrowDown:'down', ArrowLeft:'left', ArrowRight:'right', Tab: e.shiftKey?'left':'right', Enter:'down' };
        if (navKeys[e.key]) {
            e.preventDefault();
            if (e.key === 'Enter') this._commitEdit();
            this._move(navKeys[e.key]);
            return;
        }
        if (e.key === 'F2') { e.preventDefault(); this._startEdit(this.sel); return; }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (this.sel.dataset.editable) { e.preventDefault(); this._startEdit(this.sel); const xc = this.sel.querySelector('.xc'); xc.textContent=''; }
            return;
        }
        // printable char → start editing and replace content
        if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key.length === 1 && this.sel.dataset.editable) {
            this._startEdit(this.sel);
            const xc = this.sel.querySelector('.xc'); xc.textContent = e.key;
            // move cursor to end
            const r = document.createRange(); r.selectNodeContents(xc); r.collapse(false);
            getSelection().removeAllRanges(); getSelection().addRange(r);
        }
    }

    _move(dir) {
        if (!this.sel) return;
        const r = parseInt(this.sel.dataset.r);
        const c = parseInt(this.sel.dataset.ci); // column index
        let nr = r, nc = c;
        if (dir === 'up')    nr--;
        if (dir === 'down')  nr++;
        if (dir === 'left')  nc--;
        if (dir === 'right') nc++;
        const next = this.el.querySelector(`td[data-r="${nr}"][data-ci="${nc}"]`);
        if (next) { this._select(next); next.scrollIntoView({ block:'nearest', inline:'nearest' }); }
    }
}

let activeGrid = null;

function activateGrid(containerId) {
    if (activeGrid) { try { activeGrid.destroy(); } catch(e){} }
    const el = document.getElementById(containerId);
    if (el) activeGrid = new ExcelGrid(el);
}

// ═══════════════════════════════════════════════════════════════════════════════
// CONTEXT MENU
// ═══════════════════════════════════════════════════════════════════════════════
let ctxTd = null;
function showCtx(e, td) {
    ctxTd = td;
    const menu = document.getElementById('ctx');
    menu.style.display = 'block';
    menu.style.left = Math.min(e.clientX, innerWidth  - 170) + 'px';
    menu.style.top  = Math.min(e.clientY, innerHeight - 120) + 'px';
}
document.addEventListener('click', () => { document.getElementById('ctx').style.display='none'; });
document.getElementById('ctx-del-row').addEventListener('click', () => {
    if (!ctxTd) return;
    const tr = ctxTd.closest('tr');
    const txId = tr?.dataset.txId;
    const arrId = tr?.dataset.arrId;
    const custId = tr?.dataset.custId;
    if (!txId && !arrId) return;
    if (!confirm('Delete this row?')) return;
    const url = txId ? r('txDelete', txId) : r('arrDelete', arrId);
    api('DELETE', url).then(() => { tr.remove(); reindexRows(tr.closest('tbody')); if(custId) refreshCustHeader(parseInt(custId)); }).catch(e => alert(e.message));
});
document.getElementById('ctx-ins-below').addEventListener('click', () => {
    if (!ctxTd) return;
    const tr = ctxTd.closest('tr');
    if (tr?.dataset.custId) addNewTxRow(parseInt(tr.dataset.custId), tr);
});
document.getElementById('ctx-ins-above').addEventListener('click', () => {
    if (!ctxTd) return;
    const tr = ctxTd.closest('tr');
    if (tr?.dataset.custId) addNewTxRow(parseInt(tr.dataset.custId), tr, true);
});

function reindexRows(tbody) {
    if (!tbody) return;
    [...tbody.querySelectorAll('tr[data-row-idx]')].forEach((tr, i) => {
        tr.dataset.rowIdx = i;
        const rh = tr.querySelector('.rh');
        if (rh) rh.textContent = 7 + i;
        tr.querySelectorAll('td[data-r]').forEach(td => td.dataset.r = 7 + i);
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
// TABS
// ═══════════════════════════════════════════════════════════════════════════════
const loaded = new Set();
let activeSv = 'master';

function switchTab(svId) {
    activeSv = svId;
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.sv === svId));
    document.querySelectorAll('.sv').forEach(v => v.classList.toggle('active', v.id === `sv-${svId}`));
    if (!loaded.has(svId)) { loaded.add(svId); loadSheet(svId); }
    else activateGrid(`ct-${svId}`);
}

document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => switchTab(t.dataset.sv)));

function loadSheet(sv) {
    if (sv === 'master') loadMaster();
    else if (sv === 'detail') loadDetail();
    else if (sv.startsWith('cust-')) loadCust(parseInt(sv.replace('cust-','')));
}

function getYM() {
    return { year: +document.getElementById('tb-year').value, month: +document.getElementById('tb-month').value };
}

document.getElementById('tb-go').addEventListener('click', () => { loaded.delete(activeSv); loadSheet(activeSv); });

// ═══════════════════════════════════════════════════════════════════════════════
// MASTER SHEET (总表)
// ═══════════════════════════════════════════════════════════════════════════════
async function loadMaster() {
    ld('ld-master', true); st('Loading 总表…');
    try {
        const d = await api('GET', ROUTES.master, getYM());
        renderMaster(d);
        activateGrid('ct-master');
        st('Ready');
    } catch(e) { st('Error: '+e.message); }
    finally { ld('ld-master', false); }
}

function renderMaster(d) {
    const colKeys  = Object.keys(d.currency_cols || {});  // sgd,thb,pgk…
    const colLabels= Object.values(d.currency_cols || {}); // SGD,THB,PGK…
    const manual   = d.manual_daily || {};
    const { year, month } = d;

    // ── Col index map: 0=date,1..10=currencies,11=bal,12=profit,13=profit%
    // left section
    let html = `<div style="display:flex;gap:0;height:100%">
    <div id="master-left-panel" style="overflow-x:auto;flex-shrink:0">
    <table class="xl" id="xl-master-left">
    <colgroup><col style="width:36px">${'<col style="width:90px">'.repeat(1+colKeys.length+4)}</colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch" data-col="B">Date</th>
            ${colLabels.map((l,i) => `<th class="ch num" data-col="${String.fromCharCode(67+i)}">${l}</th>`).join('')}
            <th class="ch num">BAL MYR</th>
            <th class="ch num">PROFIT</th>
            <th class="ch num">PROFIT %</th>
            <th class="ch num">NEED PAY</th>
        </tr>
        <tr>
            <th class="rh">1</th>
            <td class="bg-green bold ctr" colspan="${2+colKeys.length+4}" data-r="1" data-ci="1">
                <span class="xc ro">Year: ${year} — Month: ${String(month).padStart(2,'0')}</span>
            </td>
        </tr>
        <tr>
            <th class="rh">2</th>
            <td class="bg-green bold ctr" colspan="${2+colKeys.length+4}" data-r="2" data-ci="1">
                <span class="xc ro">NEEDPAY: ${fmt(d.need_pay)} &nbsp;|&nbsp; Net BUY: ${fmt(d.header?.net_buy||0)}</span>
            </td>
        </tr>
        <tr>
            <th class="rh">3</th>
            <td class="bg-total bold ctr" data-r="3" data-ci="1"><span class="xc ro">TOTAL</span></td>
            ${colKeys.map((k,i) => {
                const tot = (d.currency_totals||[]).find(r=>r.currency===colLabels[i]);
                return `<td class="bg-total num" data-r="3" data-ci="${2+i}"><span class="xc ro">${tot?fmt(tot.total_in):''}</span></td>`;
            }).join('')}
            <td class="bg-total num" data-r="3" data-ci="${2+colKeys.length}"><span class="xc ro">${fmt(d.daily_rows?.reduce((s,r)=>s+r.myr_balance,0)||0)}</span></td>
            <td class="bg-total num" data-r="3" data-ci="${3+colKeys.length}"><span class="xc ro">${fmt(d.daily_rows?.reduce((s,r)=>s+r.profit,0)||0)}</span></td>
            <td class="bg-total" data-r="3" data-ci="${4+colKeys.length}"><span class="xc ro"></span></td>
            <td class="bg-total num" data-r="3" data-ci="${5+colKeys.length}"><span class="xc ro">${fmt(d.need_pay)}</span></td>
        </tr>
    </thead>
    <tbody>`;

    (d.daily_rows||[]).forEach((row, idx) => {
        const r     = 4 + idx;
        const mday  = manual[idx+1] || {};
        const profit= row.profit;
        html += `<tr>
            <th class="rh">${r}</th>
            <td class="bg-yellow ctr" data-r="${r}" data-ci="1"><span class="xc ro">${row.date}</span></td>
            ${colKeys.map((k,i) => {
                const val = mday[k] ?? 0;
                return `<td class="bg-lblue num" data-r="${r}" data-ci="${2+i}" data-col="${String.fromCharCode(67+i)}"
                    data-editable="1" data-save-path="${ROUTES.masterDaily}" data-save-method="PATCH"
                    data-save-field="${k}" data-extra='{"year":${year},"month":${month},"day":${idx+1}}'
                    data-val="${val}">
                    <span class="xc">${val || ''}</span>
                </td>`;
            }).join('')}
            <td class="bg-calc num" data-r="${r}" data-ci="${2+colKeys.length}" data-formula="Σ MYR">
                <span class="xc ro">${row.myr_balance ? fmt(row.myr_balance) : ''}</span>
            </td>
            <td class="num" data-r="${r}" data-ci="${3+colKeys.length}" data-formula="ΔDay" style="${profit<0?'color:#c00':''}">
                <span class="xc ro">${profit ? fmt(profit) : ''}</span>
            </td>
            <td class="num" data-r="${r}" data-ci="${4+colKeys.length}" data-formula="Profit/Vol">
                <span class="xc ro">${row.profit_rate ? (row.profit_rate*100).toFixed(2)+'%' : ''}</span>
            </td>
            <td class="num" data-r="${r}" data-ci="${5+colKeys.length}">
                <span class="xc ro"></span>
            </td>
        </tr>`;
    });

    html += `</tbody></table></div>

    <!-- panel splitter -->
    <div id="master-splitter" style="width:6px;background:#888;cursor:col-resize;flex-shrink:0;position:relative;transition:background .15s">
        <div style="position:absolute;inset:0"></div>
    </div>

    <!-- right section: customer rows -->
    <div id="master-right-panel" style="overflow-x:auto;flex:1;min-width:180px">
    <table class="xl" id="xl-master-right">
    <colgroup><col style="width:36px"><col style="width:36px"><col style="width:110px"><col style="width:90px"><col style="width:80px"><col style="width:80px"><col style="width:60px"><col style="width:60px"><col style="width:80px"><col style="width:60px"><col style="width:60px"></colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch ctr">NO.</th>
            <th class="ch">Name</th>
            <th class="ch num">Balance</th>
            <th class="ch num">BUY IN</th>
            <th class="ch num">SELL OUT</th>
            <th class="ch num">TRX IN</th>
            <th class="ch num">TRX OUT</th>
            <th class="ch num">PROFIT/day</th>
            <th class="ch num">TRX IN#</th>
            <th class="ch ctr">ACTIVE</th>
        </tr>
        <tr><th class="rh">1</th><td class="bg-green" colspan="10"><span class="xc ro"></span></td></tr>
        <tr><th class="rh">2</th><td class="bg-green" colspan="10"><span class="xc ro"></span></td></tr>
        <tr><th class="rh">3</th>${'<td class="bg-total"><span class="xc ro"></span></td>'.repeat(10)}</tr>
    </thead>
    <tbody>`;

    (d.customer_rows||[]).forEach((cr, i) => {
        const r = 4 + i;
        html += `<tr>
            <th class="rh">${r}</th>
            <td class="ctr" data-r="${r}" data-ci="20"><span class="xc ro">${i+1}</span></td>
            <td class="bold" data-r="${r}" data-ci="21" style="cursor:pointer;padding:0 4px" onclick="switchTab('cust-${cr.customer_id}')">
                <span class="xc ro" style="color:#1155cc">${cr.name}</span>
            </td>
            <td class="num" data-r="${r}" data-ci="22"><span class="xc ro">${fmt(cr.balance)}</span></td>
            <td class="num bg-lblue" data-r="${r}" data-ci="23"><span class="xc ro">${fmt(cr.buy_in)}</span></td>
            <td class="num" data-r="${r}" data-ci="24"><span class="xc ro">${fmt(cr.sell_out)}</span></td>
            <td class="ctr" data-r="${r}" data-ci="25"><span class="xc ro">${cr.count_buy_in}</span></td>
            <td class="ctr" data-r="${r}" data-ci="26"><span class="xc ro">${cr.count_sell_out}</span></td>
            <td class="num ${(cr.profit_on_date||0)<0?'':'bg-lgreen'}" data-r="${r}" data-ci="27"><span class="xc ro">${cr.profit_on_date!=null?fmt(cr.profit_on_date):''}</span></td>
            <td class="ctr" data-r="${r}" data-ci="28"><span class="xc ro">${cr.buy_in_on_date??''}</span></td>
            <td class="ctr ${cr.active_on_date?'bg-lgreen':''}" data-r="${r}" data-ci="29"><span class="xc ro">${cr.active_on_date?'✓':''}</span></td>
        </tr>`;
    });

    html += `</tbody></table></div></div>`;  /* close right-panel + flex wrapper */
    document.getElementById('ct-master').innerHTML = html;

    // wire up editable master-left cells with extra JSON payload
    document.querySelectorAll('#xl-master-left td[data-editable]').forEach(td => {
        td.dataset.editable = '1';
        const extra = JSON.parse(td.dataset.extra || '{}');
        td._saveExtra = extra;
    });

    // column-level resize on right table headers
    const rightTable = document.getElementById('xl-master-right');
    if (rightTable) makeResizable(rightTable);

    // panel-level splitter drag
    initPanelSplitter();
}

// intercept grid save for master daily cells
const _origSave = ExcelGrid.prototype._save;
ExcelGrid.prototype._save = async function(td, xc, value) {
    if (td._saveExtra) {
        const { savePath, saveField } = td.dataset;
        if (!savePath) return;
        td.classList.add('saving'); td.classList.remove('save-err');
        try {
            await api('PATCH', savePath, { ...td._saveExtra, col: saveField, value });
            td.dataset.val = value;
        } catch(e) { xc.textContent = this.origVal; td.classList.add('save-err'); st('Save error: '+e.message); }
        td.classList.remove('saving');
        return;
    }
    return _origSave.call(this, td, xc, value);
};

// ═══════════════════════════════════════════════════════════════════════════════
// DETAIL SHEET (总表详情) — read-only
// ═══════════════════════════════════════════════════════════════════════════════
async function loadDetail() {
    ld('ld-detail',true); st('Loading 总表详情…');
    try {
        const d = await api('GET', ROUTES.detail, getYM());
        renderDetail(d);
        activateGrid('ct-detail');
        st('Ready');
    } catch(e) { st('Error: '+e.message); }
    finally { ld('ld-detail',false); }
}

function renderDetail(d) {
    const dates = d.dates || [];
    let html = `<table class="xl"><colgroup><col style="width:36px"><col style="width:110px">${dates.map(()=>'<col style="width:52px">').join('')}</colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch">Customer</th>
            ${dates.map((dt,i) => `<th class="ch ctr" style="font-size:10px" data-col="${String.fromCharCode(65+i)}">${dt.slice(5)}</th>`).join('')}
        </tr>
        <tr>
            <th class="rh">3</th>
            <td class="bg-total bold" data-r="3" data-ci="1"><span class="xc ro">TOTAL</span></td>
            ${dates.map((dt,i) => {
                const cnt = (d.customers||[]).reduce((s,c)=>s+(c.dates[dt]?.buy_in_count||0),0);
                return `<td class="bg-total num" data-r="3" data-ci="${2+i}"><span class="xc ro">${cnt||''}</span></td>`;
            }).join('')}
        </tr>
    </thead><tbody>`;

    (d.customers||[]).forEach((cu,ri) => {
        html += `<tr>
            <th class="rh">${4+ri}</th>
            <td class="bold" data-r="${4+ri}" data-ci="1" style="cursor:pointer;padding:0 6px" onclick="switchTab('cust-${cu.customer_id}')">
                <span class="xc ro" style="color:#1155cc">${cu.customer_name}</span>
            </td>
            ${dates.map((dt,ci) => {
                const cell = cu.dates[dt]||{};
                const cnt = cell.buy_in_count||0, act = cell.active||0, prf = cell.profit||0;
                const inner = [cnt?`<span style="color:#1155cc;font-weight:700">${cnt}</span>`:'', act?`<span style="color:#27ae60">✓</span>`:'', prf?`<span style="color:#c00;font-size:10px">${fmt(prf)}</span>`:''].filter(Boolean).join(' ');
                return `<td class="${act?'bg-lgreen':''} ctr" data-r="${4+ri}" data-ci="${2+ci}"><span class="xc ro" style="font-size:10px">${inner}</span></td>`;
            }).join('')}
        </tr>`;
    });

    html += '</tbody></table>';
    document.getElementById('ct-detail').innerHTML = html;
}

// ═══════════════════════════════════════════════════════════════════════════════
// CUSTOMER SHEET
// ═══════════════════════════════════════════════════════════════════════════════
const custData = {}; // cache: custId → {customer, summary, transactions, arrangements}

async function loadCust(custId) {
    ld(`ld-cust-${custId}`,true); st('Loading…');
    try {
        const d = await api('GET', r('customer', custId));
        custData[custId] = d;
        renderCust(custId, d);
        activateGrid(`ct-cust-${custId}`);
        st('Ready');
    } catch(e) { st('Error: '+e.message); }
    finally { ld(`ld-cust-${custId}`,false); }
}

function renderCust(custId, d) {
    const { customer, summary, transactions, arrangements } = d;
    const CI = { // column indices for navigation
        date:0, currency:1, amtIn:2, amtOut:3, rate:4, myrConv:5, myrOut:6, myrIn:7, remark:8, costRate:9, profit:10, action:11
    };

    let html = `
    <!-- summary header -->
    <div style="background:#217346;color:#fff;padding:5px 10px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;font-size:12px;border-bottom:2px solid #1a5c38">
        <span style="font-weight:700;font-size:13px">${customer.name}</span>
        <span>Balance: <strong id="cust-bal-${custId}" style="color:#ffd700">${fmt(summary.balance)}</strong></span>
        <span>Pending MYR: <strong id="cust-pend-${custId}" style="color:#ff9800">${fmt(summary.pending_myr)}</strong></span>
        <span>Total Profit: <strong id="cust-prf-${custId}" style="color:#a5d6a7">${fmt(summary.total_profit)}</strong></span>
        <button onclick="toggleToday(${custId})" id="cust-today-btn-${custId}"
            style="background:${summary.check_today?'#e67e22':'#1a5c38'};border:1px solid rgba(255,255,255,.3);color:#fff;padding:2px 10px;cursor:pointer;font-size:11px;border-radius:2px">
            ${summary.check_today ? 'TODAY ONLY' : 'ALL TIME'}
        </button>
    </div>
    <!-- totals strip -->
    <div id="cust-totals-${custId}" style="background:#e8f5e9;padding:3px 10px;font-size:11px;border-bottom:1px solid #c8e6c9;display:flex;gap:16px">
        <span>BUY IN: <strong>${fmt(summary.totals.amount_in)}</strong></span>
        <span>SELL OUT: <strong>${fmt(summary.totals.amount_out)}</strong></span>
        <span>CONV MYR: <strong>${fmt(summary.totals.myr_converted)}</strong></span>
        <span>MYR OUT: <strong>${fmt(summary.totals.myr_out)}</strong></span>
        <span>MYR IN: <strong>${fmt(summary.totals.myr_in)}</strong></span>
    </div>

    <!-- transaction table -->
    <table class="xl" style="width:100%">
    <colgroup>
        <col style="width:36px"><col style="width:95px"><col style="width:64px">
        <col style="width:90px"><col style="width:90px"><col style="width:80px">
        <col style="width:90px"><col style="width:80px"><col style="width:80px">
        <col style="width:120px"><col style="width:80px"><col style="width:80px"><col style="width:36px">
    </colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch bg-dark" data-col="A">Date</th>
            <th class="ch bg-dark" data-col="B">Curr</th>
            <th class="ch bg-blue num" data-col="C">BUY IN</th>
            <th class="ch bg-blue num" data-col="D">SELL OUT</th>
            <th class="ch bg-gray num" data-col="E">Rate</th>
            <th class="ch bg-green num" data-col="F">CONV MYR ƒ</th>
            <th class="ch bg-teal num" data-col="G">MYR OUT</th>
            <th class="ch bg-teal num" data-col="H">MYR IN</th>
            <th class="ch bg-dark" data-col="I">Remark</th>
            <th class="ch bg-gray num" data-col="K">Cost Rate</th>
            <th class="ch bg-green num" data-col="L">PROFIT ƒ</th>
            <th class="ch"></th>
        </tr>
        <tr>
            <th class="rh">6</th>
            <td class="bg-total bold" data-r="6" data-ci="0"><span class="xc ro">TOTAL</span></td>
            <td class="bg-total" data-r="6" data-ci="1"><span class="xc ro"></span></td>
            <td class="bg-total num bold" id="t6-amtIn-${custId}"  data-r="6" data-ci="2"><span class="xc ro">${fmt(summary.totals.amount_in)}</span></td>
            <td class="bg-total num bold" id="t6-amtOut-${custId}" data-r="6" data-ci="3"><span class="xc ro">${fmt(summary.totals.amount_out)}</span></td>
            <td class="bg-total" data-r="6" data-ci="4"><span class="xc ro"></span></td>
            <td class="bg-total num bold" id="t6-conv-${custId}"   data-r="6" data-ci="5"><span class="xc ro">${fmt(summary.totals.myr_converted)}</span></td>
            <td class="bg-total num bold" id="t6-mOut-${custId}"   data-r="6" data-ci="6"><span class="xc ro">${fmt(summary.totals.myr_out)}</span></td>
            <td class="bg-total num bold" id="t6-mIn-${custId}"    data-r="6" data-ci="7"><span class="xc ro">${fmt(summary.totals.myr_in)}</span></td>
            <td class="bg-total" data-r="6" data-ci="8"><span class="xc ro"></span></td>
            <td class="bg-total" data-r="6" data-ci="9"><span class="xc ro"></span></td>
            <td class="bg-total num bold" id="t6-prf-${custId}"    data-r="6" data-ci="10"><span class="xc ro">${fmt(summary.total_profit)}</span></td>
            <td class="bg-total" data-r="6" data-ci="11"><span class="xc ro"></span></td>
        </tr>
    </thead>
    <tbody id="tx-body-${custId}">`;

    transactions.forEach((tx, i) => { html += txRowHtml(tx, i, custId); });

    // blank new-entry row
    html += newTxRowHtml(custId, transactions.length);

    html += `</tbody></table>

    <!-- arrangement table -->
    <div style="background:#37474f;color:#fff;padding:4px 10px;font-weight:600;font-size:11px;display:flex;justify-content:space-between;align-items:center;margin-top:6px">
        <span>ARRANGEMENTS — Pending: <span id="cust-pend2-${custId}">${fmt(summary.pending_myr)}</span></span>
    </div>
    <table class="xl" style="width:100%">
    <colgroup>
        <col style="width:36px"><col style="width:95px"><col style="width:130px"><col style="width:110px">
        <col style="width:80px"><col style="width:90px"><col style="width:90px"><col style="width:60px"><col style="width:90px">
    </colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch bg-dark" data-col="N">Date</th>
            <th class="ch bg-dark" data-col="O">Account No.</th>
            <th class="ch bg-dark" data-col="P">Name</th>
            <th class="ch bg-dark" data-col="Q">Bank</th>
            <th class="ch bg-blue num" data-col="R">Arranging</th>
            <th class="ch bg-green num" data-col="S">Done</th>
            <th class="ch bg-dark ctr" data-col="T">Done?</th>
            <th class="ch bg-gray" data-col="U">By</th>
        </tr>
    </thead>
    <tbody id="arr-body-${custId}">`;

    arrangements.forEach((arr, i) => { html += arrRowHtml(arr, i, custId); });
    html += newArrRowHtml(custId, arrangements.length);

    html += `</tbody></table>`;

    document.getElementById(`ct-cust-${custId}`).innerHTML = html;
}

// ── Transaction row HTML ───────────────────────────────────────────────────────
function txRowHtml(tx, idx, custId) {
    const r    = 7 + idx;
    const txId = tx.id;
    const profit= parseFloat(tx.profit||0);

    // fields that affect formula columns → trigger recalcRow on save
    const recalcFields = new Set(['amount_in','amount_out','rate','cost_rate']);

    const cell = (ci, field, val, cls='', readOnly=false) => {
        const ro       = readOnly ? 'ro' : '';
        const edit     = readOnly ? '' : 'data-editable="1"';
        const savePath = readOnly ? '' : `data-save-path="${r('txUpdate', txId)}" data-save-method="PUT" data-save-field="${field}"`;
        const formula  = readOnly && field ? `data-formula="${field}"` : '';
        const recalc   = (!readOnly && recalcFields.has(field)) ? 'data-recalc-row="1"' : '';
        return `<td class="${cls}" data-r="${r}" data-ci="${ci}" data-cust-id="${custId}" ${edit} ${savePath} ${formula} ${recalc}>
            <span class="xc ${ro}">${val}</span>
        </td>`;
    };

    return `<tr data-tx-id="${txId}" data-cust-id="${custId}" data-row-idx="${idx}" data-r="${r}">
        <th class="rh">${r}</th>
        ${cell(0, 'date',       tx.date||'',                       'bg-yellow ctr', false)}
        ${cell(1, 'currency',   tx.currency||'',                   'ctr bold',      false)}
        ${cell(2, 'amount_in',  fmt(tx.amount_in,4),               'bg-lblue num',  false)}
        ${cell(3, 'amount_out', fmt(tx.amount_out,4),              'num',           false)}
        ${cell(4, 'rate',       fmtR(tx.rate,6),                   'num',           false)}
        ${cell(5, 'E×(C−D)',    fmt(tx.myr_converted),             'bg-calc num',   true)}
        ${cell(6, 'myr_out',    fmt(tx.myr_out),                   'num',           false)}
        ${cell(7, 'myr_in',     fmt(tx.myr_in),                    'bg-lgreen num', false)}
        ${cell(8, 'remark',     tx.remark||'',                     '',              false)}
        ${cell(9, 'cost_rate',  fmtR(tx.cost_rate,6),              'num',           false)}
        ${cell(10,'C×(K−E)',    fmt(profit),                       `bg-calc num ${profit<0?'':''}`, true)}
        <td class="ctr" data-r="${r}" data-ci="11">
            <span class="xc ro" style="font-size:10px;color:#1155cc;cursor:pointer" onclick="deleteTxRow(${txId},${custId},this.closest('tr'))">✕</span>
        </td>
    </tr>`;
}

function newTxRowHtml(custId, idx) {
    const r = 7 + idx;
    const fields = ['date','currency','amount_in','amount_out','rate','','myr_out','myr_in','remark','cost_rate','',''];
    const cls    = ['bg-yellow ctr','ctr bold','bg-lblue num','num','num','bg-calc num','num','bg-lgreen num','','num','bg-calc num',''];
    const ro     = [0,0,0,0,0,1,0,0,0,0,1,1];
    return `<tr data-new-tx="${custId}" data-cust-id="${custId}" data-row-idx="${idx}" data-r="${r}">
        <th class="rh dim">*</th>
        ${fields.map((f,ci) => `<td class="${cls[ci]}" data-r="${r}" data-ci="${ci}" data-cust-id="${custId}" ${ro[ci]?'':' data-editable="1" data-new-tx-field="'+f+'"'}>
            <span class="xc ${ro[ci]?'ro dim':''}" ${!ro[ci]?'data-placeholder="'+f+'"':''}></span>
        </td>`).join('')}
    </tr>`;
}

// ── Arrangement row HTML ───────────────────────────────────────────────────────
function arrRowHtml(arr, idx, custId) {
    const r = 7 + idx;
    const cell = (ci, field, val, cls='') => `<td class="${cls}" data-r="${r}" data-ci="${ci}" data-cust-id="${custId}"
        data-editable="1" data-save-path="${r('arrUpdate', arr.id)}" data-save-method="PUT" data-save-field="${field}">
        <span class="xc">${val}</span></td>`;
    return `<tr data-arr-id="${arr.id}" data-cust-id="${custId}" data-row-idx="${idx}" class="${arr.is_done?'bg-lgreen':''}">
        <th class="rh">${r}</th>
        ${cell(0,'date',              arr.date||'',              'bg-yellow ctr')}
        ${cell(1,'account_number',    arr.account_number||'',   '')}
        ${cell(2,'beneficiary_name',  arr.beneficiary_name||'', '')}
        ${cell(3,'bank',              arr.bank||'',             '')}
        ${cell(4,'arranging_amount',  fmt(arr.arranging_amount),'bg-lblue num')}
        ${cell(5,'done_amount',       fmt(arr.done_amount),     'bg-lgreen num')}
        ${cell(6,'is_done',           arr.is_done?'YES':'NO',   'ctr')}
        ${cell(7,'processed_by',      arr.processed_by||'',     '')}
    </tr>`;
}

function newArrRowHtml(custId, idx) {
    const r = 7 + idx;
    const fields = ['date','account_number','beneficiary_name','bank','arranging_amount','done_amount','is_done','processed_by'];
    const cls    = ['bg-yellow ctr','','','','bg-lblue num','bg-lgreen num','ctr',''];
    return `<tr data-new-arr="${custId}" data-cust-id="${custId}" data-row-idx="${idx}" data-r="${r}">
        <th class="rh dim">*</th>
        ${fields.map((f,ci) => `<td class="${cls[ci]}" data-r="${r}" data-ci="${ci}" data-cust-id="${custId}" data-editable="1" data-new-arr-field="${f}">
            <span class="xc dim" data-placeholder="${f}"></span>
        </td>`).join('')}
    </tr>`;
}

// ── Inline save: intercept new-row Enter to create record ─────────────────────
// Attach to grid via event delegation on sheet area
document.getElementById('sheet-area').addEventListener('keydown', async e => {
    if (e.key !== 'Enter') return;
    const td  = e.target.closest('td');
    if (!td) return;
    const tr  = td.closest('tr');
    if (!tr) return;

    const custId = tr.dataset.newTx;
    const newArr = tr.dataset.newArr;

    if (custId) {
        e.stopPropagation();
        await saveNewTxRow(parseInt(custId), tr);
    } else if (newArr) {
        e.stopPropagation();
        await saveNewArrRow(parseInt(newArr), tr);
    }
});

async function saveNewTxRow(custId, tr) {
    const cells = [...tr.querySelectorAll('[data-new-tx-field]')];
    const data  = { fx_customer_id: custId };
    cells.forEach(td => { data[td.dataset.newTxField] = td.querySelector('.xc').textContent.trim(); });
    if (!data.date) { st('Date is required'); return; }
    try {
        st('Saving…');
        const resp = await api('POST', ROUTES.txStore, data);
        // Replace new row with saved row, add another blank
        const idx  = parseInt(tr.dataset.rowIdx);
        tr.outerHTML = txRowHtml(resp.data, idx, custId);
        const tbody= document.getElementById(`tx-body-${custId}`);
        tbody.insertAdjacentHTML('beforeend', newTxRowHtml(custId, idx+1));
        reindexRows(tbody);
        refreshCustHeader(custId);
        st('Saved');
    } catch(e) { st('Error: '+e.message); }
}

async function saveNewArrRow(custId, tr) {
    const cells = [...tr.querySelectorAll('[data-new-arr-field]')];
    const data  = { fx_customer_id: custId };
    cells.forEach(td => { data[td.dataset.newArrField] = td.querySelector('.xc').textContent.trim(); });
    if (!data.date) { st('Date is required'); return; }
    try {
        st('Saving…');
        const resp = await api('POST', ROUTES.arrStore, data);
        const idx  = parseInt(tr.dataset.rowIdx);
        tr.outerHTML = arrRowHtml(resp.data, idx, custId);
        const tbody= document.getElementById(`arr-body-${custId}`);
        tbody.insertAdjacentHTML('beforeend', newArrRowHtml(custId, idx+1));
        reindexRows(tbody);
        refreshCustHeader(custId);
        st('Saved');
    } catch(e) { st('Error: '+e.message); }
}

// ── Inline edit save: recalc F and L after C/D/E/K change ────────────────────
function recalcRow(tr) {
    if (!tr) return;
    const xc = field => tr.querySelector(`[data-save-field="${field}"] .xc`);
    const v  = field => parseFloat(xc(field)?.textContent || 0) || 0;

    const amtIn   = v('amount_in');
    const amtOut  = v('amount_out');
    const rate    = v('rate');
    const costRate= v('cost_rate');

    const conv   = rate * (amtIn - amtOut);
    const profit = amtIn * (costRate - rate);

    const convCell   = tr.querySelector('[data-formula="E×(C−D)"] .xc');
    const profitCell = tr.querySelector('[data-formula="C×(K−E)"] .xc');
    if (convCell)   convCell.textContent   = fmt(conv);
    if (profitCell) profitCell.textContent = fmt(profit);
}

// ── Delete row ────────────────────────────────────────────────────────────────
async function deleteTxRow(txId, custId, tr) {
    if (!confirm('Delete this row?')) return;
    try {
        await api('DELETE', r('txDelete', txId));
        tr.remove();
        reindexRows(document.getElementById(`tx-body-${custId}`));
        refreshCustHeader(custId);
    } catch(e) { alert(e.message); }
}

// ── Insert blank row via context menu ────────────────────────────────────────
function addNewTxRow(custId, refTr, above = false) {
    const tbody = document.getElementById(`tx-body-${custId}`);
    if (!tbody) return;
    // count existing data rows to get next index
    const rows = [...tbody.querySelectorAll('tr[data-tx-id], tr[data-new-tx]')];
    const idx  = rows.length > 0 ? rows.length - 1 : 0; // place before the existing blank row
    const newRowHtml = newTxRowHtml(custId, idx);
    if (above && refTr && refTr.parentNode === tbody) {
        refTr.insertAdjacentHTML('beforebegin', newRowHtml);
    } else if (refTr && refTr.parentNode === tbody) {
        refTr.insertAdjacentHTML('afterend', newRowHtml);
    } else {
        tbody.insertAdjacentHTML('beforeend', newRowHtml);
    }
    reindexRows(tbody);
}

// ── Refresh customer header after edits ───────────────────────────────────────
async function refreshCustHeader(custId) {
    try {
        const d = await api('GET', r('customer', custId));
        custData[custId] = d;
        const s = d.summary;
        const set = (id, val) => { const el=document.getElementById(id); if(el) { const xc=el.querySelector('.xc'); (xc||el).textContent=val; } };
        set(`cust-bal-${custId}`,   fmt(s.balance));
        set(`cust-pend-${custId}`,  fmt(s.pending_myr));
        set(`cust-pend2-${custId}`, fmt(s.pending_myr));
        set(`cust-prf-${custId}`,   fmt(s.total_profit));
        set(`t6-amtIn-${custId}`,   fmt(s.totals.amount_in));
        set(`t6-amtOut-${custId}`,  fmt(s.totals.amount_out));
        set(`t6-conv-${custId}`,    fmt(s.totals.myr_converted));
        set(`t6-mOut-${custId}`,    fmt(s.totals.myr_out));
        set(`t6-mIn-${custId}`,     fmt(s.totals.myr_in));
        set(`t6-prf-${custId}`,     fmt(s.total_profit));
    } catch(e) {}
}

// ── Toggle Check Today ────────────────────────────────────────────────────────
async function toggleToday(custId) {
    try {
        const d = await api('POST', r('toggleToday', custId));
        const btn = document.getElementById(`cust-today-btn-${custId}`);
        if(btn){ btn.textContent = d.check_today ? 'TODAY ONLY' : 'ALL TIME'; btn.style.background=d.check_today?'#e67e22':'#1a5c38'; }
        await refreshCustHeader(custId);
    } catch(e) { alert(e.message); }
}

// ── Add Customer ──────────────────────────────────────────────────────────────
['tb-add-cust','btn-add-tab'].forEach(id => document.getElementById(id)?.addEventListener('click', () => {
    document.getElementById('mc-name').value='';
    document.getElementById('mc-bal').value='0';
    openMo('mo-cust');
}));
document.getElementById('mc-save').addEventListener('click', async () => {
    const name = document.getElementById('mc-name').value.trim();
    if (!name) { alert('Name required'); return; }
    try {
        const d = await api('POST', ROUTES.custStore, { name, initial_balance: document.getElementById('mc-bal').value });
        const c = d.customer;
        // add sheet view
        const sa = document.getElementById('sheet-area');
        const div= document.createElement('div');
        div.className='sv'; div.id=`sv-cust-${c.id}`;
        div.innerHTML=`<div class="ldo" id="ld-cust-${c.id}"><div class="spin"></div>Loading…</div><div id="ct-cust-${c.id}"></div>`;
        sa.appendChild(div);
        // add tab
        const tb = document.getElementById('tab-bar');
        const tab= document.createElement('div');
        tab.className='tab'; tab.dataset.sv=`cust-${c.id}`; tab.dataset.cid=c.id; tab.textContent=c.name;
        tab.addEventListener('click', ()=>switchTab(tab.dataset.sv));
        tb.insertBefore(tab, document.getElementById('btn-add-tab'));
        closeMo('mo-cust');
        switchTab(`cust-${c.id}`);
    } catch(e){ alert(e.message); }
});

// ── Panel splitter (left / right master sections) ─────────────────────────────
function initPanelSplitter() {
    const splitter  = document.getElementById('master-splitter');
    const leftPanel = document.getElementById('master-left-panel');
    const rightPanel= document.getElementById('master-right-panel');
    if (!splitter || !leftPanel || !rightPanel) return;

    splitter.addEventListener('mousedown', e => {
        e.preventDefault();
        const startX = e.clientX;
        const startW = leftPanel.offsetWidth;
        splitter.style.background = '#217346';
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';

        const onMove = ev => {
            const w = Math.max(120, startW + ev.clientX - startX);
            leftPanel.style.width = w + 'px';
            leftPanel.style.flexShrink = '0';
        };
        const onUp = () => {
            splitter.style.background = '#888';
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup',   onUp);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup',   onUp);
    });

    // highlight on hover
    splitter.addEventListener('mouseenter', () => { splitter.style.background = '#555'; });
    splitter.addEventListener('mouseleave', () => { splitter.style.background = '#888'; });
}

// ── Column resize ─────────────────────────────────────────────────────────────
function makeResizable(table) {
    const headerCells = table.querySelectorAll('thead tr:first-child th');
    const cols        = table.querySelectorAll('colgroup col');

    headerCells.forEach((th, idx) => {
        if (th.classList.contains('rh')) return; // skip row-number header
        th.classList.add('resizable');

        const handle = document.createElement('div');
        handle.className = 'col-rz';
        th.appendChild(handle);

        handle.addEventListener('mousedown', e => {
            e.preventDefault();
            e.stopPropagation();
            const startX = e.clientX;
            const startW = th.offsetWidth;
            handle.classList.add('rz-active');

            const onMove = ev => {
                const w = Math.max(30, startW + ev.clientX - startX);
                th.style.width = w + 'px';
                if (cols[idx]) cols[idx].style.width = w + 'px';
            };
            const onUp = () => {
                handle.classList.remove('rz-active');
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        });
    });
}

// ── Boot ──────────────────────────────────────────────────────────────────────
loaded.add('master');
loadMaster();
</script>
</body>
</html>
