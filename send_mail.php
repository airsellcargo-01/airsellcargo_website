<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Configuration - Change this to your email
    $to = "airsellcargo@gmail.com"; 
    
    // 2. Collect and sanitize input
    $name = strip_tags(trim($_SERVER["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = htmlspecialchars($_POST["feedback"]);
     $service = htmlspecialchars($_POST["service-type"]);
    // 3. Prepare Email Headers
    $email_subject = "New Contact from Airsell Portal: $subject";
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // 4. Build the Email Body
    $body = "You have received a new message from your website contact form.\n\n".
            "Name: $name\n".
            "Email: $email\n".
            "subject: $subject\n\n".
            "feedback:\n$message";

    // 5. Send the Email
    if (mail($to, $email_subject, $body, $headers)) {
        // Success: Redirect back or show a message
        echo "Thank you! Your message has been sent to Airsell Cargo.";
    } else {
        // Failure
        echo "Error: Something went wrong. Please try again or email us directly.";
    }
} else {
    echo "Invalid Request.";
}
?>
