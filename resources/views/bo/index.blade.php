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
    <button class="nav-btn" data-page="banktx"        title="FX Ledger">
        <!-- grid icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
    </button>
    <button class="nav-btn" data-page="customers"     title="Customers">
        <!-- person icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-4 7a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
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
    <a class="nav-btn" href="{{ route('fx.index') }}" title="FX Spreadsheet" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">
        <!-- table/spreadsheet icon -->
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="9" x2="9" y2="21"/></svg>
    </a>
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
        <div class="filter-row">
            <span class="filter-label">Customer</span>
            <span class="filter-colon">:</span>
            <input id="tx-customer" class="filter-input" type="text" placeholder="Search by customer name">
        </div>
        <div class="filter-row">
            <span class="filter-label">Currency</span>
            <span class="filter-colon">:</span>
            <select id="tx-currency" class="filter-input">
                <option value="">ALL</option>
                <option value="AUD">AUD</option>
                <option value="PGK">PGK</option>
                <option value="MYR">MYR</option>
                <option value="SGD">SGD</option>
                <option value="USDT">USDT</option>
                <option value="ABA USD">ABA USD</option>
                <option value="THB">THB</option>
                <option value="VND">VND</option>
            </select>
        </div>
        <div class="filter-row">
            <span class="filter-label">Date</span>
            <span class="filter-colon">:</span>
            <div class="filter-half">
                <input id="tx-date-from" class="filter-input" type="date" value="{{ date('Y-m-01') }}">
                <input id="tx-date-to"   class="filter-input" type="date" value="{{ date('Y-m-d') }}">
            </div>
        </div>
    </div>
    <button class="btn-green" id="tx-search-btn">SEARCH</button>
    <button class="btn-orange" id="tx-export-btn">EXPORT</button>
    <div class="summary-row">
        <span>Record: <strong id="tx-record-count">0</strong>&nbsp; MYR Conv. Total: <strong id="tx-total">0.00</strong></span>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:90px;">Date</th>
                    <th>Customer</th>
                    <th style="width:60px;">CCY</th>
                    <th style="width:90px;text-align:right;">BUY IN</th>
                    <th style="width:90px;text-align:right;">SELL OUT</th>
                    <th style="width:80px;text-align:right;">Rate</th>
                    <th style="width:100px;text-align:right;">MYR CONV.</th>
                    <th style="width:80px;text-align:right;">MYR OUT</th>
                    <th style="width:80px;text-align:right;">MYR IN</th>
                    <th>Remark</th>
                    <th style="width:80px;text-align:right;">Profit</th>
                </tr>
            </thead>
            <tbody id="tx-tbody">
                <tr><td colspan="11" style="text-align:center;color:#999;padding:20px;">Use the filters above and click SEARCH.</td></tr>
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
        <input type="text" id="ctx-search" placeholder="Search by name" style="flex:1;min-width:120px;">
        <select id="ctx-customer-select" style="max-width:220px;">
            <option value="">-- Select Customer --</option>
        </select>
        <button class="icon-btn" title="Add Customer" id="ctx-add-btn" style="font-size:18px;">+</button>
    </div>
    <div style="padding:6px 8px;background:#e8e8e8;">
        <button class="bank-selector-btn" id="ctx-active-customer-btn">Select a customer</button>
    </div>
    <div class="btx-summary">
        <div class="s-cell">INITIAL BAL<br><span class="s-val" id="ctx-initial">0.00</span></div>
        <div class="s-cell">TOTAL IN<br><span class="s-val" id="ctx-total-in">0.00</span></div>
        <div class="s-cell">TOTAL OUT<br><span class="s-val" id="ctx-total-out">0.00</span></div>
        <div class="s-cell">BALANCE<br><span class="s-val" id="ctx-balance">0.00</span></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table" id="ctx-table">
            <thead>
                <tr>
                    <th style="width:36px;">NO.</th>
                    <th style="width:90px;">DATE</th>
                    <th style="width:60px;">CCY</th>
                    <th style="width:90px;">BUY IN</th>
                    <th style="width:90px;">SELL OUT</th>
                    <th style="width:80px;">RATE</th>
                    <th style="width:100px;">MYR CONV.</th>
                    <th style="width:90px;">MYR OUT</th>
                    <th style="width:90px;">MYR IN</th>
                    <th>REMARK</th>
                    <th style="width:80px;">COST RATE</th>
                    <th style="width:90px;">PROFIT</th>
                </tr>
            </thead>
            <tbody id="ctx-tbody">
                <tr id="ctx-empty-row"><td colspan="12" style="text-align:center;color:#999;padding:20px;">Select a customer to load transactions.</td></tr>
                <tr id="ctx-new-row" style="display:none;">
                    <td></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="date"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="currency"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="amount_in"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="amount_out"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="rate"></span></td>
                    <td></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="myr_out"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="myr_in"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="remark"></span></td>
                    <td><span class="editable-cell" contenteditable="true" data-field="cost_rate"></span></td>
                    <td></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td id="ctx-foot-in">0.00</td>
                    <td id="ctx-foot-out">0.00</td>
                    <td></td>
                    <td id="ctx-foot-myr">0.00</td>
                    <td id="ctx-foot-myr-out">0.00</td>
                    <td id="ctx-foot-myr-in">0.00</td>
                    <td></td>
                    <td></td>
                    <td id="ctx-foot-profit">0.00</td>
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
            <li><a href="#" class="cf-nav" data-report="bank">Bank</a></li>
            <li><a href="#" class="cf-nav" data-report="staff">Staff</a></li>
            <li><a href="#" class="cf-nav" data-report="activity">Activity Log</a></li>
        </ul>
    </div>
    <div class="cf-content">
        <!-- ── Transaction Report ─────────────────────────────────── -->
        <div id="cf-panel-transaction" class="cf-panel">
            <h2>Transaction Report</h2>
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
                        <tr>
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

        <!-- ── Bank Report ────────────────────────────────────────── -->
        <div id="cf-panel-bank" class="cf-panel" style="display:none;">
            <h2>Bank Report</h2>
            <div class="cf-filter-row">
                <label>Period</label><span class="cf-colon">:</span>
                <div class="cf-date-range">
                    <input type="date" id="cfb-from" value="{{ date('Y-m-01') }}">
                    <input type="date" id="cfb-to"   value="{{ date('Y-m-t') }}">
                </div>
            </div>
            <div class="cf-filter-row">
                <label>Display</label><span class="cf-colon">:</span>
                <div class="cf-toggle" id="cfb-toggle">
                    <button class="active" data-display="daily">Daily</button>
                    <button data-display="monthly">Monthly</button>
                    <button data-display="yearly">Yearly</button>
                </div>
            </div>
            <div class="cf-filter-row">
                <label>Bank</label><span class="cf-colon">:</span>
                <select id="cfb-bank" class="cf-select" style="min-width:240px;">
                    <option value="">All Banks</option>
                </select>
            </div>
            <a class="recalc-link" id="cfb-recalc">RECALCULATE</a>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Net</th>
                        </tr>
                    </thead>
                    <tbody id="cfb-tbody"></tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="cfb-tot-in">0.00</td>
                            <td id="cfb-tot-out">0.00</td>
                            <td id="cfb-tot-net">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ── Staff Report ───────────────────────────────────────── -->
        <div id="cf-panel-staff" class="cf-panel" style="display:none;">
            <h2>Staff Report</h2>
            <div class="cf-filter-row">
                <label>Period</label><span class="cf-colon">:</span>
                <div class="cf-date-range">
                    <input type="date" id="cfs-from" value="{{ date('Y-m-01') }}">
                    <input type="date" id="cfs-to"   value="{{ date('Y-m-t') }}">
                </div>
            </div>
            <a class="recalc-link" id="cfs-recalc">RECALCULATE</a>

            <h3 style="color:#e04040;font-size:14px;margin:10px 0 6px;">Deposit</h3>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Staff</th><th>Total Approval</th><th>Total Reject</th><th>Response</th><th>Process</th></tr></thead>
                    <tbody id="cfs-dep-tbody"></tbody>
                    <tfoot><tr style="background:#fde8c8;">
                        <td><strong>TOTAL</strong></td>
                        <td id="cfs-dep-approve">0</td>
                        <td id="cfs-dep-reject">0</td>
                        <td id="cfs-dep-response">0s</td>
                        <td id="cfs-dep-process">0s</td>
                    </tr></tfoot>
                </table>
            </div>

            <h3 style="color:#e04040;font-size:14px;margin:14px 0 6px;">Withdraw</h3>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Staff</th><th>Total Approval</th><th>Total Reject</th><th>Response</th><th>Process</th></tr></thead>
                    <tbody id="cfs-wd-tbody"></tbody>
                    <tfoot><tr style="background:#fde8c8;">
                        <td><strong>TOTAL</strong></td>
                        <td id="cfs-wd-approve">0</td>
                        <td id="cfs-wd-reject">0</td>
                        <td id="cfs-wd-response">0s</td>
                        <td id="cfs-wd-process">0s</td>
                    </tr></tfoot>
                </table>
            </div>

        </div>

        <!-- ── Activity Log ───────────────────────────────────────── -->
        <div id="cf-panel-activity" class="cf-panel" style="display:none;">
            <h2>Activity Log</h2>
            <div class="cf-filter-row">
                <label>Date</label><span class="cf-colon">:</span>
                <div class="cf-date-range">
                    <input type="date" id="cfa-from" value="{{ date('Y-m-01') }}">
                    <input type="date" id="cfa-to"   value="{{ date('Y-m-t') }}">
                </div>
            </div>
            <div class="cf-filter-row">
                <label>Action</label><span class="cf-colon">:</span>
                <select id="cfa-action" class="cf-select" style="min-width:200px;">
                    <option value="">ALL</option>
                    <option value="inactive">INACTIVE</option>
                    <option value="active">ACTIVE</option>
                    <option value="created">CREATED</option>
                    <option value="updated">UPDATED</option>
                    <option value="deleted">DELETED</option>
                </select>
            </div>
            <a class="recalc-link" id="cfa-recalc">SEARCH</a>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Username</th>
                            <th>Player Name</th>
                            <th>Mobile</th>
                            <th>Action By</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="cfa-tbody"></tbody>
                </table>
            </div>
            <div id="cfa-pagination" style="padding:6px 8px;font-size:12px;color:#555;"></div>
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
            <option value=10>ACTIVE</option>
            <option value=20>INACTIVE</option>
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
            </select>
        </div>
        <div class="filter-row">
            <span class="filter-label">Status</span>
            <span class="filter-colon">:</span>
            <select id="adm-status" class="filter-input">
                <option value="all">ALL</option>
                <option value=10 selected>ACTIVE</option>
                <option value=20>INACTIVE</option>
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
<!-- PAGE: CUSTOMERS                                                          -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div id="page-customers" class="page-section">
    <div class="filter-wrap">
        <div class="filter-row">
            <span class="filter-label">Search</span>
            <span class="filter-colon">:</span>
            <input id="bc-search" class="filter-input" type="text" placeholder="Filter by name…">
        </div>
        <div class="filter-row">
            <span class="filter-label">Status</span>
            <span class="filter-colon">:</span>
            <select id="bc-status" class="filter-input">
                <option value="all">ALL</option>
                <option value="active" selected>ACTIVE</option>
                <option value="inactive">INACTIVE</option>
            </select>
        </div>
    </div>
    <div style="padding:8px;">
        <button class="create-btn" id="bc-create-btn">+ CREATE</button>
    </div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Initial Balance (MYR)</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody id="bc-tbody">
                <tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">Loading…</td></tr>
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
                    <option value=10>ACTIVE</option>
                    <option value=20>INACTIVE</option>
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
            <div class="modal-field"><label>Phone Number</label>
                <div style="display:flex;align-items:center;gap:0;">
                    <span style="background:#e8e8e8;border:1px solid #ccc;border-right:none;padding:5px 8px;font-size:12px;height:30px;display:flex;align-items:center;color:#333;white-space:nowrap;">+60</span>
                    <input type="text" id="ma-phone" style="border-top-left-radius:0;border-bottom-left-radius:0;flex:1;">
                </div>
            </div>
            <div class="modal-field"><label>Password <span id="ma-pw-hint" style="color:#aaa;font-size:11px;">(leave blank to keep)</span></label><input type="password" id="ma-password"></div>
            <div class="modal-field"><label>Role *</label>
                <select id="ma-role" style="height:30px;width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;">
                </select>
            </div>
            <div class="modal-field"><label>Status</label>
                <select id="ma-status" style="height:30px;width:100%;border:1px solid #ccc;padding:3px 6px;font-size:12px;">
                    <option value=10>ACTIVE</option>
                    <option value=20>INACTIVE</option>
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

