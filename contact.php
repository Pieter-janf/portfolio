<?php
// Contactformulier verwerking

// Stel variabelen in voor formulier
$message_sent = false;
$errors = [];

// Controleer of het formulier is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Anti-spam check (honeypot)
    if (!empty($_POST['honeypot'])) {
        die("Spam gedetecteerd!");
    }
    
    // Valideer naam
    if (empty($_POST['name'])) {
        $errors[] = "Naam is verplicht";
    } else {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    }
    
    // Valideer email
    if (empty($_POST['email'])) {
        $errors[] = "Email is verplicht";
    } else {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Ongeldig email formaat";
        }
    }
    
    // Valideer onderwerp
    if (empty($_POST['subject'])) {
        $errors[] = "Onderwerp is verplicht";
    } else {
        $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    }
    
    // Valideer bericht
    if (empty($_POST['message'])) {
        $errors[] = "Bericht is verplicht";
    } else {
        $message = htmlspecialchars($_POST['message']);
    }
    
    // Als er geen fouten zijn, verstuur het bericht
    if (empty($errors)) {
        // Ontvanger email instellen - verander naar jouw email
        $to = "pjfockaert@gmail.com";
        
        // Email headers instellen
        $headers = "From: " . $name . " <" . $email . ">\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Email inhoud
        $email_content = "
        <html>
        <head>
            <title>Nieuw bericht van je portfolio</title>
        </head>
        <body>
            <h2>Je hebt een nieuw bericht ontvangen</h2>
            <p><strong>Naam:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Onderwerp:</strong> $subject</p>
            <p><strong>Bericht:</strong></p>
            <p>$message</p>
        </body>
        </html>
        ";
        
        // Verstuur email
        $success = mail($to, "Nieuw bericht: $subject", $email_content, $headers);
        
        if ($success) {
            $message_sent = true;
            // Redirect om dubbele form submission te voorkomen
            header('Location: index.php?contact=success#contact');
            exit;
        } else {
            $errors[] = "Er is een probleem opgetreden bij het versturen van je bericht. Probeer het later opnieuw.";
        }
    }
}
?>
