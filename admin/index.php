<?php
include './includes/auth.php';
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard – Virtue & Verve</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="./css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

  <div class="cursor" id="cursor"></div>
  <div class="cursor-ring" id="cursorRing"></div>
  <div class="overlay" id="overlay"></div>

  <!-- ── SIDEBAR ── -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <a class="logo-mark" href="index.html">Virtue &amp; <span>Verve</span></a>
      <div class="logo-sub">Admin Dashboard</div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-label">Overview</div>
      <a class="nav-item active" href="./index.html"><span class="nav-icon"><i class="fa-solid fa-grip"></i></span><span class="nav-label">Dashboard</span></a>
      <a class="nav-item" href="./categories.php"><span class="nav-icon"><i class="fa-solid fa-layer-group"></i></span><span class="nav-label">Categories</span></a>
      <a class="nav-item" href="./admin-products.html"><span class="nav-icon"><i class="fa-solid fa-box"></i></span><span class="nav-label">Products</span></a>
      <a class="nav-item" href="./admin-orders.html"><span class="nav-icon"><i class="fa-solid fa-cart-shopping"></i></span><span class="nav-label">Orders</span><span class="nav-badge red">12</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-people-group"></i></span><span class="nav-label">Customers</span></a>
      
    </div>

    <!-- <div class="sidebar-section">
      <div class="sidebar-section-label">Finance</div>
      <a class="nav-item" href="#"><span class="nav-icon">💰</span><span class="nav-label">Revenue</span></a>
      <a class="nav-item" href="#"><span class="nav-icon">🧾</span><span class="nav-label">Invoices</span><span class="nav-badge">3</span></a>
      <a class="nav-item" href="#"><span class="nav-icon">🏷️</span><span class="nav-label">Discounts</span></a>
    </div> -->

    <div class="sidebar-section">
      <div class="sidebar-section-label">Store</div>
      <a class="nav-item" href="shop.html" target="_blank"><span class="nav-icon"><i class="fa-solid fa-store"></i></span><span class="nav-label">View Store</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-star"></i></span><span class="nav-label">Reviews</span><span class="nav-badge">7</span></a>
      <a class="nav-item" href="#"><span class="nav-icon"><i class="fa-solid fa-gear"></i></span><span class="nav-label">Settings</span></a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile" onclick="showToast('Profile settings coming soon!')">
        <div class="admin-avatar">VV</div>
        <div>
          <div class="admin-name">Virtue &amp; Verve</div>
          <div class="admin-role">Store Administrator</div>
        </div>
        <div class="admin-more">⋯</div>
      </div>
    </div>
  </aside>

  <!-- ── MAIN ── -->
  <div class="main">

    <!-- TOP BAR -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="menu-toggle" id="menuToggle">☰</button>
        <h1 class="page-heading">Dashboard <span>Overview</span></h1>
      </div>
      <div class="topbar-right">
        <div class="topbar-date" id="topbarDate"></div>
        <div class="search-bar">
          <span style="color:var(--light);font-size:0.9rem"><i class="fa-solid fa-magnifying-glass"></i></span>
          <input type="text" placeholder="Search orders, products…"/>
        </div>
        <div class="icon-btn" id="notifBtn" title="Notifications">
          <i class="fa-solid fa-bell"></i> <span class="notif-dot"></span>
        </div>
        <div class="icon-btn" title="Export" onclick="showToast('Export report as CSV…')"><i class="fa-solid fa-file-csv"></i></div>
      </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

      <!-- WELCOME BANNER -->
      <div class="welcome-banner">
        <div class="banner-orb borb1"></div>
        <div class="banner-orb borb2"></div>
        <div class="banner-orb borb3"></div>
        <div class="banner-text">
          <div class="banner-greeting">Thursday, 14 May 2026</div>
          <h2 class="banner-title">Good morning, <em>Verve Admin</em> </h2>
          <p class="banner-sub">Your store had a great week — revenue is up 18% from last week!</p>
          <div class="banner-badge"><span class="dot"></span> Store is Live & Running</div>
        </div>
        <div class="banner-right">
          <div class="banner-stat">
            <div class="banner-stat-num">₦200,000</div>
            <div class="banner-stat-label">Today's Revenue</div>
          </div>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="kpi-grid">
        <div class="kpi-card" style="--accent:var(--primary);animation-delay:0.1s">
          <div class="kpi-head">
            <div class="kpi-icon" style="background:var(--primary-faint)"><i style="color: #17988e;" class="fa-solid fa-dollar-sign"></i></div>
            <div class="kpi-trend up">↑ 18%</div>
          </div>
          <div class="kpi-value" data-format="currency">₦300,000</div>
          <div class="kpi-label">Total Revenue</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" data-width="78%"></div></div>
          <div class="kpi-sub">₦2.4M target this month</div>
        </div>
        <div class="kpi-card" style="--accent:var(--gold);animation-delay:0.2s">
          <div class="kpi-head">
            <div class="kpi-icon" style="background:var(--gold-light)"><i style="color: #aa9b80;" class="fa-solid fa-box"></i></div>
            <div class="kpi-trend up">↑ 24%</div>
          </div>
          <div class="kpi-value" data-target="342" data-prefix="">0</div>
          <div class="kpi-label">Total Orders</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" data-width="68%" style="background:var(--gold)"></div></div>
          <div class="kpi-sub">12 pending fulfillment</div>
        </div>
        <div class="kpi-card" style="--accent:#e07b3d;animation-delay:0.3s">
          <div class="kpi-head">
            <div class="kpi-icon" style="background:#fff3e8"><i style="color: #aa9b80;" class="fa-solid fa-people-group"></i></div>
            <div class="kpi-trend up">↑ 11%</div>
          </div>
          <div class="kpi-value" data-target="1204" data-prefix="">0</div>
          <div class="kpi-label">Registered Customers</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" data-width="55%" style="background:#e07b3d"></div></div>
          <div class="kpi-sub">38 joined this week</div>
        </div>
        <div class="kpi-card" style="--accent:var(--danger);animation-delay:0.4s">
          <div class="kpi-head">
            <div class="kpi-icon" style="background:var(--danger-light)"><i style="color: #a59898;" class="fa-solid fa-cart-shopping"></i></div>
            <div class="kpi-trend down">↓ 3%</div>
          </div>
          <div class="kpi-value" data-target="8.4" data-prefix="" data-suffix="%">0</div>
          <div class="kpi-label">Cart Abandonment</div>
          <div class="kpi-bar"><div class="kpi-bar-fill" data-width="22%" style="background:var(--danger)"></div></div>
          <div class="kpi-sub">Industry avg. 12% — great job!</div>
        </div>
      </div>

      <!-- CHARTS ROW -->
      <div class="charts-row">

        <!-- Revenue Chart -->
        <div class="card" style="animation-delay:0.35s">
          <div class="card-head">
            <div class="card-title">Revenue <em>Trend</em></div>
            <div class="card-actions">
              <button class="tab-btn active" onclick="setChartPeriod('week',this)">Week</button>
              <button class="tab-btn" onclick="setChartPeriod('month',this)">Month</button>
              <button class="tab-btn" onclick="setChartPeriod('year',this)">Year</button>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-wrap" style="position:relative">
              <div class="chart-tooltip" id="chartTooltip"></div>
              <svg class="chart-svg" viewBox="0 0 560 220" id="revenueChart">
                <defs>
                  <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#088178" stop-opacity="0.25"/>
                    <stop offset="100%" stop-color="#088178" stop-opacity="0"/>
                  </linearGradient>
                </defs>
                <!-- Grid lines -->
                <line class="chart-grid" x1="0" y1="40" x2="560" y2="40"/>
                <line class="chart-grid" x1="0" y1="90" x2="560" y2="90"/>
                <line class="chart-grid" x1="0" y1="140" x2="560" y2="140"/>
                <line class="chart-grid" x1="0" y1="190" x2="560" y2="190"/>
                <!-- Y labels -->
                <text class="chart-label" x="0" y="38">₦400k</text>
                <text class="chart-label" x="0" y="88">₦300k</text>
                <text class="chart-label" x="0" y="138">₦200k</text>
                <text class="chart-label" x="0" y="188">₦100k</text>
                <!-- Chart path (week data) -->
                <path id="chartArea" class="chart-area" d=""/>
                <path id="chartLine" class="chart-line" d=""/>
                <!-- Dots -->
                <g id="chartDots"></g>
                <!-- X labels -->
                <g id="chartXLabels"></g>
              </svg>
            </div>
            <div style="display:flex;gap:24px;margin-top:12px">
              <div><div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--dark)">₦1.85M</div><div style="font-size:0.68rem;color:var(--light);text-transform:uppercase;letter-spacing:1px">This Period</div></div>
              <div><div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--primary)">+18%</div><div style="font-size:0.68rem;color:var(--light);text-transform:uppercase;letter-spacing:1px">vs Last Period</div></div>
              <div><div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--gold)">₦5,400</div><div style="font-size:0.68rem;color:var(--light);text-transform:uppercase;letter-spacing:1px">Avg Order Value</div></div>
            </div>
          </div>
        </div>

        <!-- Donut Chart -->
        <div class="card" style="animation-delay:0.45s">
          <div class="card-head">
            <div class="card-title">Sales by <em>Category</em></div>
          </div>
          <div class="card-body">
            <div class="donut-wrap">
              <div class="donut-svg-wrap">
                <svg class="donut-svg" width="140" height="140" viewBox="0 0 140 140">
                  <circle cx="70" cy="70" r="54" fill="none" stroke="var(--border)" stroke-width="16"/>
                  <!-- Segments: total circumference = 2π×54 ≈ 339.3 -->
                  <circle id="seg1" cx="70" cy="70" r="54" fill="none" stroke="#088178" stroke-width="16" stroke-dasharray="0 339.3" stroke-dashoffset="0" stroke-linecap="butt" transform="rotate(-90 70 70)" style="transition:stroke-dasharray 1.2s cubic-bezier(0.25,0.46,0.45,0.94)"/>
                  <circle id="seg2" cx="70" cy="70" r="54" fill="none" stroke="#c9a96e" stroke-width="16" stroke-dasharray="0 339.3" stroke-dashoffset="0" stroke-linecap="butt" transform="rotate(-90 70 70)" style="transition:stroke-dasharray 1.2s 0.15s cubic-bezier(0.25,0.46,0.45,0.94)"/>
                  <circle id="seg3" cx="70" cy="70" r="54" fill="none" stroke="#e07b3d" stroke-width="16" stroke-dasharray="0 339.3" stroke-dashoffset="0" stroke-linecap="butt" transform="rotate(-90 70 70)" style="transition:stroke-dasharray 1.2s 0.3s cubic-bezier(0.25,0.46,0.45,0.94)"/>
                  <circle id="seg4" cx="70" cy="70" r="54" fill="none" stroke="#0aac9f" stroke-width="16" stroke-dasharray="0 339.3" stroke-dashoffset="0" stroke-linecap="butt" transform="rotate(-90 70 70)" style="transition:stroke-dasharray 1.2s 0.45s cubic-bezier(0.25,0.46,0.45,0.94)"/>
                  <circle id="seg5" cx="70" cy="70" r="54" fill="none" stroke="#e05252" stroke-width="16" stroke-dasharray="0 339.3" stroke-dashoffset="0" stroke-linecap="butt" transform="rotate(-90 70 70)" style="transition:stroke-dasharray 1.2s 0.6s cubic-bezier(0.25,0.46,0.45,0.94)"/>
                </svg>
                <div class="donut-center">
                  <div class="donut-pct">342</div>
                  <div class="donut-pct-label">orders</div>
                </div>
              </div>
              <div class="donut-legend">
                <div class="legend-item"><div class="legend-dot" style="background:#088178"></div><span class="legend-name">Handbags</span><span class="legend-val">38%</span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#c9a96e"></div><span class="legend-name">Tote Bags</span><span class="legend-val">24%</span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#e07b3d"></div><span class="legend-name">Clutches</span><span class="legend-val">18%</span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#0aac9f"></div><span class="legend-name">Backpacks</span><span class="legend-val">12%</span></div>
                <div class="legend-item"><div class="legend-dot" style="background:#e05252"></div><span class="legend-name">Crossbody</span><span class="legend-val">8%</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTTOM GRID: Orders + Top Products -->
      <div class="bottom-grid">

        <!-- Recent Orders -->
        <div class="card" style="animation-delay:0.5s">
          <div class="card-head">
            <div class="card-title">Recent <em>Orders</em></div>
            <button class="tab-btn active" onclick="showToast('Viewing all orders…')">View All →</button>
          </div>
          <div class="card-body" style="padding-top:14px;overflow-x:auto">
            <table class="orders-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Product</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="ordersBody"></tbody>
            </table>
          </div>
        </div>

        <!-- Top Products -->
        <div class="card" style="animation-delay:0.55s">
          <div class="card-head">
            <div class="card-title">Top <em>Products</em></div>
          </div>
          <div class="card-body" style="padding-top:8px">
            <div class="product-list" id="productList"></div>
          </div>
        </div>
      </div>

      <!-- LAST ROW: Activity + Customers -->
      <div class="last-row">

        <!-- Activity Feed -->
        <div class="card" style="animation-delay:0.6s">
          <div class="card-head">
            <div class="card-title">Live <em>Activity</em></div>
            <span style="font-size:0.72rem;color:var(--primary);display:flex;align-items:center;gap:5px"><span style="width:6px;height:6px;background:var(--primary);border-radius:50%;display:inline-block;animation:pulse 1.4s infinite"></span> Real-time</span>
          </div>
          <div class="card-body" style="padding-top:8px">
            <div class="activity-feed" id="activityFeed"></div>
          </div>
        </div>

        <!-- Top Customers -->
        <div class="card" style="animation-delay:0.65s">
          <div class="card-head">
            <div class="card-title">Top <em>Customers</em></div>
          </div>
          <div class="card-body" style="padding-top:8px">
            <div class="customer-list" id="customerList"></div>
          </div>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->

  <!-- NOTIFICATION PANEL -->
  <div class="notif-panel" id="notifPanel">
    <div class="notif-panel-head">
      <h2 class="notif-panel-title">Notifications</h2>
      <button class="notif-close" id="notifClose">✕</button>
    </div>
    <div id="notifList"></div>
  </div>

  <!-- TOAST -->
  <div class="toast" id="toast"><span>✨</span><span id="toastMsg">Done!</span></div>

  <script>
  // ── DATA ────────────────────────────────────────────────────────────
  const orders = [
    { id:'#VV-1042', name:'Adaeze Okafor', avatar:'👩🏾', product:'Milano Structured Bag', amount:'₦45,000', status:'delivered',  date:'May 13' },
    { id:'#VV-1041', name:'Fatima Aliyu',  avatar:'👩🏽', product:'Velvet Night Clutch',   amount:'₦18,000', status:'processing', date:'May 13' },
    { id:'#VV-1040', name:'Blessing Eze',  avatar:'👩🏿', product:'Cognac Leather Satchel',amount:'₦62,000', status:'delivered',  date:'May 12' },
    { id:'#VV-1039', name:'Chisom Nwosu',  avatar:'👩🏾', product:'Canvas Weekend Tote',   amount:'₦16,500', status:'pending',    date:'May 12' },
    { id:'#VV-1038', name:'Amaka Dike',    avatar:'👩🏽', product:'Chain Crossbody',        amount:'₦27,000', status:'processing', date:'May 11' },
    { id:'#VV-1037', name:'Ngozi Eze',     avatar:'👩🏿', product:'Luxury Croc Tote',       amount:'₦75,000', status:'delivered',  date:'May 11' },
    { id:'#VV-1036', name:'Sade Okonkwo', avatar:'👩🏾', product:'Urban Backpack',          amount:'₦29,000', status:'cancelled',  date:'May 10' },
  ];

  const topProducts = [
    { name:'Milano Structured Bag', cat:'Handbag',  revenue:'₦270,000', units:6,  img:'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=100&q=60' },
    { name:'Cognac Leather Satchel',cat:'Handbag',  revenue:'₦186,000', units:3,  img:'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=100&q=60' },
    { name:'Luxury Croc Tote',      cat:'Tote Bag', revenue:'₦150,000', units:2,  img:'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=100&q=60' },
    { name:'Velvet Night Clutch',   cat:'Clutch',   revenue:'₦108,000', units:6,  img:'https://images.unsplash.com/photo-1575032617751-6ddec2089882?w=100&q=60' },
    { name:'Canvas Weekend Tote',   cat:'Tote Bag', revenue:'₦99,000',  units:6,  img:'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=100&q=60' },
  ];

  const activities = [
    { icon:'🛍️', bg:'var(--primary-faint)', msg:'<strong>Adaeze Okafor</strong> placed a new order — Milano Structured Bag', time:'2 minutes ago' },
    { icon:'👤', bg:'#f0f7ff',              msg:'<strong>Temi Adeola</strong> registered a new account', time:'14 minutes ago' },
    { icon:'⭐', bg:'var(--gold-light)',     msg:'<strong>Ngozi Eze</strong> left a 5-star review on Luxury Croc Tote', time:'31 minutes ago' },
    { icon:'📦', bg:'var(--success-light)', msg:'Order <strong>#VV-1040</strong> was marked as delivered', time:'1 hour ago' },
    { icon:'🏷️', bg:'#fff3e8',              msg:'Promo code <strong>VERVE10</strong> used — 10% discount applied', time:'2 hours ago' },
    { icon:'❌', bg:'var(--danger-light)',   msg:'Order <strong>#VV-1036</strong> was cancelled by customer', time:'4 hours ago' },
  ];

  const customers = [
    { avatar:'👩🏿', name:'Ngozi Eze',     email:'ngozi@email.com',  spent:'₦292,000', orders:6, star:true },
    { avatar:'👩🏾', name:'Adaeze Okafor', email:'ada@email.com',    spent:'₦225,000', orders:8, star:true },
    { avatar:'👩🏽', name:'Fatima Aliyu',  email:'fatima@email.com', spent:'₦144,000', orders:5, star:false },
    { avatar:'👩🏾', name:'Chisom Nwosu',  email:'chisom@email.com', spent:'₦115,500', orders:4, star:false },
    { avatar:'👩🏽', name:'Amaka Dike',    email:'amaka@email.com',  spent:'₦94,000',  orders:3, star:false },
  ];

  const notifications = [
    { icon:'📦', bg:'var(--primary-faint)', msg:'<strong>12 orders</strong> are pending fulfilment', time:'Just now', unread:true },
    { icon:'⚠️', bg:'var(--warning-light)', msg:'Stock low: <strong>Velvet Night Clutch</strong> (2 left)', time:'20 min ago', unread:true },
    { icon:'💰', bg:'var(--success-light)', msg:'Payment of <strong>₦75,000</strong> confirmed for #VV-1037', time:'1 hr ago', unread:true },
    { icon:'👤', bg:'#f0f7ff',              msg:'<strong>5 new customers</strong> joined today', time:'3 hrs ago', unread:false },
    { icon:'⭐', bg:'var(--gold-light)',     msg:'New 5-star review from Ngozi Eze', time:'5 hrs ago', unread:false },
    { icon:'🏷️', bg:'#fff3e8',              msg:'Promo code VERVE10 has been used 24 times', time:'Yesterday', unread:false },
  ];

  // ── RENDER ───────────────────────────────────────────────────────────
  function renderOrders() {
    document.getElementById('ordersBody').innerHTML = orders.map((o, i) => `
      <tr style="animation-delay:${i * 0.06}s">
        <td class="order-id">${o.id}</td>
        <td><div class="customer-cell"><div class="cust-avatar">${o.avatar}</div><span class="cust-name">${o.name}</span></div></td>
        <td>${o.product}</td>
        <td class="order-amount">${o.amount}</td>
        <td><span class="status-pill ${o.status}"><span class="sdot"></span>${o.status.charAt(0).toUpperCase()+o.status.slice(1)}</span></td>
        <td>${o.date}</td>
        <td><button class="action-link" onclick="showToast('Viewing order ${o.id}…')">View</button></td>
      </tr>
    `).join('');
  }

  function renderTopProducts() {
    document.getElementById('productList').innerHTML = topProducts.map((p, i) => `
      <div class="product-row" style="animation-delay:${i*0.07}s" onclick="showToast('Viewing ${p.name}…')">
        <div class="prod-rank">${i+1}</div>
        <div class="prod-img"><img src="${p.img}" alt="${p.name}" loading="lazy"/></div>
        <div class="prod-info">
          <div class="prod-name">${p.name}</div>
          <div class="prod-cat">${p.cat}</div>
        </div>
        <div class="prod-stats">
          <div class="prod-revenue">${p.revenue}</div>
          <div class="prod-units">${p.units} sold</div>
        </div>
      </div>
    `).join('');
  }

  function renderActivity() {
    document.getElementById('activityFeed').innerHTML = activities.map((a, i) => `
      <div class="activity-item" style="animation-delay:${i*0.07}s">
        <div class="activity-dot-wrap">
          <div class="activity-dot" style="background:${a.bg}">${a.icon}</div>
          ${i < activities.length - 1 ? '<div class="activity-line"></div>' : ''}
        </div>
        <div class="activity-content">
          <div class="activity-msg">${a.msg}</div>
          <div class="activity-time">${a.time}</div>
        </div>
      </div>
    `).join('');
  }

  function renderCustomers() {
    document.getElementById('customerList').innerHTML = customers.map((c, i) => `
      <div class="customer-row" style="animation-delay:${i*0.07}s">
        <div class="cust-big-avatar">${c.avatar}</div>
        <div class="cust-details">
          <div class="cust-full-name">${c.name} ${c.star ? '<span class="cust-star">★</span>' : ''}</div>
          <div class="cust-email">${c.email}</div>
        </div>
        <div class="cust-spent">
          <div class="cust-spent-val">${c.spent}</div>
          <div class="cust-orders-num">${c.orders} orders</div>
        </div>
      </div>
    `).join('');
  }

  function renderNotifications() {
    document.getElementById('notifList').innerHTML = notifications.map(n => `
      <div class="notif-item ${n.unread ? 'notif-unread' : ''}">
        <div class="notif-icon-wrap" style="background:${n.bg}">${n.icon}</div>
        <div>
          <div class="notif-msg">${n.msg}</div>
          <div class="notif-time">${n.time}</div>
        </div>
      </div>
    `).join('');
  }

  // ── DATE ─────────────────────────────────────────────────────────────
  const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const now    = new Date();
  document.getElementById('topbarDate').textContent = `${days[now.getDay()]}, ${months[now.getMonth()]} ${now.getDate()}, ${now.getFullYear()}`;

  // ── COUNT-UP ANIMATION ───────────────────────────────────────────────
  function countUp(el, target, prefix, suffix, format) {
    const duration = 1600;
    const start = performance.now();
    const step = ts => {
      const prog = Math.min((ts - start) / duration, 1);
      const ease = 1 - Math.pow(1 - prog, 3);
      let val = target * ease;
      let display = format === 'currency'
        ? '₦' + Math.round(val).toLocaleString('en-NG')
        : (suffix ? val.toFixed(1) + suffix : Math.round(val).toString());
      el.textContent = (prefix || '') + display;
      if (prog < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  // Animate today's revenue banner
  setTimeout(() => {
    const el = document.getElementById('todayRevNum');
    countUp(el, 247500, '₦', '', 'currency');
  }, 400);

  // ── KPI BAR FILLS ────────────────────────────────────────────────────
  const barObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.querySelectorAll('.kpi-bar-fill').forEach(b => { b.style.width = b.dataset.width; });
        e.target.querySelectorAll('[data-target]').forEach(el => {
          const t = parseFloat(el.dataset.target);
          countUp(el, t, el.dataset.prefix || '', el.dataset.suffix || '', el.dataset.format || '');
        });
        barObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('.kpi-card').forEach(c => barObs.observe(c));

  // ── DONUT CHART ──────────────────────────────────────────────────────
  const C = 2 * Math.PI * 54; // ≈ 339.3
  const segments = [0.38, 0.24, 0.18, 0.12, 0.08];
  const segIds   = ['seg1','seg2','seg3','seg4','seg5'];
  let offset = 0;
  const donutObs = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
      segments.forEach((pct, i) => {
        const el = document.getElementById(segIds[i]);
        const len = pct * C;
        el.style.strokeDasharray = `${len} ${C - len}`;
        el.style.strokeDashoffset = -offset;
        offset += len;
      });
      donutObs.unobserve(entries[0].target);
    }
  }, { threshold: 0.4 });
  const donutEl = document.querySelector('.donut-svg-wrap');
  if (donutEl) donutObs.observe(donutEl);

  // ── REVENUE CHART ────────────────────────────────────────────────────
  const chartData = {
    week:  { labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], values:[180000,240000,195000,310000,280000,380000,265000] },
    month: { labels:['W1','W2','W3','W4'], values:[520000,680000,590000,760000] },
    year:  { labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], values:[620000,540000,780000,710000,890000,760000,840000,920000,800000,950000,1100000,980000] }
  };

  function drawChart(period) {
    const { labels, values } = chartData[period];
    const svgW = 560, svgH = 220, padL = 44, padB = 24, padT = 16, padR = 10;
    const w = svgW - padL - padR;
    const h = svgH - padB - padT;
    const max = Math.max(...values) * 1.1;
    const pts = values.map((v, i) => ({
      x: padL + (i / (values.length - 1)) * w,
      y: padT + h - (v / max) * h
    }));
    const linePath = pts.map((p,i) => (i===0?'M':'L') + p.x.toFixed(1)+','+p.y.toFixed(1)).join(' ');
    const areaPath = linePath + ` L${pts[pts.length-1].x.toFixed(1)},${(padT+h).toFixed(1)} L${padL},${(padT+h).toFixed(1)} Z`;

    document.getElementById('chartLine').setAttribute('d', linePath);
    document.getElementById('chartArea').setAttribute('d', areaPath);

    const dotsG = document.getElementById('chartDots');
    dotsG.innerHTML = pts.map((p, i) => `
      <circle class="chart-dot" cx="${p.x.toFixed(1)}" cy="${p.y.toFixed(1)}" r="4"
        data-val="${values[i]}" data-label="${labels[i]}"
        onmouseenter="showChartTip(event,this)"
        onmouseleave="document.getElementById('chartTooltip').style.opacity=0"/>
    `).join('');

    const xlG = document.getElementById('chartXLabels');
    xlG.innerHTML = pts.map((p, i) => `<text class="chart-label" x="${p.x.toFixed(1)}" y="${(svgH-4).toFixed(1)}" text-anchor="middle">${labels[i]}</text>`).join('');

    // Animate line drawing
    const line = document.getElementById('chartLine');
    const len = line.getTotalLength?.() || 600;
    line.style.strokeDasharray = len;
    line.style.strokeDashoffset = len;
    line.style.transition = 'none';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        line.style.transition = 'stroke-dashoffset 1.4s cubic-bezier(0.25,0.46,0.45,0.94)';
        line.style.strokeDashoffset = 0;
      });
    });
  }

  function showChartTip(e, el) {
    const tip = document.getElementById('chartTooltip');
    const val = parseInt(el.dataset.val);
    tip.textContent = `${el.dataset.label}: ₦${val.toLocaleString('en-NG')}`;
    tip.style.opacity = 1;
    const rect = el.closest('.chart-wrap').getBoundingClientRect();
    const er = e.clientX - rect.left;
    const et = e.clientY - rect.top;
    tip.style.left = (er + 12) + 'px';
    tip.style.top  = (et - 30) + 'px';
  }

  function setChartPeriod(p, btn) {
    document.querySelectorAll('.card-actions .tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    drawChart(p);
  }

  // Init chart after paint
  setTimeout(() => drawChart('week'), 300);

  // ── NOTIFICATIONS ────────────────────────────────────────────────────
  document.getElementById('notifBtn').addEventListener('click', () => {
    document.getElementById('notifPanel').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
  });
  document.getElementById('notifClose').addEventListener('click', () => {
    document.getElementById('notifPanel').classList.remove('open');
    document.getElementById('overlay').classList.remove('visible');
  });
  document.getElementById('overlay').addEventListener('click', () => {
    document.getElementById('notifPanel').classList.remove('open');
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('visible');
  });

  // ── MOBILE SIDEBAR ───────────────────────────────────────────────────
  document.getElementById('menuToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
  });



  // ── CURSOR ───────────────────────────────────────────────────────────
  const cursor = document.getElementById('cursor');
  const ring   = document.getElementById('cursorRing');
  document.addEventListener('mousemove', e => {
    cursor.style.left = e.clientX + 'px'; cursor.style.top = e.clientY + 'px';
    setTimeout(() => { ring.style.left = e.clientX + 'px'; ring.style.top = e.clientY + 'px'; }, 80);
  });
  document.querySelectorAll('a, button, [onclick], .nav-item, .product-row, .kpi-card').forEach(el => {
    el.addEventListener('mouseenter', () => { cursor.style.transform='translate(-50%,-50%) scale(1.8)'; cursor.style.background='var(--gold)'; });
    el.addEventListener('mouseleave', () => { cursor.style.transform='translate(-50%,-50%) scale(1)'; cursor.style.background='var(--primary)'; });
  });

  // ── TOAST ────────────────────────────────────────────────────────────
  function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => t.classList.remove('show'), 2800);
  }

  // ── LIVE ACTIVITY TICKER ─────────────────────────────────────────────
  const liveEvents = [
    { icon:'🛍️', bg:'var(--primary-faint)', msg:'<strong>New order</strong> #VV-1043 placed — ₦33,000', time:'Just now' },
    { icon:'👤', bg:'#f0f7ff',              msg:'<strong>New customer</strong> Zara Uche registered', time:'Just now' },
    { icon:'💳', bg:'var(--success-light)', msg:'Payment confirmed for <strong>#VV-1042</strong>', time:'Just now' },
  ];
  let liveIdx = 0;
  setInterval(() => {
    const item = { ...liveEvents[liveIdx % liveEvents.length], time: 'Just now' };
    activities.unshift(item);
    activities.pop();
    renderActivity();
    liveIdx++;
  }, 12000);

  // ── INIT ─────────────────────────────────────────────────────────────
  renderOrders();
  renderTopProducts();
  renderActivity();
  renderCustomers();
  renderNotifications();
  </script>
</body>
</html>
