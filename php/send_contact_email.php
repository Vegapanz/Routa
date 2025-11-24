<?php
/**
 * Contact Form Handler
 * Sends contact form messages to admin email
 */

session_start();
require_once 'config.php';
require_once 'email_config.php';

header('Content-Type: application/json');

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    
    if (empty($email)) {
        throw new Exception('Email is required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    if (empty($subject)) {
        throw new Exception('Subject is required');
    }
    
    if (empty($message)) {
        throw new Exception('Message is required');
    }
    
    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    // Prepare email
    if (!EMAIL_ENABLED) {
        throw new Exception('Email sending is currently disabled');
    }
    
    $mail = new PHPMailer(true);
    
    // Server settings
    if (EMAIL_METHOD === 'smtp') {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
    }
    
    // Recipients
    $mail->setFrom(SMTP_USERNAME, EMAIL_FROM_NAME);
    $mail->addAddress(SMTP_USERNAME); // Send to your Gmail
    $mail->addReplyTo($email, $name); // Reply to the sender
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = "Contact Form: " . $subject;
    
    // Email body with styling
    $emailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    </head>
    <body style='margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif; background-color: #f5f5f5;'>
        <table role='presentation' style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='padding: 40px 20px;'>
                    <table role='presentation' style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                        <!-- Header -->
                        <tr>
                            <td style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px 40px; text-align: center;'>
                                <h1 style='margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;'>📧 New Contact Form Message</h1>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style='padding: 40px;'>
                                <p style='margin: 0 0 20px 0; font-size: 16px; color: #374151;'>You have received a new message from the Routa contact form.</p>
                                
                                <!-- Sender Info -->
                                <div style='background-color: #f8fafc; border-left: 4px solid #10b981; padding: 20px; margin-bottom: 30px; border-radius: 8px;'>
                                    <table role='presentation' style='width: 100%;'>
                                        <tr>
                                            <td style='padding: 5px 0;'>
                                                <strong style='color: #1e293b;'>From:</strong>
                                                <span style='color: #64748b;'>" . $name . "</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 5px 0;'>
                                                <strong style='color: #1e293b;'>Email:</strong>
                                                <a href='mailto:" . $email . "' style='color: #10b981; text-decoration: none;'>" . $email . "</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 5px 0;'>
                                                <strong style='color: #1e293b;'>Subject:</strong>
                                                <span style='color: #64748b;'>" . $subject . "</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 5px 0;'>
                                                <strong style='color: #1e293b;'>Date:</strong>
                                                <span style='color: #64748b;'>" . date('F j, Y g:i A') . "</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Message -->
                                <div style='margin-bottom: 30px;'>
                                    <h3 style='margin: 0 0 15px 0; color: #1e293b; font-size: 18px;'>Message:</h3>
                                    <div style='background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;'>
                                        <p style='margin: 0; color: #374151; line-height: 1.6; white-space: pre-wrap;'>" . nl2br($message) . "</p>
                                    </div>
                                </div>
                                
                                <!-- Reply Button -->
                                <table role='presentation' style='width: 100%;'>
                                    <tr>
                                        <td style='text-align: center; padding: 20px 0;'>
                                            <a href='mailto:" . $email . "?subject=Re: " . rawurlencode($subject) . "' style='display: inline-block; padding: 14px 32px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;'>Reply to " . $name . "</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background-color: #f8fafc; padding: 30px 40px; text-align: center; border-top: 1px solid #e5e7eb;'>
                                <p style='margin: 0 0 10px 0; color: #64748b; font-size: 14px;'>This message was sent via the Routa contact form</p>
                                <p style='margin: 0; color: #9ca3af; font-size: 12px;'>
                                    <a href='https://routa.ph' style='color: #10b981; text-decoration: none;'>Routa</a> - Making transportation easier
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";
    
    $mail->Body = $emailBody;
    
    // Alternative plain text version
    $mail->AltBody = "New Contact Form Message\n\n" .
                     "From: $name\n" .
                     "Email: $email\n" .
                     "Subject: $subject\n" .
                     "Date: " . date('F j, Y g:i A') . "\n\n" .
                     "Message:\n" . $message;
    
    // Send email
    $mail->send();
    
    // Log the contact form submission (optional)
    if (EMAIL_LOG_ENABLED) {
        $logEntry = date('Y-m-d H:i:s') . " - Contact form from: $name ($email) - Subject: $subject\n";
        $logFile = __DIR__ . '/../logs/contact_form.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
    ]);
    
} catch (PHPMailerException $e) {
    // PHPMailer specific error
    error_log("PHPMailer Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email. Please try again or contact us directly at ' . SMTP_USERNAME
    ]);
} catch (Exception $e) {
    // General error
    error_log("Contact Form Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
