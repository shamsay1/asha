/* ============================================================
   LAND VALUATION MANAGEMENT SYSTEM - SHARED JS
   ============================================================ */

const API = 'php/';

/* ---------------- API HELPERS ---------------- */
async function apiGet(endpoint) {
  const res = await fetch(API + endpoint, { credentials: 'same-origin' });
  return res.json();
}

async function apiPost(endpoint, data) {
  const res = await fetch(API + endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(data || {})
  });
  return res.json();
}

/* ---------------- FORMATTERS ---------------- */
function fmt(n) {
  return 'TZS ' + Math.round(parseFloat(n) || 0).toLocaleString();
}

function initials(name) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
}

function roleLabel(r) {
  return r ? r.charAt(0).toUpperCase() + r.slice(1) : '';
}

function escape(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));
}

function methodLabel(m) {
  return ({
    mpesa:       'M-Pesa',
    tigopesa:    'Tigo Pesa',
    airtelmoney: 'Airtel Money',
    bank:        'Bank Transfer',
    card:        'Credit / Debit Card'
  })[m] || m;
}

function landIcon(type) {
  return type === 'commercial' ? '🏢'
       : type === 'agricultural' ? '🌾'
       : '🏠';
}

function statusText(status) {
  return ({
    pending: 'Awaiting Valuation',
    valued:  'Ready for Sale',
    sold:    'Sold',
    rejected: 'Rejected'
  })[status] || status;
}

/* ---------------- LABELS for valuation ---------------- */
const ROAD_LABEL  = { main: 'Main road frontage', side: 'Side road access', interior: 'Interior (no road frontage)' };
const DIST_LABEL  = { near: 'Near city center (< 2 km)', moderate: 'Moderate (2–10 km)', far: 'Far (> 10 km)' };
const SOIL_LABEL  = { fertile: 'Fertile', moderate: 'Moderate', poor: 'Poor' };
const WATER_LABEL = { river: 'River / lake nearby', well: 'Well / borehole', none: 'No water source' };

/* ---------------- AUTH GUARD ---------------- */
async function requireAuth(allowedRoles) {
  const data = await apiGet('get_current_user.php');
  if (!data.success) {
    window.location.href = 'index.html';
    return null;
  }
  if (allowedRoles && !allowedRoles.includes(data.user.role)) {
    alert('Access denied for your role.');
    window.location.href = 'dashboard.html';
    return null;
  }
  return data.user;
}

/* ---------------- LAYOUT (sidebar + topbar) ---------------- */
const NAV = {
  seller: [
    { page: 'dashboard.html', label: 'Dashboard',  icon: '📊' },
    { page: 'add_land.html',  label: 'Add Land',   icon: '➕' },
    { page: 'land_list.html', label: 'My Lands',   icon: '🗺️' },
    { page: 'reports.html',   label: 'Reports',    icon: '📈' }
  ],
  buyer: [
    { page: 'dashboard.html', label: 'Dashboard',     icon: '📊' },
    { page: 'land_list.html', label: 'Browse Lands',  icon: '🔎' },
    { page: 'payment.html',   label: 'My Purchases',  icon: '💳' }
  ],
  authority: [
    { page: 'dashboard.html', label: 'Dashboard',          icon: '📊' },
    { page: 'land_list.html', label: 'All Lands',          icon: '🗺️' },
    { page: 'valuation.html', label: 'Pending Valuations', icon: '⚖️' },
    { page: 'reports.html',   label: 'Reports',            icon: '📈' }
  ],
  admin: [
    { page: 'dashboard.html', label: 'Dashboard',     icon: '📊' },
    { page: 'users.html',     label: 'Manage Users',  icon: '👥' },
    { page: 'land_list.html', label: 'All Lands',     icon: '🗺️' },
    { page: 'payment.html',   label: 'Transactions',  icon: '💳' },
    { page: 'reports.html',   label: 'Reports',       icon: '📈' }
  ]
};

function renderLayout(user, currentPage, pageTitle) {
  const navItems = (NAV[user.role] || []).map(n => `
    <a href="${n.page}" class="nav-item ${currentPage === n.page ? 'active' : ''}">
      <span class="nav-icon">${n.icon}</span> ${n.label}
    </a>
  `).join('');

  return `
    <div class="app-layout">
      <aside class="sidebar">
        <div class="sidebar-brand">
          <h2>🏞️ LVMS</h2>
          <p>Land Valuation System</p>
        </div>
        ${navItems}
        <a href="#" class="nav-item" id="logoutBtn" style="margin-top:20px;">
          <span class="nav-icon">🚪</span> Logout
        </a>
      </aside>
      <header class="topbar">
        <h1>${escape(pageTitle)}</h1>
        <div class="user-chip">
          <div class="user-info" style="text-align:right;">
            <div class="name">${escape(user.name)}</div>
            <div class="role">${roleLabel(user.role)}</div>
          </div>
          <div class="user-avatar">${initials(user.name)}</div>
        </div>
      </header>
      <main class="main" id="pageContent"></main>
    </div>
  `;
}

function attachLogoutHandler() {
  const btn = document.getElementById('logoutBtn');
  if (btn) btn.addEventListener('click', async (e) => {
    e.preventDefault();
    await apiGet('logout.php');
    window.location.href = 'index.html';
  });
}

/* ---------------- ALERTS ---------------- */
function showAlert(container, type, message) {
  const div = document.createElement('div');
  div.className = `alert alert-${type}`;
  div.textContent = message;
  container.prepend(div);
  setTimeout(() => div.remove(), 4000);
}
