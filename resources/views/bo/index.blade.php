<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Back Office</title>
<style>
/* ── Reset & base ─────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: Arial, Helvetica, sans-serif; font-size: 13px; background: #fff; }
button { cursor: pointer; }
input, select, textarea { font-family: inherit; font-size: 13px; }

/* ── Announcement bar ─────────────────────────────────────────────────── */
#ann-bar {
    background: #444;
    color: #fff;
    font-size: 12px;
    height: 22px;
    display: flex;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
}
#ann-bar .ann-date {
    flex-shrink: 0;
    padding: 0 10px;
    color: #ccc;
    font-size: 11px;
    border-right: 1px solid #666;
}
#ann-bar .ann-track-wrap {
    flex: 1;
    overflow: hidden;
    position: relative;
}
#ann-bar .ann-track {
    display: inline-block;
    white-space: nowrap;
    animation: ticker 25s linear infinite;
    padding-left: 100%;
}
#ann-bar .ann-track:hover { animation-play-state: paused; }
@keyframes ticker {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
#ann-bar .ann-link { color: #ff0; text-decoration: none; margin-left: 6px; }

/* ── Navigation bar ───────────────────────────────────────────────────── */
#nav-bar {
    background: linear-gradient(to bottom, #e84040, #c43030);
    display: flex;
    height: 62px;
}
#nav-bar .nav-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    transition: background 0.15s;
    border-right: 1px solid rgba(255,255,255,0.15);
}
#nav-bar .nav-btn:last-child { border-right: none; }
#nav-bar .nav-btn:hover  { background: rgba(0,0,0,0.15); }
#nav-bar .nav-btn.active { background: rgba(0,0,0,0.3); }

/* ── Page sections ────────────────────────────────────────────────────── */
.page-section { display: none; }
.page-section.active { display: block; }

