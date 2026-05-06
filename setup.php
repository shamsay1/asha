<?php
/* ============================================================
   ONE-TIME SETUP SCRIPT
   Run this ONCE after importing schema.sql.
   It will set the password to "1234" (properly hashed) for all
   demo accounts that still have the "NEEDS_HASH" placeholder.
   You can safely delete this file afterwards.
   ============================================================ */

require_once 'php/config.php';

$hashed = password_hash('1234', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE password = 'NEEDS_HASH'");
$stmt->bind_param("s", $hashed);
$stmt->execute();
$updated = $stmt->affected_rows;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
  <title>LVMS Setup</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="auth-wrap">
    <div class="auth-card">
      <h1 style="color:#1e3a5f; text-align:center;">🏞️ LVMS Setup</h1>

      <?php if ($updated > 0): ?>
        <div class="alert alert-success" style="margin-top:20px;">
          ✅ Successfully hashed passwords for <strong><?= $updated ?></strong> demo account(s).
        </div>
        <p>You can now log in with any of these demo accounts using password <strong>1234</strong>:</p>
        <ul style="margin: 15px 0 15px 20px;">
          <li><code>seller@demo.com</code></li>
          <li><code>buyer@demo.com</code></li>
          <li><code>authority@demo.com</code></li>
          <li><code>admin@demo.com</code></li>
        </ul>
        <div class="alert alert-info">
          ⚠️ For security, please <strong>delete this file (setup.php)</strong> from your server now.
        </div>
      <?php else: ?>
        <div class="alert alert-info" style="margin-top:20px;">
          No accounts needed setup (already hashed). System is ready.
        </div>
      <?php endif; ?>

      <a href="index.html" class="btn btn-primary btn-block" style="margin-top:20px;">Go to Login</a>
    </div>
  </div>
</body>
</html>
