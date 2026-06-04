<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Customer Portal</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Segoe UI',Arial,sans-serif;font-size:12px;background:#f0f0f0;overflow:hidden;user-select:none}

/* ── Portal nav ──────────────────────────────────────────────────────────── */
#bo-nav{background:linear-gradient(to bottom,#e84040,#c43030);display:flex;height:52px;align-items:center;padding:0 14px;justify-content:space-between;flex-shrink:0}
#bo-nav .nav-title{color:#fff;font-size:14px;font-weight:700;letter-spacing:1px}
#bo-nav .nav-user{color:rgba(255,255,255,.85);font-size:12px;display:flex;align-items:center;gap:10px}
#bo-nav .nav-logout{color:#fff;background:rgba(0,0,0,.25);border:none;padding:4px 12px;font-size:11px;cursor:pointer;border-radius:2px}
#bo-nav .nav-logout:hover{background:rgba(0,0,0,.4)}

/* ── Layout ─────────────────────────────────────────────────────────────── */
#app{display:flex;flex-direction:column;height:calc(100vh - 52px)}

/* ── Toolbar ─────────────────────────────────────────────────────────────── */
#toolbar{background:#283593;color:#fff;padding:0 10px;display:flex;align-items:center;gap:8px;flex-shrink:0;height:38px}
#toolbar .tb-title{font-weight:700;font-size:13px;margin-right:6px}
#toolbar select,#toolbar input[type=number]{background:#1a237e;color:#fff;border:1px solid #3949ab;padding:1px 5px;font-size:12px;height:24px;border-radius:2px;outline:none}
#toolbar button{background:#3949ab;color:#fff;border:none;padding:0 10px;font-size:12px;cursor:pointer;height:24px;border-radius:2px}
#toolbar button:hover{background:#303f9f}
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
#tab-bar{background:#d4d4d4;border-top:2px solid #283593;display:flex;align-items:flex-end;padding:0 4px;flex-shrink:0;height:26px;overflow-x:auto;gap:1px}
.tab{padding:3px 14px;background:#bbb;border:1px solid #999;border-bottom:none;cursor:pointer;font-size:11px;border-radius:3px 3px 0 0;white-space:nowrap;color:#444}
.tab:hover{background:#ccc}
.tab.active{background:#fff;color:#000;font-weight:600;border-color:#aaa}

/* ── Excel table ─────────────────────────────────────────────────────────── */
.xl{border-collapse:collapse;table-layout:fixed;border-spacing:0}
.xl th,.xl td{border:1px solid #d0d0d0;height:20px;padding:0;position:relative;white-space:nowrap}
.rh,.ch{background:#f2f2f2;color:#666;font-weight:400;font-size:11px;text-align:center;position:sticky;z-index:3}
.rh{left:0;width:36px;border-right:2px solid #bbb;z-index:2}
.ch{top:0;height:18px;border-bottom:2px solid #bbb}
.rh.ch{z-index:4}
.xc{display:block;width:100%;height:100%;padding:0 3px;line-height:20px;overflow:hidden;text-overflow:ellipsis;outline:none;white-space:pre;cursor:default}
.xc.ro{color:#1a1a1a}
td.xl-sel{outline:2px solid #1155cc;outline-offset:-1px;z-index:10}

/* Cell colours — identical to FX sheet */
.bg-green {background:#283593!important;color:#fff!important;font-weight:600;text-align:center}
.bg-dgreen{background:#1a237e!important;color:#fff!important;font-weight:600;text-align:center}
.bg-blue  {background:#283593!important;color:#fff!important;font-weight:600;text-align:center}
.bg-teal  {background:#00acc1!important;color:#fff!important;font-weight:600;text-align:center}
.bg-dark  {background:#283593!important;color:#fff!important;font-weight:600;text-align:center}
.bg-gray  {background:#546e7a!important;color:#fff!important;font-weight:600;text-align:center}
.bg-green .xc,.bg-dgreen .xc,.bg-blue .xc,.bg-teal .xc,.bg-dark .xc,.bg-gray .xc{color:#fff!important}
.bg-yellow{background:#fffde7}
.bg-lgreen{background:#e0f7fa}
.bg-lblue {background:#e3f2fd}
.bg-total {background:#f0f0f0;font-weight:600}
.bg-calc  {background:#e8eaf6}
.num{text-align:right}
.ctr{text-align:center}
.bold{font-weight:600}
.dim{color:#999;font-style:italic}

/* ── Loading ─────────────────────────────────────────────────────────────── */
.ldo{display:none;position:absolute;inset:0;background:rgba(255,255,255,.65);z-index:100;align-items:center;justify-content:center}
.ldo.show{display:flex}
.spin{width:28px;height:28px;border:3px solid #ccc;border-top-color:#283593;border-radius:50%;animation:sp .7s linear infinite;margin-right:8px}
@keyframes sp{to{transform:rotate(360deg)}}

/* ── Empty state ─────────────────────────────────────────────────────────── */
#no-customers{display:none;align-items:center;justify-content:center;height:100%;color:#aaa;font-size:14px}
</style>
</head>
<body>

<!-- Portal nav -->
<div id="bo-nav">
    <span class="nav-title">📊 CUSTOMER PORTAL</span>
    <div class="nav-user">
        <span>{{ Auth::guard('web')->user()->username ?? Auth::guard('web')->user()->fullname ?? Auth::guard('web')->user()->email }}</span>
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="nav-logout">LOGOUT</button>
        </form>
    </div>
</div>

<div id="app">

    <!-- Toolbar -->
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
        <span id="tb-status">Loading…</span>
    </div>

    <!-- Formula bar -->
    <div id="fbar">
        <input id="fbar-ref" readonly value="">
        <span id="fbar-sep">ƒx</span>
        <input id="fbar-val" readonly placeholder="Select a cell…">
    </div>

    <!-- Sheet area -->
    <div id="sheet-area">
        <div id="no-customers">No accounts are assigned to you.</div>
    </div>

    <!-- Tab bar -->
    <div id="tab-bar"></div>

</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const BASE = '{{ rtrim(url("/"), "/") }}/portal';

async function portalGet(path, params) {
    let url = `${BASE}/${path}`;
    if (params) url += '?' + new URLSearchParams(params);
    const res = await fetch(url, { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF } });
    if (!res.ok) { const e = await res.json().catch(() => ({})); throw new Error(e.message || res.status); }
    return res.json();
}

const fmt  = (v, d=2) => (parseFloat(v)||0).toLocaleString('en-MY', { minimumFractionDigits:d, maximumFractionDigits:d });
const fmtR = (v, d=4) => (parseFloat(v)||0).toFixed(d);
const st   = msg => { document.getElementById('tb-status').textContent = msg; };
const ld   = (id, show) => document.getElementById(id)?.classList.toggle('show', show);

function getYM() {
    return {
        year:  +document.getElementById('tb-year').value,
        month: +document.getElementById('tb-month').value,
    };
}

// ── Formula bar on cell click ─────────────────────────────────────────────────
let selTd = null;
document.getElementById('sheet-area').addEventListener('mousedown', e => {
    const td = e.target.closest('td[data-r]');
    if (!td) return;
    if (selTd) selTd.classList.remove('xl-sel');
    selTd = td;
    td.classList.add('xl-sel');
    document.getElementById('fbar-ref').value = (td.dataset.col || '') + (td.dataset.r || '');
    const xc = td.querySelector('.xc');
    document.getElementById('fbar-val').value = td.dataset.formula
        ? `= ${td.dataset.formula}`
        : (xc ? xc.textContent : '');
});

// ── Tab switching ─────────────────────────────────────────────────────────────
let activeSv = null;
const loaded = new Set();

function switchTab(custId) {
    activeSv = custId;
    document.querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.cid == custId));
    document.querySelectorAll('.sv').forEach(v  => v.classList.toggle('active', v.id === `sv-cust-${custId}`));
    if (!loaded.has(custId)) { loaded.add(custId); loadCust(custId); }
}

async function loadCust(custId) {
    ld(`ld-cust-${custId}`, true);
    st('Loading…');
    try {
        const d = await portalGet(`customers/${custId}`, getYM());
        renderCust(custId, d);
        st('Ready');
    } catch(e) {
        st('Error: ' + e.message);
        const ct = document.getElementById(`ct-cust-${custId}`);
        if (ct) ct.innerHTML = `<div style="padding:20px;color:#c00;">${e.message}</div>`;
    }
    ld(`ld-cust-${custId}`, false);
}

// ── Render customer sheet (read-only, mirrors FX sheet layout exactly) ────────
function renderCust(custId, d) {
    const { customer, summary, transactions, arrangements } = d;

    let html = `
    <div style="background:#283593;color:#fff;padding:5px 10px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;font-size:12px;border-bottom:2px solid #1a237e">
        <span style="font-weight:700;font-size:13px">${customer.name}</span>
        <span>Balance: <strong style="color:#ffd700">${fmt(summary.balance)}</strong>
            <span style="font-size:10px;opacity:.7">(Opening: ${fmt(customer.initial_balance)})</span></span>
        <span>Pending MYR: <strong style="color:#ff9800">${fmt(summary.pending_myr)}</strong></span>
        <span>Total Profit: <strong style="color:#a5d6a7">${fmt(summary.total_profit)}</strong></span>
    </div>
    <div style="background:#e0f7fa;padding:3px 10px;font-size:11px;border-bottom:1px solid #b2ebf2;display:flex;gap:16px">
        <span>BUY IN: <strong>${fmt(summary.totals.amount_in,4)}</strong></span>
        <span>SELL OUT: <strong>${fmt(summary.totals.amount_out,4)}</strong></span>
        <span>CONV MYR: <strong>${fmt(summary.totals.myr_converted)}</strong></span>
        <span>MYR OUT: <strong>${fmt(summary.totals.myr_out)}</strong></span>
        <span>MYR IN: <strong>${fmt(summary.totals.myr_in)}</strong></span>
    </div>

    <table class="xl" style="width:100%">
    <colgroup>
        <col style="width:36px"><col style="width:95px"><col style="width:64px">
        <col style="width:90px"><col style="width:90px"><col style="width:80px">
        <col style="width:90px"><col style="width:80px"><col style="width:80px">
        <col style="width:120px"><col style="width:80px"><col style="width:80px">
    </colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch bg-dark"      data-col="A">Date</th>
            <th class="ch bg-dark"      data-col="B">Curr</th>
            <th class="ch bg-blue num"  data-col="C">BUY IN</th>
            <th class="ch bg-blue num"  data-col="D">SELL OUT</th>
            <th class="ch bg-gray num"  data-col="E">Rate</th>
            <th class="ch bg-green num" data-col="F">CONV MYR ƒ</th>
            <th class="ch bg-teal num"  data-col="G">MYR OUT</th>
            <th class="ch bg-teal num"  data-col="H">MYR IN</th>
            <th class="ch bg-dark"      data-col="I">Remark</th>
            <th class="ch bg-gray num"  data-col="K">Cost Rate</th>
            <th class="ch bg-green num" data-col="L">PROFIT ƒ</th>
        </tr>
        <tr>
            <th class="rh">6</th>
            <td class="bg-total bold"     data-r="6" data-ci="0"><span class="xc ro">TOTAL</span></td>
            <td class="bg-total"          data-r="6" data-ci="1"><span class="xc ro"></span></td>
            <td class="bg-total num bold" data-r="6" data-ci="2"><span class="xc ro">${fmt(summary.totals.amount_in,4)}</span></td>
            <td class="bg-total num bold" data-r="6" data-ci="3"><span class="xc ro">${fmt(summary.totals.amount_out,4)}</span></td>
            <td class="bg-total"          data-r="6" data-ci="4"><span class="xc ro"></span></td>
            <td class="bg-total num bold" data-r="6" data-ci="5"><span class="xc ro">${fmt(summary.totals.myr_converted)}</span></td>
            <td class="bg-total num bold" data-r="6" data-ci="6"><span class="xc ro">${fmt(summary.totals.myr_out)}</span></td>
            <td class="bg-total num bold" data-r="6" data-ci="7"><span class="xc ro">${fmt(summary.totals.myr_in)}</span></td>
            <td class="bg-total"          data-r="6" data-ci="8"><span class="xc ro"></span></td>
            <td class="bg-total"          data-r="6" data-ci="9"><span class="xc ro"></span></td>
            <td class="bg-total num bold" data-r="6" data-ci="10" data-formula="C×(K−E)"><span class="xc ro">${fmt(summary.total_profit)}</span></td>
        </tr>
    </thead>
    <tbody>`;

    transactions.forEach((tx, i) => {
        const r      = 7 + i;
        const profit = parseFloat(tx.profit || 0);
        html += `<tr>
            <th class="rh">${r}</th>
            <td class="bg-yellow ctr"  data-r="${r}" data-ci="0" data-col="A"><span class="xc ro">${(tx.date||'').substring(0,10)}</span></td>
            <td class="ctr bold"       data-r="${r}" data-ci="1" data-col="B"><span class="xc ro">${tx.currency||''}</span></td>
            <td class="bg-lblue num"   data-r="${r}" data-ci="2" data-col="C"><span class="xc ro">${fmt(tx.amount_in,4)}</span></td>
            <td class="num"            data-r="${r}" data-ci="3" data-col="D"><span class="xc ro">${fmt(tx.amount_out,4)}</span></td>
            <td class="num"            data-r="${r}" data-ci="4" data-col="E"><span class="xc ro">${fmtR(tx.rate,6)}</span></td>
            <td class="bg-calc num"    data-r="${r}" data-ci="5" data-col="F" data-formula="E×(C−D)"><span class="xc ro">${fmt(tx.myr_converted)}</span></td>
            <td class="num"            data-r="${r}" data-ci="6" data-col="G"><span class="xc ro">${fmt(tx.myr_out)}</span></td>
            <td class="bg-lgreen num"  data-r="${r}" data-ci="7" data-col="H"><span class="xc ro">${fmt(tx.myr_in)}</span></td>
            <td                        data-r="${r}" data-ci="8" data-col="I"><span class="xc ro">${tx.remark||''}</span></td>
            <td class="num"            data-r="${r}" data-ci="9" data-col="K"><span class="xc ro">${fmtR(tx.cost_rate,6)}</span></td>
            <td class="bg-calc num"    data-r="${r}" data-ci="10" data-col="L" data-formula="C×(K−E)" style="${profit<0?'color:#c00':''}"><span class="xc ro">${fmt(profit)}</span></td>
        </tr>`;
    });

    if (!transactions.length) {
        html += `<tr><td colspan="12" style="text-align:center;color:#aaa;padding:16px;font-style:italic;">No transactions for this period.</td></tr>`;
    }

    html += `</tbody></table>

    <div style="background:#283593;color:#fff;padding:4px 10px;font-weight:600;font-size:11px;margin-top:6px">
        ARRANGEMENTS — Pending: ${fmt(summary.pending_myr)}
    </div>
    <table class="xl" style="width:100%">
    <colgroup>
        <col style="width:36px"><col style="width:95px"><col style="width:130px"><col style="width:110px">
        <col style="width:80px"><col style="width:90px"><col style="width:90px"><col style="width:60px"><col style="width:90px">
    </colgroup>
    <thead>
        <tr>
            <th class="rh ch"></th>
            <th class="ch bg-dark"      data-col="N">Date</th>
            <th class="ch bg-dark"      data-col="O">Account No.</th>
            <th class="ch bg-dark"      data-col="P">Name</th>
            <th class="ch bg-dark"      data-col="Q">Bank</th>
            <th class="ch bg-blue num"  data-col="R">Arranging</th>
            <th class="ch bg-green num" data-col="S">Done</th>
            <th class="ch bg-dark ctr"  data-col="T">Done?</th>
            <th class="ch bg-gray"      data-col="U">By</th>
        </tr>
    </thead>
    <tbody>`;

    arrangements.forEach((arr, i) => {
        const r = 7 + i;
        html += `<tr class="${arr.is_done ? 'bg-lgreen' : ''}">
            <th class="rh">${r}</th>
            <td class="bg-yellow ctr"  data-r="${r}" data-ci="0" data-col="N"><span class="xc ro">${arr.date||''}</span></td>
            <td                        data-r="${r}" data-ci="1" data-col="O"><span class="xc ro">${arr.account_number||''}</span></td>
            <td                        data-r="${r}" data-ci="2" data-col="P"><span class="xc ro">${arr.beneficiary_name||''}</span></td>
            <td                        data-r="${r}" data-ci="3" data-col="Q"><span class="xc ro">${arr.bank||''}</span></td>
            <td class="bg-lblue num"   data-r="${r}" data-ci="4" data-col="R"><span class="xc ro">${fmt(arr.arranging_amount)}</span></td>
            <td class="bg-lgreen num"  data-r="${r}" data-ci="5" data-col="S"><span class="xc ro">${fmt(arr.done_amount)}</span></td>
            <td class="ctr"            data-r="${r}" data-ci="6" data-col="T"><span class="xc ro">${arr.is_done ? 'YES' : 'NO'}</span></td>
            <td                        data-r="${r}" data-ci="7" data-col="U"><span class="xc ro">${arr.processed_by||''}</span></td>
        </tr>`;
    });

    if (!arrangements.length) {
        html += `<tr><td colspan="9" style="text-align:center;color:#aaa;padding:12px;font-style:italic;">No arrangements.</td></tr>`;
    }

    html += `</tbody></table>`;

    document.getElementById(`ct-cust-${custId}`).innerHTML = html;
}

// ── Init: load assigned customers and build tabs ──────────────────────────────
async function init() {
    try {
        const customers = await portalGet('my-customers');

        if (!customers.length) {
            document.getElementById('no-customers').style.display = 'flex';
            st('No accounts assigned.');
            return;
        }

        const sheetArea = document.getElementById('sheet-area');
        const tabBar    = document.getElementById('tab-bar');

        customers.forEach(c => {
            const sv = document.createElement('div');
            sv.className = 'sv';
            sv.id = `sv-cust-${c.id}`;
            sv.innerHTML = `<div class="ldo" id="ld-cust-${c.id}"><div class="spin"></div>Loading…</div><div id="ct-cust-${c.id}"></div>`;
            sheetArea.appendChild(sv);

            const tab = document.createElement('div');
            tab.className  = 'tab';
            tab.dataset.cid = c.id;
            tab.textContent = c.name;
            tab.addEventListener('click', () => switchTab(c.id));
            tabBar.appendChild(tab);
        });

        switchTab(customers[0].id);
    } catch(e) {
        st('Error: ' + e.message);
    }
}

// ── Reload on year/month change ──────────────────────────────────────────────
document.getElementById('tb-go').addEventListener('click', () => {
    if (activeSv !== null) { loaded.clear(); loadCust(activeSv); }
});

init();
</script>
</body>
</html>
