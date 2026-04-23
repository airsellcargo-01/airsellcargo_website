<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $to = "sales@airsellcargo.com"; 
    
    // Collect and sanitize input
    $name    = strip_tags(trim($_POST["name"])); 
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone   = htmlspecialchars($_POST["phone"]);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = htmlspecialchars($_POST["feedback"]);
    $service = htmlspecialchars($_POST["service-type"]);

    // Prepare headers
    $email_subject = "Airsell Portal Inquiry: $subject";
    $headers  = "From: portal@airsellcargo.com\r\n"; // must be domain email
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Build body
    $body  = "New message from Airsell Cargo Website Form:\n\n";
    $body .= "Customer Name: $name\n";
    $body .= "Customer Email: $email\n";
    $body .= "Phone Number: $phone\n";
    $body .= "Service Required: $service\n";
    $body .= "Subject: $subject\n\n";
    $body .= "Message Details:\n$message\n\n";
    $body .= "--- End of Message ---";

    // Send
    if (mail($to, $email_subject, $body, $headers)) {
        echo "Thank you! Your inquiry has been sent to the Airsell Cargo team.";
    } else {
        error_log("Mail failed: " . print_r(error_get_last(), true));
        echo "Error: Please email us directly at sales@airsellcargo.com";
    }
} else {
    echo "Access Denied.";
}
?>
