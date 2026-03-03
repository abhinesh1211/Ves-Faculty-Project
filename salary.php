<?php
if ($_POST) {
    $filename = "faculty_master.csv";  // <<<< SAME FILE - Faculty + Salary same!
    
    // Salary headers (different columns)
    $salary_headers = ['Date', 'Faculty Name', 'Email', 'Bill Date', 'Class', 'Semester', 
                      'Subject', 'Month', 'Theory Hrs', 'Theory Rate', 'Practical Hrs', 
                      'Practical Rate', 'TOTAL AMOUNT'];
    
    $total = ($_POST['theory_hours'] ?? 0) * ($_POST['theory_rate'] ?? 0) + 
             ($_POST['practical_hours'] ?? 0) * ($_POST['practical_rate'] ?? 0);
    
    $row = [date('Y-m-d H:i'), $_POST['faculty_name'], $_POST['email'], $_POST['bill_date'], 
            $_POST['class'], $_POST['semester'], $_POST['subject'], $_POST['month'],
            $_POST['theory_hours'], $_POST['theory_rate'], $_POST['practical_hours'],
            $_POST['practical_rate'], $total];
    
    $file = fopen($filename, 'a');
    
    // Check if salary section already exists (skip headers if exists)
    $existing = file_get_contents($filename);
    if (strpos($existing, 'Faculty Name') === false) {
        fputcsv($file, $salary_headers);
    }
    
    fputcsv($file, $row);
    fclose($file);
    
    echo "<div style='text-align:center; padding:50px; background:#fef3c7; margin:50px; border-radius:15px;'>";
    echo "<h1 style='color:#92400e;'>💰 SALARY BILL SAVED!</h1>";
    echo "<h2>Total: ₹" . number_format($total, 2) . "</h2>";
    echo "<a href='$filename' download style='background:linear-gradient(45deg,#f59e0b,#d97706); color:white; padding:20px 40px; text-decoration:none; border-radius:12px; font-size:20px;'>📥 DOWNLOAD MASTER EXCEL</a>";
    echo "</div>";
}
?>