<!-- Admin IP Info Modal -->
<div class="modal-overlay" id="modal-admin-ip">
    <div class="modal-box" style="width:340px;">
        <div class="modal-header">
            <span id="modal-admin-ip-title">Login Info</span>
            <button class="modal-close" data-close="modal-admin-ip">&times;</button>
        </div>
        <div class="modal-body" style="font-size:12px;">
            <div class="modal-field">
                <label>Username</label>
                <div id="mip-username" style="padding:5px 0;font-weight:bold;"></div>
            </div>
            <div class="modal-field">
                <label>Last Login IP</label>
                <div id="mip-ip" style="padding:5px 0;font-weight:bold;"></div>
            </div>
            <div class="modal-field">
                <label>Last Login Date</label>
                <div id="mip-date" style="padding:5px 0;font-weight:bold;"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-admin-ip">Close</button>
        </div>
    </div>
</div>

<!-- Customer Create / Edit Modal -->
<div class="modal-overlay" id="modal-customer">
    <div class="modal-box">
        <div class="modal-header">
            <span id="modal-customer-title">Create Customer</span>
            <button class="modal-close" data-close="modal-customer">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="bc-id">
            <div class="modal-field"><label>Name *</label><input type="text" id="bc-name"></div>
            <div class="modal-field"><label>Initial Balance (MYR)</label><input type="number" step="0.01" id="bc-bal" value="0"></div>
            <div class="modal-field" id="bc-status-wrap" style="display:none;"><label>Status</label>
                <select id="bc-status-field" style="width:100%;border:1px solid #ccc;padding:5px 8px;font-size:12px;height:30px;">
                    <option value="active">ACTIVE</option>
                    <option value="inactive">INACTIVE</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-customer">Cancel</button>
            <button class="btn-save" id="bc-save">Create</button>
        </div>
    </div>
