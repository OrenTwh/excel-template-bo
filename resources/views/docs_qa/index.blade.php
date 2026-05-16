<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QA Testing Guide — FX Back Office System</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --green:#217346; --green-light:#e8f5e9; --green-mid:#2e9e60;
    --blue:#1565c0;  --blue-light:#e3f2fd;
    --orange:#e67e22; --orange-light:#fff3e0;
    --red:#c62828;   --red-light:#ffebee;
    --gray:#f5f5f5;  --border:#ddd;
    --text:#222;     --muted:#666;
    --purple:#6a1b9a; --purple-light:#f3e5f5;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',Arial,sans-serif;font-size:14px;color:var(--text);background:#fafafa;line-height:1.6}

/* ── Layout ─────────────────────────────────────────────────────────────── */
.page-wrap{display:flex;min-height:100vh}
.sidebar{width:270px;flex-shrink:0;background:#1a1a2e;color:#ccc;position:sticky;top:0;height:100vh;overflow-y:auto;padding:0 0 40px}
.main{flex:1;max-width:920px;padding:40px 48px;overflow-x:hidden}

/* ── Sidebar ─────────────────────────────────────────────────────────────── */
.sb-logo{background:#c62828;color:#fff;padding:18px 20px;font-weight:700;font-size:15px;letter-spacing:.3px}
.sb-logo small{display:block;font-weight:400;font-size:11px;opacity:.8;margin-top:2px}
.sb-section{padding:10px 20px 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#888;margin-top:8px}
.sb-link{display:block;padding:7px 20px 7px 24px;color:#bbb;text-decoration:none;font-size:13px;border-left:3px solid transparent;transition:all .15s}
.sb-link:hover{color:#fff;border-left-color:#e53935;background:rgba(255,255,255,.05)}
.sb-link.active{color:#fff;border-left-color:#e53935;background:rgba(229,57,53,.15)}
.sb-link.sub{padding-left:36px;font-size:12px;color:#999}

/* ── Typography ──────────────────────────────────────────────────────────── */
h1{font-size:28px;font-weight:700;color:#c62828;margin-bottom:8px}
h2{font-size:20px;font-weight:600;color:#1a1a2e;margin:40px 0 14px;padding-bottom:8px;border-bottom:2px solid var(--border)}
h3{font-size:15px;font-weight:600;color:#333;margin:24px 0 10px}
h4{font-size:13px;font-weight:600;color:#555;margin:16px 0 6px;text-transform:uppercase;letter-spacing:.5px}
p{margin-bottom:10px;color:#444}
a{color:var(--blue);text-decoration:none}
a:hover{text-decoration:underline}
code{background:#f0f0f0;border:1px solid #ddd;border-radius:3px;padding:1px 5px;font-family:monospace;font-size:12px;color:#c7254e}
strong{font-weight:600}
kbd{background:#eee;border:1px solid #bbb;border-radius:3px;padding:1px 5px;font-size:12px;font-family:monospace}

/* ── Components ──────────────────────────────────────────────────────────── */
.lead{font-size:15px;color:#555;margin-bottom:0}

.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;vertical-align:middle;margin:1px}
.badge-pass  {background:#e8f5e9;color:#217346}
.badge-fail  {background:#ffebee;color:#c62828}
.badge-warn  {background:var(--orange-light);color:var(--orange)}
.badge-info  {background:var(--blue-light);color:var(--blue)}
.badge-stub  {background:#f3e5f5;color:#6a1b9a}

.callout{border-left:4px solid;border-radius:0 6px 6px 0;padding:12px 16px;margin:16px 0;font-size:13px}
.callout-info   {border-color:var(--blue);  background:var(--blue-light)}
.callout-tip    {border-color:var(--green); background:var(--green-light)}
.callout-warning{border-color:var(--orange);background:var(--orange-light)}
.callout-danger {border-color:var(--red);   background:var(--red-light)}
.callout-stub   {border-color:var(--purple);background:var(--purple-light)}
.callout-title  {font-weight:700;display:block;margin-bottom:4px}

.card{background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.card-title{font-weight:600;font-size:14px;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.card-icon{font-size:20px}

/* Checklist table */
.qa-table{width:100%;border-collapse:collapse;margin:14px 0;font-size:13px}
.qa-table th{background:#1a1a2e;color:#fff;padding:8px 12px;text-align:left;font-weight:600;font-size:12px}
.qa-table td{padding:7px 12px;border-bottom:1px solid var(--border);vertical-align:top}
.qa-table tr:nth-child(even) td{background:#f9f9f9}
.qa-table td:first-child{font-weight:500;white-space:nowrap;min-width:180px}
.qa-table .chk{text-align:center;width:36px;font-size:16px}

/* Standard doc table */
.doc-table{width:100%;border-collapse:collapse;margin:14px 0;font-size:13px}
.doc-table th{background:var(--green);color:#fff;padding:8px 12px;text-align:left;font-weight:600;font-size:12px}
.doc-table td{padding:7px 12px;border-bottom:1px solid var(--border);vertical-align:top}
.doc-table tr:nth-child(even) td{background:#f9f9f9}
.doc-table td:first-child{font-weight:500;white-space:nowrap}

/* Steps */
.steps{counter-reset:step;list-style:none;margin:12px 0}
.steps li{counter-increment:step;display:flex;gap:12px;margin-bottom:14px;align-items:flex-start}
.steps li::before{content:counter(step);flex-shrink:0;width:26px;height:26px;background:#c62828;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;margin-top:1px}

/* Section label */
.section-label{display:inline-flex;align-items:center;gap:6px;background:#1a1a2e;color:#fff;padding:3px 10px;border-radius:3px;font-size:11px;font-weight:600;letter-spacing:.5px;margin-bottom:10px}

/* Print */
@media print{
    .sidebar{display:none}
    .main{max-width:100%;padding:20px}
    h2{page-break-before:always}
    h2:first-of-type{page-break-before:avoid}
    .callout,.card{break-inside:avoid}
}
@media(max-width:768px){
    .sidebar{display:none}
    .main{padding:24px 20px}
}
</style>
</head>
<body>
<div class="page-wrap">

<!-- ══════════════════════════════════════════════════════════ SIDEBAR -->
<nav class="sidebar">
    <div class="sb-logo">
        🔍 QA Testing Guide
        <small>FX Back Office — Pre-Deploy</small>
    </div>

    <div class="sb-section">Overview</div>
    <a class="sb-link active" href="#scope">Scope & Setup</a>
    <a class="sb-link" href="#shared-data">Shared Data — FX &amp; BO</a>
    <a class="sb-link" href="#known-stubs">Known Stubs / Not Ready</a>

    <div class="sb-section">FX Routes (/fx)</div>
    <a class="sb-link" href="#fx-entry">Page Entry</a>
    <a class="sb-link" href="#fx-master">Master Sheet (总表)</a>
    <a class="sb-link sub" href="#fx-master-daily">Daily Cell Edits</a>
    <a class="sb-link sub" href="#fx-master-right">Customer Summary Panel</a>
    <a class="sb-link" href="#fx-detail">Detail Sheet (总表详情)</a>
    <a class="sb-link" href="#fx-customer">Customer Sheets</a>
    <a class="sb-link sub" href="#fx-tx">Transactions</a>
    <a class="sb-link sub" href="#fx-arr">Arrangements</a>
    <a class="sb-link sub" href="#fx-toggle">Check Today Toggle</a>
    <a class="sb-link" href="#fx-add-cust">Add Customer</a>
    <a class="sb-link sub" href="#fx-edit-cust">Edit Customer</a>
    <a class="sb-link sub" href="#fx-del-cust">Delete Customer</a>

    <div class="sb-section">BO Routes (/bo)</div>
    <a class="sb-link" href="#bo-entry">Page Entry</a>
    <a class="sb-link" href="#bo-transactions">Transactions</a>
    <a class="sb-link" href="#bo-banks">Banks</a>
    <a class="sb-link sub" href="#bo-bank-amount">Edit Amount</a>
    <a class="sb-link sub" href="#bo-bank-history">History</a>
    <a class="sb-link" href="#bo-banktx">FX Ledger</a>
    <a class="sb-link" href="#bo-cashflow">Cashflow Reports</a>
    <a class="sb-link" href="#bo-customers">Customers (BO)</a>
    <a class="sb-link sub" href="#bo-cust-edit">Edit Customer</a>
    <a class="sb-link sub" href="#bo-cust-delete">Delete Customer</a>
    <a class="sb-link" href="#bo-admins">Admins</a>
    <a class="sb-link" href="#bo-auth">Password &amp; Logout</a>

    <div class="sb-section">Cross-Surface</div>
    <a class="sb-link" href="#cross-balance">Balance Consistency</a>
    <a class="sb-link" href="#cross-tx">Transaction Sync</a>
</nav>

<!-- ══════════════════════════════════════════════════════════ MAIN -->
<main class="main">

<!-- ── Cover ─────────────────────────────────────────────────────────── -->
<section id="scope">
<h1>QA Testing Guide</h1>
<p class="lead">Pre-deploy checklist for the FX Back Office system. Covers all <code>/fx</code> and <code>/bo</code> routes, shared data, and known limitations.</p>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:24px 0">
    <div class="card">
        <div class="card-title"><span class="card-icon">📊</span> FX Surface</div>
        <p style="font-size:13px;color:#555;margin:0">Excel-like SPA at <code>/fx</code>. Manages customer FX transactions, arrangements, and master summaries.</p>
    </div>
    <div class="card">
        <div class="card-title"><span class="card-icon">🏦</span> BO Surface</div>
        <p style="font-size:13px;color:#555;margin:0">Dashboard SPA at <code>/bo</code>. Manages bank accounts, daily bank entries, cashflow, and admin users.</p>
    </div>
</div>

<div class="callout callout-info">
    <span class="callout-title">ℹ️ Both surfaces share the same customer &amp; transaction data</span>
    <code>fx_customers</code> and <code>fx_transactions</code> are read and written by both <code>/fx</code> and <code>/bo</code>. Changes made from one surface appear immediately on the other. <code>BoTransaction</code> is a separate, unrelated model (payment gateway ledger).
</div>

<h3>Test Environment Checklist</h3>
<table class="qa-table">
    <tr><th class="chk">✓</th><th>Item</th><th>Notes</th></tr>
    <tr><td class="chk">☐</td><td>Database seeded</td><td>At least 2–3 active <code>FxCustomer</code> records with existing transactions.</td></tr>
    <tr><td class="chk">☐</td><td>At least one BoBank exists</td><td>Required for bank transaction tests.</td></tr>
    <tr><td class="chk">☐</td><td>Admin account available</td><td>Logged in and session active.</td></tr>
    <tr><td class="chk">☐</td><td>Browser console open</td><td>Watch for JS errors on every sheet load and CRUD action.</td></tr>
    <tr><td class="chk">☐</td><td>Network tab open</td><td>Verify API calls return 200/201 and not 302 (redirect to login).</td></tr>
</table>
</section>

<!-- ── Shared Data ─────────────────────────────────────────────────────── -->
<h2 id="shared-data">Shared Data — FX &amp; BO</h2>

<p>The tables below are written by both surfaces. QA must verify that an action on one surface is reflected on the other.</p>

<table class="doc-table">
    <tr><th>Model / Table</th><th>FX can…</th><th>BO can…</th><th>Key difference</th></tr>
    <tr>
        <td><code>FxCustomer</code><br><small>fx_customers</small></td>
        <td>List (active only for tabs), Create, <strong>Update</strong>, <strong>Delete</strong></td>
        <td>List (all statuses), Create, <strong>Update</strong>, <strong>Delete</strong></td>
        <td>BO customer page shows all statuses including inactive. Delete is soft-delete. Status change is only editable via Update.</td>
    </tr>
    <tr>
        <td><code>FxTransaction</code><br><small>fx_transactions</small></td>
        <td>Create, Update, <strong>Delete</strong></td>
        <td>Create, Update — <strong>no delete</strong></td>
        <td>Deleting from FX removes the row from BO customer view too. BO has no delete route.</td>
    </tr>
    <tr>
        <td><code>FxArrangement</code><br><small>fx_arrangements</small></td>
        <td>Create, Update, Delete</td>
        <td><strong>Not exposed</strong></td>
        <td>BO customer view has no arrangements section or Pending MYR field.</td>
    </tr>
</table>

<div class="callout callout-warning">
    <span class="callout-title">⚠️ Balance formula is duplicated</span>
    Both surfaces compute <code>Balance = initial_balance + myr_converted − myr_out + myr_in</code>, but via different code paths. FX uses a DB aggregate; BO loads all rows into PHP first. Results must match — flag any discrepancy immediately.
</div>

<!-- ── Known Stubs ─────────────────────────────────────────────────────── -->
<h2 id="known-stubs">Known Stubs / Features Not Ready</h2>

<div class="callout callout-tip">
    <span class="callout-title">✅ No outstanding stubs</span>
    Transaction export is implemented and Tips &amp; Rating sections have been removed (no underlying data model). All routes are functional.
</div>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- FX ROUTES                                                              -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<h2 id="fx-entry">FX — Page Entry</h2>
<div class="section-label">GET /fx/</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Navigate to <code>/fx</code></td><td>Page loads. Toolbar shows current year and month. 总表 tab is active and starts loading.</td></tr>
    <tr><td class="chk">☐</td><td>Tab bar</td><td>Shows 总表, 总表详情, then one tab per <strong>active</strong> customer. Inactive customers have no tab.</td></tr>
    <tr><td class="chk">☐</td><td>Formula bar</td><td>Shows <code>ƒx</code> and "Select a cell…" placeholder on load. Updates when a cell is clicked.</td></tr>
    <tr><td class="chk">☐</td><td>Status indicator (top right)</td><td>Shows "Ready" after master sheet loads. Shows "Error: …" if the API call fails.</td></tr>
</table>

<!-- ── Master Sheet ────────────────────────────────────────────────────── -->
<h2 id="fx-master">FX — Master Sheet (总表)</h2>
<div class="section-label">GET /fx/master?year=&amp;month=</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Default load</td><td>Loads current year and month. Spinner shows then hides. Row count matches days in the month (28–31 rows).</td></tr>
    <tr><td class="chk">☐</td><td>Change year/month, click ↻</td><td>Master sheet reloads with new period data. Row 1 header updates to show new year/month.</td></tr>
    <tr><td class="chk">☐</td><td>TOTAL row (row 3)</td><td>Currency totals in row 3 match the sum of the daily rows below. BAL MYR total is the sum of all daily BAL MYR values.</td></tr>
    <tr><td class="chk">☐</td><td>Row 2 header figures</td><td>NEEDPAY = sum of all active customer balances. Net BUY = total BUY IN − total SELL OUT (all time).</td></tr>
    <tr><td class="chk">☐</td><td>Splitter drag</td><td>Dragging the vertical bar between the left and right panels resizes both panels correctly.</td></tr>
    <tr><td class="chk">☐</td><td>Customer name click (right panel)</td><td>Clicking a customer name in the right panel switches to that customer's tab.</td></tr>
</table>

<h3 id="fx-master-daily">Daily Cell Edits (左侧 currency cells)</h3>
<div class="section-label">PATCH /fx/master-daily</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Double-click a currency cell (light blue)</td><td>Cell enters edit mode. Value is editable. Formula bar shows current value.</td></tr>
    <tr><td class="chk">☐</td><td>Type a value, press <kbd>Enter</kbd></td><td>💾 saving spinner appears briefly on the cell. Value persists on reload.</td></tr>
    <tr><td class="chk">☐</td><td>Type a non-numeric value</td><td>API returns 422. Cell outline turns red. Value reverts to the previous value.</td></tr>
    <tr><td class="chk">☐</td><td>Press <kbd>Escape</kbd> mid-edit</td><td>Cell reverts to original value without saving.</td></tr>
    <tr><td class="chk">☐</td><td>Tab navigation between cells</td><td><kbd>Tab</kbd> moves focus right, <kbd>Shift+Tab</kbd> moves left. Arrow keys navigate without editing.</td></tr>
    <tr><td class="chk">☐</td><td>Reload master sheet after edits</td><td>Previously saved daily values are shown (light blue cells). They survive a reload and a tab switch + return.</td></tr>
</table>

<h3 id="fx-master-right">Customer Summary Panel (右侧)</h3>

<div class="callout callout-info">
    <span class="callout-title">ℹ️ Date parameter</span>
    The PROFIT/day, TRX IN#, and ACTIVE columns on the right panel are only populated when a <code>?date=</code> parameter is passed to the master API. Without a selected date, these columns will be blank — this is expected.
</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Customer rows present</td><td>One row per active customer, ordered by ID. Inactive customers do not appear.</td></tr>
    <tr><td class="chk">☐</td><td>Balance column</td><td>Matches the balance shown in the customer's own sheet header.</td></tr>
    <tr><td class="chk">☐</td><td>BUY IN / SELL OUT columns</td><td>Affected by the customer's Check Today toggle — verify they change when toggle is flipped.</td></tr>
</table>

<!-- ── Detail Sheet ────────────────────────────────────────────────────── -->
<h2 id="fx-detail">FX — Detail Sheet (总表详情)</h2>
<div class="section-label">GET /fx/detail?year=&amp;month=</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Click 总表详情 tab</td><td>Sheet loads. Columns = one per day in the month. Rows = one per active customer.</td></tr>
    <tr><td class="chk">☐</td><td>Cell content</td><td>A cell with activity shows: blue buy-in count, green ✓ (active flag), red profit amount. Empty cell = no activity on that date.</td></tr>
    <tr><td class="chk">☐</td><td>TOTAL row (row 3)</td><td>Shows the sum of buy-in counts across all customers for each date column.</td></tr>
    <tr><td class="chk">☐</td><td>Customer name click</td><td>Clicking a customer name navigates to their customer sheet tab.</td></tr>
    <tr><td class="chk">☐</td><td>Change month, reload</td><td>Column headers update to the new month's dates. Data reloads correctly.</td></tr>
    <tr><td class="chk">☐</td><td>Sheet is read-only</td><td>No cells should be editable in 总表详情. Double-clicking or typing should have no effect.</td></tr>
</table>

<!-- ── Customer Sheet ──────────────────────────────────────────────────── -->
<h2 id="fx-customer">FX — Customer Sheets</h2>
<div class="section-label">GET /fx/customer/{id}</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Click a customer tab</td><td>Sheet loads. Green header shows Balance, Pending MYR, Total Profit. Transaction table and arrangement table render.</td></tr>
    <tr><td class="chk">☐</td><td>Balance formula</td><td><code>Balance = initial_balance + Σ(myr_converted) + Σ(myr_in) − Σ(myr_out)</code>. Verify manually against a known customer.</td></tr>
    <tr><td class="chk">☐</td><td>Pending MYR formula</td><td><code>Pending MYR = Σ(arranging_amount) − Σ(done_amount)</code>. Shown in header and in the Arrangements section footer.</td></tr>
    <tr><td class="chk">☐</td><td>TOTAL row (row 6)</td><td>Sums of BUY IN, SELL OUT, CONV MYR, MYR OUT, MYR IN across all transactions (or today only when Check Today is ON).</td></tr>
    <tr><td class="chk">☐</td><td>CONV MYR column (F)</td><td>Read-only. Formula bar shows <code>E×(C−D)</code>. Value = Rate × (BUY_IN − SELL_OUT).</td></tr>
    <tr><td class="chk">☐</td><td>PROFIT column (L)</td><td>Read-only. Formula bar shows <code>C×(K−E)</code>. Value = BUY_IN × (Cost_Rate − Rate).</td></tr>
</table>

<h3 id="fx-tx">Transactions — Create, Edit, Delete</h3>
<div class="section-label">POST · PUT · DELETE /fx/transactions</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>New row — date is required</td><td>Pressing <kbd>Enter</kbd> on a new row without a date shows "Date is required" in the status bar. No record created.</td></tr>
    <tr><td class="chk">☐</td><td>Add a full transaction (all fields)</td><td>Row is saved, converted to a data row. A new blank row appears below. Header totals refresh.</td></tr>
    <tr><td class="chk">☐</td><td>CONV MYR and PROFIT update on save</td><td>After pressing <kbd>Enter</kbd>, columns F and L on the new row show the correct calculated values from the API response.</td></tr>
    <tr><td class="chk">☐</td><td>Edit amount_in on existing row</td><td>Double-click, change value, press <kbd>Enter</kbd>. CONV MYR and PROFIT cells in the same row update immediately (client-side recalc). Header totals also refresh.</td></tr>
    <tr><td class="chk">☐</td><td>Edit rate on existing row</td><td>Same as above — both formula columns recalculate after save.</td></tr>
    <tr><td class="chk">☐</td><td>Delete a row (✕ button)</td><td>Confirm dialog appears. On confirm, row is removed. Row numbers reindex. Header totals update.</td></tr>
    <tr><td class="chk">☐</td><td>Delete a row (right-click menu)</td><td>Context menu shows "Delete row". Deletes the transaction and reindexes.</td></tr>
    <tr><td class="chk">☐</td><td>Insert row above/below (right-click)</td><td>A new blank row is inserted at the correct position. Row numbers reindex. Saving the new row creates a real record.</td></tr>
    <tr><td class="chk">☐</td><td>Currency dropdown on new row</td><td>Dropdown shows: AUD, PGK, MYR, SGD, USDT, ABA USD, THB, VND. Selecting one sets the currency on save.</td></tr>
    <tr><td class="chk">☐</td><td>Reload after edits</td><td>Navigate away to another tab, come back — all saved changes are preserved. No phantom rows.</td></tr>
</table>

<h3 id="fx-arr">Arrangements — Create, Edit, Delete</h3>
<div class="section-label">POST · PUT · DELETE /fx/arrangements</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Add a new arrangement (date required)</td><td>Without date: status bar shows error. With date: row saves, new blank row appears.</td></tr>
    <tr><td class="chk">☐</td><td>Fill arranging_amount, leave done_amount as 0</td><td>Pending MYR in header increases by arranging_amount.</td></tr>
    <tr><td class="chk">☐</td><td>Edit done_amount to match arranging_amount</td><td>Pending MYR decreases accordingly.</td></tr>
    <tr><td class="chk">☐</td><td>Set is_done to YES</td><td>Row turns green. Pending MYR recalculates.</td></tr>
    <tr><td class="chk">☐</td><td>Delete an arrangement (right-click)</td><td>Row removed. Pending MYR updates. Row numbers reindex.</td></tr>
    <tr><td class="chk">☐</td><td>Pending MYR consistency</td><td>Header value = Arrangements section footer value. Both equal <code>Σ(arranging_amount) − Σ(done_amount)</code>.</td></tr>
</table>

<h3 id="fx-toggle">Check Today Toggle</h3>
<div class="section-label">POST /fx/customers/{id}/toggle-today</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Initial state</td><td>Button shows "ALL TIME" (green background).</td></tr>
    <tr><td class="chk">☐</td><td>Click toggle</td><td>Button turns orange, shows "TODAY ONLY". TOTAL row (row 6) re-fetches and shows only today's BUY IN / SELL OUT / CONV MYR / MYR OUT / MYR IN.</td></tr>
    <tr><td class="chk">☐</td><td>Balance is unaffected by toggle</td><td>Balance in header does not change — it is always all-time.</td></tr>
    <tr><td class="chk">☐</td><td>Toggle persists on reload</td><td>Navigate away and back. Button still shows "TODAY ONLY" if it was toggled on.</td></tr>
    <tr><td class="chk">☐</td><td>Master right panel reflects toggle</td><td>After toggling, reload the master sheet — BUY IN and SELL OUT columns for this customer update.</td></tr>
</table>

<!-- ── Add Customer ────────────────────────────────────────────────────── -->
<h2 id="fx-add-cust">FX — Add Customer</h2>
<div class="section-label">POST /fx/customers</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Open modal (+ Customer button or tab bar +)</td><td>Modal appears with Name and Opening Balance fields.</td></tr>
    <tr><td class="chk">☐</td><td>Submit without name</td><td>Alert: "Name required". No record created.</td></tr>
    <tr><td class="chk">☐</td><td>Submit with duplicate name</td><td>API returns 422. Alert shows the validation error message.</td></tr>
    <tr><td class="chk">☐</td><td>Submit valid name + opening balance</td><td>Modal closes. New tab appears in the tab bar with a ✕ button. Clicking the tab loads an empty customer sheet.</td></tr>
    <tr><td class="chk">☐</td><td>New customer appears in master right panel</td><td>After reloading the master sheet, the new customer row appears with correct balance.</td></tr>
    <tr><td class="chk">☐</td><td>New customer appears in BO customer list</td><td>Visit <code>/bo</code> → Customers page — the new customer is listed.</td></tr>
</table>

<h3 id="fx-edit-cust">Edit Customer</h3>
<div class="section-label">PUT /fx/customers/{id}</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>✏ Edit button visible</td><td>On any customer sheet, the green header bar shows a "✏ Edit" button aligned to the right.</td></tr>
    <tr><td class="chk">☐</td><td>Open Edit modal</td><td>Modal pre-fills Name, Opening Balance, and Status from the current customer record.</td></tr>
    <tr><td class="chk">☐</td><td>Submit without name</td><td>Alert: "Name is required". No API call made.</td></tr>
    <tr><td class="chk">☐</td><td>Submit duplicate name (another customer)</td><td>API returns 422. Alert shows the validation error. Modal stays open.</td></tr>
    <tr><td class="chk">☐</td><td>Update name</td><td>Modal closes. Tab label updates to the new name (✕ button preserved). Sheet reloads and shows updated name in the green header.</td></tr>
    <tr><td class="chk">☐</td><td>Update opening balance</td><td>Sheet reloads. Balance in the green header recalculates based on the new initial_balance value.</td></tr>
    <tr><td class="chk">☐</td><td>Set status to Inactive</td><td>Customer is saved as inactive. On next full page reload the customer tab no longer appears (FX only shows active in tabs). Verify via BO customer page that status is "inactive".</td></tr>
    <tr><td class="chk">☐</td><td>Edit modal also has Delete button</td><td>A red "Delete" button appears in the modal footer (bottom-left). Clicking it closes the modal and triggers the standard delete confirmation flow.</td></tr>
</table>

<h3 id="fx-del-cust">Delete Customer</h3>
<div class="section-label">DELETE /fx/customers/{id}</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>✕ button on tab</td><td>Each customer tab shows a small ✕ on the right. Clicking the tab label switches to it; clicking ✕ does not switch tabs.</td></tr>
    <tr><td class="chk">☐</td><td>Click ✕ — confirm dialog</td><td>Browser confirm appears: "Delete this customer and remove their sheet? This cannot be undone."</td></tr>
    <tr><td class="chk">☐</td><td>Cancel on confirm</td><td>Nothing happens. Tab and sheet remain.</td></tr>
    <tr><td class="chk">☐</td><td>Confirm delete</td><td>Customer is soft-deleted. Tab is removed from the bar. Sheet div is removed from the DOM. If the deleted tab was active, view switches to the master sheet.</td></tr>
    <tr><td class="chk">☐</td><td>Deleted customer no longer in master right panel</td><td>After reload, the customer row is gone from 总表 right panel.</td></tr>
    <tr><td class="chk">☐</td><td>Deleted customer visible in BO (inactive)</td><td>Visit BO → Customers, filter by ALL — the deleted customer appears as soft-deleted (will be absent since soft-delete removes from active list; confirm with DB if needed).</td></tr>
    <tr><td class="chk">☐</td><td>Delete via Edit modal</td><td>Same confirm dialog triggers. Same outcome as clicking ✕ directly.</td></tr>
</table>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- BO ROUTES                                                               -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<h2 id="bo-entry">BO — Page Entry</h2>
<div class="section-label">GET /bo/</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Navigate to <code>/bo</code></td><td>SPA shell loads. Announcement ticker appears at the top with the latest active announcement text (or default "Welcome to the Back Office." if none).</td></tr>
    <tr><td class="chk">☐</td><td>Announcement — no active record</td><td>Falls back to "Welcome to the Back Office." — no error, no blank bar.</td></tr>
</table>

<!-- ── BO Transactions ─────────────────────────────────────────────────── -->
<h2 id="bo-transactions">BO — Transactions</h2>
<div class="section-label">GET /bo/transactions</div>

<div class="callout callout-info">
    <span class="callout-title">ℹ️ Read-only in BO</span>
    <code>BoTransaction</code> records (the payment gateway ledger) are view-only in the BO surface. There is no create/edit/delete for them here.
</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Load with no filters</td><td>Returns paginated results (50/page). Shows total record count and total amount across all matching records.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by transaction_id</td><td>LIKE search — partial match works.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by customer (id or phone)</td><td>Both customer_id and customer_phone are searched. Returns rows matching either.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by date range</td><td><code>date_from</code> and <code>date_to</code> are inclusive. Boundary dates (exact match) are included.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by status = "all"</td><td>No status filter is applied — all statuses returned.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by bo_bank_id</td><td>Results show only transactions linked to that bank. Bank name is visible via eager-loaded relation.</td></tr>
    <tr><td class="chk">☐</td><td>Sort: pending_new (default)</td><td>Records ordered by <code>transacted_at DESC</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Sort: pending_old</td><td>Records ordered by <code>transacted_at ASC</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Total amount field</td><td>Reflects the sum of <strong>all filtered records</strong>, not just the current page. Verify by filtering to a known set.</td></tr>
    <tr>
        <td class="chk">☐</td>
        <td>Export button (ADVANCED mode only)</td>
        <td>Clicking EXPORT downloads a <code>.csv</code> file with all currently filtered records. Verify filename contains today's date. Verify column headers: Transaction ID, Customer ID, Phone, Type, Amount, Status, Agent, Bank, Other Info, Transacted At.</td>
    </tr>
    <tr>
        <td class="chk">☐</td>
        <td>Export respects active filters</td>
        <td>Apply a date range and agent filter, then export. CSV must contain only matching records — not the full unfiltered table.</td>
    </tr>
</table>

<!-- ── BO Banks ────────────────────────────────────────────────────────── -->
<h2 id="bo-banks">BO — Banks</h2>
<div class="section-label">GET · POST · PUT /bo/banks</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>List banks (no filter)</td><td>All banks returned, ordered by display_order then id.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by status=active</td><td>Only active banks returned.</td></tr>
    <tr><td class="chk">☐</td><td>Create bank — missing required field</td><td>Omit bank_name, account_name, or account_number. Expect 422 with validation message.</td></tr>
    <tr><td class="chk">☐</td><td>Create bank — all fields valid</td><td>Bank created. <code>bank_id</code> is auto-generated (unix timestamp). Status defaults to "active".</td></tr>
    <tr><td class="chk">☐</td><td>Edit bank details (PUT)</td><td>Can update: display_order, gateway, bank_name, account_name, account_number, remark, status, config. Response returns updated record.</td></tr>
</table>

<h3 id="bo-bank-amount">Edit Bank Balance (Amount)</h3>
<div class="section-label">PUT /bo/banks/{id}/amount</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Update to a higher value</td><td>Bank balance updates. A <code>BoBankTransaction</code> record is auto-created with description "Manual balance adjustment: X → Y" and <code>amount_in = diff</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Update to a lower value</td><td>Bank balance decreases. Auto-created record has <code>amount_out = |diff|</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Save the same value</td><td>Balance unchanged. No auto-created history entry (diff = 0).</td></tr>
    <tr><td class="chk">☐</td><td>Submit non-numeric value</td><td>422 returned. Balance unchanged.</td></tr>
    <tr><td class="chk">☐</td><td>Verify in bank history</td><td>After an edit, open the bank's history — the adjustment entry appears at the top.</td></tr>
</table>

<h3 id="bo-bank-history">Bank History</h3>
<div class="section-label">GET /bo/banks/{id}/history</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>History loads for existing bank</td><td>Up to 200 rows, ordered by date desc then id desc.</td></tr>
    <tr><td class="chk">☐</td><td>History for non-existent bank</td><td>404 returned.</td></tr>
</table>

<!-- ── Bank Transactions ───────────────────────────────────────────────── -->
<h2 id="bo-banktx">BO — FX Ledger (Bank Transactions)</h2>
<div class="section-label">GET · POST · PUT · DELETE /bo/bank-transactions</div>

<div class="callout callout-danger">
    <span class="callout-title">⚠️ Balance is maintained by increment/decrement — no DB transaction wrapping</span>
    Every write (store, update, delete) adjusts <code>bo_banks.balance</code> via a separate query. If the process is interrupted between the two writes, the balance may drift. Test store → update → delete in sequence and verify balance stays consistent.
</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>GET without bank_id</td><td>Returns 422 with error "bank_id required".</td></tr>
    <tr><td class="chk">☐</td><td>GET with bank_id, no date</td><td>Defaults to today. Returns bank record, start_bal, today_in, balance, and rows.</td></tr>
    <tr><td class="chk">☐</td><td>start_bal calculation</td><td><code>start_bal = bank.balance − (today_in − today_out)</code>. Verify: start_bal + net today = current balance.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by type=in</td><td>Only rows with amount_in &gt; 0 returned.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by type=out</td><td>Only rows with amount_out &gt; 0 returned.</td></tr>
    <tr><td class="chk">☐</td><td>Search hits description and ref_id</td><td>Partial string match works on both fields.</td></tr>
    <tr><td class="chk">☐</td><td>Create entry with amount_in</td><td>Row saved. Bank balance increments by amount_in.</td></tr>
    <tr><td class="chk">☐</td><td>Create entry with amount_out</td><td>Row saved. Bank balance decrements by amount_out.</td></tr>
    <tr><td class="chk">☐</td><td>Create without bo_bank_id or date</td><td>422 returned. No record created. Balance unchanged.</td></tr>
    <tr><td class="chk">☐</td><td>Update amount_in from 100 to 200</td><td>Bank balance increases by 100 (the diff). Row is updated.</td></tr>
    <tr><td class="chk">☐</td><td>Update: switch amount_in to amount_out</td><td>Balance correctly reverses — decrements by original in + new out.</td></tr>
    <tr><td class="chk">☐</td><td>Delete a row with amount_in</td><td>Row hard-deleted. Bank balance decrements by amount_in (balance reversal).</td></tr>
    <tr><td class="chk">☐</td><td>Sequence: add 500 in → edit to 300 in → delete</td><td>Starting balance + 500 → + 300 → back to starting balance. Final balance must match starting balance exactly.</td></tr>
</table>

<!-- ── Cashflow ────────────────────────────────────────────────────────── -->
<h2 id="bo-cashflow">BO — Cashflow Reports</h2>
<div class="section-label">GET /bo/cashflow · /bo/cashflow/bank · /bo/cashflow/staff · /bo/cashflow/activity</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Cashflow — default load</td><td>Defaults to current month (startOfMonth → endOfMonth). Grouped daily.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow — display=monthly</td><td>Periods are formatted <code>YYYY-MM</code>. Multiple days collapse into one row per month.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow — display=yearly</td><td>Periods are formatted <code>YYYY</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow totals row</td><td><code>totals.deposit_amount</code> = sum of all deposit_amount rows returned. Verify manually.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow bank — no bank_id filter</td><td>All banks included in the period.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow bank — with bank_id filter</td><td>Only that bank's entries are shown.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow staff — deposit table</td><td>Rows grouped by agent_username. Shows approval count, rejection count, avg response time.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow staff — avg response on pending records</td><td>Known limitation: pending records inflate the average (measured from created_at to updated_at which may be the same). Verify this is acceptable display behaviour.</td></tr>
    <tr>
        <td class="chk">☐</td>
        <td>Cashflow staff — tips &amp; rating sections</td>
        <td>These sections have been removed from the UI. Verify neither "Tips" nor "Rating" headings or tables appear in the staff report view.</td>
    </tr>
    <tr><td class="chk">☐</td><td>Cashflow activity — default load</td><td>Current month, paginated 50/page. Latest activity at top.</td></tr>
    <tr><td class="chk">☐</td><td>Cashflow activity — action filter</td><td>Filters by exact match on Spatie activity log <code>description</code> field.</td></tr>
</table>

<!-- ── BO Customers ────────────────────────────────────────────────────── -->
<h2 id="bo-customers">BO — Customers Page</h2>
<div class="section-label">GET · POST · PUT · DELETE /bo/customers &nbsp;|&nbsp; GET /bo/customers/{id} &nbsp;|&nbsp; POST · PUT /bo/customers/transactions</div>

<div class="callout callout-warning">
    <span class="callout-title">⚠️ Same data as FX — cross-surface verification required</span>
    Customers and transactions created or edited here are the same records seen in <code>/fx</code>. Run the cross-surface tests in the section below after these.
</div>

<div class="callout callout-info">
    <span class="callout-title">ℹ️ Dedicated Customers nav page</span>
    The BO nav bar has a dedicated <strong>Customers</strong> page (person icon). The "FX Ledger" tab (grid icon) is a separate view showing FX customer transaction rows. They are different pages.
</div>

<h4>Customer List, Create</h4>
<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Navigate to Customers page</td><td>Table loads with all active customers. Each row shows ID, Name, Initial Balance, Status badge (green ACTIVE / red INACTIVE), and EDIT + DELETE buttons.</td></tr>
    <tr><td class="chk">☐</td><td>Status filter — ALL</td><td>Shows all customers including inactive ones.</td></tr>
    <tr><td class="chk">☐</td><td>Status filter — INACTIVE</td><td>Shows only customers with status = inactive.</td></tr>
    <tr><td class="chk">☐</td><td>Name search filter</td><td>Filters the in-memory list client-side — no API call. Partial match, case-insensitive.</td></tr>
    <tr><td class="chk">☐</td><td>Create customer — valid</td><td>Modal: Name + Initial Balance. On save, customer created with status=active. List refreshes. New customer appears.</td></tr>
    <tr><td class="chk">☐</td><td>Create customer — blank name</td><td>Alert: "Name is required". No API call.</td></tr>
    <tr><td class="chk">☐</td><td>Create customer — duplicate name</td><td>422 returned. Alert shows validation error.</td></tr>
    <tr><td class="chk">☐</td><td>New customer available in FX Ledger dropdown</td><td>After creating, switch to the FX Ledger page — new customer appears in the customer dropdown immediately (shared in-memory list).</td></tr>
</table>

<h3 id="bo-cust-edit">Edit Customer</h3>
<div class="section-label">PUT /bo/customers/{id}</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Click EDIT on a customer row</td><td>Modal opens titled "Edit Customer". Name, Initial Balance, and Status fields are pre-filled.</td></tr>
    <tr><td class="chk">☐</td><td>Status field visible on edit</td><td>Status dropdown (ACTIVE / INACTIVE) is visible only when editing — not shown on the create modal.</td></tr>
    <tr><td class="chk">☐</td><td>Update name</td><td>Name updates. Customer list refreshes. FX Ledger customer dropdown reflects new name.</td></tr>
    <tr><td class="chk">☐</td><td>Update initial balance</td><td>Balance column in list updates. Balance on FX customer sheet recalculates on next reload.</td></tr>
    <tr><td class="chk">☐</td><td>Set status to INACTIVE</td><td>Status badge turns red. Customer still visible in the BO list (status filter = ALL or INACTIVE). FX tabs will no longer show this customer after a page reload.</td></tr>
    <tr><td class="chk">☐</td><td>Submit duplicate name</td><td>422 returned. Alert shows error. Record unchanged.</td></tr>
</table>

<h3 id="bo-cust-delete">Delete Customer</h3>
<div class="section-label">DELETE /bo/customers/{id}</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Click DELETE — confirm dialog</td><td>Browser confirm: "Delete customer &quot;[name]&quot;? This cannot be undone."</td></tr>
    <tr><td class="chk">☐</td><td>Cancel</td><td>Nothing changes.</td></tr>
    <tr><td class="chk">☐</td><td>Confirm delete</td><td>Customer is soft-deleted. Row removed from the in-memory list. Table re-renders. FX Ledger dropdown no longer shows this customer.</td></tr>
    <tr><td class="chk">☐</td><td>Deleted customer no longer in FX tabs</td><td>After full page reload of <code>/fx</code>, the deleted customer tab is absent.</td></tr>
</table>

<h4>FX Ledger Transactions (via BO)</h4>
<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>View customer detail (FX Ledger page)</td><td>Returns customer, all transactions ordered by date/id, and summary (total_myr_in, total_myr_out, balance). No arrangements section.</td></tr>
    <tr><td class="chk">☐</td><td>BO balance vs FX balance</td><td>Balance shown in BO must match the Balance shown on the same customer's FX sheet header. See <a href="#cross-balance">Cross-Surface: Balance</a>.</td></tr>
    <tr><td class="chk">☐</td><td>Create transaction via BO</td><td>Record appears in the BO customer transaction list and in the FX customer sheet after reload.</td></tr>
    <tr><td class="chk">☐</td><td>Update transaction via BO</td><td>CONV MYR and PROFIT are recalculated. Updated values match the FX sheet.</td></tr>
    <tr><td class="chk">☐</td><td>No delete transaction in BO</td><td>Confirm there is no delete option in the BO transaction interface.</td></tr>
    <tr><td class="chk">☐</td><td>No arrangements in BO</td><td>The BO customer detail response and UI do not show arrangements or Pending MYR.</td></tr>
</table>

<!-- ── Admins ─────────────────────────────────────────────────────────── -->
<h2 id="bo-admins">BO — Admins</h2>
<div class="section-label">GET · POST · PUT /bo/admins</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>List admins — no filter</td><td>All administrators returned with mapped fields (username = name field, name = fullname).</td></tr>
    <tr><td class="chk">☐</td><td>Filter by name (partial)</td><td>Matches fullname OR name (username). Partial match works.</td></tr>
    <tr><td class="chk">☐</td><td>Filter by role and status</td><td>Exact match. Passing "all" skips the filter.</td></tr>
    <tr><td class="chk">☐</td><td>Create admin — all required fields</td><td>Admin created. Password is hashed. Email defaults to <code>{username}@bo.local</code> if not provided.</td></tr>
    <tr><td class="chk">☐</td><td>Create admin — duplicate username</td><td>422 returned. Username must be unique on <code>administrators.name</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Create admin — password under 6 chars</td><td>422 returned.</td></tr>
    <tr><td class="chk">☐</td><td>Update admin — change role/status</td><td>Updated immediately. No password change if password field is blank.</td></tr>
    <tr><td class="chk">☐</td><td>Update admin — provide new password</td><td>Password is hashed and updated. Old password no longer works for login.</td></tr>
</table>

<!-- ── Password & Logout ───────────────────────────────────────────────── -->
<h2 id="bo-auth">BO — Password &amp; Logout</h2>
<div class="section-label">POST /bo/update-password &nbsp;|&nbsp; POST /bo/logout</div>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Update password — correct current password</td><td>Password updated. Response <code>{"success":true}</code>.</td></tr>
    <tr><td class="chk">☐</td><td>Update password — wrong current password</td><td>422 returned with message "Current password is incorrect."</td></tr>
    <tr><td class="chk">☐</td><td>Update password — confirmation mismatch</td><td>422 returned (new_password confirmation fails).</td></tr>
    <tr><td class="chk">☐</td><td>Update password — new password under 6 chars</td><td>422 returned.</td></tr>
    <tr><td class="chk">☐</td><td>Logout</td><td>Session invalidated, CSRF token regenerated. Response contains redirect URL to login page. User cannot access authenticated routes after logout.</td></tr>
</table>

<!-- ══════════════════════════════════════════════════════════════════════ -->
<!-- CROSS-SURFACE TESTS                                                     -->
<!-- ══════════════════════════════════════════════════════════════════════ -->

<h2 id="cross-balance">Cross-Surface — Balance Consistency</h2>

<div class="callout callout-danger">
    <span class="callout-title">⚠️ Critical — run after any transaction create/edit/delete</span>
    FX and BO compute balance via different code paths. Both must produce the same result.
</div>

<ol class="steps">
    <li>Pick a customer with at least 3 existing transactions and a non-zero initial balance.</li>
    <li>Open their FX sheet. Note the <strong>Balance</strong> shown in the green header.</li>
    <li>Open <code>/bo/customers/{id}</code> (API or BO UI). Note the <strong>balance</strong> in the summary.</li>
    <li>Both values must be identical. If not — the formula divergence is a bug.</li>
    <li>Create a new transaction via the BO customer tx store. Reload both surfaces. Balances must still match.</li>
    <li>Delete a transaction from the FX surface. Reload the BO customer view. Balance must update correctly in BO even though BO has no delete route.</li>
</ol>

<h2 id="cross-tx">Cross-Surface — Transaction Sync</h2>

<table class="qa-table">
    <tr><th class="chk">✓</th><th>Test</th><th>Expected</th></tr>
    <tr><td class="chk">☐</td><td>Create tx via FX → check BO</td><td>Transaction visible in <code>bo/customers/{id}</code> response immediately.</td></tr>
    <tr><td class="chk">☐</td><td>Create tx via BO → check FX</td><td>After reloading the customer tab in FX, new row appears in the transaction table.</td></tr>
    <tr><td class="chk">☐</td><td>Edit tx via BO → check FX</td><td>Updated values (including recalculated CONV MYR and PROFIT) appear in FX sheet after reload.</td></tr>
    <tr><td class="chk">☐</td><td>Delete tx via FX → check BO customer total</td><td>BO customer summary totals (myr_in, myr_out, balance) update correctly without the deleted row.</td></tr>
    <tr><td class="chk">☐</td><td>Create customer via FX → check BO list</td><td>New customer appears in BO Customers page immediately (same table).</td></tr>
    <tr><td class="chk">☐</td><td>Create customer via BO → check FX tabs</td><td>New customer tab does not auto-appear in FX (requires page reload). After reload, new tab is present since customer is active.</td></tr>
    <tr><td class="chk">☐</td><td>Edit customer name via FX → check BO</td><td>Updated name appears in BO Customers list after it re-fetches from the API.</td></tr>
    <tr><td class="chk">☐</td><td>Edit customer name via BO → check FX</td><td>FX tab label and sheet header show new name after reloading that customer sheet.</td></tr>
    <tr><td class="chk">☐</td><td>Set customer inactive via FX Edit modal → check BO</td><td>BO Customers page (filter ALL) shows status badge as red INACTIVE.</td></tr>
    <tr><td class="chk">☐</td><td>Set customer inactive via BO Edit → check FX</td><td>After full FX page reload, the customer tab is absent (FX only shows active). Existing sheet data is untouched if the tab was already loaded.</td></tr>
    <tr><td class="chk">☐</td><td>Delete customer via FX → check BO</td><td>After FX delete, BO Customers page no longer shows the customer (soft-deleted, excluded from default query).</td></tr>
    <tr><td class="chk">☐</td><td>Delete customer via BO → check FX</td><td>After BO delete, FX page reload shows no tab for that customer.</td></tr>
    <tr><td class="chk">☐</td><td>Arrangements created in FX — invisible in BO</td><td>BO customer detail does not include arrangements. Pending MYR is absent from BO customer summary — confirm this is accepted behaviour.</td></tr>
</table>

<hr style="margin:40px 0;border:none;border-top:1px solid #eee">
<p style="font-size:12px;color:#aaa;text-align:center">
    FX Back Office System — QA Testing Guide &nbsp;|&nbsp; Generated {{ date('d M Y') }}
    &nbsp;|&nbsp; <a href="#scope" style="color:#aaa">Back to top ↑</a>
    &nbsp;|&nbsp; <a href="javascript:window.print()" style="color:#aaa">🖨 Print</a>
</p>

</main>
</div>

<script>
const sections = document.querySelectorAll('section[id], h2[id], h3[id]');
const sbLinks  = document.querySelectorAll('.sb-link');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) current = s.id; });
    sbLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + current));
}, { passive: true });
</script>
</body>
</html>
