
<?php
require 'vendor/autoload.php'; // For PHPMailer
require 'config.php';         // Contains your API and SMTP credentials

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = $_POST['country'];
    
    // Get data from OpenAI
    if (isset($_POST['get_info'])) {
        $response = getCountryInfo($country);
        echo "<h2>Information for $country</h2>";
        echo "<p>$response</p>";
    }

    // Send data via email
    if (isset($_POST['send_email'])) {
        $response = getCountryInfo($country);
        sendEmail($response);
        echo "<p>Email sent successfully to seventh.sky@gmail.com</p>";
    }
}

function getCountryInfo($country) {
    $apiKey = OPENAI_API_KEY; // Defined in config.php
    $ch = curl_init('https://api.openai.com/v1/completions');
    $data = [
        "model" => "gpt-4o",
        "prompt" => "Provide the GDP and main imports and exports for $country.",
        "max_tokens" => 150
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    print_r($result);
    //return $result['choices'][0]['text'] ?? 'No response';
}

function sendEmail($response) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom(SMTP_USER, 'G20 Info');
        $mail->addAddress('seventh.sky@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'G20 Country Information';
        $mail->Body = nl2br($response);

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
