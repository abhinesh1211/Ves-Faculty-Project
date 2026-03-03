<?php
error_reporting(E_ALL & ~E_WARNING);
if ($_POST) {
    $filename = "faculty_master.csv";  // SAME FILE HAR BAAR
    
    // Data prepare karo
    $row = [date('Y-m-d H:i'), $_POST['salutation'], $_POST['fullname'], $_POST['gender'], 
            $_POST['dob'], $_POST['email'], $_POST['phone'], $_POST['qualification'],
            $_POST['resumelink'], $_POST['aadhaar'], $_POST['aadhaarlink'], $_POST['pan'], 
            $_POST['account'], $_POST['ifsc']];
    
    // FILE MEIN SAVE (download NHI)
    $file = fopen($filename, 'a');
    if (filesize($filename) == 0) {
        $headers = ['Date', 'Salutation', 'Full Name', 'Gender', 'DOB', 'Email', 'Phone', 'Qualification', 'Resume', 'Aadhaar', 'Aadhaar Link', 'PAN', 'Account', 'IFSC'];
        fputcsv($file, $headers);
    }
    fputcsv($file, $row);
    fclose($file);
    
    // SUCCESS PAGE - DOWNLOAD BUTTON WITHOUT AUTO-DOWNLOAD
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Success</title>
        <style>
        body { font-family: Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin:0; padding:50px; }
        .container { max-width:600px; margin:auto; background:white; padding:40px; border-radius:20px; box-shadow:0 20px 40px rgba(0,0,0,0.1); text-align:center; }
        .success { color:#10b981; font-size:28px; margin-bottom:20px; }
        .download-btn { background:linear-gradient(45deg, #10b981, #059669); color:white; padding:18px 40px; font-size:20px; border:none; border-radius:12px; text-decoration:none; display:inline-block; margin:10px; box-shadow:0 10px 30px rgba(16,185,129,0.4); }
        .download-btn:hover { transform:translateY(-2px); box-shadow:0 15px 40px rgba(16,185,129,0.6); }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success">✅ DATA SAVED SUCCESSFULLY!</div>
            <h2>All data saved in <strong>faculty_master.csv</strong></h2>
            <p>File Size: <?php echo round(filesize($filename)/1024, 1); ?> KB</p>
            
            <!-- DOWNLOAD SIRF YAHAN - CLICK KARNA PADega -->
            <a href="<?php echo $filename; ?>" download class="download-btn">📥 DOWNLOAD MASTER FILE</a>
            
            <div style="margin-top:30px;">
                <a href="register.html" style="background:#1e40af; color:white; padding:12px 24px; text-decoration:none; border-radius:8px; margin:5px;">➕ Add Another</a>
                <a href="index.html" style="background:#6b7280; color:white; padding:12px 24px; text-decoration:none; border-radius:8px; margin:5px;">🏠 Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    header("Location: register.html");
    exit;
}
?>
