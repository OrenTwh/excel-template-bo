<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>用户指南 — FX 后台管理系统</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --green:#217346; --green-light:#e8f5e9; --green-mid:#2e9e60;
    --blue:#1565c0;  --blue-light:#e3f2fd;
    --orange:#e67e22; --orange-light:#fff3e0;
    --red:#c62828;   --gray:#f5f5f5; --border:#ddd;
    --text:#222; --muted:#666;
}
html{scroll-behavior:smooth}
body{font-family:'Inter', "Microsoft YaHei", Arial, sans-serif;font-size:14px;color:var(--text);background:#fafafa;line-height:1.6}

/* ── 布局 ─────────────────────────────────────────────────────────────── */
.page-wrap{display:flex;min-height:100vh}
.sidebar{width:260px;flex-shrink:0;background:#1a1a2e;color:#ccc;position:sticky;top:0;height:100vh;overflow-y:auto;padding:0 0 40px}
.main{flex:1;max-width:900px;padding:40px 48px;overflow-x:hidden}

/* ── 侧边栏 ─────────────────────────────────────────────────────────────── */
.sb-logo{background:var(--green);color:#fff;padding:18px 20px;font-weight:700;font-size:15px;letter-spacing:.3px}
.sb-logo small{display:block;font-weight:400;font-size:11px;opacity:.8;margin-top:2px}
.sb-section{padding:10px 20px 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#888;margin-top:8px}
.sb-link{display:block;padding:7px 20px 7px 24px;color:#bbb;text-decoration:none;font-size:13px;border-left:3px solid transparent;transition:all .15s}
.sb-link:hover{color:#fff;border-left-color:var(--green-mid);background:rgba(255,255,255,.05)}
.sb-link.active{color:#fff;border-left-color:var(--green-mid);background:rgba(46,158,96,.15)}
.sb-link.sub{padding-left:36px;font-size:12px;color:#999}

/* ── 字体排版 ──────────────────────────────────────────────────────────── */
h1{font-size:28px;font-weight:700;color:var(--green);margin-bottom:8px}
h2{font-size:20px;font-weight:600;color:#1a1a2e;margin:40px 0 14px;padding-bottom:8px;border-bottom:2px solid var(--border)}
h3{font-size:15px;font-weight:600;color:#333;margin:24px 0 10px}
h4{font-size:13px;font-weight:600;color:#555;margin:16px 0 6px;text-transform:uppercase;letter-spacing:.5px}
p{margin-bottom:10px;color:#444}
a{color:var(--blue);text-decoration:none}
a:hover{text-decoration:underline}
code{background:#f0f0f0;border:1px solid #ddd;border-radius:3px;padding:1px 5px;font-family:monospace;font-size:12px;color:#c7254e}
strong{font-weight:600}

/* ── 组件 ──────────────────────────────────────────────────────────── */
.lead{font-size:15px;color:#555;margin-bottom:0}

.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;vertical-align:middle;margin-left:4px}
.badge-green {background:var(--green-light);color:var(--green)}
.badge-blue  {background:var(--blue-light); color:var(--blue)}
.badge-orange{background:var(--orange-light);color:var(--orange)}
.badge-gray  {background:#eee;color:#555}

.callout{border-left:4px solid;border-radius:0 6px 6px 0;padding:12px 16px;margin:16px 0;font-size:13px}
.callout-info   {border-color:var(--blue);  background:var(--blue-light)}
.callout-tip    {border-color:var(--green); background:var(--green-light)}
.callout-warning{border-color:var(--orange);background:var(--orange-light)}
.callout-title  {font-weight:700;display:block;margin-bottom:4px}

.card{background:#fff;border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.card-title{font-weight:600;font-size:14px;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.card-icon{font-size:20px}

/* 步骤 */
.steps{counter-reset:step;list-style:none;margin:12px 0}
.steps li{counter-increment:step;display:flex;gap:12px;margin-bottom:14px;align-items:flex-start}
.steps li::before{content:counter(step);flex-shrink:0;width:26px;height:26px;background:var(--green);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;margin-top:1px}

/* 表格 */
.doc-table{width:100%;border-collapse:collapse;margin:14px 0;font-size:13px}
.doc-table th{background:var(--green);color:#fff;padding:8px 12px;text-align:left;font-weight:600;font-size:12px}
.doc-table td{padding:7px 12px;border-bottom:1px solid var(--border)}
.doc-table tr:nth-child(even) td{background:#f9f9f9}
.doc-table td:first-child{font-weight:500;white-space:nowrap}

/* 公式框 */
.formula-box{background:#1a1a2e;color:#a5d6a7;border-radius:6px;padding:12px 16px;font-family:monospace;font-size:13px;margin:10px 0;line-height:1.8}
.formula-box .fx-comment{color:#888}
.formula-box .fx-result{color:#ffd700}

/* 列颜色标签 */
.chip{display:inline-block;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;color:#fff;margin:1px}
.chip-green {background:#217346}
.chip-blue  {background:#1565c0}
.chip-teal  {background:#00796b}
.chip-dark  {background:#424242}
.chip-gray  {background:#757575}
.chip-orange{background:#e67e22}

/* 截图占位符 */
.mock-screen{background:#fff;border:1px solid #ccc;border-radius:6px;overflow:hidden;margin:14px 0;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.mock-toolbar{background:var(--green);color:#fff;padding:7px 12px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:10px}
.mock-tabs{background:#d8d8d8;border-top:2px solid var(--green);display:flex;gap:2px;padding:0 8px}
.mock-tab{padding:4px 14px;font-size:11px;background:#c0c0c0;border-radius:3px 3px 0 0;cursor:default}
.mock-tab.active{background:#fff;font-weight:bold}
.mock-body{padding:10px;font-size:11px;color:#555;background:#fafafa;min-height:60px}
.mock-row{display:flex;gap:0;margin-bottom:2px}
.mock-cell{border:1px solid #e0e0e0;padding:2px 6px;font-size:11px;min-width:60px;white-space:nowrap}
.mock-cell.hdr{background:#217346;color:#fff;font-weight:bold;border-color:#1a5c38}
.mock-cell.row-hdr{background:#f2f2f2;color:#888;width:30px;text-align:center;border-color:#ccc}
.mock-cell.yellow{background:#fffde7}
.mock-cell.light-green{background:#e8f5e9}
.mock-cell.light-blue{background:#e3f2fd}
.mock-cell.total{background:#f5f5dc;font-weight:bold}
.mock-cell.num{text-align:right}

/* 打印 ────────────────────────────────────────────────────────────────────── */
@media print{
    .sidebar{display:none}
    .main{max-width:100%;padding:20px}
    h2{page-break-before:always}
    h2:first-of-type{page-break-before:avoid}
    .mock-screen,.callout,.card{break-inside:avoid}
}

/* 响应式 */
@media(max-width:768px){
    .sidebar{display:none}
    .main{padding:24px 20px}
}
</style>
</head>
<body>
<div class="page-wrap">

<nav class="sidebar">
    <div class="sb-logo">
        📊 FX 后台管理系统
        <small>用户指南 v1.0</small>
    </div>

    <div class="sb-section">入门指南</div>
    <a class="sb-link active" href="#overview">系统概览</a>
    <a class="sb-link" href="#access">访问系统</a>
    <a class="sb-link" href="#navigation">导航说明</a>

    <div class="sb-section">外汇电子表格 (FX)</div>
    <a class="sb-link" href="#master">总表 (Master Sheet)</a>
    <a class="sb-link sub" href="#master-left">每日流水</a>
    <a class="sb-link sub" href="#master-right">客户摘要</a>
    <a class="sb-link" href="#detail">总表详情 (Detail Sheet)</a>
    <a class="sb-link" href="#customer-sheet">客户个人表</a>
    <a class="sb-link sub" href="#cust-header">余额与统计</a>
    <a class="sb-link sub" href="#cust-tx">添加交易</a>
    <a class="sb-link sub" href="#cust-arr">马币结算安排</a>
    <a class="sb-link sub" href="#check-today">当日统计开关</a>
    <a class="sb-link" href="#add-customer">新增客户</a>
    <a class="sb-link" href="#formulas">计算字段说明</a>

    <div class="sb-section">后台管理 (BO)</div>
    <a class="sb-link" href="#bo-dashboard">BO 控制台</a>
    <a class="sb-link sub" href="#bo-banks">银行账户</a>
    <a class="sb-link sub" href="#bo-banktx">银行交易流水</a>
    <a class="sb-link sub" href="#bo-cashflow">现金流报表</a>
    <a class="sb-link sub" href="#bo-admins">管理员管理</a>

    <div class="sb-section">参考资料</div>
    <a class="sb-link" href="#column-ref">字段参考</a>
    <a class="sb-link" href="#glossary">术语表</a>
</nav>

<main class="main">

<section id="overview">
    <h1>FX 后台管理系统 — 用户指南</h1>
    <p class="lead">本指南详细说明了 FX 后台管理系统的各项操作流程，包括客户交易记录、银行流水跟踪、现金流监控及管理员权限管理。</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:24px 0">
        <div class="card">
            <div class="card-title"><span class="card-icon">📊</span> FX 电子表格</div>
            <p style="font-size:13px;color:#555;margin:0">记录客户外汇交易、跟踪利润、管理马币安排，延续 Google 表格的操作习惯。</p>
        </div>
        <div class="card">
            <div class="card-title"><span class="card-icon">🏦</span> BO 控制台</div>
            <p style="font-size:13px;color:#555;margin:0">日常银行流水录入、现金流报表查看、银行账户维护及后台用户控制。</p>
        </div>
    </div>

    <div class="callout callout-info">
        <span class="callout-title">ℹ️ 两个独立工具</span>
        <strong>FX 电子表格</strong> (<code>/fx</code>) 处理客户层面的外汇交易记录；<strong>BO 控制台</strong> (<code>/bo</code>) 处理银行层面的运营。两者通过同一账号访问。
    </div>
</section>

<h2 id="access">访问系统</h2>

<ol class="steps">
    <li>打开浏览器，前往管理员提供的后台地址。</li>
    <li>使用您的用户名和密码登录。如有提示，请完成多重身份验证 (MFA)。</li>
    <li>登录后，您可以通过以下链接进入对应工具：
        <br><br>
        <table class="doc-table" style="margin:8px 0">
            <tr><th>工具</th><th>URL 路径</th><th>主要用途</th></tr>
            <tr><td>FX 电子表格</td><td><code>…/fx</code></td><td>客户外币交易管理</td></tr>
            <tr><td>BO 控制台</td><td><code>…/bo</code></td><td>银行操作与现金流统计</td></tr>
        </table>
    </li>
</ol>

<h2 id="navigation">导航说明</h2>

<h3>FX 电子表格 — 工具栏与标签页</h3>

<div class="mock-screen">
    <div class="mock-toolbar">
        📊 FX 电子表格 &nbsp;|&nbsp; 年份: 2026 &nbsp; 月份: 04 &nbsp;
        <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:2px">↻ 刷新</span>
        <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:2px">+ 客户</span>
    </div>
    <div style="padding:2px 8px;background:#f5f5f5;border-bottom:1px solid #ddd;font-size:11px;color:#888">ƒx &nbsp; A1</div>
    <div class="mock-tabs">
        <div class="mock-tab active">总表</div>
        <div class="mock-tab">总表详情</div>
        <div class="mock-tab">客户1</div>
        <div class="mock-tab">客户2</div>
        <div class="mock-tab">+</div>
    </div>
    <div class="mock-body">表格内容在此加载……</div>
</div>

<table class="doc-table">
    <tr><th>元素</th><th>说明</th></tr>
    <tr><td>年份 / 月份</td><td>更改所有表格显示的时间段。更改后请点击 <strong>↻ 刷新</strong>。</td></tr>
    <tr><td>↻ 刷新</td><td>重新加载当前可见表格的数据。</td></tr>
    <tr><td>+ 客户</td><td>创建新客户并为其新增一个标签页。</td></tr>
    <tr><td>标签栏 (底部)</td><td>点击标签页切换工作表。首次点击会加载数据。</td></tr>
    <tr><td>公式栏</td><td>显示选中单元格的值或公式（仅供参考，不可直接编辑）。</td></tr>
</table>

<h2 id="master">总表 &nbsp;<span style="font-size:14px;color:#888">(Master Sheet)</span></h2>
<p>总表是所选月份所有活动的<strong>只读汇总</strong>，由左右两个独立的部分组成。</p>

<h3 id="master-left">左侧区域 — 每日流水</h3>
<p>每一行代表所选月份的一个日历日。</p>

<div class="mock-screen">
    <div class="mock-tabs" style="border-top:none;border-bottom:1px solid #ccc;background:#fff">
        <span style="padding:6px 12px;font-size:11px;color:#888">总表 &gt; 每日流水</span>
    </div>
    <div style="overflow-x:auto;padding:8px">
        <div class="mock-row">
            <div class="mock-cell row-hdr"></div>
            <div class="mock-cell hdr">日期</div>
            <div class="mock-cell hdr num">SGD</div>
            <div class="mock-cell hdr num">THB</div>
            <div class="mock-cell hdr num">…</div>
            <div class="mock-cell hdr num">马币余额</div>
            <div class="mock-cell hdr num">利润</div>
            <div class="mock-cell hdr num">利润%</div>
        </div>
        <div class="mock-row">
            <div class="mock-cell row-hdr">3</div>
            <div class="mock-cell total">合计 (TOTAL)</div>
            <div class="mock-cell total num">0.00</div>
            <div class="mock-cell total num">0.00</div>
            <div class="mock-cell total num">…</div>
            <div class="mock-cell total num">0.00</div>
            <div class="mock-cell total num">0.00</div>
            <div class="mock-cell total num">—</div>
        </div>
        <div class="mock-row">
            <div class="mock-cell row-hdr">4</div>
            <div class="mock-cell yellow">2026-04-01</div>
            <div class="mock-cell num" style="color:#1a5c38;font-weight:bold">1,200.00</div>
            <div class="mock-cell num"></div>
            <div class="mock-cell num">…</div>
            <div class="mock-cell light-green num">1,248.00</div>
            <div class="mock-cell num" style="color:#1a5c38">48.00</div>
            <div class="mock-cell num">3.85%</div>
        </div>
    </div>
</div>

<table class="doc-table">
    <tr><th>字段</th><th>含义</th></tr>
    <tr><td>日期</td><td>日历日期（自动生成，每日一行）。</td></tr>
    <tr><td>SGD / THB / USDT 等</td><td>当天从所有客户买入的外币总额 (BUY IN)，按币种分组。</td></tr>
    <tr><td>马币余额 (BAL MYR)</td><td>当天的净马币余额 = <em>总折算马币 + 马币存入 − 马币支出</em>。</td></tr>
    <tr><td>利润 (PROFIT)</td><td>当日马币余额相对于前一天的变化。绿色表示盈利。</td></tr>
    <tr><td>利润 % (PROFIT %)</td><td>利润 ÷ 外币总成交额。反映利润率水平。</td></tr>
</table>

<h3 id="master-right">右侧区域 — 客户摘要</h3>
<p>每一行代表一名客户。点击客户姓名可直接跳转到其个人表格。</p>

<table class="doc-table">
    <tr><th>字段</th><th>含义</th></tr>
    <tr><td>余额 (Balance)</td><td>该客户当前的实时马币余额。</td></tr>
    <tr><td>买入 (BUY IN)</td><td>买入客户外币的总量（取决于客户表的“当日统计”开关，显示当日或累计）。</td></tr>
    <tr><td>卖出 (BUY OUT)</td><td>卖给客户外币的总量。</td></tr>
    <tr><td>交易次数</td><td>买入/卖出的总交易笔数。</td></tr>
    <tr><td>利润 (PROFIT)</td><td>所选日期内该客户产生的总利润。</td></tr>
    <tr><td>交易次数 #</td><td>所选日期内的买入交易次数。</td></tr>
    <tr><td>活跃 ✓</td><td>打勾表示该客户在所选日期内有至少一笔交易。</td></tr>
</table>

<div class="callout callout-tip">
    <span class="callout-title">💡 提示 — NEEDPAY (总应付)</span>
    总表顶部的 <strong>NEEDPAY</strong> 数值是所有客户当前马币余额的总和 — 即业务欠所有客户的资金总量。
</div>

<h2 id="detail">总表详情 &nbsp;<span style="font-size:14px;color:#888">(总表详情)</span></h2>
<p><strong>矩阵视图</strong> — 纵轴为客户，横轴为当月日期。用于快速观察哪些客户在哪些日期处于活跃状态。</p>

<div class="mock-screen">
    <div style="overflow-x:auto;padding:8px">
        <div class="mock-row">
            <div class="mock-cell row-hdr"></div>
            <div class="mock-cell hdr" style="min-width:80px">客户</div>
            <div class="mock-cell hdr num">04-01</div>
            <div class="mock-cell hdr num">04-02</div>
            <div class="mock-cell hdr num">04-03</div>
            <div class="mock-cell hdr num">…</div>
        </div>
        <div class="mock-row">
            <div class="mock-cell row-hdr">3</div>
            <div class="mock-cell total">合计</div>
            <div class="mock-cell total num">2</div>
            <div class="mock-cell total num">0</div>
            <div class="mock-cell total num">1</div>
            <div class="mock-cell total num">…</div>
        </div>
        <div class="mock-row">
            <div class="mock-cell row-hdr">4</div>
            <div class="mock-cell" style="font-weight:bold">客户1</div>
            <div class="mock-cell light-green" style="text-align:center;font-size:10px">2<br>✓<br><span style="color:#c00">48.00</span></div>
            <div class="mock-cell"></div>
            <div class="mock-cell light-green" style="text-align:center;font-size:10px">1<br>✓</div>
            <div class="mock-cell"></div>
        </div>
    </div>
</div>

<p>每个单元格最多显示三项内容：</p>
<table class="doc-table">
    <tr><th>数值</th><th>颜色</th><th>含义</th></tr>
    <tr><td>数字 (如 2)</td><td style="color:#1155cc">蓝色</td><td>当天买入交易的次数。</td></tr>
    <tr><td>✓</td><td style="color:#27ae60">绿色</td><td>客户活跃（当天有任何类型的交易）。</td></tr>
    <tr><td>金额 (如 48.00)</td><td style="color:#c00">红色</td><td>当天该客户产生的总利润。</td></tr>
</table>

<h2 id="customer-sheet">客户个人表 &nbsp;<span style="font-size:14px;color:#888">(客户1, 客户2, …)</span></h2>
<p>每名客户都有独立的标签页。您在这里录入外币交易和马币结算安排。</p>

<h3 id="cust-header">表头 — 余额与汇总</h3>
<p>顶部的绿色状态栏显示该客户的实时计算结果：</p>

<table class="doc-table">
    <tr><th>字段</th><th>说明</th><th>计算公式</th></tr>
    <tr><td><strong>BALANCE (余额)</strong></td><td>当前欠客户/客户欠业务的马币。</td><td><code>B2 = Σ(F,H) − Σ(G) + 初始余额</code></td></tr>
    <tr><td><strong>PENDING MYR (待结算)</strong></td><td>已安排但尚未支付完成的马币。</td><td><code>O4 = 安排金额 − 已办金额</code></td></tr>
    <tr><td><strong>TOTAL PROFIT (总利润)</strong></td><td>该客户累计产生的总利润。</td><td><code>L6 = Σ(L7:L9998)</code></td></tr>
    <tr><td><strong>当日统计 (CHECK TODAY)</strong></td><td>切换开关 — 见 <a href="#check-today">下方</a> 详情。</td><td><code>L1</code></td></tr>
</table>

<div class="callout callout-tip">
    <span class="callout-title">💡 合计行 (第 6 行)</span>
    表头下方的横条显示合计：买入、卖出、折算马币、马币支出和马币存入的总和。当<strong>当日统计</strong>开启时，仅显示<strong>今天的数值</strong>；关闭时显示累计数值。
</div>

<h3 id="cust-tx">添加 / 编辑交易</h3>

<ol class="steps">
    <li>点击交易列表底部的 <strong>+ 点击添加交易</strong>，或点击现有行的 <strong>✏</strong> 按钮进行编辑。</li>
    <li>在弹窗中填写详细信息：
        <table class="doc-table" style="margin-top:10px">
            <tr><th>字段</th><th>列</th><th>必填?</th><th>备注</th></tr>
            <tr><td>日期</td><td>A</td><td>✅ 是</td><td>外汇交易日期。</td></tr>
            <tr><td>币种</td><td>B</td><td>—</td><td>SGD, THB, USDT, AUD 等。</td></tr>
            <tr><td>买入 (BUY IN)</td><td>C</td><td>—</td><td>从客户处购入的外币金额。</td></tr>
            <tr><td>卖出 (SELL OUT)</td><td>D</td><td>—</td><td>向客户卖出的外币金额。</td></tr>
            <tr><td>汇率 (Rate)</td><td>E</td><td>—</td><td>本次成交的汇率。</td></tr>
            <tr><td>成本价 (Cost Rate)</td><td>K</td><td>—</td><td>您获取该币种的原始成本汇率。</td></tr>
            <tr><td>马币支出</td><td>G</td><td>—</td><td>支付给客户的马币。</td></tr>
            <tr><td>马币存入</td><td>H</td><td>—</td><td>从客户处收到的马币。</td></tr>
            <tr><td>备注</td><td>I</td><td>—</td><td>该笔交易的任何说明。</td></tr>
        </table>
    </li>
    <li><strong>折算马币</strong> 和 <strong>利润</strong> 在录入时会自动计算，无需手动填写。</li>
    <li>点击 <strong>保存 (Save)</strong>。表格和表头合计会立即刷新。</li>
</ol>

<div class="callout callout-warning">
    <span class="callout-title">⚠️ 删除交易</span>
    打开编辑弹窗，点击红色 <strong>删除</strong> 按钮。操作不可撤销，删除后所有余额会自动重新计算。
</div>

<h4>计算列逻辑</h4>

<div class="formula-box">
<span class="fx-comment">// 折算马币 (列 F)</span>
F = 汇率 × (买入 − 卖出)
<span class="fx-comment">// 例: 汇率=4.80, 买入=1000, 卖出=0  →</span> <span class="fx-result">F = 4.80 × 1000 = 4,800.00</span>

<span class="fx-comment">// 利润 (列 L)</span>
L = 买入 × (成本价 − 汇率)
<span class="fx-comment">// 例: 买入=1000, 成本价=4.75, 汇率=4.80  →</span> <span class="fx-result">L = 1000 × (4.75−4.80) = −50.00</span>
</div>

<p style="font-size:13px;color:#555">如果利润为负数，表示成本汇率高于成交汇率 — 即您购买该货币的价格高于您卖出的价格。</p>

<h3 id="cust-arr">马币结算安排 (Arrangements)</h3>
<p>安排表用于跟踪向客户转账马币的状态 — 包括待办金额和结算进度。</p>

<ol class="steps">
    <li>点击“结算安排”顶部的 <strong>+ 添加</strong>，或点击现有行的 <strong>✏</strong>。</li>
    <li>填写详情：</li>
</ol>

<table class="doc-table">
    <tr><th>字段</th><th>列</th><th>备注</th></tr>
    <tr><td>日期</td><td>N</td><td>安排转账的日期。</td></tr>
    <tr><td>账号</td><td>O</td><td>收款人的银行账号。</td></tr>
    <tr><td>收款人姓名</td><td>P</td><td>账户持有人的姓名。</td></tr>
    <tr><td>银行</td><td>Q</td><td>银行名称。</td></tr>
    <tr><td>安排金额</td><td>R</td><td>计划转账的马币金额。</td></tr>
    <tr><td>已办金额</td><td>S</td><td>实际已转出的马币金额。</td></tr>
    <tr><td>已办?</td><td>T</td><td>全部结算后标记为“是”，该行将变绿。</td></tr>
    <tr><td>经办人</td><td>U</td><td>处理此项安排的员工。</td></tr>
</table>

<div class="formula-box">
<span class="fx-comment">// 待结算马币 (表头 O4)</span>
待结算 = 安排总额 − 已办总额
<span class="fx-result">= Σ(R) − Σ(S)</span>
</div>

<h3 id="check-today">当日统计开关 (Check Today)</h3>

<div style="display:flex;gap:16px;margin:12px 0;flex-wrap:wrap">
    <div class="card" style="flex:1;min-width:200px;border-color:var(--green)">
        <div class="card-title">🟢 累计数据 (默认)</div>
        <p style="font-size:13px;margin:0">合计行显示自首笔交易以来的所有数据。余额和利润始终显示累计值。</p>
    </div>
    <div class="card" style="flex:1;min-width:200px;border-color:var(--orange)">
        <div class="card-title">🟠 仅限今日</div>
        <p style="font-size:13px;margin:0">合计行仅显示今天的交易。适用于每日结账对账。完成后切换回“累计”。</p>
    </div>
</div>

<p>点击客户表头中的 <strong>累计 / 仅限今日</strong> 按钮进行切换。此设置对每个客户独立生效且会自动保存。</p>

<h2 id="add-customer">新增客户</h2>

<ol class="steps">
    <li>点击工具栏的 <strong>+ 客户</strong>，或者点击标签栏最右侧的 <strong>+</strong> 按钮。</li>
    <li>输入客户的 <strong>姓名</strong> (这将作为标签页名称)。</li>
    <li>输入 <strong>初始余额</strong> — 如果是老客户，请输入开始使用系统前的欠款，新客户请填 0。</li>
    <li>点击 <strong>创建</strong>。新标签页将立即出现。</li>
</ol>

<div class="callout callout-warning">
    <span class="callout-title">⚠️ 客户名称必须唯一</span>
    客户名称是全系统识别客户的唯一依据。请勿创建重名客户。
</div>

<h2 id="formulas">计算字段 — 快速查阅</h2>

<table class="doc-table">
    <tr><th>字段</th><th>计算逻辑 (通俗易懂版)</th><th>显示位置</th></tr>
    <tr><td>折算马币 (F)</td><td>汇率 × (买入 − 卖出)</td><td>交易表, 合计行</td></tr>
    <tr><td>利润 (L)</td><td>买入 × (成本价 − 汇率)</td><td>交易表, 合计行, 客户表头</td></tr>
    <tr><td>余额 (B2)</td><td>Σ 折算马币 + Σ 马币存入 − Σ 马币支出 + 初始余额</td><td>客户表头, 总表右侧</td></tr>
    <tr><td>待结算马币 (O4)</td><td>Σ 安排金额 − Σ 已办金额</td><td>客户表头, 安排表页脚</td></tr>
    <tr><td>买入合计 (C6)</td><td>若开启“当日统计”，显示今天总和；否则显示全表总和</td><td>客户表合计行</td></tr>
    <tr><td>总应付 (NEEDPAY)</td><td>所有客户余额的总和</td><td>总表表头</td></tr>
    <tr><td>净买入 (Net BUY)</td><td>所有客户 (买入 − 卖出) 的总额</td><td>总表表头</td></tr>
    <tr><td>每日利润 (总表)</td><td>当天马币余额 − 前一天马币余额</td><td>总表左侧 PROFIT 列</td></tr>
    <tr><td>利润率 (总表)</td><td>当日利润 ÷ 当日外币总交易量</td><td>总表左侧 PROFIT% 列</td></tr>
</table>

<h2 id="bo-dashboard">BO 控制台</h2>
<p>BO 控制台是一个独立的管理工具，用于管理银行账户、每日银行流水、现金流报表和管理员账号。访问路径为 <code>…/bo</code>。</p>

<p>顶部导航栏包含六个核心功能：</p>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin:16px 0">
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">⇄</div>
        <strong style="font-size:12px">流水查询</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">搜索并列出所有交易记录</p>
    </div>
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">⊞</div>
        <strong style="font-size:12px">银行流水录入</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">每日银行对账单录入</p>
    </div>
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">📈</div>
        <strong style="font-size:12px">现金流</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">交易与利润统计报表</p>
    </div>
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">🏛</div>
        <strong style="font-size:12px">银行管理</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">管理各银行账号与余额</p>
    </div>
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">🪪</div>
        <strong style="font-size:12px">管理员</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">后台后台账号与权限管理</p>
    </div>
    <div class="card" style="text-align:center;padding:14px">
        <div style="font-size:24px">☰</div>
        <strong style="font-size:12px">菜单</strong>
        <p style="font-size:11px;color:#666;margin:4px 0 0">修改密码、设置、注销</p>
    </div>
</div>

<h3 id="bo-banks">银行管理 (Banks)</h3>
<p>维护您的银行账户列表。每个账户都会实时跟踪当前余额。</p>
<ul style="margin:8px 0 8px 24px;font-size:13px">
    <li><strong>创建 (CREATE)</strong> — 添加新的银行账户。</li>
    <li><strong>编辑金额 (EDIT AMOUNT)</strong> — 直接手动调整银行余额（用于初置或纠错）。</li>
    <li><strong>历史 (HISTORY)</strong> — 查看该账户的所有流水。</li>
    <li><strong>状态过滤</strong> — 查看“活跃”或“停用”的账号。</li>
</ul>

<h3 id="bo-banktx">银行流水录入 (Bank Transactions)</h3>
<p>这是每日对账的工作页 — 类似于在银行存折上记账。</p>
<ol class="steps">
    <li>选择 <strong>日期</strong> 并从下拉框或青色按钮中选择 <strong>银行</strong>。</li>
    <li>摘要栏显示：<strong>起始 (START)</strong>（当日初始余额）、<strong>今日流入 (TODAY)</strong>（今日净变动）、<strong>最终余额 (BALANCE)</strong>。</li>
    <li>在最后一行（<em>"按回车键保存"</em>）输入流水详情。填写描述、转入 (IN) 或转出 (OUT) 金额、时间、参考 ID 等。按下 <kbd>Enter</kbd> 保存。</li>
    <li>点击现有行的 <strong>✏</strong> 可直接行内编辑。</li>
</ol>

<div class="callout callout-info">
    <span class="callout-title">ℹ️ 余额自动更新</span>
    在流水页添加或编辑记录时，对应银行的余额会自动调整，无需单独修改银行管理里的余额。
</div>

<h3 id="bo-cashflow">现金流 (Cashflow)</h3>
<p>按日、月或年汇总充值/提现数据。</p>
<table class="doc-table">
    <tr><th>控件</th><th>用途</th></tr>
    <tr><td>周期 (从/到)</td><td>报表的时间范围。</td></tr>
    <tr><td>显示模式 — 日/月/年</td><td>数据的聚合方式。</td></tr>
    <tr><td>类型</td><td>按交易类型过滤。</td></tr>
    <tr><td>重新计算 (RECALCULATE)</td><td>按当前设置运行报表。</td></tr>
</table>

<h3 id="bo-admins">管理员管理 (Admins)</h3>
<p>管理员工后台账号。</p>
<ul style="margin:8px 0 8px 24px;font-size:13px">
    <li>使用 <strong>姓名 / 角色 / 状态</strong> 过滤条件并点击 <strong>搜索 (SEARCH)</strong>。</li>
    <li>点击 <strong>创建 (CREATE)</strong> 添加新管理员，设置角色和密码。</li>
    <li>点击 <strong>编辑 (EDIT)</strong> 更新资料或重置密码（密码留空则不修改）。</li>
    <li>点击 <strong>IP</strong> 管理该账户允许登录的 IP 地址白名单。</li>
</ul>

<h2 id="column-ref">字段参考表</h2>
<p>客户表格中每一列的含义快速查询。</p>

<table class="doc-table">
    <tr><th>列</th><th>标签</th><th>类型</th><th>详细描述</th></tr>
    <tr><td>A</td><td>日期</td><td><span class="chip chip-dark">手动录入</span></td><td>交易日期。</td></tr>
    <tr><td>B</td><td>币种</td><td><span class="chip chip-dark">手动录入</span></td><td>货币代码 (SGD, THB, USDT, AUD 等)。</td></tr>
    <tr><td>C</td><td>买入 (BUY IN)</td><td><span class="chip chip-blue">手动录入</span></td><td>从客户手中买入的外币。</td></tr>
    <tr><td>D</td><td>卖出 (SELL OUT)</td><td><span class="chip chip-blue">手动录入</span></td><td>卖给客户的外币。</td></tr>
    <tr><td>E</td><td>汇率</td><td><span class="chip chip-gray">手动录入</span></td><td>本次交易所使用的成交汇率。</td></tr>
    <tr><td>F</td><td>折算马币</td><td><span class="chip chip-green">自动计算</span></td><td>E × (C − D)。对应的净马币金额。</td></tr>
    <tr><td>G</td><td>马币支出</td><td><span class="chip chip-teal">手动录入</span></td><td>实际支付给客户的马币金额。</td></tr>
    <tr><td>H</td><td>马币存入</td><td><span class="chip chip-teal">手动录入</span></td><td>从客户收取的马币金额。</td></tr>
    <tr><td>I</td><td>备注</td><td><span class="chip chip-dark">手动录入</span></td><td>自由填写的文本说明。</td></tr>
    <tr><td>K</td><td>成本价</td><td><span class="chip chip-gray">手动录入</span></td><td>买入此货币时的原始成本率。</td></tr>
    <tr><td>L</td><td>利润</td><td><span class="chip chip-green">自动计算</span></td><td>C × (K − E)。该笔交易的利润。</td></tr>
    <tr><td>N</td><td>日期 (安排)</td><td><span class="chip chip-orange">结算安排</span></td><td>计划转账的日期。</td></tr>
    <tr><td>O</td><td>账号</td><td><span class="chip chip-orange">结算安排</span></td><td>收款银行账号。</td></tr>
    <tr><td>P</td><td>姓名</td><td><span class="chip chip-orange">结算安排</span></td><td>收款人姓名。</td></tr>
    <tr><td>Q</td><td>银行</td><td><span class="chip chip-orange">结算安排</span></td><td>收款银行名称。</td></tr>
    <tr><td>R</td><td>安排金额</td><td><span class="chip chip-orange">结算安排</span></td><td>计划转出的马币数。</td></tr>
    <tr><td>S</td><td>已办金额</td><td><span class="chip chip-orange">结算安排</span></td><td>已经实际转出的马币数。</td></tr>
    <tr><td>T</td><td>已办</td><td><span class="chip chip-orange">结算安排</span></td><td>是/否 — 是否完成结算？</td></tr>
    <tr><td>U</td><td>经办人</td><td><span class="chip chip-orange">结算安排</span></td><td>处理此笔转账的员工。</td></tr>
</table>

<h2 id="glossary">术语表</h2>

<table class="doc-table">
    <tr><th>术语</th><th>含义</th></tr>
    <tr><td>买入 (BUY IN)</td><td>业务从客户处购买外币（客户卖给公司）。</td></tr>
    <tr><td>卖出 (SELL OUT)</td><td>业务向客户出售外币（客户从公司购买）。</td></tr>
    <tr><td>汇率 (E)</td><td>交易当天的成交价格。</td></tr>
    <tr><td>成本价 (K)</td><td>业务获取外币的底价 — 用于计算利润空间。</td></tr>
    <tr><td>折算马币 (F)</td><td>本次交易中外币净头寸对应的马币价值。</td></tr>
    <tr><td>利润 (L)</td><td>成交价与成本价之间的差价利润（或亏损）。</td></tr>
    <tr><td>余额 (BALANCE)</td><td>累计欠客户或客户欠公司的马币总数。</td></tr>
    <tr><td>总应付 (NEEDPAY)</td><td>所有客户余额的总和 — 公司需要准备支付给所有客户的总资金。</td></tr>
    <tr><td>结算安排 (Arrangement)</td><td>为了清算客户余额而计划的银行转账。</td></tr>
    <tr><td>待结算马币</td><td>安排的总额减去实际已付的总额。</td></tr>
    <tr><td>当日统计</td><td>控制合计行只显示当天业务的切换模式。</td></tr>
    <tr><td>总表 (总表)</td><td>月度汇总表，展示每天的整体情况及所有客户摘要。</td></tr>
    <tr><td>总表详情 (总表详情)</td><td>以矩阵形式展示当月每天客户的活跃度与利润情况。</td></tr>
</table>

<hr style="margin:40px 0;border:none;border-top:1px solid #eee">
<p style="font-size:12px;color:#aaa;text-align:center">
    FX 后台管理系统 — 用户指南 &nbsp;|&nbsp; 生成日期 {{ date('Y年m月d日') }}
    &nbsp;|&nbsp; <a href="#overview" style="color:#aaa">回到顶部 ↑</a>
    &nbsp;|&nbsp; <a href="javascript:window.print()" style="color:#aaa">🖨 打印文档</a>
</p>

</main>
</div>

<script>
// 滚动时激活侧边栏链接
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