</div>

<!-- Bank History Modal -->
<div class="modal-overlay" id="modal-bank-history">
    <div class="modal-box" style="width:700px;">
        <div class="modal-header">
            <span id="modal-bank-history-title">Bank History</span>
            <button class="modal-close" data-close="modal-bank-history">&times;</button>
        </div>
        <div class="modal-body" style="padding:0;max-height:70vh;overflow-y:auto;">
            <table class="data-table" style="margin:0;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>In</th>
                        <th>Out</th>
                        <th>Fee</th>
                        <th>Time</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="bh-tbody"></tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" data-close="modal-bank-history">Close</button>
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
let ctxActiveCustomerId = null;
let ctxActiveCustomerName = '';
let allCustomers   = [];
let allBanks       = [];
let allRoles       = [];

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
        if (page === 'banks')     loadBanks();
        if (page === 'banktx')    initCustomerTx();
        if (page === 'cashflow')  loadCashflow();
        if (page === 'customers') initBoCustomers();
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
document.getElementById('tx-search-btn').addEventListener('click', loadTransactions);
document.getElementById('tx-export-btn')?.addEventListener('click', () => {
    const params = new URLSearchParams({
        customer:  document.getElementById('tx-customer').value,
        currency:  document.getElementById('tx-currency').value,
        date_from: document.getElementById('tx-date-from').value,
        date_to:   document.getElementById('tx-date-to').value,
    });
    [...params.keys()].forEach(k => { if (!params.get(k)) params.delete(k); });
    window.location.href = `${API}/transactions/export?${params.toString()}`;
});

