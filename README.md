# Land Valuation Management System (LVMS)

**Student:** Asha Othman Khamis
**Registration:** KIST/ICT/24/0095
**Supervisor:** Mr. Mudathir Salum Ali
**Course:** Information Communication and Technology with Business (ICT)
**Institute:** Karume Institute of Science and Technology

A web-based Land Valuation Management Information System built with HTML, CSS, JavaScript, PHP, and MySQL.

---

## 📁 Project Structure

```
lvms/
├── index.html              ← Login page
├── register.html           ← Registration page
├── dashboard.html          ← Role-aware dashboard
├── add_land.html           ← Sellers add new lands
├── land_list.html          ← Browse lands
├── land_details.html       ← Full property details
├── valuation.html          ← Authority valuation page
├── payment.html            ← Buyer payment + admin transactions
├── reports.html            ← Reports per role
├── users.html              ← Admin user management
│
├── css/
│   └── style.css           ← All application styling
│
├── js/
│   └── app.js              ← Shared API helpers, layout, helpers
│
├── php/                    ← Backend API endpoints
│   ├── config.php          ← Database connection (EDIT IF NEEDED)
│   ├── login.php
│   ├── logout.php
│   ├── register.php        ← Blocks self-registration of authority/admin
│   ├── get_current_user.php
│   ├── add_land.php
│   ├── get_lands.php
│   ├── valuation.php       ← Valuation formula + confirm
│   ├── process_payment.php ← Payment simulation w/ TXN ID + codes
│   ├── get_payments.php
│   ├── manage_users.php    ← Admin: add/delete users
│   └── get_stats.php
│
├── database/
│   └── schema.sql          ← MySQL CREATE TABLE + seed data
│
└── README.md               ← (this file)
```

---

## 🚀 Setup Instructions (XAMPP / WAMP)

### Step 1 — Install XAMPP
Download and install [XAMPP](https://www.apachefriends.org/) (or WAMP). Make sure it includes Apache, MySQL, and PHP 7.4+.

### Step 2 — Copy the project files
Copy the entire `lvms` folder into the htdocs directory:

- **Windows (XAMPP):** `C:\xampp\htdocs\lvms\`
- **Mac (XAMPP):** `/Applications/XAMPP/htdocs/lvms/`
- **Linux:** `/opt/lampp/htdocs/lvms/`

### Step 3 — Start Apache and MySQL
Open the XAMPP control panel and click **Start** next to Apache and MySQL.

### Step 4 — Import the database
1. Open `http://localhost/phpmyadmin/` in your browser
2. Click **Import** in the top menu
3. Click **Choose File** and select `database/schema.sql`
4. Click **Go** at the bottom

This will:
- Create the `lvms_db` database
- Create three tables: `users`, `lands`, `payments`
- Insert four demo accounts and four sample lands

### Step 5 — Verify database connection (if needed)
Open `php/config.php` and confirm:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Default for XAMPP
define('DB_PASS', '');         // Default for XAMPP (empty)
define('DB_NAME', 'lvms_db');
```
If your MySQL has a different password, update `DB_PASS`.

### Step 6 — Run the one-time setup script
Visit **http://localhost/lvms/setup.php** in your browser. This will:
- Properly hash the password "1234" for all demo accounts
- Show a confirmation page
- After it succeeds, **delete `setup.php`** from your server

### Step 7 — Open the system in your browser
Visit: **http://localhost/lvms/**

---

## 🔑 Demo Accounts

All demo accounts use the password **`1234`**:

| Email | Role | What they can do |
|---|---|---|
| `seller@demo.com` | Seller | Add lands, view own listings & valuations |
| `buyer@demo.com` | Buyer | Browse valued lands, make payment |
| `authority@demo.com` | Authority | Perform official valuation on pending lands |
| `admin@demo.com` | Admin | Manage all users, lands, transactions |

---

## 📐 Valuation Formula

The system uses a multi-factor valuation formula:

```
Value = ZoneRate × Area
      × TypeMultiplier
      × RoadAccessMultiplier
      × ContextMultiplier
      + UtilityBonus
```

**Zone rates (TZS per m²):**
- Urban: 50,000 · Suburban: 25,000 · Rural: 10,000

**Type multipliers:**
- Commercial: ×1.5 · Residential: ×1.2 · Agricultural: ×0.8

**Road access multipliers:**
- Main road: ×1.4 · Side road: ×1.0 · Interior: ×0.7

**Context multipliers (depend on land type):**

For **residential** and **commercial** land:
- Distance to center — Near (<2 km): ×1.2 · Moderate (2–10 km): ×1.0 · Far (>10 km): ×0.85

For **agricultural** land:
- Soil quality — Fertile: ×1.3 · Moderate: ×1.0 · Poor: ×0.7
- Water source — River: ×1.2 · Well: ×1.1 · None: ×1.0

**Utility bonus:**
- +5% per available utility (electricity, water, road, sewage)

The formula lives in `php/valuation.php`.

---

## 🔒 Security Features

1. **Password hashing** — Passwords are hashed using PHP's `password_hash()` / bcrypt
2. **Role-based access** — Each PHP endpoint checks the session role
3. **Self-registration block** — Authority and Admin accounts cannot be self-registered
4. **Prepared statements** — All SQL uses prepared statements (SQL injection protection)
5. **HTML escaping** — All user-entered data is escaped before display

---

## 💳 Payment Simulation

The payment system simulates real payment gateways without moving real money. When a buyer pays:

1. A **Transaction ID** is generated: `TXN-LVS-YYYYMMDD-XXXXXX`
2. A provider-specific **Confirmation Code** is generated:
   - M-Pesa: `QGH7K2MNX9` (10 random characters)
   - Tigo Pesa: `TGAB12CD34`
   - Airtel Money: `AMXY99ZP01`
   - Bank Transfer: `TZB123456789`
   - Card: `AUTH-XY98ZK`
3. The payment is recorded in the `payments` table
4. The land status changes to `sold`
5. A receipt modal displays with all transaction details

---

## 🎯 User Workflows

### Seller workflow
1. Register as Seller → Login
2. Click **Add Land** → fill in property details
3. Wait for Authority to perform valuation
4. Once valued, the land becomes available for buyers

### Buyer workflow
1. Register as Buyer → Login
2. Click **Browse Lands** → see all valued lands
3. Click a land → view details + valuation breakdown
4. Click **Proceed to Payment** → choose method → pay
5. Receive receipt with transaction ID & confirmation code

### Authority workflow
1. Login (created by admin)
2. Dashboard shows pending valuations
3. Click **Value Now** → review calculated valuation
4. Click **Confirm Valuation** to publish

### Administrator workflow
1. Login as admin
2. **Manage Users** → view, add, or delete any user (including Authority/Admin)
3. **All Lands** → see every land in the system
4. **Transactions** → view all payments
5. **Reports** → system-wide statistics

---

## 🛠️ Troubleshooting

**"Database connection failed"**
- Make sure MySQL is running in XAMPP control panel
- Check credentials in `php/config.php`

**"Page not found"**
- Make sure files are inside `htdocs/lvms/`
- Visit `http://localhost/lvms/` (not `http://localhost/lvms/index.html`)

**Demo accounts don't log in**
- The password hashes in `schema.sql` are bcrypt hashes of `1234`
- If they fail, log in to phpMyAdmin and re-import the schema

**Login redirects back to login**
- PHP sessions may not be enabled — check `php/config.php` has `session_start()`
- Browser may be blocking cookies

---

## 📝 License

Educational project submitted as part of ICT coursework at Karume Institute of Science and Technology.
