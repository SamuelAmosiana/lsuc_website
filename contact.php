<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    $application_type = isset($_POST['application_type']) ? htmlspecialchars($_POST['application_type']) : 'General Application';

    $to      = "admissions@lsuczm.com"; // ✅ Your target email
    $subject = "New $application_type - $name";
    $body    = "You have received a new application:\n\n".
               "Name: $name\n".
               "Email: $email\n".
               "Application Type: $application_type\n\n".
               "Message:\n$message\n";

    $headers = "From: no-reply@lsuczm.com\r\n"; 
    $headers .= "Reply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "✅ Success! Your application has been sent.";
    } else {
        echo "❌ Error sending your application. Please try again later.";
    }
}
?>
?>