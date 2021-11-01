<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require '../vendor/autoload.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header('Content-Type: application/json; charset=utf-8');

    // Create simple response function
    function response($message = "", $code = 200, $data = []) {
        $response = array(
            "message" => $message,
            "code" => $code,
            "data" => $data,
        );
        return json_encode($response);
    }

    // Check form was submitted
    if (!$_SERVER['REQUEST_METHOD'] == 'POST') {
        echo response("Form was not sent", 500);
        exit;
    }

    // error array 
    $errors;

    // Sanitize inputs
    $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST["message"], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    // Validate inputs
    if (!$name) $errors["name"] = "name cannot be empty";
    if (!$message) $errors["message"] = "message cannot be empty";
    if (!$email) $errors["email"] = "email cannot be empty";

    if (!empty($errors)) {
        echo response("validation errors", 400, $errors);
        exit;
    }

    // Initialize PhpMailer
    $mail = new PHPMailer(true);

    try {
        
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false; 
        $mail->Port = 25; 

        $mail->setFrom('portfolio_email@jamesphelps.co.uk', 'Portfolio');
        $mail->addAddress('portfolio_email@jamesphelps.co.uk', 'Portfolio Email'); 
        $mail->addReplyTo($email, $name);
        $mail->addBCC('james.phelps1995@live.com');
    
        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Message From Portfolio';
        $mail->Body = "
            <h3>Message from: <b>{$name}</b></h3>
            <p>{$message}</p>
            <p>Reply to: {$email}</p>
        ";

        $mail->AltBody = 
            "Message from: " . $name . "\r\n" .
            $message . "\r\n" . 
            "Reply to: " . $email;

        $mail->send();

    } catch (Exception $e) {

        echo response("Message could not be sent. Mailer Error: {$mail->ErrorInfo}", 500);

    }
    
    echo response('Sent!');
?>
