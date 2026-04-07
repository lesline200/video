<?php
// login.php — VidGenius Login Page
session_start();


require_once 'database/config.php';
// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard/layout.php');
    exit;
}

$api_url = getenv('API_URL') ?: 'http://localhost:8080';
?>


//signup

<?php
// signup.php — VidGenius Signup Page
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}
   
$api_url       = getenv('API_URL')         ?: 'http://localhost:3001';
$google_client = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID';
?>    


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 // connexion google




kikh sjwj hqpc pwub //forget password













 