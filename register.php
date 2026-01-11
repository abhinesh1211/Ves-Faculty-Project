<?php
/* 
   1. DATABASE CONNECTION
 */

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "faculty_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* 
   2. GET FORM DATA
 */

$fname   = $_POST['fname'];
$mname   = $_POST['mname'];
$lname   = $_POST['lname'];
$email   = $_POST['email'];
$phone   = $_POST['phone'];
$aadhaar = $_POST['aadhaar'];
$pan     = $_POST['pan'];
$account = $_POST['account'];
$ifsc    = $_POST['ifsc'];

/* 
   3. FILE UPLOAD SETUP
 */

$uploadDir = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* 
   4. UPLOAD FILES
 */

$resumeName  = time() . "_resume_" . $_FILES["resume"]["name"];
$aadhaarName = time() . "_aadhaar_" . $_FILES["aadhaar_file"]["name"];
$panName     = time() . "_pan_" . $_FILES["pan_file"]["name"];

$resumePath  = $uploadDir . $resumeName;
$aadhaarPath = $uploadDir . $aadhaarName;
$panPath     = $uploadDir . $panName;

move_uploaded_file($_FILES["resume"]["tmp_name"], $resumePath);
move_uploaded_file($_FILES["aadhaar_file"]["tmp_name"], $aadhaarPath);
move_uploaded_file($_FILES["pan_file"]["tmp_name"], $panPath);

/* 
   5. INSERT DATA INTO DATABASE
 */

$sql = "INSERT INTO faculty_registration
(fname, mname, lname, email, phone, aadhaar, pan, account, ifsc, resume, aadhaar_file, pan_file)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssssssssss",
    $fname,
    $mname,
    $lname,
    $email,
    $phone,
    $aadhaar,
    $pan,
    $account,
    $ifsc,
    $resumePath,
    $aadhaarPath,
    $panPath
);

/* 6. EXECUTE QUERY */

if ($stmt->execute()) {
    echo "<h2>Registration Successful ✅</h2>";
    echo "<a href='register.html'>Go Back</a>";
} else {
    echo "Error: " . $stmt->error;
}

// 7. CLOSE CONNECTION


$stmt->close();
$conn->close();
?>