async function loadTransactions(page = 1) {
    const params = {
        customer:  document.getElementById('tx-customer').value,
        currency:  document.getElementById('tx-currency').value,
        date_from: document.getElementById('tx-date-from').value,
        date_to:   document.getElementById('tx-date-to').value,
        page,
    };
    try {
        const d = await api('GET', 'transactions', params);
        document.getElementById('tx-record-count').textContent = d.records;
        document.getElementById('tx-total').textContent        = d.total;

        const tbody = document.getElementById('tx-tbody');
        if (!d.data.length) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align:center;color:#999;padding:20px;">No records found.</td></tr>`;
            return;
        }
        tbody.innerHTML = d.data.map(tx => `
            <tr>
                <td>${(tx.date ?? '').substring(0, 10)}</td>
                <td>${tx.customer?.name ?? ''}</td>
                <td>${tx.currency ?? ''}</td>
                <td style="text-align:right;">${parseFloat(tx.amount_in  ?? 0).toFixed(4)}</td>
                <td style="text-align:right;">${parseFloat(tx.amount_out ?? 0).toFixed(4)}</td>
                <td style="text-align:right;">${parseFloat(tx.rate       ?? 0).toFixed(6)}</td>
                <td style="text-align:right;">${parseFloat(tx.myr_converted ?? 0).toFixed(2)}</td>
                <td style="text-align:right;">${parseFloat(tx.myr_out    ?? 0).toFixed(2)}</td>
                <td style="text-align:right;">${parseFloat(tx.myr_in     ?? 0).toFixed(2)}</td>
                <td>${tx.remark ?? ''}</td>
                <td style="text-align:right;">${parseFloat(tx.profit     ?? 0).toFixed(2)}</td>
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
    // placeholder — extend if other bank dropdowns are added
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
    const bank = allBanks.find(b => b.id == id);
    const bankLabel = bank ? `${bank.bank_name} (${bank.account_name})` : `Bank #${id}`;
    document.getElementById('modal-bank-history-title').textContent = `History — ${bankLabel}`;

    const tbody = document.getElementById('bh-tbody');
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">Loading…</td></tr>`;
    openModal('modal-bank-history');

    try {
        const rows = await api('GET', `banks/${id}/history`);
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">No history found.</td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map(r => {
            const date = r.date ? r.date.substring(0, 10) : '-';
            return `<tr>
                <td>${date}</td>
                <td>${r.description ?? ''}</td>
                <td>${r.amount_in ? parseFloat(r.amount_in).toFixed(2) : ''}</td>
                <td>${r.amount_out ? parseFloat(r.amount_out).toFixed(2) : ''}</td>
                <td>${r.fee ? parseFloat(r.fee).toFixed(2) : ''}</td>
                <td>${r.time ?? ''}</td>
                <td>${r.remarks ?? ''}</td>
            </tr>`;
        }).join('');
    } catch(e) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#c00;padding:20px;">${e.message}</td></tr>`;
    }
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
// CUSTOMER TRANSACTIONS
// ─────────────────────────────────────────────────────────────────────────────
async function loadCustomers() {
    try {
        allCustomers = await api('GET', 'customers');
        refreshCustomerDropdown();
    } catch(e) { console.error('Failed to load customers:', e); }
}

function refreshCustomerDropdown() {
    const opts = allCustomers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    document.getElementById('ctx-customer-select').innerHTML = `<option value="">-- Select Customer --</option>${opts}`;
}