/* ── Shared form area (cream background) ─────────────────────────────── */
.filter-wrap {
    background: #fef9e7;
    padding: 6px 0;
    border-bottom: 1px solid #e8d97f;
}
.filter-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f0e490;
    padding: 4px 8px;
    min-height: 32px;
}
.filter-row:last-child { border-bottom: none; }
.filter-label {
    width: 90px;
    font-weight: bold;
    color: #333;
    flex-shrink: 0;
}
.filter-colon { width: 14px; flex-shrink: 0; color: #555; }
.filter-input {
    flex: 1;
    border: 1px solid #d4c87a;
    background: #fff;
    padding: 4px 8px;
    outline: none;
    height: 28px;
}
.filter-input:focus { border-color: #a89e30; }
select.filter-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; padding-right: 24px; }
.filter-half { flex: 1; display: flex; gap: 0; }
.filter-half .filter-input { flex: 1; }
.filter-half .filter-input:first-child { border-right: none; }

.btn-green {
    display: block; width: 100%; height: 36px;
    background: #27ae60; color: #fff; border: none;
    font-size: 13px; font-weight: bold; letter-spacing: 1px; cursor: pointer;
}
.btn-green:hover { background: #229954; }
.btn-orange {
    display: block; width: 100%; height: 36px;
    background: #e67e22; color: #fff; border: none;
    font-size: 13px; font-weight: bold; letter-spacing: 1px; cursor: pointer;
}
.btn-orange:hover { background: #ca6f1e; }

/* ── Summary row ──────────────────────────────────────────────────────── */
.summary-row {
    background: #fef9e7;
    padding: 5px 8px;
    font-size: 12px;
    color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e8d97f;
}
.advanced-link { color: #0077cc; text-decoration: none; font-size: 12px; cursor: pointer; }

/* ── Table ────────────────────────────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th {
    background: #333;
    color: #fff;
    padding: 7px 8px;
    text-align: left;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}
.data-table thead th:last-child { text-align: right; }
.data-table tbody td {
    padding: 5px 8px;
    border-bottom: 1px solid #e8e8e8;
    font-size: 12px;
    vertical-align: middle;
}
.data-table tbody tr:nth-child(even) { background: #f9f9f9; }
.data-table tfoot td {
    padding: 5px 8px;
    font-weight: bold;
    font-size: 12px;
    background: #fef9e7;
    border-top: 2px solid #ccc;
}
.tbl-link { color: #0077cc; text-decoration: none; cursor: pointer; font-size: 11px; margin-right: 6px; }
.tbl-link:hover { text-decoration: underline; }
.tbl-btn { font-size: 11px; padding: 2px 7px; border: 1px solid #bbb; background: #f5f5f5; cursor: pointer; margin-right: 2px; border-radius: 2px; }
.tbl-btn:hover { background: #e8e8e8; }

/* ── Bank Transaction page specific ───────────────────────────────────── */
#page-banktx .btx-toolbar {
    background: #e0e0e0;
    padding: 6px 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
#page-banktx .btx-toolbar input[type=date] {
    border: 1px solid #aaa; padding: 3px 6px; height: 28px; font-size: 12px;
}
#page-banktx .btx-toolbar select {
    border: 1px solid #aaa; padding: 3px 20px 3px 6px; height: 28px; font-size: 12px;
    appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 6px center; background-color:#fff;
}
#page-banktx .btx-toolbar input[type=text] {
    border: 1px solid #aaa; padding: 3px 6px; height: 28px; font-size: 12px;
}
#page-banktx .btx-toolbar .icon-btn {
    width: 28px; height: 28px; border: 1px solid #aaa; background: #f5f5f5;
    display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px;
}
.bank-selector-btn {
    background: #00bcd4; color: #fff; border: none; padding: 5px 14px;
    font-size: 12px; font-weight: bold; cursor: pointer; min-height: 28px;
}
.bank-selector-btn:hover { background: #00a0b5; }
.btx-summary {
    display: flex; background: #00bcd4; color: #fff; padding: 0;
}
.btx-summary .s-cell {
    flex: 1; padding: 5px 10px; font-size: 12px; font-weight: bold;
    border-right: 1px solid rgba(255,255,255,0.3);
}
.btx-summary .s-cell:last-child { border-right: none; }
.btx-summary .s-val { font-size: 14px; }

/* Editable cell */
.editable-cell { outline: none; min-width: 40px; }
.editable-cell:focus { background: #fffde7; box-shadow: inset 0 0 0 1px #e6b800; }
.editing-row td { background: #fffde7 !important; }

/* ── Cashflow page ────────────────────────────────────────────────────── */
#page-cashflow { display: none; }
#page-cashflow.active { display: flex; }
.cf-sidebar {
    width: 180px; flex-shrink: 0; border-right: 1px solid #ddd;
    background: #f5f5f5; padding-top: 10px;
}
.cf-sidebar h3 { font-size: 13px; color: #555; padding: 4px 12px 8px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 6px; }
.cf-sidebar ul { list-style: none; }
.cf-sidebar ul li a {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 12px; color: #333; text-decoration: none; font-size: 12px;
}
.cf-sidebar ul li a:hover { background: #e8e8e8; }
.cf-sidebar ul li a.active { color: #e04040; font-weight: bold; }
.cf-sidebar ul li.sub a { padding-left: 24px; color: #666; font-size: 11px; }
.cf-content { flex: 1; padding: 16px; overflow-y: auto; }
.cf-content h2 { color: #e04040; font-size: 16px; margin-bottom: 14px; }
.cf-filter-row { display: flex; align-items: center; margin-bottom: 10px; gap: 10px; }
.cf-filter-row label { width: 70px; color: #333; font-size: 12px; flex-shrink: 0; }
.cf-colon { width: 14px; flex-shrink: 0; }
.cf-date-range { display: flex; gap: 6px; }
.cf-date-range input { border: 1px solid #ccc; padding: 3px 6px; font-size: 12px; height: 28px; }
.cf-toggle { display: flex; gap: 0; }
.cf-toggle button {
    padding: 4px 14px; border: 1px solid #ccc; background: #f0f0f0;
    font-size: 12px; cursor: pointer;
}
.cf-toggle button:not(:first-child) { border-left: none; }
.cf-toggle button.active { background: #007bff; color: #fff; border-color: #007bff; }
.cf-select { border: 1px solid #ccc; padding: 3px 24px 3px 6px; font-size: 12px; height: 28px; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 6px center; background-color:#fff; }
.recalc-link { color: #0077cc; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-block; margin-bottom: 10px; }
.recalc-link:hover { text-decoration: underline; }

/* ── Banks page ───────────────────────────────────────────────────────── */
#page-banks .banks-toolbar {
    display: flex; align-items: center; gap: 8px; padding: 8px;
    background: #f9f9f9; border-bottom: 1px solid #ddd;
}
#page-banks .banks-toolbar label { font-size: 12px; }
#page-banks .banks-toolbar select { border: 1px solid #ccc; padding: 3px 24px 3px 6px; font-size: 12px; height: 28px; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23666'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 6px center; background-color:#fff; }
.create-btn { background: #e67e22; color: #fff; border: none; padding: 5px 16px; font-size: 12px; font-weight: bold; cursor: pointer; border-radius: 2px; }
.create-btn:hover { background: #ca6f1e; }

/* ── Admins page ──────────────────────────────────────────────────────── */
.admin-filter-wrap { background: #fef9e7; border-bottom: 1px solid #e8d97f; }
.admin-btn-row { display: flex; gap: 0; }
.admin-btn-row .btn-green { border-right: 1px solid #1e8449; }

/* ── Right sidebar (menu) ─────────────────────────────────────────────── */
#menu-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 999;
}
#menu-overlay.open { display: block; }
#menu-sidebar {
    position: fixed; top: 0; right: -300px; width: 280px; height: 100%;
    background: #333; color: #fff; z-index: 1000; transition: right 0.25s ease;
    display: flex; flex-direction: column; overflow-y: auto;
}
#menu-sidebar.open { right: 0; }
.menu-top { padding: 12px 14px; border-bottom: 1px solid #444; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.menu-top select { border: 1px solid #666; background: #222; color: #fff; padding: 3px 8px; font-size: 12px; }
.menu-tz { font-size: 11px; color: #aaa; }
.menu-item {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px; border-bottom: 1px solid #444;
    color: #ddd; font-size: 13px; cursor: pointer; text-decoration: none;
}
.menu-item:hover { background: #444; }
.menu-item .mi-icon { font-size: 16px; width: 20px; text-align: center; }

/* ── Modal ────────────────────────────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 2000;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff; width: 420px; max-width: 95vw;
    border-radius: 3px; overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.modal-header {
    background: #e84040; color: #fff; padding: 10px 14px;
    display: flex; justify-content: space-between; align-items: center;
    font-weight: bold; font-size: 13px;
}
.modal-close { background: none; border: none; color: #fff; font-size: 18px; cursor: pointer; line-height: 1; }
.modal-body { padding: 14px; }
.modal-field { margin-bottom: 10px; }
.modal-field label { display: block; font-size: 12px; color: #555; margin-bottom: 3px; }
.modal-field input, .modal-field select, .modal-field textarea {
    width: 100%; border: 1px solid #ccc; padding: 5px 8px; font-size: 12px; height: 30px;
}
.modal-field textarea { height: 60px; resize: vertical; }
.modal-footer { padding: 10px 14px; border-top: 1px solid #eee; display: flex; gap: 8px; justify-content: flex-end; }
.modal-footer .btn-save { background: #27ae60; color: #fff; border: none; padding: 6px 20px; font-size: 12px; font-weight: bold; cursor: pointer; }
.modal-footer .btn-cancel { background: #ccc; border: none; padding: 6px 14px; font-size: 12px; cursor: pointer; }

/* ── Scrollbar ────────────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-thumb { background: #bbb; border-radius: 3px; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 600px) {
    .filter-label { width: 70px; font-size: 12px; }
    .data-table thead th, .data-table tbody td { padding: 4px 5px; font-size: 11px; }
    #menu-sidebar { width: 100%; right: -100%; }
}
</style>
</head>
<body>

<!-- ── Announcement bar ─────────────────────────────────────────────────── -->
<div id="ann-bar">
    <div class="ann-date" id="ann-clock">--</div>
    <div class="ann-track-wrap">
        <span class="ann-track" id="ann-text">Loading…</span>
    </div>
</div>

<!-- ── Navigation bar ──────────────────────────────────────────────────── -->
<div id="nav-bar">
    <button class="nav-btn active" data-page="transactions"  title="Transactions">
        <!-- arrows icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4"/></svg>
    </button>
    <button class="nav-btn" data-page="banktx"        title="Bank Transaction">
        <!-- grid icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    </button>
    <button class="nav-btn" data-page="cashflow"      title="Cashflow">
        <!-- trend-up icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
    </button>
    <button class="nav-btn" data-page="banks"         title="Banks">
        <!-- bank/institution icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 2 7 22 7"/></svg>
    </button>
    <button class="nav-btn" data-page="admins"        title="Admin">
        <!-- id card icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><circle cx="8" cy="13" r="2"/><path d="M14 11h4M14 15h4M2 10h20"/></svg>
    </button>
    <button class="nav-btn" data-page="menu"          title="Menu">
        <!-- hamburger icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="6"  x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PAGE: TRANSACTIONS                                                      -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-transactions" class="page-section active">
    <div class="filter-wrap">
        <!-- basic filters -->
        <div class="filter-row">
            <span class="filter-label">ID</span>
            <span class="filter-colon">:</span>
            <input id="tx-id" class="filter-input" type="text" placeholder="Search by Transaction ID">
        </div>
        <div class="filter-row">
            <span class="filter-label">Customer</span>
            <span class="filter-colon">:</span>
            <input id="tx-customer" class="filter-input" type="text" placeholder="Search by Customer ID / Phone">
        </div>
        <div class="filter-row">
            <span class="filter-label">Type</span>
            <span class="filter-colon">:</span>
            <select id="tx-type" class="filter-input">
                <option value="all">ALL</option>
                <option value="active" selected>ACTIVE</option>
                <option value="inactive">INACTIVE</option>
            </select>
        </div>
        <div class="filter-row">
            <span class="filter-label">Date</span>
            <span class="filter-colon">:</span>
            <div class="filter-half">
                <input id="tx-date-from" class="filter-input" type="date" value="{{ date('Y-m-d') }}">
                <input id="tx-date-to"   class="filter-input" type="date" placeholder="End Date">
            </div>
        </div>
        <div class="filter-row">
            <span class="filter-label">Amount</span>
            <span class="filter-colon">:</span>
            <div class="filter-half">
                <input id="tx-amount-min" class="filter-input" type="number" step="0.01" placeholder="Min">
                <input id="tx-amount-max" class="filter-input" type="number" step="0.01" placeholder="Max">
            </div>
        </div>
        <div class="filter-row">
            <span class="filter-label">Status</span>
            <span class="filter-colon">:</span>
            <select id="tx-status" class="filter-input">
                <option value="pending_new" selected>PENDING (NEW TO OLD)</option>
                <option value="pending_old">PENDING (OLD TO NEW)</option>
                <option value="approved">APPROVED</option>
                <option value="rejected">REJECTED</option>
            </select>
        </div>
        <!-- advanced filters (hidden by default) -->
        <div id="tx-advanced-filters" style="display:none;">
            <div class="filter-row">
                <span class="filter-label">Agent</span>
                <span class="filter-colon">:</span>
                <input id="tx-agent" class="filter-input" type="text" placeholder="Search by agent username">
            </div>
            <div class="filter-row">
                <span class="filter-label">Mer. Bank</span>
                <span class="filter-colon">:</span>
                <select id="tx-bank" class="filter-input">
                    <option value="all">Please Select</option>
                </select>
            </div>
            <div class="filter-row">
                <span class="filter-label">Other Info</span>
                <span class="filter-colon">:</span>
                <input id="tx-other-info" class="filter-input" type="text" placeholder="e.g. Member Bank Acc No / Remark">
            </div>
        </div>
    </div>
    <button class="btn-green" id="tx-search-btn">SEARCH</button>
    <div id="tx-export-wrap" style="display:none;"><button class="btn-orange" id="tx-export-btn">EXPORT</button></div>
    <div class="summary-row">
        <span>Record: <strong id="tx-record-count">0</strong>&nbsp; Total: <strong id="tx-total">0.00</strong></span>
        <a class="advanced-link" id="tx-advanced-toggle">ADVANCED &#9658;</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody id="tx-tbody">
                <tr><td colspan="2" style="text-align:center;color:#999;padding:20px;">Use the filters above and click SEARCH.</td></tr>
            </tbody>
        </table>
    </div>
    <div id="tx-pagination" style="padding:6px 8px;font-size:12px;color:#555;"></div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PAGE: BANK TRANSACTION                                                  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-banktx" class="page-section">
    <div class="btx-toolbar">
        <input type="date" id="btx-date" value="{{ date('Y-m-d') }}">
        <button class="icon-btn" title="Copy" id="btx-copy-btn">&#128203;</button>
        <button class="icon-btn" title="Alert" id="btx-alert-btn">&#9888;</button>
        <select id="btx-type-filter">
            <option value="all">ALL</option>
            <option value="in">IN</option>
            <option value="out">OUT</option>
        </select>
        <select id="btx-subtype-filter">
            <option value="all">ALL</option>
        </select>
        <input type="text" id="btx-search" placeholder="Search Description / ID" style="flex:1;min-width:120px;">
        <select id="btx-bank-select" style="max-width:160px;">
            <option value="">-- Select Bank --</option>
        </select>
    </div>
    <div style="padding:6px 8px;background:#e8e8e8;">
        <button class="bank-selector-btn" id="btx-active-bank-btn">Select a bank</button>
    </div>
    <div class="btx-summary">
        <div class="s-cell">START<br><span class="s-val" id="btx-start">0.00</span></div>
        <div class="s-cell">TODAY<br><span class="s-val" id="btx-today">0.00</span></div>
        <div class="s-cell">BALANCE<br><span class="s-val" id="btx-balance">0.00</span></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table" id="btx-table">
            <thead>
                <tr>
                    <th style="width:36px;">NO.</th>
                    <th>DESCRIPTION</th>
                    <th style="width:90px;">IN</th>
                    <th style="width:90px;">OUT</th>
                    <th style="width:80px;">TIME</th>
                    <th style="width:90px;">ID</th>
                    <th style="width:80px;">MATCH</th>
                    <th style="width:80px;">FEE</th>
                    <th style="width:100px;">REMARKS</th>
                    <th style="width:80px;">INFO</th>
                </tr>
            </thead>
            <tbody id="btx-tbody">
                <tr id="btx-empty-row"><td colspan="10" style="text-align:center;color:#999;padding:20px;">Select a bank to load transactions.</td></tr>
                <tr id="btx-new-row" style="display:none;">
                    <td></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="description" placeholder="Press enter to save"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="amount_in"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="amount_out"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="time"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="ref_id"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="match"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="fee"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="remarks"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="info"></span></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">TOTAL</td>
                    <td id="btx-total-in">0.00</td>
                    <td id="btx-total-out">0.00</td>
                    <td colspan="5"></td>
                    <td id="btx-total-fee">0.00</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PAGE: CASHFLOW                                                          -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-cashflow" class="page-section">
    <div class="cf-sidebar">
        <h3>&#128200; Reports</h3>
        <ul>
            <li><a href="#" class="active cf-nav" data-report="transaction">Transaction <span>&#9654;</span></a></li>
            <li class="sub"><a href="#" class="cf-nav" data-report="bank">Bank</a></li>
            <li class="sub"><a href="#" class="cf-nav" data-report="staff">Staff</a></li>
            <li class="sub"><a href="#" class="cf-nav" data-report="activity">Activity Log</a></li>
        </ul>
    </div>
    <div class="cf-content">
        <h2 id="cf-title">Transaction Report</h2>
        <div class="cf-filter-row">
            <label>Period</label><span class="cf-colon">:</span>
            <div class="cf-date-range">
                <input type="date" id="cf-from" value="{{ date('Y-m-01') }}">
                <input type="date" id="cf-to"   value="{{ date('Y-m-t') }}">
            </div>
        </div>
        <div class="cf-filter-row">
            <label>Display</label><span class="cf-colon">:</span>
            <div class="cf-toggle">
                <button class="active" data-display="daily">Daily</button>
                <button data-display="monthly">Monthly</button>
                <button data-display="yearly">Yearly</button>
            </div>
        </div>
        <div class="cf-filter-row">
            <label>Type</label><span class="cf-colon">:</span>
            <select id="cf-type" class="cf-select">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <a class="recalc-link" id="cf-recalc">RECALCULATE</a>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Deposit</th>
                        <th></th>
                        <th>Withdraw</th>
                        <th></th>
                        <th>Net</th>
                    </tr>
                </thead>
                <tbody id="cf-tbody"></tbody>
                <tfoot>
                    <tr id="cf-tfoot">
                        <td><strong>Total</strong></td>
                        <td id="cf-tot-dep-cnt">0</td>
                        <td id="cf-tot-dep-amt">0.00</td>
                        <td id="cf-tot-wd-cnt">0</td>
                        <td id="cf-tot-wd-amt">0.00</td>
                        <td id="cf-tot-net">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PAGE: BANKS                                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-banks" class="page-section">
    <div class="banks-toolbar">
        <label>Status :</label>
        <select id="banks-status-filter">
            <option value="all">ALL</option>
            <option value="active">ACTIVE</option>
            <option value="inactive">INACTIVE</option>
        </select>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bank ID</th>
                    <th>D. Order</th>
                    <th>Gateway</th>
                    <th>Bank Name</th>
                    <th>Account Name</th>
                    <th>Account Number</th>
                    <th>Balance</th>
                    <th>Remark</th>
                    <th>Action</th>
                    <th>Config</th>
                </tr>
            </thead>
            <tbody id="banks-tbody">
                <tr><td colspan="10" style="text-align:center;color:#999;padding:20px;">Loading…</td></tr>
            </tbody>
        </table>
    </div>
    <div style="padding:8px;">
        <button class="create-btn" id="banks-create-btn">CREATE</button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PAGE: ADMINS                                                            -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-admins" class="page-section">
    <div class="admin-filter-wrap filter-wrap">
        <div class="filter-row">
            <span class="filter-label">Name</span>
            <span class="filter-colon">:</span>
            <input id="adm-name" class="filter-input" type="text" placeholder="">
        </div>
        <div class="filter-row">
            <span class="filter-label">Role</span>
            <span class="filter-colon">:</span>
            <select id="adm-role" class="filter-input">
                <option value="all">ALL</option>
                <option value="admin" selected>ADMIN</option>
                <option value="agent">AGENT</option>
            </select>
        </div>
        <div class="filter-row">
            <span class="filter-label">Status</span>
            <span class="filter-colon">:</span>
            <select id="adm-status" class="filter-input">
                <option value="all">ALL</option>
                <option value="active" selected>ACTIVE</option>
                <option value="inactive">INACTIVE</option>
            </select>
        </div>
    </div>
    <div class="admin-btn-row">
        <button class="btn-green" id="adm-search-btn" style="flex:1;">SEARCH</button>
        <button class="btn-orange" id="adm-create-btn" style="flex:1;">CREATE</button>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Created By</th>
                    <th>Last Login</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody id="adm-tbody">
                <tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">Click SEARCH to load.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- RIGHT SIDEBAR: MENU                                                     -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="menu-overlay"></div>
<div id="menu-sidebar">
    <div class="menu-top">
        <select id="menu-lang">
            <option value="en">English</option>
            <option value="zh">中文</option>
            <option value="ms">Bahasa</option>
        </select>
        <div class="menu-tz">System: +08:00 Device: <span id="menu-device-tz">+08:00</span></div>
    </div>
    <a class="menu-item" id="menu-setting-btn">
        <span class="mi-icon">&#9881;</span> SETTING
    </a>
    <a class="menu-item" id="menu-security-btn">
        <span class="mi-icon">&#128274;</span> SECURITY
    </a>
    <a class="menu-item" id="menu-password-btn">
        <span class="mi-icon">&#128272;</span> PASSWORD
    </a>
    <a class="menu-item" id="menu-logout-btn">
        <span class="mi-icon">&#128682;</span> LOGOUT
    </a>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- MODALS                                                                  -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->

<!-- Bank Create/Edit Modal -->
<div class="modal-overlay" id="modal-bank">
    <div class="modal-box">
        <div class="modal-header">
            <span id="modal-bank-title">Create Bank</span>
            <button class="modal-close" data-close="modal-bank">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modal-bank-id">
            <div class="modal-field"><label>Bank Name *</label><input type="text" id="mb-bank-name"></div>
            <div class="modal-field"><label>Account Name *</label><input type="text" id="mb-account-name"></div>
            <div class="modal-field"><label>Account Number *</label><input type="text" id="mb-account-number"></div>
            <div class="modal-field"><label>Gateway</label><input type="text" id="mb-gateway"></div>
            <div class="modal-field"><label>Display Order</label><input type="number" id="mb-display-order" value="0"></div>
            <div class="modal-field"><label>Remark</label><textarea id="mb-remark"></textarea></div>
            <div class="modal-field"><label>Status</label>
                <select id="mb-status" style="height:30px;width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;">
                    <option value="active">ACTIVE</option>
                    <option value="inactive">INACTIVE</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-bank">Cancel</button>
            <button class="btn-save" id="modal-bank-save">Save</button>
        </div>
    </div>
</div>

<!-- Bank Edit Amount Modal -->
<div class="modal-overlay" id="modal-bank-amount">
    <div class="modal-box">
        <div class="modal-header">
            <span>Edit Balance</span>
            <button class="modal-close" data-close="modal-bank-amount">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mba-bank-id">
            <div class="modal-field"><label>Balance</label><input type="number" step="0.01" id="mba-balance"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-bank-amount">Cancel</button>
            <button class="btn-save" id="mba-save">Save</button>
        </div>
    </div>
</div>

<!-- Admin Create/Edit Modal -->
<div class="modal-overlay" id="modal-admin">
    <div class="modal-box">
        <div class="modal-header">
            <span id="modal-admin-title">Create Admin</span>
            <button class="modal-close" data-close="modal-admin">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="modal-admin-id">
            <div class="modal-field"><label>Username *</label><input type="text" id="ma-username"></div>
            <div class="modal-field"><label>Full Name *</label><input type="text" id="ma-fullname"></div>
            <div class="modal-field"><label>Email</label><input type="email" id="ma-email"></div>
            <div class="modal-field"><label>Password <span id="ma-pw-hint" style="color:#aaa;font-size:11px;">(leave blank to keep)</span></label><input type="password" id="ma-password"></div>
            <div class="modal-field"><label>Role *</label>
                <select id="ma-role" style="height:30px;width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;">
                    <option value="admin">ADMIN</option>
                    <option value="agent">AGENT</option>
                </select>
            </div>
            <div class="modal-field"><label>Status</label>
                <select id="ma-status" style="height:30px;width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;">
                    <option value="active">ACTIVE</option>
                    <option value="inactive">INACTIVE</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-admin">Cancel</button>
            <button class="btn-save" id="modal-admin-save">Save</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="modal-password">
    <div class="modal-box">
        <div class="modal-header">
            <span>Change Password</span>
            <button class="modal-close" data-close="modal-password">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-field"><label>Current Password</label><input type="password" id="pw-current"></div>
            <div class="modal-field"><label>New Password</label><input type="password" id="pw-new"></div>
            <div class="modal-field"><label>Confirm Password</label><input type="password" id="pw-confirm"></div>
            <div id="pw-error" style="color:#c00;font-size:12px;margin-top:6px;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-password">Cancel</button>
            <button class="btn-save" id="pw-save">Save</button>
        </div>
    </div>
</div>

<script>
// ── Globals ──────────────────────────────────────────────────────────────────
const BASE   = '{{ rtrim(url("/"), "/") }}';
const ADMIN  = '{{ config("services.url.admin_path", "admin") }}';
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const API    = `${BASE}/${ADMIN}/bo`;

let txAdvanced     = false;
let cfDisplay      = 'daily';
let btxActiveBankId = null;
let btxActiveBankName = '';
let allBanks       = [];

// ── AJAX helper ───────────────────────────────────────────────────────────────
async function api(method, path, data = null) {
    const opts = {
        method,
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    };
    let url = `${API}/${path}`;
    if (method === 'GET' && data) {
        url += '?' + new URLSearchParams(data).toString();
    } else if (data) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(data);
    }
    const res = await fetch(url, opts);
    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.message || `HTTP ${res.status}`);
    }
    return res.json();
}

// ── Clock ─────────────────────────────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    const wd  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][now.getDay()];
    const pad = n => String(n).padStart(2,'0');
    document.getElementById('ann-clock').textContent =
        `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} (${wd}) ` +
        `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}
updateClock();
setInterval(updateClock, 1000);

// ── Announcement ──────────────────────────────────────────────────────────────
async function loadAnnouncement() {
    try {
        const d = await api('GET', 'announcement');
        const el = document.getElementById('ann-text');
        el.innerHTML = `${d.text} — To get latest Maintenance Announcement click → <a class="ann-link" href="#">Here</a>`;
    } catch(e) {}
}
loadAnnouncement();

// ── Navigation ────────────────────────────────────────────────────────────────
document.querySelectorAll('#nav-bar .nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const page = btn.dataset.page;
        if (page === 'menu') { openMenu(); return; }

        document.querySelectorAll('#nav-bar .nav-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.page-section').forEach(s => s.classList.remove('active'));
        document.getElementById(`page-${page}`).classList.add('active');

        // lazy-load on first visit
        if (page === 'banks')    loadBanks();
        if (page === 'banktx')   initBankTx();
        if (page === 'cashflow') loadCashflow();
    });
});

// ── Modals ────────────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.close));
});
document.querySelectorAll('.modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) ov.classList.remove('open'); });
});

// ─────────────────────────────────────────────────────────────────────────────
// TRANSACTIONS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('tx-advanced-toggle').addEventListener('click', () => {
    txAdvanced = !txAdvanced;
    document.getElementById('tx-advanced-filters').style.display = txAdvanced ? '' : 'none';
    document.getElementById('tx-export-wrap').style.display      = txAdvanced ? '' : 'none';
    document.getElementById('tx-advanced-toggle').textContent    = txAdvanced ? 'ADVANCED ◀' : 'ADVANCED ▶';
});

document.getElementById('tx-search-btn').addEventListener('click', loadTransactions);
document.getElementById('tx-export-btn')?.addEventListener('click', () => {
    api('GET', 'transactions/export').then(() => alert('Export queued.')).catch(e => alert(e.message));
});

async function loadTransactions(page = 1) {
    const params = {
        transaction_id: document.getElementById('tx-id').value,
        customer:       document.getElementById('tx-customer').value,
        type:           document.getElementById('tx-type').value,
        date_from:      document.getElementById('tx-date-from').value,
        date_to:        document.getElementById('tx-date-to').value,
        amount_min:     document.getElementById('tx-amount-min').value,
        amount_max:     document.getElementById('tx-amount-max').value,
        status_order:   document.getElementById('tx-status').value,
        page,
    };
    if (txAdvanced) {
        params.agent      = document.getElementById('tx-agent').value;
        params.bo_bank_id = document.getElementById('tx-bank').value;
        params.other_info = document.getElementById('tx-other-info').value;
    }
    try {
        const d = await api('GET', 'transactions', params);
        document.getElementById('tx-record-count').textContent = d.records;
        document.getElementById('tx-total').textContent        = d.total;

        const tbody = document.getElementById('tx-tbody');
        if (!d.data.length) {
            tbody.innerHTML = `<tr><td colspan="2" style="text-align:center;color:#999;padding:20px;">No records found.</td></tr>`;
            return;
        }
        tbody.innerHTML = d.data.map(tx => `
            <tr>
                <td>
                    <div><strong>${tx.transaction_id}</strong></div>
                    <div style="color:#666;font-size:11px;">${tx.customer_id ?? ''} ${tx.customer_phone ?? ''} &bull; ${tx.type} &bull; ${tx.status}</div>
                    <div style="color:#333;">${tx.amount}</div>
                </td>
                <td style="text-align:right;">
                    <button class="tbl-btn">VIEW</button>
                </td>
            </tr>`).join('');

        renderPagination('tx-pagination', d.current, d.pages, loadTransactions);
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// BANKS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('banks-status-filter').addEventListener('change', loadBanks);
document.getElementById('banks-create-btn').addEventListener('click', () => {
    document.getElementById('modal-bank-title').textContent = 'Create Bank';
    document.getElementById('modal-bank-id').value = '';
    ['mb-bank-name','mb-account-name','mb-account-number','mb-gateway','mb-remark'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('mb-display-order').value = '0';
    document.getElementById('mb-status').value = 'active';
    openModal('modal-bank');
});

async function loadBanks() {
    const status = document.getElementById('banks-status-filter').value;
    try {
        allBanks = await api('GET', 'banks', { status });
        renderBanks(allBanks);
        refreshBankDropdowns();
    } catch(e) {
        document.getElementById('banks-tbody').innerHTML =
            `<tr><td colspan="10" style="text-align:center;color:#c00;">${e.message}</td></tr>`;
    }
}

function renderBanks(banks) {
    const tbody = document.getElementById('banks-tbody');
    if (!banks.length) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center;color:#999;padding:20px;">No banks found.</td></tr>`;
        return;
    }
    tbody.innerHTML = banks.map(b => `
        <tr>
            <td>${b.bank_id}</td>
            <td>${b.display_order}</td>
            <td>${b.gateway ?? '-'}</td>
            <td>${b.bank_name}</td>
            <td>${b.account_name}</td>
            <td>${b.account_number}</td>
            <td>${parseFloat(b.balance).toFixed(2)}</td>
            <td>${b.remark ?? ''}</td>
            <td>
                <a class="tbl-link" onclick="bankEditAmount(${b.id}, ${b.balance})">EDIT AMOUNT</a>
                <a class="tbl-link" onclick="bankHistory(${b.id})">HISTORY</a>
                <a class="tbl-link" onclick="bankEdit(${b.id})">EDIT</a>
            </td>
            <td></td>
        </tr>`).join('');
}

function refreshBankDropdowns() {
    const opts = allBanks.map(b => `<option value="${b.id}">${b.bank_name} – ${b.account_name}</option>`).join('');
    document.getElementById('tx-bank').innerHTML      = `<option value="all">Please Select</option>${opts}`;
    document.getElementById('btx-bank-select').innerHTML = `<option value="">-- Select Bank --</option>${opts}`;
}

function bankEdit(id) {
    const b = allBanks.find(x => x.id == id);
    if (!b) return;
    document.getElementById('modal-bank-title').textContent = 'Edit Bank';
    document.getElementById('modal-bank-id').value          = b.id;
    document.getElementById('mb-bank-name').value           = b.bank_name;
    document.getElementById('mb-account-name').value        = b.account_name;
    document.getElementById('mb-account-number').value      = b.account_number;
    document.getElementById('mb-gateway').value             = b.gateway ?? '';
    document.getElementById('mb-display-order').value       = b.display_order;
    document.getElementById('mb-remark').value              = b.remark ?? '';
    document.getElementById('mb-status').value              = b.status;
    openModal('modal-bank');
}

function bankEditAmount(id, balance) {
    document.getElementById('mba-bank-id').value  = id;
    document.getElementById('mba-balance').value  = balance;
    openModal('modal-bank-amount');
}

async function bankHistory(id) {
    try {
        const rows = await api('GET', `banks/${id}/history`);
        alert(`History: ${rows.length} transactions found.\n(Full history table — wire to a modal as needed)`);
    } catch(e) { alert(e.message); }
}

document.getElementById('modal-bank-save').addEventListener('click', async () => {
    const id   = document.getElementById('modal-bank-id').value;
    const body = {
        bank_name:      document.getElementById('mb-bank-name').value,
        account_name:   document.getElementById('mb-account-name').value,
        account_number: document.getElementById('mb-account-number').value,
        gateway:        document.getElementById('mb-gateway').value,
        display_order:  document.getElementById('mb-display-order').value,
        remark:         document.getElementById('mb-remark').value,
        status:         document.getElementById('mb-status').value,
    };
    try {
        if (id) {
            await api('PUT', `banks/${id}`, body);
        } else {
            await api('POST', 'banks', body);
        }
        closeModal('modal-bank');
        loadBanks();
    } catch(e) { alert('Error: ' + e.message); }
});

document.getElementById('mba-save').addEventListener('click', async () => {
    const id      = document.getElementById('mba-bank-id').value;
    const balance = document.getElementById('mba-balance').value;
    try {
        await api('PUT', `banks/${id}/amount`, { balance });
        closeModal('modal-bank-amount');
        loadBanks();
    } catch(e) { alert('Error: ' + e.message); }
});

// ─────────────────────────────────────────────────────────────────────────────
// BANK TRANSACTIONS
// ─────────────────────────────────────────────────────────────────────────────
function initBankTx() {
    if (!allBanks.length) loadBanks().then(() => { if (!btxActiveBankId && allBanks.length) selectBtxBank(allBanks[0].id); });
    else if (!btxActiveBankId && allBanks.length) selectBtxBank(allBanks[0].id);
}

document.getElementById('btx-bank-select').addEventListener('change', function() {
    if (this.value) selectBtxBank(parseInt(this.value));
});
document.getElementById('btx-active-bank-btn').addEventListener('click', () => {
    document.getElementById('btx-bank-select').focus();
});
document.getElementById('btx-date').addEventListener('change', () => {
    if (btxActiveBankId) loadBankTransactions();
});
['btx-type-filter','btx-subtype-filter'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        if (btxActiveBankId) loadBankTransactions();
    });
});
document.getElementById('btx-search').addEventListener('input', debounce(() => {
    if (btxActiveBankId) loadBankTransactions();
}, 400));

function selectBtxBank(id) {
    btxActiveBankId = id;
    const bank = allBanks.find(b => b.id == id);
    btxActiveBankName = bank ? `${bank.bank_name} (${bank.account_name})` : `Bank #${id}`;
    document.getElementById('btx-active-bank-btn').textContent = btxActiveBankName;
    document.getElementById('btx-bank-select').value = id;
    loadBankTransactions();
}

async function loadBankTransactions() {
    const params = {
        bank_id: btxActiveBankId,
        date:    document.getElementById('btx-date').value,
        type:    document.getElementById('btx-type-filter').value,
        search:  document.getElementById('btx-search').value,
    };
    try {
        const d = await api('GET', 'bank-transactions', params);
        document.getElementById('btx-start').textContent   = d.start_bal;
        document.getElementById('btx-today').textContent   = d.today_in;
        document.getElementById('btx-balance').textContent = d.balance;
        document.getElementById('btx-total-in').textContent  = d.total_in;
        document.getElementById('btx-total-out').textContent = d.total_out;

        const tbody = document.getElementById('btx-tbody');
        // keep the new-row template
        const newRow = document.getElementById('btx-new-row');

        // remove old data rows
        tbody.querySelectorAll('tr[data-btx-id]').forEach(r => r.remove());
        document.getElementById('btx-empty-row').style.display = d.rows.length ? 'none' : '';

        let totalFee = 0;
        d.rows.forEach((row, i) => {
            totalFee += parseFloat(row.fee ?? 0);
            const tr = buildBtxRow(row, i + 1);
            tbody.insertBefore(tr, newRow);
        });
        document.getElementById('btx-total-fee').textContent = totalFee.toFixed(2);

        // show new-row for data entry
        newRow.style.display = btxActiveBankId ? '' : 'none';
        newRow.querySelectorAll('.editable-cell').forEach(c => c.textContent = '');

    } catch(e) {
        alert('Error loading bank transactions: ' + e.message);
    }
}

function buildBtxRow(row, no) {
    const tr = document.createElement('tr');
    tr.dataset.btxId = row.id;
    const fields = ['description','amount_in','amount_out','time','ref_id','match','fee','remarks','info'];
    tr.innerHTML = `<td>${no}</td>` + fields.map(f => `
        <td><span class="editable-cell" contenteditable="true" data-field="${f}" data-id="${row.id}">${row[f] ?? ''}</span></td>
    `).join('');

    // save on Enter
    tr.querySelectorAll('.editable-cell').forEach(cell => {
        cell.addEventListener('keydown', async e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                await saveBtxRow(tr, row.id);
            }
        });
        cell.addEventListener('blur', () => saveBtxRow(tr, row.id));
    });
    return tr;
}

async function saveBtxRow(tr, id) {
    const body = {};
    tr.querySelectorAll('.editable-cell').forEach(c => {
        body[c.dataset.field] = c.textContent.trim();
    });
    try {
        const isNew = !id;
        if (isNew) {
            body.bo_bank_id = btxActiveBankId;
            body.date       = document.getElementById('btx-date').value;
            await api('POST', 'bank-transactions', body);
        } else {
            await api('PUT', `bank-transactions/${id}`, body);
        }
        loadBankTransactions();
    } catch(e) {
        console.error('Save BTX row:', e);
    }
}

// new-row enter key
document.getElementById('btx-new-row').querySelectorAll('.editable-cell').forEach(cell => {
    cell.addEventListener('keydown', async e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            await saveBtxRow(document.getElementById('btx-new-row'), null);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// CASHFLOW
// ─────────────────────────────────────────────────────────────────────────────
document.querySelectorAll('.cf-toggle button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.cf-toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        cfDisplay = btn.dataset.display;
    });
});
document.getElementById('cf-recalc').addEventListener('click', loadCashflow);

async function loadCashflow() {
    const params = {
        date_from: document.getElementById('cf-from').value,
        date_to:   document.getElementById('cf-to').value,
        display:   cfDisplay,
        type:      document.getElementById('cf-type').value,
    };
    try {
        const d = await api('GET', 'cashflow', params);
        const tbody = document.getElementById('cf-tbody');
        if (!d.rows.length) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">No data.</td></tr>`;
        } else {
            tbody.innerHTML = d.rows.map(r => `
                <tr>
                    <td>${r.period}</td>
                    <td>${r.deposit_count}</td>
                    <td>${parseFloat(r.deposit_amount).toFixed(2)}</td>
                    <td>${r.withdraw_count}</td>
                    <td>${parseFloat(r.withdraw_amount).toFixed(2)}</td>
                    <td>${parseFloat(r.net).toFixed(2)}</td>
                </tr>`).join('');
        }
        const t = d.totals;
        document.getElementById('cf-tot-dep-cnt').textContent = t.deposit_count;
        document.getElementById('cf-tot-dep-amt').textContent = t.deposit_amount;
        document.getElementById('cf-tot-wd-cnt').textContent  = t.withdraw_count;
        document.getElementById('cf-tot-wd-amt').textContent  = t.withdraw_amount;
        document.getElementById('cf-tot-net').textContent     = t.net;
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

// Cashflow sidebar nav
document.querySelectorAll('.cf-nav').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.cf-nav').forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        const titles = { transaction:'Transaction Report', bank:'Bank Report', staff:'Staff Report', activity:'Activity Log' };
        document.getElementById('cf-title').textContent = titles[a.dataset.report] ?? 'Report';
        loadCashflow();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ADMINS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('adm-search-btn').addEventListener('click', loadAdmins);
document.getElementById('adm-create-btn').addEventListener('click', () => {
    document.getElementById('modal-admin-title').textContent = 'Create Admin';
    document.getElementById('modal-admin-id').value = '';
    document.getElementById('ma-pw-hint').style.display = 'none';
    ['ma-username','ma-fullname','ma-email','ma-password'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('ma-role').value   = 'admin';
    document.getElementById('ma-status').value = 'active';
    document.getElementById('ma-username').disabled = false;
    openModal('modal-admin');
});

async function loadAdmins() {
    const params = {
        name:   document.getElementById('adm-name').value,
        role:   document.getElementById('adm-role').value,
        status: document.getElementById('adm-status').value,
    };
    try {
        const data = await api('GET', 'admins', params);
        const tbody = document.getElementById('adm-tbody');
        if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No admins found.</td></tr>`;
            return;
        }
        tbody.innerHTML = data.map(a => `
            <tr>
                <td>${a.username}</td>
                <td>${a.name}</td>
                <td>${a.created_by}</td>
                <td>${a.last_login}</td>
                <td style="text-align:right;">
                    <button class="tbl-btn" onclick="adminEdit(${JSON.stringify(a).replace(/"/g,'&quot;')})">EDIT</button>
                    <button class="tbl-btn">IP</button>
                </td>
            </tr>`).join('');
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

function adminEdit(a) {
    document.getElementById('modal-admin-title').textContent = 'Edit Admin';
    document.getElementById('modal-admin-id').value          = a.id;
    document.getElementById('ma-username').value             = a.username;
    document.getElementById('ma-username').disabled          = true;
    document.getElementById('ma-fullname').value             = a.name;
    document.getElementById('ma-email').value                = '';
    document.getElementById('ma-password').value             = '';
    document.getElementById('ma-pw-hint').style.display      = '';
    document.getElementById('ma-role').value                 = a.role;
    document.getElementById('ma-status').value               = a.status;
    openModal('modal-admin');
}

document.getElementById('modal-admin-save').addEventListener('click', async () => {
    const id   = document.getElementById('modal-admin-id').value;
    const body = {
        username: document.getElementById('ma-username').value,
        fullname: document.getElementById('ma-fullname').value,
        email:    document.getElementById('ma-email').value,
        password: document.getElementById('ma-password').value,
        role:     document.getElementById('ma-role').value,
        status:   document.getElementById('ma-status').value,
    };
    try {
        if (id) {
            await api('PUT', `admins/${id}`, body);
        } else {
            await api('POST', 'admins', body);
        }
        closeModal('modal-admin');
        loadAdmins();
    } catch(e) { alert('Error: ' + e.message); }
});

// ─────────────────────────────────────────────────────────────────────────────
// MENU SIDEBAR
// ─────────────────────────────────────────────────────────────────────────────
function openMenu() {
    document.getElementById('menu-sidebar').classList.add('open');
    document.getElementById('menu-overlay').classList.add('open');
}
function closeMenu() {
    document.getElementById('menu-sidebar').classList.remove('open');
    document.getElementById('menu-overlay').classList.remove('open');
}
document.getElementById('menu-overlay').addEventListener('click', closeMenu);

// device timezone offset
try {
    const offset = -new Date().getTimezoneOffset();
    const sign   = offset >= 0 ? '+' : '-';
    const h      = String(Math.floor(Math.abs(offset)/60)).padStart(2,'0');
    const m      = String(Math.abs(offset)%60).padStart(2,'0');
    document.getElementById('menu-device-tz').textContent = `${sign}${h}:${m}`;
} catch(e) {}

document.getElementById('menu-password-btn').addEventListener('click', () => {
    closeMenu();
    ['pw-current','pw-new','pw-confirm'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('pw-error').textContent = '';
    openModal('modal-password');
});

document.getElementById('menu-logout-btn').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to logout?')) return;
    try {
        const d = await api('POST', 'logout');
        window.location.href = d.redirect ?? '/';
    } catch(e) { window.location.href = '/'; }
});

document.getElementById('pw-save').addEventListener('click', async () => {
    const np = document.getElementById('pw-new').value;
    const cp = document.getElementById('pw-confirm').value;
    document.getElementById('pw-error').textContent = '';
    if (np !== cp) {
        document.getElementById('pw-error').textContent = 'Passwords do not match.';
        return;
    }
    try {
        await api('POST', 'update-password', {
            current_password:      document.getElementById('pw-current').value,
            new_password:          np,
            new_password_confirmation: cp,
        });
        closeModal('modal-password');
        alert('Password updated successfully.');
    } catch(e) {
        document.getElementById('pw-error').textContent = e.message;
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────────────────────────────────────────────
function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function renderPagination(containerId, current, total, loadFn) {
    const el = document.getElementById(containerId);
    if (total <= 1) { el.innerHTML = ''; return; }
    let html = '';
    for (let i = 1; i <= total; i++) {
        html += `<button onclick="${loadFn.name}(${i})" style="margin:1px 2px;padding:2px 8px;${i==current?'font-weight:bold;background:#e84040;color:#fff;border:none;':'border:1px solid #ccc;background:#f5f5f5;'}">${i}</button>`;
    }
    el.innerHTML = 'Page: ' + html;
}
</script>
</body>
</html>
