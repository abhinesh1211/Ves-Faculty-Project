<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: new_salary.html');
    exit;
}

// 1. UNIQUE BILL NUMBER + DATA
$timestamp = date('YmdHis');
$bill_number = 'VS-' . $timestamp;
$faculty_name = htmlspecialchars($_POST['faculty_name'] ?? 'Faculty');
$coordinator_email = $_POST['coordinator_email'] ?? '';

$theory_hours = array_map('floatval', $_POST['theory_hours'] ?? []);
$practical_hours = array_map('floatval', $_POST['practical_hours'] ?? []);
$total_theory = array_sum($theory_hours);
$total_practical = array_sum($practical_hours);
$theory_rate = floatval($_POST['theory_rate'] ?? 0);
$practical_rate = floatval($_POST['practical_rate'] ?? 0);
$total_amount = round(($total_theory * $theory_rate) + ($total_practical * $practical_rate), 2);

// 2. SAVE DATABASE (SECURE)
$conn = mysqli_connect('localhost', 'root', '', 'ves_salary');
if (!$conn) {
    die('Database connection failed');
}

$faculty_email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
$bill_date = mysqli_real_escape_string($conn, $_POST['bill_date'] ?? '');
$class = mysqli_real_escape_string($conn, $_POST['class'] ?? '');
$semester = mysqli_real_escape_string($conn, $_POST['semester'] ?? '');
$subject = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
$month = mysqli_real_escape_string($conn, $_POST['month'] ?? '');
$coord_email = mysqli_real_escape_string($conn, $coordinator_email);

$sql = "INSERT INTO salary_bills (bill_number, faculty_name, email, coordinator_email, bill_date, class, semester, subject, month, theory_hours, practical_hours, theory_rate, practical_rate, total_amount, status) VALUES (
    '$bill_number', '$faculty_name', '$faculty_email', '$coord_email', '$bill_date', '$class', '$semester', '$subject', '$month', 
    $total_theory, $total_practical, $theory_rate, $practical_rate, $total_amount, 'pending'
)";

if (mysqli_query($conn, $sql)) {
    $bill_id = mysqli_insert_id($conn);
} else {
    die('Database error: ' . mysqli_error($conn));
}
mysqli_close($conn);

// 3. CREATE BILLS DIRECTORY
$bills_dir = __DIR__ . '/bills';
if (!is_dir($bills_dir)) {
    mkdir($bills_dir, 0777, true);
}

// 4. GENERATE PDF - FIXED PATH (Absolute Path Solution)
require_once(__DIR__ . '/TCPDF-main/tcpdf.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('VES Faculty System');
$pdf->SetAuthor('VES College');
$pdf->SetTitle('Salary Bill ' . $bill_number);
$pdf->SetSubject('Visiting Faculty Salary Bill');

$pdf->AddPage();
$pdf->SetFont('times', 'B', 20);
$pdf->Cell(0, 15, '🧾 VISITING FACULTY SALARY BILL', 0, 1, 'C', false, '', 0, false, 'T', 'M');
$pdf->Ln(5);

$pdf->SetFont('times', '', 12);
$pdf->Cell(0, 8, 'Bill No: ' . $bill_number . ' | Date: ' . date('d-m-Y'), 0, 1, 'C');
$pdf->Ln(10);

// FACULTY DETAILS
$html = '
<table border="1" cellpadding="8" style="font-size:12px;">
    <tr style="background-color:#f8f9fa;">
        <th width="30%">Faculty Name</th>
        <td width="70%">' . $faculty_name . '</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>' . htmlspecialchars($_POST['email'] ?? '') . '</td>
    </tr>
    <tr>
        <th>Class/Semester</th>
        <td>' . htmlspecialchars($_POST['class'] ?? '') . '/' . htmlspecialchars($_POST['semester'] ?? '') . '</td>
    </tr>
    <tr>
        <th>Subject</th>
        <td>' . htmlspecialchars($_POST['subject'] ?? '') . '</td>
    </tr>
    <tr>
        <th>Month</th>
        <td>' . htmlspecialchars($_POST['month'] ?? '') . '</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// THEORY
$pdf->Ln(10);
$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, '📖 THEORY LECTURES', 0, 1);
$pdf->SetFont('times', '', 12);
$html = '
<table border="1" cellpadding="8">
    <tr style="background-color:#e3f2fd;">
        <th>Total Hours</th><th>Rate (₹/hr)</th><th>Amount</th>
    </tr>
    <tr>
        <td>' . number_format($total_theory, 2) . '</td>
        <td>₹' . number_format($theory_rate, 0) . '</td>
        <td>₹' . number_format($total_theory * $theory_rate, 2) . '</td>
    </tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// PRACTICAL
$pdf->Ln(5);
$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 10, '🛠️ PRACTICAL SESSIONS', 0, 1);
$pdf->SetFont('times', '', 12);
$html = '
<table border="1" cellpadding="8">
    <tr style="background-color:#e3f2fd;">
        <th>Total Hours</th><th>Rate (₹/hr)</th><th>Amount</th>
    </tr>
    <tr>
        <td>' . number_format($total_practical, 2) . '</td>
        <td>₹' . number_format($practical_rate, 0) . '</td>
        <td>₹' . number_format($total_practical * $practical_rate, 2) . '</td>
    </tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// TOTAL