function initCustomerTx() {
    if (!allCustomers.length) loadCustomers().then(() => { if (!ctxActiveCustomerId && allCustomers.length) selectCtxCustomer(allCustomers[0].id); });
    else if (!ctxActiveCustomerId && allCustomers.length) selectCtxCustomer(allCustomers[0].id);
}

document.getElementById('ctx-customer-select').addEventListener('change', function() {
    if (this.value) selectCtxCustomer(parseInt(this.value));
});
document.getElementById('ctx-active-customer-btn').addEventListener('click', () => {
    document.getElementById('ctx-customer-select').focus();
});
document.getElementById('ctx-search').addEventListener('input', debounce(() => {
    // filter dropdown by search text
    const q = document.getElementById('ctx-search').value.toLowerCase();
    const sel = document.getElementById('ctx-customer-select');
    sel.innerHTML = `<option value="">-- Select Customer --</option>` +
        allCustomers.filter(c => c.name.toLowerCase().includes(q))
            .map(c => `<option value="${c.id}">${c.name}</option>`).join('');
}, 300));

// Add customer button
document.getElementById('ctx-add-btn').addEventListener('click', () => {
    const name = prompt('Enter customer name:');
    if (!name || !name.trim()) return;
    const balance = prompt('Initial balance (default 0):', '0');
    api('POST', 'customers', { name: name.trim(), initial_balance: parseFloat(balance) || 0 })
        .then(() => { loadCustomers(); })
        .catch(e => alert('Error: ' + e.message));
});

function selectCtxCustomer(id) {
    ctxActiveCustomerId = id;
    const cust = allCustomers.find(c => c.id == id);
    ctxActiveCustomerName = cust ? cust.name : `Customer #${id}`;
    document.getElementById('ctx-active-customer-btn').textContent = ctxActiveCustomerName;
    document.getElementById('ctx-customer-select').value = id;
    loadCustomerTransactions();
}

async function loadCustomerTransactions() {
    if (!ctxActiveCustomerId) return;
    try {
        const d = await api('GET', `customers/${ctxActiveCustomerId}`);
        const cust = d.customer;
        const txs  = d.transactions;
        const summary = d.summary;

        document.getElementById('ctx-initial').textContent   = parseFloat(cust.initial_balance).toFixed(2);
        document.getElementById('ctx-total-in').textContent  = summary.total_myr_in ?? '0.00';
        document.getElementById('ctx-total-out').textContent = summary.total_myr_out ?? '0.00';
        document.getElementById('ctx-balance').textContent   = summary.balance ?? '0.00';

        const tbody  = document.getElementById('ctx-tbody');
        const newRow = document.getElementById('ctx-new-row');

        // remove old data rows
        tbody.querySelectorAll('tr[data-ctx-id]').forEach(r => r.remove());
        document.getElementById('ctx-empty-row').style.display = txs.length ? 'none' : '';

        let totIn = 0, totOut = 0, totMyr = 0, totMyrOut = 0, totMyrIn = 0, totProfit = 0;
        txs.forEach((tx, i) => {
            totIn     += parseFloat(tx.amount_in ?? 0);
            totOut    += parseFloat(tx.amount_out ?? 0);
            totMyr    += parseFloat(tx.myr_converted ?? 0);
            totMyrOut += parseFloat(tx.myr_out ?? 0);
            totMyrIn  += parseFloat(tx.myr_in ?? 0);
            totProfit += parseFloat(tx.profit ?? 0);
            const tr = buildCtxRow(tx, i + 1);
            tbody.insertBefore(tr, newRow);
        });

        document.getElementById('ctx-foot-in').textContent      = totIn.toFixed(4);
        document.getElementById('ctx-foot-out').textContent     = totOut.toFixed(4);
        document.getElementById('ctx-foot-myr').textContent     = totMyr.toFixed(2);
        document.getElementById('ctx-foot-myr-out').textContent = totMyrOut.toFixed(2);
        document.getElementById('ctx-foot-myr-in').textContent  = totMyrIn.toFixed(2);
        document.getElementById('ctx-foot-profit').textContent  = totProfit.toFixed(2);

        // show new-row
        newRow.style.display = ctxActiveCustomerId ? '' : 'none';
        newRow.querySelectorAll('.editable-cell').forEach(c => c.textContent = '');
        // default date to today
        const dateCell = newRow.querySelector('[data-field="date"]');
        if (dateCell) dateCell.textContent = new Date().toISOString().substring(0, 10);

    } catch(e) {
        alert('Error loading customer: ' + e.message);
    }
}

