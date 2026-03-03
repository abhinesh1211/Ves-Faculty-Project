<?php
$pdo = new PDO("mysql:host=localhost;dbname=ves_salary", "root", "");
$stmt = $pdo->query("SELECT * FROM salary_bills WHERE status='coordinator_approved' ORDER BY created_at DESC");
$bills = $stmt->fetchAll();
$total_pending = array_sum(array_column($bills, 'total_amount'));
?>

<!DOCTYPE html>
<html>
<head><title>Admin Dashboard</title>
<style>body{font-family:'Times New Roman',serif;margin:0;background:#f8fafc;}
.header{background:linear-gradient(135deg,#1e4d8f,#3468b2);color:white;padding:30px;text-align:center;}
.container{max-width:1200px;margin:30px auto;}
.stats{display:flex;gap:20px;margin-bottom:30px;}
.stat-card{flex:1;background:white;padding:25px;border-radius:12px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
table{width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 15px 35px rgba(0,0,0,0.1);}
th,td{padding:18px 15px;text-align:left;border-bottom:1px solid #e5e7eb;}
th{background:#1e4d8f;color:white;font-weight:bold;}
.btn-final{background:#10b981;color:white;padding:12px 20px;text-decoration:none;border-radius:8px;font-weight:bold;}
</style>
</head>
<body>
<div class="header">
  <h1>📊 VES Admin Dashboard</h1>
  <p>Coordinator Approved Bills (<?php echo count($bills); ?>)</p>
</div>
<div class="container">
  <div class="stats">
    <div class="stat-card">
      <div style="font-size:2.5em;color:#f59e0b;"><?php echo count($bills); ?></div>
      <div>Pending Final Approval</div>
    </div>
    <div class="stat-card">
      <div style="font-size:2em;color:#10b981;">₹<?php echo number_format($total_pending,2); ?></div>
      <div>Total Pending Amount</div>
    </div>
  </div>
  
  <?php if (empty($bills)): ?>
    <div style="text-align:center;padding:60px;background:white;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.1);">
      <h2>✅ No pending bills</h2>
      <p>All bills processed successfully!</p>
    </div>
  <?php else: ?>
    <table>
      <tr><th>ID</th><th>Bill#</th><th>Faculty</th><th>Class/Sem</th><th>Total ₹</th><th>Coordinator</th><th>Action</th></tr>
      <?php foreach($bills as $bill): ?>
      <tr style="background:#dcfce7;">
        <td><strong>#<?php echo $bill['id']; ?></strong></td>
        <td><?php echo htmlspecialchars($bill['bill_number']); ?></td>
        <td><?php echo htmlspecialchars($bill['faculty_name']); ?></td>
        <td><?php echo $bill['class'] . '/' . $bill['semester']; ?></td>
        <td><strong style="color:#10b981;">₹<?php echo number_format($bill['total_amount'],2); ?></strong></td>
        <td><?php echo htmlspecialchars($bill['coordinator_email']); ?></td>
        <td><a href="admin_approve.php?id=<?php echo $bill['id']; ?>" class="btn-final">✅ FINAL APPROVE</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
