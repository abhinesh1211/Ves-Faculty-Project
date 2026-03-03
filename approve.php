<?php
$conn = mysqli_connect('localhost', 'root', '', 'ves_salary');
$bill_id = intval($_GET['id']);

// APPROVE + GENERATE EXCEL
if (mysqli_query($conn, "UPDATE salary_bills SET status='approved' WHERE id=$bill_id")) {
    
    // GET APPROVED BILL DATA
    $result = mysqli_query($conn, "SELECT * FROM salary_bills WHERE id=$bill_id");
    $bill = mysqli_fetch_assoc($result);
    
    // GENERATE EXCEL (CSV)
    $excel_content = "VES Faculty Salary Bill\n";
    $excel_content .= "Bill No,Faculty Name,Email,Class/Semester,Subject,Month,Theory Hours,Theory Rate,Practical Hours,Practical Rate,Total Amount,Status,Date\n";
    $excel_content .= "\"{$bill['bill_number']}\",\"{$bill['faculty_name']}\",\"{$bill['email']}\",\"{$bill['class']}/{$bill['semester']}\",\"{$bill['subject']}\",\"{$bill['month']}\",{$bill['theory_hours']},{$bill['theory_rate']},{$bill['practical_hours']},{$bill['practical_rate']},{$bill['total_amount']},\"{$bill['status']}\",\"{$bill['created_at']}\"\n";
    
    $excel_filename = "approved_bills/{$bill['bill_number']}.csv";
    if (!is_dir('approved_bills')) mkdir('approved_bills', 0777, true);
    file_put_contents($excel_filename, $excel_content);
    
    echo "<!DOCTYPE html>
    <html><head><title>✅ APPROVED</title>
    <style>body{font-family:Arial;background:#d4edda;padding:50px;text-align:center;}
    .success{background:#d4edda;padding:40px;border-radius:15px;max-width:600px;margin:auto;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
    h1{color:#155724;font-size:2.5em;}
    .btn{display:inline-block;padding:15px 30px;background:#28a745;color:white;text-decoration:none;border-radius:8px;margin:10px;font-weight:bold;}
    .excel-info{background:#fff3cd;padding:20px;border-radius:8px;margin:20px 0;}
    </style></head>
    <body>
    <div class='success'>
        <h1>✅ Bill Approved Successfully!</h1>
        <p><strong>Bill No:</strong> {$bill['bill_number']}</p>
        <p><strong>Faculty:</strong> {$bill['faculty_name']}</p>
        <div class='excel-info'>
            <strong>📊 Excel Generated:</strong><br>
            <a href='$excel_filename' download style='color:#856404;'>💾 Download Excel</a>
        </div>
        <a href='new_coordinator.php' class='btn'>← Back to Dashboard</a>
        <a href='new_salary.html' class='btn' style='background:#007bff;'>➕ New Bill</a>
    </div>
    </body></html>";
} else {
    echo "❌ Error approving bill";
}
mysqli_close($conn);
?>
