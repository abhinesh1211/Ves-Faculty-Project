<?php
// Database Configuration
$host = 'localhost';
$dbname = 'faculty_db';
$username = 'root';  // Change if needed
$password = '';      // Change if needed

$success_message = '';
$error_message = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}

if (isset($_POST['submit_bill']) && empty($error_message)) {
    try {
        $pdo->beginTransaction();
        
        // Get and sanitize form data
        $faculty_name = htmlspecialchars(trim($_POST['faculty_name']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $salary = floatval($_POST['salary']);
        $class_name = htmlspecialchars(trim($_POST['class_name']));
        $semester = intval($_POST['semester']);
        $subject = htmlspecialchars(trim($_POST['subject']));
        $month = htmlspecialchars(trim($_POST['month']));
        $theory_hours = floatval($_POST['theory_total']);
        $theory_rate = floatval($_POST['theory_rate']);
        $practical_hours = floatval($_POST['practical_total']);
        $practical_rate = floatval($_POST['practical_rate']);
        $total_bill = floatval($_POST['total_bill']);
        $bill_date = $_POST['bill_date'];
        $gender = $_POST['gender'];

        // Validation
        if (empty($faculty_name) || empty($email) || empty($phone) || empty($class_name) || empty($subject) || empty($month)) {
            throw new Exception('Please fill all required fields.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }
        if (strlen($phone) !== 10) {
            throw new Exception('Phone must be 10 digits.');
        }

        // Check/Create Faculty
        $stmt = $pdo->prepare("SELECT id FROM faculty WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$faculty) {
            // Insert new faculty
            $stmt = $pdo->prepare("INSERT INTO faculty (faculty_name, email, phone, gender, salary) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$faculty_name, $email, $phone, $gender, $salary]);
            $faculty_id = $pdo->lastInsertId();
        } else {
            $faculty_id = $faculty['id'];
        }

        // Generate unique bill number
        $bill_number = 'VFB-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);

        // Insert Bill Header
        $stmt = $pdo->prepare("
            INSERT INTO bills (bill_number, faculty_id, bill_date, class_name, semester, subject, month, 
                              theory_hours, theory_rate, practical_hours, practical_rate, total_bill, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $bill_number, $faculty_id, $bill_date, $class_name, $semester, $subject, $month,
            $theory_hours, $theory_rate, $practical_hours, $practical_rate, $total_bill, $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        $bill_id = $pdo->lastInsertId();

        // Insert Theory Lectures
        if (isset($_POST['theory_dates']) && is_array($_POST['theory_dates'])) {
            foreach ($_POST['theory_dates'] as $index => $details) {
                if (!empty($details['date']) || !empty($details['hours'])) {
                    $stmt = $pdo->prepare("
                        INSERT INTO theory_lectures (bill_id, sr_no, lecture_date, day, from_time, to_time, hours) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $bill_id, 
                        $index + 1, 
                        $details['date'] ?? '', 
                        $details['day'] ?? '', 
                        $details['from'] ?? '', 
                        $details['to'] ?? '', 
                        floatval($details['hours'] ?? 0)
                    ]);
                }
            }
        }

        // Insert Practical Lectures
        if (isset($_POST['practical_dates']) && is_array($_POST['practical_dates'])) {
            foreach ($_POST['practical_dates'] as $index => $details) {
                if (!empty($details['date']) || !empty($details['hours'])) {
                    $stmt = $pdo->prepare("
                        INSERT INTO practical_lectures (bill_id, sr_no, lecture_date, day, from_time, to_time, hours) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $bill_id, 
                        $index + 1, 
                        $details['date'] ?? '', 
                        $details['day'] ?? '', 
                        $details['from'] ?? '', 
                        $details['to'] ?? '', 
                        floatval($details['hours'] ?? 0)
                    ]);
                }
            }
        }

        $pdo->commit();
        $success_message = "✅ Bill saved to DATABASE!<br><strong>Bill Number: $bill_number</strong><br>Bill ID: $bill_id<br>Faculty ID: $faculty_id";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "❌ Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bill Processing Result</title>
<link rel="stylesheet" href="salary.css">
<style>
.success-msg { background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; border-left: 4px solid #22c55e; margin: 20px 0; }
.error-msg { background: #fee2e2; color: #dc2626; padding: 20px; border-radius: 10px; border-left: 4px solid #dc2626; margin: 20px 0; }
.btn { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px; }
.btn:hover { background: #1d4ed8; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 Bill Processing Result</h1>
    </div>

    <?php if ($success_message): ?>
        <div class="success-msg">
            <strong><?php echo $success_message; ?></strong>
        </div>
        <p>
            <a href="salary.html" class="btn">➕ New Bill</a>
            <a href="view_bills.php" class="btn">📂 View Bills</a>
        </p>
    <?php elseif ($error_message): ?>
        <div class="error-msg">
            <strong><?php echo $error_message; ?></strong>
        </div>
        <p><a href="salary.html" class="btn">🔄 Try Again</a></p>
    <?php else: ?>
        <p><a href="salary.html">Go to Salary Calculator</a></p>
    <?php endif; ?>

    <hr style="margin: 30px 0;">
    <p><small>Server Time: <?php echo date('Y-m-d H:i:s'); ?></small></p>
</div>
</body>
</html>