function buildCtxRow(tx, no) {
    const tr = document.createElement('tr');
    tr.dataset.ctxId = tx.id;
    const date = tx.date ? tx.date.substring(0, 10) : '';
    const editableFields = ['date','currency','amount_in','amount_out','rate','myr_out','myr_in','remark','cost_rate'];
    const readonlyFields = { myr_converted: tx.myr_converted, profit: tx.profit };

    tr.innerHTML = `<td>${no}</td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="date" data-id="${tx.id}">${date}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="currency" data-id="${tx.id}">${tx.currency ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="amount_in" data-id="${tx.id}">${tx.amount_in ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="amount_out" data-id="${tx.id}">${tx.amount_out ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="rate" data-id="${tx.id}">${tx.rate ?? ''}</span></td>` +
        `<td>${parseFloat(tx.myr_converted ?? 0).toFixed(2)}</td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="myr_out" data-id="${tx.id}">${tx.myr_out ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="myr_in" data-id="${tx.id}">${tx.myr_in ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="remark" data-id="${tx.id}">${tx.remark ?? ''}</span></td>` +
        `<td><span class="editable-cell" contenteditable="true" data-field="cost_rate" data-id="${tx.id}">${tx.cost_rate ?? ''}</span></td>` +
        `<td>${parseFloat(tx.profit ?? 0).toFixed(2)}</td>`;

    tr.querySelectorAll('.editable-cell').forEach(cell => {
        cell.addEventListener('keydown', async e => {
            if (e.key === 'Enter') { e.preventDefault(); await saveCtxRow(tr, tx.id); }
        });
        cell.addEventListener('blur', () => saveCtxRow(tr, tx.id));
    });
    return tr;
}

async function saveCtxRow(tr, id) {
    const body = {};
    tr.querySelectorAll('.editable-cell').forEach(c => {
        body[c.dataset.field] = c.textContent.trim();
    });
    try {
        if (!id) {
            body.fx_customer_id = ctxActiveCustomerId;
            await api('POST', 'customers/transactions', body);
        } else {
            await api('PUT', `customers/transactions/${id}`, body);
        }
        loadCustomerTransactions();
    } catch(e) {
        console.error('Save CTX row:', e);
    }
}

// new-row enter key
document.getElementById('ctx-new-row').querySelectorAll('.editable-cell').forEach(cell => {
    cell.addEventListener('keydown', async e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            await saveCtxRow(document.getElementById('ctx-new-row'), null);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// CASHFLOW / REPORTS
// ─────────────────────────────────────────────────────────────────────────────
let cfActiveReport = 'transaction';
let cfbDisplay     = 'daily';

// Transaction report display toggle
document.querySelectorAll('.cf-toggle button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.cf-toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        cfDisplay = btn.dataset.display;
    });
});
document.getElementById('cf-recalc').addEventListener('click', loadCashflowTransaction);

// Bank report display toggle
document.querySelectorAll('#cfb-toggle button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#cfb-toggle button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        cfbDisplay = btn.dataset.display;
    });
});
document.getElementById('cfb-recalc').addEventListener('click', loadCashflowBank);
document.getElementById('cfs-recalc').addEventListener('click', loadCashflowStaff);
document.getElementById('cfa-recalc').addEventListener('click', () => loadCashflowActivity(1));

// Sidebar nav — switch panels
document.querySelectorAll('.cf-nav').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.cf-nav').forEach(x => x.classList.remove('active'));
        a.classList.add('active');
        cfActiveReport = a.dataset.report;

        // show/hide panels
        document.querySelectorAll('.cf-panel').forEach(p => p.style.display = 'none');
        const panel = document.getElementById(`cf-panel-${cfActiveReport}`);
        if (panel) panel.style.display = '';

        // populate bank dropdown if needed
        if (cfActiveReport === 'bank') refreshCfBankDropdown();
    });
});

function refreshCfBankDropdown() {
    const opts = allBanks.map(b => `<option value="${b.id}">${b.bank_name} – ${b.account_name}</option>`).join('');
    document.getElementById('cfb-bank').innerHTML = `<option value="">All Banks</option>${opts}`;
}

// Called on page nav to cashflow (initial load)
function loadCashflow() {
    if (cfActiveReport === 'transaction') loadCashflowTransaction();
    else if (cfActiveReport === 'bank')   loadCashflowBank();
    else if (cfActiveReport === 'staff')  loadCashflowStaff();
    else if (cfActiveReport === 'activity') loadCashflowActivity(1);
}

