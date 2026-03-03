<?php
$conn = mysqli_connect('localhost', 'root', '', 'ves_salary');
$bill_id = intval($_GET['id']);

if (mysqli_query($conn, "UPDATE salary_bills SET status='rejected' WHERE id=$bill_id")) {
    $result = mysqli_query($conn, "SELECT bill_number, faculty_name FROM salary_bills WHERE id=$bill_id");
    $bill = mysqli_fetch_assoc($result);
    
    echo "<!DOCTYPE html>
    <html><head><title>❌ REJECTED</title>
    <style>body{font-family:Arial;background:#f8d7da;padding:50px;text-align:center;}
    .reject{background:#f8d7da;padding:40px;border-radius:15px;max-width:600px;margin:auto;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
    h1{color:#721c24;font-size:2.5em;}
    .btn{display:inline-block;padding:15px 30px;background:#dc3545;color:white;text-decoration:none;border-radius:8px;margin:10px;font-weight:bold;}
    </style></head>
    <body>
    <div class='reject'>
        <h1>❌ Bill Rejected!</h1>
        <p><strong>Bill No:</strong> {$bill['bill_number']}</p>
        <p><strong>Faculty:</strong> {$bill['faculty_name']}</p>
        <a href='new_coordinator.php' class='btn'>← Back to Dashboard</a>
    </div>
    </body></html>";
} else {
    echo "❌ Error rejecting bill";
}
mysqli_close($conn);
?>
