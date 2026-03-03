<?php
$pdo = new PDO("mysql:host=localhost;dbname=ves_salary", "root", "");
$bill_id = $_GET['id'] ?? 0;

if (isset($_GET['approve'])) {
    $stmt = $pdo->prepare("UPDATE salary_bills SET status='coordinator_approved' WHERE id=?");
    $stmt->execute([$bill_id]);
    
    $stmt = $pdo->prepare("SELECT * FROM salary_bills WHERE id=?");
    $stmt->execute([$bill_id]);
    $bill = $stmt->fetch();
    
    // Email Admin
    mail('admin@ves.ac.in', "New Bill Approved - {$bill['bill_number']}", 
         "Bill {$bill['bill_number']} approved. Total: ₹{$bill['total_amount']}\nView: http://localhost/ves-Faculty/admin_dashboard.php");
    
    echo "<div style='text-align:center;padding:60px;background:#d1fae5;color:#065f46;border-radius:20px;max-width:600px;margin:50px auto;'>✅ Bill Approved! Admin notified.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Coordinator Approval</title>
<style>body{font-family:Arial;background:#f8fafc;margin:0;padding:20px;}
.container{max-width:600px;margin:50px auto;background:white;padding:40px;border-radius:15px;box-shadow:0 15px 35px rgba(0,0,0,0.1);}
.btn{display:inline-block;padding:18px 40px;color:white;text-decoration:none;border-radius:12px;font-size:18px;margin:15px;font-weight:bold;}
.approve{background:#10b981;}.reject{background:#dc2626;}
h1{color:#1e4d8f;text-align:center;}
</style>
</head>
<body>
<div class="container">
  <h1>🔍 Bill Verification Required</h1>
  <h2>Bill ID: #<?php echo $bill_id; ?></h2>
  <div style="text-align:center;margin:40px 0;">
    <a href="?id=<?php echo $bill_id; ?>&approve=1" class="btn approve">✅ APPROVE & FORWARD TO ADMIN</a><br>
    <a href="coordinator_reject.php?id=<?php echo $bill_id; ?>" class="btn reject">❌ REJECT BILL</a>
  </div>
</div>
</body>
</html>