$pdf->Ln(10);
$pdf->SetFillColor(30, 77, 143);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('times', 'B', 18);
$pdf->Cell(0, 20, '💰 TOTAL BILL AMOUNT: ₹' . number_format($total_amount, 2), 1, 1, 'C', true);

// SIGNATURES
$pdf->Ln(15);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('times', '', 12);
$html = '
<table border="1" cellpadding="8" style="font-size:11px;">
    <tr>
        <td width="50%" style="text-align:center;"><strong>Faculty Signature</strong><br>___________________</td>
        <td width="50%" style="text-align:center;"><strong>Coordinator Approval</strong><br>___________________</td>
    </tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->SetY(-20);
$pdf->SetFont('times', '', 10);
$pdf->Cell(0, 6, 'Generated by VES Faculty System on ' . date('d-m-Y H:i'), 0, 0, 'C');

// 🔥 FIXED: Use ABSOLUTE PATH for PDF saving
$pdf_filename = $bills_dir . '/' . $bill_number . '.pdf';
$pdf_content = $pdf->Output('', 'S');  // Get PDF as string
file_put_contents($pdf_filename, $pdf_content);  // Save with PHP (bypasses TCPDF bug)

// 5. SEND EMAIL
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$email_sent = false;
if (!empty($coordinator_email)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'abhinesh.mahindrakar.4672806@ves.ac.in';
        $mail->Password = 'xzjc ipqe opkp gaba';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('abhinesh.mahindrakar.4672806@ves.ac.in', 'VES Faculty System');
        $mail->addAddress($coordinator_email);
        $mail->addReplyTo($_POST['email'] ?? '', $faculty_name);
        $mail->addAttachment($pdf_filename);  // Attach saved PDF
        
        $mail->isHTML(true);
        $mail->Subject = "🧾 VES: Visiting Faculty Bill $bill_number";
        $mail->Body = "
        <h2 style='color: #1e4d8f;'>VES: Visiting Faculty Bill #$bill_number</h2>
        <p><strong>Faculty:</strong> $faculty_name</p>
        <p><strong>Total Amount:</strong> <span style='font-size: 24px; color: #10b981;'>₹" . number_format($total_amount, 2) . "</span></p>
        <p><em>Complete PDF attached. Click links below to approve/reject:</em></p>
        <p>
            <a href='http://localhost/ves-Faculty1/ves-Faculty/approve.php?id=$bill_id' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>✅ APPROVE</a>
            <a href='http://localhost/ves-Faculty1/ves-Faculty/reject.php?id=$bill_id' style='background: #ef4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>❌ REJECT</a>
        </p>";
        
        $mail->send();
        $email_sent = true;
    } catch (Exception $e) {
        // Silent fail
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>✅ Bill Submitted Successfully!</title>
    <style>
        *{font-family:'Times New Roman',serif;margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#d1fae5 0%,#a7f3d0 100%);min-height:100vh;padding:60px 20px;display:flex;align-items:center;justify-content:center;}
        .success-box{background:white;padding:80px 60px;border-radius:25px;box-shadow:0 25px 60px rgba(0,0,0,0.15);text-align:center;max-width:600px;width:100%;}
        .checkmark{font-size:80px;color:#10b981;margin-bottom:20px;animation:bounce 1s ease-in-out;}
        @keyframes bounce{0%,20%,50%,80%,100%{transform:translateY(0);}40%{transform:translateY(-20px);}60%{transform:translateY(-10px);}}
        h1{color:#065f46;font-size:2.5em;margin-bottom:15px;}
        .bill-info{background:linear-gradient(135deg,#e3f2fd,#bbdefb);padding:30px;border-radius:20px;margin:30px 0;box-shadow:0 8px 25px rgba(30,77,143,0.15);}
        .bill-info strong{font-size:1.3em;color:#1e4d8f;}
        .btn{display:inline-block;padding:18px 50px;background:#1e4d8f;color:white;text-decoration:none;border-radius:12px;font-size:18px;font-weight:bold;margin:10px;transition:all .3s;box-shadow:0 4px 15px rgba(0,0,0,0.2);}
        .btn:hover{transform:translateY(-2px);}
        .status{background:#d4edda;color:#155724;padding:15px;border-radius:10px;margin:20px 0;border-left:4px solid #10b981;}
    </style>
</head>
<body>
<div class="success-box">
    <div class="checkmark">✅</div>
    <h1>Bill Submitted Successfully!</h1>
    
    <div class="bill-info">
        <strong>📄 Bill No:</strong> <?php echo $bill_number; ?><br><br>
        <strong>👨‍🏫 Faculty:</strong> <?php echo $faculty_name; ?><br><br>
        <strong>💰 Amount:</strong> <span style="font-size:1.5em;color:#10b981;">₹<?php echo number_format($total_amount, 2); ?></span>
    </div>
    
    <div class="status">
        ✅ <strong>Database saved</strong> (ID: <?php echo $bill_id; ?>)<br>
        ✅ <strong>PDF generated:</strong> bills/<?php echo $bill_number; ?>.pdf<br>
        <?php if($email_sent): ?>
            ✅ <strong>Email sent</strong> to coordinator
        <?php else: ?>
            ⚠️ <strong>Manual send:</strong> Check bills/ folder or configure Gmail App Password
        <?php endif; ?>
    </div>
    
    <a href="new_salary.html" class="btn">➕ New Bill</a>
    <a href="bills/<?php echo $bill_number; ?>.pdf" class="btn" style="background:#10b981;">📄 Download PDF</a>
</div>
</body>
</html>