// ── Transaction Report ───────────────────────────────────────────────────────
async function loadCashflowTransaction() {
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

// ── Bank Report ──────────────────────────────────────────────────────────────
async function loadCashflowBank() {
    const params = {
        date_from: document.getElementById('cfb-from').value,
        date_to:   document.getElementById('cfb-to').value,
        display:   cfbDisplay,
        bank_id:   document.getElementById('cfb-bank').value,
    };
    try {
        const d = await api('GET', 'cashflow/bank', params);
        const tbody = document.getElementById('cfb-tbody');
        if (!d.rows.length) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#999;padding:20px;">No data.</td></tr>`;
        } else {
            tbody.innerHTML = d.rows.map(r => `
                <tr>
                    <td>${r.period}</td>
                    <td>${parseFloat(r.total_in).toFixed(2)}</td>
                    <td>${parseFloat(r.total_out).toFixed(2)}</td>
                    <td>${parseFloat(r.net).toFixed(2)}</td>
                </tr>`).join('');
        }
        document.getElementById('cfb-tot-in').textContent  = d.totals.total_in;
        document.getElementById('cfb-tot-out').textContent = d.totals.total_out;
        document.getElementById('cfb-tot-net').textContent = d.totals.net;
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

// ── Staff Report ─────────────────────────────────────────────────────────────
async function loadCashflowStaff() {
    const params = {
        date_from: document.getElementById('cfs-from').value,
        date_to:   document.getElementById('cfs-to').value,
    };
    try {
        const d = await api('GET', 'cashflow/staff', params);

        // Deposit table
        const depTbody = document.getElementById('cfs-dep-tbody');
        depTbody.innerHTML = d.deposit.rows.length ? d.deposit.rows.map(r => `
            <tr>
                <td>${r.staff}</td>
                <td>${r.total_approval}</td>
                <td>${r.total_reject}</td>
                <td>${r.response}</td>
                <td>${r.process}</td>
            </tr>`).join('') : `<tr><td colspan="5" style="text-align:center;color:#999;">No data.</td></tr>`;
        document.getElementById('cfs-dep-approve').textContent  = d.deposit.totals.total_approval;
        document.getElementById('cfs-dep-reject').textContent   = d.deposit.totals.total_reject;
        document.getElementById('cfs-dep-response').textContent = d.deposit.totals.response;
        document.getElementById('cfs-dep-process').textContent  = d.deposit.totals.process;

        // Withdraw table
        const wdTbody = document.getElementById('cfs-wd-tbody');
        wdTbody.innerHTML = d.withdraw.rows.length ? d.withdraw.rows.map(r => `
            <tr>
                <td>${r.staff}</td>
                <td>${r.total_approval}</td>
                <td>${r.total_reject}</td>
                <td>${r.response}</td>
                <td>${r.process}</td>
            </tr>`).join('') : `<tr><td colspan="5" style="text-align:center;color:#999;">No data.</td></tr>`;
        document.getElementById('cfs-wd-approve').textContent  = d.withdraw.totals.total_approval;
        document.getElementById('cfs-wd-reject').textContent   = d.withdraw.totals.total_reject;
        document.getElementById('cfs-wd-response').textContent = d.withdraw.totals.response;
        document.getElementById('cfs-wd-process').textContent  = d.withdraw.totals.process;

    } catch(e) {
        alert('Error: ' + e.message);
    }
}

// ── Activity Log ─────────────────────────────────────────────────────────────
async function loadCashflowActivity(page) {
    const params = {
        date_from: document.getElementById('cfa-from').value,
        date_to:   document.getElementById('cfa-to').value,
        action:    document.getElementById('cfa-action').value,
        page,
    };
    try {
        const d = await api('GET', 'cashflow/activity', params);
        const tbody = document.getElementById('cfa-tbody');
        if (!d.data.length) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">No activity found.</td></tr>`;
        } else {
            tbody.innerHTML = d.data.map(r => `
                <tr>
                    <td>${r.date_time}</td>
                    <td>${r.username}</td>
                    <td>${r.player_name}</td>
                    <td>${r.mobile}</td>
                    <td>${r.action_by}</td>
                    <td>${r.description}</td>
                </tr>`).join('');
        }
        renderPagination('cfa-pagination', d.current, d.pages, loadCashflowActivity);
    } catch(e) {
        alert('Error: ' + e.message);
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// ROLES
// ─────────────────────────────────────────────────────────────────────────────
async function loadRoles() {
    try {
        allRoles = await api('GET', 'roles');
        const filterOpts = allRoles.map(r => `<option value="${r.id}">${r.name.toUpperCase()}</option>`).join('');
        document.getElementById('adm-role').innerHTML = `<option value="all">ALL</option>${filterOpts}`;
        document.getElementById('ma-role').innerHTML = filterOpts;
    } catch(e) {
        console.error('Failed to load roles:', e);
    }
}
loadRoles();

// ─────────────────────────────────────────────────────────────────────────────
// ADMINS
// ─────────────────────────────────────────────────────────────────────────────
document.getElementById('adm-search-btn').addEventListener('click', loadAdmins);
document.getElementById('adm-create-btn').addEventListener('click', () => {
    document.getElementById('modal-admin-title').textContent = 'Create Admin';
    document.getElementById('modal-admin-id').value = '';
    document.getElementById('ma-pw-hint').style.display = 'none';
    ['ma-username','ma-fullname','ma-email','ma-phone','ma-password'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('ma-role').value   = allRoles.length ? allRoles[0].name : '';
    document.getElementById('ma-status').value = 10;
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
                    <button class="tbl-btn" onclick="adminShowIp(${JSON.stringify(a).replace(/"/g,'&quot;')})">IP</button>
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
    document.getElementById('ma-email').value                = a.email;
    document.getElementById('ma-phone').value               = a.phone_number ?? '';
    document.getElementById('ma-password').value             = '';
    document.getElementById('ma-pw-hint').style.display      = '';
    document.getElementById('ma-role').value                 = a.role;
    document.getElementById('ma-status').value               = a.status;
    openModal('modal-admin');
}

function adminShowIp(a) {
    document.getElementById('mip-username').textContent = a.username;
    document.getElementById('mip-ip').textContent       = a.last_login_ip;
    document.getElementById('mip-date').textContent     = a.last_login;
    openModal('modal-admin-ip');
}

document.getElementById('modal-admin-save').addEventListener('click', async () => {
    const id   = document.getElementById('modal-admin-id').value;
    const body = {
        username:     document.getElementById('ma-username').value,
        fullname:     document.getElementById('ma-fullname').value,
        email:        document.getElementById('ma-email').value,
        phone_number: document.getElementById('ma-phone').value,
        password:     document.getElementById('ma-password').value,
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
// CUSTOMERS PAGE
// ─────────────────────────────────────────────────────────────────────────────
let boCustomersReady = false;

async function initBoCustomers() {
    if (!allCustomers.length) await loadCustomers();
    renderBoCustomers();
    boCustomersReady = true;
}

function renderBoCustomers() {
    const q      = (document.getElementById('bc-search')?.value || '').toLowerCase();
    const status = document.getElementById('bc-status')?.value || 'all';
    let list = allCustomers;
    if (q)              list = list.filter(c => c.name.toLowerCase().includes(q));
    if (status !== 'all') list = list.filter(c => c.status === status);

    const tbody = document.getElementById('bc-tbody');
    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#999;padding:20px;">No customers found.</td></tr>`;
        return;
    }
    tbody.innerHTML = list.map(c => {
        const active = (c.status ?? '') === 'active';
        const badge  = `<span style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:bold;background:${active?'#e8f5e9':'#fce4ec'};color:${active?'#2e7d32':'#c62828'}">${(c.status ?? '').toUpperCase()}</span>`;
        return `<tr>
            <td>${c.id}</td>
            <td>${c.name}</td>
            <td>${parseFloat(c.initial_balance ?? 0).toFixed(2)}</td>
            <td style="text-align:center;">${badge}</td>
            <td style="text-align:right;">
                <button class="tbl-btn" onclick="boCustomerEdit(${JSON.stringify(c).replace(/"/g,'&quot;')})">EDIT</button>
                <button class="tbl-btn" style="color:#c00;border-color:#c00;" onclick="boCustomerDelete(${c.id}, '${c.name.replace(/'/g,"\\'")}')">DELETE</button>
            </td>
        </tr>`;
    }).join('');
}

async function boCustomerDelete(id, name) {
    if (!confirm(`Delete customer "${name}"? This cannot be undone.`)) return;
    try {
        await api('DELETE', `customers/${id}`);
        allCustomers = allCustomers.filter(c => c.id !== id);
        refreshCustomerDropdown();
        renderBoCustomers();
    } catch(e) { alert('Error: ' + e.message); }
}

document.getElementById('bc-search').addEventListener('input', debounce(() => { if (boCustomersReady) renderBoCustomers(); }, 250));
document.getElementById('bc-status').addEventListener('change', () => { if (boCustomersReady) renderBoCustomers(); });

document.getElementById('bc-create-btn').addEventListener('click', () => {
    document.getElementById('modal-customer-title').textContent = 'Create Customer';
    document.getElementById('bc-id').value   = '';
    document.getElementById('bc-name').value = '';
    document.getElementById('bc-bal').value  = '0';
    document.getElementById('bc-status-wrap').style.display = 'none';
    document.getElementById('bc-save').textContent = 'Create';
    openModal('modal-customer');
});

function boCustomerEdit(c) {
    document.getElementById('modal-customer-title').textContent = 'Edit Customer';
    document.getElementById('bc-id').value             = c.id;
    document.getElementById('bc-name').value           = c.name;
    document.getElementById('bc-bal').value            = c.initial_balance ?? 0;
    document.getElementById('bc-status-field').value   = c.status ?? 'active';
    document.getElementById('bc-status-wrap').style.display = '';
    document.getElementById('bc-save').textContent = 'Save';
    openModal('modal-customer');
}

document.getElementById('bc-save').addEventListener('click', async () => {
    const id   = document.getElementById('bc-id').value;
    const name = document.getElementById('bc-name').value.trim();
    if (!name) { alert('Name is required'); return; }
    const body = { name, initial_balance: document.getElementById('bc-bal').value };
    if (id) body.status = document.getElementById('bc-status-field').value;
    try {
        if (id) {
            await api('PUT', `customers/${id}`, body);
        } else {
            await api('POST', 'customers', body);
        }
        closeModal('modal-customer');
        allCustomers = await api('GET', 'customers');
        refreshCustomerDropdown();
        renderBoCustomers();
    } catch(e) { alert('Error: ' + e.message); }
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
