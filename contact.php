<?php
// Contactformulier naar GitHub Issues verwerker

// Stel CORS headers in (pas aan naar jouw domein in productie)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// GitHub configuratie - VUL HIER JOUW GEGEVENS IN
$github_username = "Pieter-janf"; // Jouw GitHub gebruikersnaam
$github_repo = "Portfolio-contact"; // Jouw repository naam
$github_token = "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"; // Jouw GitHub token (veilig omdat dit nooit naar client wordt gestuurd)

// Controleer of het een POST verzoek is
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Haal de POST data op (als JSON verstuurd)
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    // Als de data direct als POST formulier is verstuurd
    if (!$data && isset($_POST)) {
        $data = $_POST;
    }

    // Valideer de input
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['subject']) || !isset($data['message'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Niet alle vereiste velden zijn ingevuld"]);
        exit;
    }

    // Anti-spam controle
    if (isset($data['honeypot']) && !empty($data['honeypot'])) {
        // Zend een "success" bericht terug om de spambot te misleiden
        echo json_encode(["success" => true, "message" => "Bericht ontvangen"]);
        exit;
    }

    // Sanitize de input data
    $name = htmlspecialchars($data['name']);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($data['subject']);
    $message = htmlspecialchars($data['message']);

    // Valideer e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ongeldig e-mailadres"]);
        exit;
    }

    // Maak de GitHub issue body
    $issue_body = <<<EOT
## Contact Formulier Bericht
**Van:** {$name}
**Email:** {$email}

**Bericht:**
{$message}

---
*Dit issue werd automatisch aangemaakt via het contactformulier op je portfolio website.*
EOT;

    // GitHub API data
    $github_data = [
        "title" => "Contact: " . $subject,
        "body" => $issue_body,
        "labels" => ["contact", "website"]
    ];

    // Aanroepen van de GitHub API
    $api_url = "https://api.github.com/repos/{$github_username}/{$github_repo}/issues";
    
    // Initialiseer cURL
    $ch = curl_init($api_url);
    
    // Stel de cURL opties in
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($github_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: PHP-GitHub-Contact-Form",
        "Authorization: token {$github_token}",
        "Accept: application/vnd.github.v3+json",
        "Content-Type: application/json"
    ]);
    
    // Voer het verzoek uit
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Controleer op fouten
    if (curl_errno($ch)) {
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "message" => "Er is een fout opgetreden bij het verzenden van je bericht"
        ]);
        
        // Log de fout (alleen in ontwikkelomgeving)
        error_log("GitHub API fout: " . curl_error($ch));
        
        curl_close($ch);
        exit;
    }
    
    // Sluit de cURL verbinding
    curl_close($ch);
    
    // Controleer of het verzoek succesvol was
    if ($http_code >= 200 && $http_code < 300) {
        // Succes - stuur bericht terug
        echo json_encode([
            "success" => true, 
            "message" => "Bedankt voor je bericht! Ik neem zo snel mogelijk contact met je op."
        ]);
    } else {
        // Fout - stuur foutmelding terug
        http_response_code($http_code);
        echo json_encode([
            "success" => false, 
            "message" => "Er is een fout opgetreden bij het verzenden van je bericht"
        ]);
        
        // Log de fout voor debugging
        $response_data = json_decode($response, true);
        if ($response_data && isset($response_data['message'])) {
            error_log("GitHub API foutmelding: " . $response_data['message']);
        }
    }
} else {
    // Als het geen POST verzoek is
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Alleen POST methode is toegestaan"]);
}
?>
