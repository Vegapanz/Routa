<?php
/**
 * Email Helper - Send emails using PHP's mail() function or SMTP
 * For production, consider using PHPMailer with SMTP for better reliability
 */

// Load email configuration
require_once __DIR__ . '/email_config.php';

/**
 * Send a simple email
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (HTML supported)
 * @param string $fromEmail Sender email
 * @param string $fromName Sender name
 * @return bool Success status
 */
function sendEmail($to, $subject, $message, $fromEmail = null, $fromName = null) {
    // Check if email is enabled
    if (!EMAIL_ENABLED) {
        error_log("Email sending is disabled in configuration");
        return false;
    }
    
    // Use default values from config if not provided
    $fromEmail = $fromEmail ?? EMAIL_FROM_ADDRESS;
    $fromName = $fromName ?? EMAIL_FROM_NAME;
    
    // Choose sending method based on configuration
    if (EMAIL_METHOD === 'smtp') {
        return sendEmailViaSMTP($to, $subject, $message, $fromEmail, $fromName);
    } else {
        return sendEmailViaMailFunction($to, $subject, $message, $fromEmail, $fromName);
    }
}

/**
 * Send email using PHPMailer with SMTP
 */
function sendEmailViaSMTP($to, $subject, $message, $fromEmail, $fromName) {
    // Check if PHPMailer is available
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    
    if (!file_exists($autoloadPath)) {
        error_log("PHPMailer autoload not found at: $autoloadPath");
        error_log("Falling back to mail() function");
        // Fallback to mail() function
        return sendEmailViaMailFunction($to, $subject, $message, $fromEmail, $fromName);
    }
    
    require_once $autoloadPath;
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        
        // Enable debug output for troubleshooting
        // $mail->SMTPDebug = 2; // Uncomment to see detailed SMTP communication
        
        // Disable SSL verification for local development (remove in production)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Set timeout
        $mail->Timeout = 30;
        
        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($fromEmail, $fromName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message); // Plain text version
        
        // Send email
        $mail->send();
        
        // Log success
        if (EMAIL_LOG_ENABLED) {
            $logMessage = sprintf(
                "[%s] Email to: %s | Subject: %s | Status: SUCCESS (SMTP)",
                date('Y-m-d H:i:s'),
                $to,
                $subject
            );
            error_log($logMessage);
        }
        
        return true;
        
    } catch (Exception $e) {
        // Log error with more details
        $errorMsg = isset($mail) && isset($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
        error_log("PHPMailer Error: " . $errorMsg);
        error_log("Exception: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        
        if (EMAIL_LOG_ENABLED) {
            $logMessage = sprintf(
                "[%s] Email to: %s | Subject: %s | Status: FAILED (SMTP) - %s",
                date('Y-m-d H:i:s'),
                $to,
                $subject,
                $errorMsg
            );
            error_log($logMessage);
        }
        
        // Try fallback to mail() function
        error_log("Attempting fallback to mail() function...");
        return sendEmailViaMailFunction($to, $subject, $message, $fromEmail, $fromName);
    }
}

/**
 * Send email using PHP's mail() function
 */
function sendEmailViaMailFunction($to, $subject, $message, $fromEmail, $fromName) {
    // Set headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>" . "\r\n";
    $headers .= "Reply-To: {$fromEmail}" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    $success = mail($to, $subject, $message, $headers);
    
    // Log email attempt
    if (EMAIL_LOG_ENABLED) {
        $logMessage = sprintf(
            "[%s] Email to: %s | Subject: %s | Status: %s (mail)",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $success ? 'SUCCESS' : 'FAILED'
        );
        error_log($logMessage);
    }
    
    return $success;
}

/**
 * Send driver application thank you email
 * 
 * @param string $email Driver's email address
 * @param string $firstName Driver's first name
 * @param string $lastName Driver's last name
 * @param int $applicationId Application ID
 * @return bool Success status
 */
function sendDriverApplicationThankYou($email, $firstName, $lastName, $applicationId) {
    $fullName = trim($firstName . ' ' . $lastName);
    $subject = "Thank You for Your Driver Application - Routa";
    
    // Create HTML email message
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 30px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content {
                background: #f9fafb;
                padding: 30px;
                border-radius: 0 0 10px 10px;
            }
            .card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .highlight {
                background: #fef3c7;
                padding: 15px;
                border-left: 4px solid #f59e0b;
                margin: 20px 0;
                border-radius: 4px;
            }
            .steps {
                list-style: none;
                padding: 0;
                counter-reset: step-counter;
            }
            .steps li {
                counter-increment: step-counter;
                padding: 10px 0;
                padding-left: 40px;
                position: relative;
            }
            .steps li::before {
                content: counter(step-counter);
                position: absolute;
                left: 0;
                background: #10b981;
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                text-align: center;
                line-height: 28px;
                font-weight: bold;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #6b7280;
                font-size: 14px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: #10b981;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                margin: 20px 0;
                font-weight: bold;
            }
            .info-box {
                background: #dbeafe;
                border: 1px solid #3b82f6;
                padding: 15px;
                border-radius: 6px;
                margin: 15px 0;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>🚗 Thank You for Applying!</h1>
        </div>
        
        <div class='content'>
            <p style='font-size: 18px;'><strong>Dear {$fullName},</strong></p>
            
            <p>Thank you for your interest in becoming a driver partner with <strong>Routa</strong>! We're excited to review your application.</p>
            
            <div class='card'>
                <h2 style='color: #10b981; margin-top: 0;'>Application Received ✅</h2>
                <p>Your application has been successfully submitted with the following details:</p>
                <p><strong>Application ID:</strong> #{$applicationId}<br>
                <strong>Name:</strong> {$fullName}<br>
                <strong>Email:</strong> {$email}<br>
                <strong>Date Submitted:</strong> " . date('F j, Y') . "</p>
            </div>
            
            <div class='highlight'>
                <strong>⏰ What happens next?</strong>
                <p style='margin: 10px 0 0 0;'>Our team will carefully review your application and submitted documents. This process typically takes <strong>2-3 business days</strong>.</p>
            </div>
            
            <div class='card'>
                <h3 style='color: #059669; margin-top: 0;'>Application Process:</h3>
                <ol class='steps'>
                    <li><strong>Document Verification</strong> - We'll review all your submitted documents</li>
                    <li><strong>Background Check</strong> - Standard verification process for all drivers</li>
                    <li><strong>Approval Notification</strong> - You'll receive an email about your application status</li>
                    <li><strong>Driver Orientation</strong> - Complete our online orientation program</li>
                    <li><strong>Start Earning</strong> - Download the driver app and start accepting rides!</li>
                </ol>
            </div>
            
            <div class='info-box'>
                <strong>📱 While You Wait:</strong>
                <ul style='margin: 10px 0;'>
                    <li>Make sure your documents are valid and up to date</li>
                    <li>Ensure your vehicle is in good condition</li>
                    <li>Keep your phone charged and GPS enabled</li>
                    <li>Check your email regularly for updates</li>
                </ul>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <p style='margin-bottom: 10px;'><strong>Questions about your application?</strong></p>
                <p style='margin: 5px 0;'>📧 Email: <a href='mailto:" . EMAIL_DRIVERS_ADDRESS . "' style='color: #10b981;'>" . EMAIL_DRIVERS_ADDRESS . "</a></p>
                <p style='margin: 5px 0;'>📞 Phone: <a href='tel:" . str_replace(' ', '', EMAIL_CONTACT_PHONE) . "' style='color: #10b981;'>" . EMAIL_CONTACT_PHONE . "</a></p>
            </div>
            
            <div class='card' style='background: #f0fdf4; border: 2px solid #10b981;'>
                <p style='margin: 0; text-align: center;'><strong>💰 Why Drive with Routa?</strong></p>
                <p style='margin: 10px 0; text-align: center;'>• Earn up to ₱25,000/month<br>
                • Flexible schedule<br>
                • Weekly payouts<br>
                • 24/7 support<br>
                • Keep 85% of your earnings</p>
            </div>
            
            <p style='margin-top: 30px;'>We appreciate your patience and look forward to welcoming you to the Routa driver community!</p>
            
            <p><strong>Best regards,</strong><br>
            The Routa Team</p>
        </div>
        
        <div class='footer'>
            <p>© " . date('Y') . " Routa. All rights reserved.</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p style='margin-top: 10px; font-size: 12px;'>
                Made with ❤️ in the Philippines
            </p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Send driver application approval email
 * 
 * @param string $email Driver's email address
 * @param string $firstName Driver's first name
 * @param string $lastName Driver's last name
 * @param string $tricycleNumber Assigned tricycle number
 * @param string $plateNumber Driver's plate number
 * @return bool Success status
 */
function sendDriverApprovalEmail($email, $firstName, $lastName, $tricycleNumber, $plateNumber) {
    $fullName = trim($firstName . ' ' . $lastName);
    $subject = "Congratulations! Your Driver Application is Approved - Routa";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 40px 30px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 32px;
            }
            .header .emoji {
                font-size: 48px;
                margin-bottom: 10px;
            }
            .content {
                background: #f9fafb;
                padding: 30px;
                border-radius: 0 0 10px 10px;
            }
            .card {
                background: white;
                padding: 25px;
                border-radius: 8px;
                margin: 20px 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .success-box {
                background: #d1fae5;
                border: 2px solid #10b981;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                text-align: center;
            }
            .info-row {
                display: table;
                width: 100%;
                margin: 10px 0;
            }
            .info-label {
                font-weight: bold;
                color: #059669;
                width: 180px;
                display: table-cell;
            }
            .info-value {
                display: table-cell;
            }
            .steps {
                list-style: none;
                padding: 0;
                counter-reset: step-counter;
            }
            .steps li {
                counter-increment: step-counter;
                padding: 15px 0;
                padding-left: 50px;
                position: relative;
                border-bottom: 1px solid #e5e7eb;
            }
            .steps li:last-child {
                border-bottom: none;
            }
            .steps li::before {
                content: counter(step-counter);
                position: absolute;
                left: 0;
                background: #10b981;
                color: white;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                text-align: center;
                line-height: 32px;
                font-weight: bold;
                font-size: 18px;
            }
            .button {
                display: inline-block;
                padding: 15px 40px;
                background: #10b981;
                color: white !important;
                text-decoration: none;
                border-radius: 8px;
                margin: 20px 0;
                font-weight: bold;
                font-size: 16px;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #6b7280;
                font-size: 14px;
            }
            .highlight {
                background: #fef3c7;
                padding: 15px;
                border-left: 4px solid #f59e0b;
                margin: 15px 0;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <div class='emoji'>🎉</div>
            <h1>Application Approved!</h1>
            <p style='margin: 10px 0 0 0; font-size: 18px;'>Welcome to the Routa Driver Family</p>
        </div>
        
        <div class='content'>
            <p style='font-size: 18px;'><strong>Dear {$fullName},</strong></p>
            
            <div class='success-box'>
                <h2 style='color: #10b981; margin-top: 0;'>✅ Your Application Has Been Approved!</h2>
                <p style='font-size: 16px; margin: 10px 0 0 0;'>Congratulations! You're now officially a Routa driver partner.</p>
            </div>
            
            <p>We're excited to welcome you to our growing community of professional drivers. Your application has been reviewed and approved!</p>
            
            <div class='card'>
                <h3 style='color: #059669; margin-top: 0;'>📋 Your Driver Information</h3>
                <div class='info-row'>
                    <div class='info-label'>Driver Name:</div>
                    <div class='info-value'>{$fullName}</div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Email:</div>
                    <div class='info-value'>{$email}</div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Tricycle Number:</div>
                    <div class='info-value'><strong>{$tricycleNumber}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Plate Number:</div>
                    <div class='info-value'><strong>{$plateNumber}</strong></div>
                </div>
                <div class='info-row'>
                    <div class='info-label'>Status:</div>
                    <div class='info-value'><span style='color: #10b981; font-weight: bold;'>✓ APPROVED</span></div>
                </div>
            </div>
            
            <div class='highlight'>
                <strong>🎯 Next Steps to Start Earning:</strong>
            </div>
            
            <div class='card'>
                <ol class='steps'>
                    <li>
                        <strong>Download the Routa Driver App</strong><br>
                        <small>Available on Google Play Store for Android devices</small>
                    </li>
                    <li>
                        <strong>Login to Your Account</strong><br>
                        <small>Use the email and password from your application</small>
                    </li>
                    <li>
                        <strong>Complete Driver Orientation</strong><br>
                        <small>Watch the training videos and complete the quiz (15 minutes)</small>
                    </li>
                    <li>
                        <strong>Go Online</strong><br>
                        <small>Toggle your status to 'Available' and start receiving ride requests</small>
                    </li>
                    <li>
                        <strong>Start Earning!</strong><br>
                        <small>Accept rides, complete trips, and earn money on your schedule</small>
                    </li>
                </ol>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='#' class='button'>Download Driver App</a>
            </div>
            
            <div class='card' style='background: #f0fdf4; border: 2px solid #10b981;'>
                <h3 style='color: #059669; margin-top: 0; text-align: center;'>💰 Driver Benefits</h3>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li><strong>Earn up to ₱25,000/month</strong> - Work at your own pace</li>
                    <li><strong>Keep 85% of earnings</strong> - Fair commission structure</li>
                    <li><strong>Weekly payouts</strong> - Fast and reliable payments</li>
                    <li><strong>Flexible schedule</strong> - Drive whenever you want</li>
                    <li><strong>24/7 support</strong> - We're always here to help</li>
                    <li><strong>Insurance coverage</strong> - Protected while on duty</li>
                </ul>
            </div>
            
            <div class='card'>
                <h3 style='color: #059669; margin-top: 0;'>📱 Need Help Getting Started?</h3>
                <p><strong>Driver Support Team:</strong></p>
                <p style='margin: 5px 0;'>📧 Email: <a href='mailto:" . EMAIL_DRIVERS_ADDRESS . "' style='color: #10b981;'>" . EMAIL_DRIVERS_ADDRESS . "</a></p>
                <p style='margin: 5px 0;'>📞 Phone: <a href='tel:" . str_replace(' ', '', EMAIL_CONTACT_PHONE) . "' style='color: #10b981;'>" . EMAIL_CONTACT_PHONE . "</a></p>
                <p style='margin-top: 15px;'><strong>Available:</strong> Monday - Sunday, 8:00 AM - 10:00 PM</p>
            </div>
            
            <div class='highlight'>
                <strong>⚠️ Important Reminders:</strong>
                <ul style='margin: 10px 0 0 0;'>
                    <li>Keep your documents valid and updated</li>
                    <li>Maintain your vehicle in good condition</li>
                    <li>Always be professional and courteous to passengers</li>
                    <li>Follow traffic rules and drive safely</li>
                    <li>Keep the app updated to the latest version</li>
                </ul>
            </div>
            
            <p style='margin-top: 30px; font-size: 16px;'>Welcome aboard! We're thrilled to have you as part of the Routa family. Let's make every ride safe, comfortable, and memorable.</p>
            
            <p><strong>Best regards,</strong><br>
            The Routa Team</p>
        </div>
        
        <div class='footer'>
            <p>© " . date('Y') . " Routa. All rights reserved.</p>
            <p style='margin-top: 10px; font-size: 12px;'>
                Made with ❤️ in the Philippines
            </p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Send driver application rejection email
 * 
 * @param string $email Driver's email address
 * @param string $firstName Driver's first name
 * @param string $lastName Driver's last name
 * @param string $reason Reason for rejection (optional)
 * @return bool Success status
 */
function sendDriverRejectionEmail($email, $firstName, $lastName, $reason = '') {
    $fullName = trim($firstName . ' ' . $lastName);
    $subject = "Driver Application Update - Routa";
    
    $defaultReason = "After careful review of your application, we regret to inform you that we are unable to approve your driver application at this time. This decision may be based on various factors including document verification, background check results, or current capacity.";
    $rejectionMessage = !empty($reason) ? $reason : $defaultReason;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
                color: white;
                padding: 30px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content {
                background: #f9fafb;
                padding: 30px;
                border-radius: 0 0 10px 10px;
            }
            .card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .info-box {
                background: #fef3c7;
                border: 1px solid #f59e0b;
                padding: 15px;
                border-radius: 6px;
                margin: 15px 0;
            }
            .footer {
                text-align: center;
                padding: 20px;
                color: #6b7280;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Application Update</h1>
        </div>
        
        <div class='content'>
            <p style='font-size: 18px;'><strong>Dear {$fullName},</strong></p>
            
            <p>Thank you for your interest in becoming a driver partner with Routa.</p>
            
            <div class='card'>
                <p>{$rejectionMessage}</p>
            </div>
            
            <div class='info-box'>
                <strong>📋 Can I Reapply?</strong>
                <p style='margin: 10px 0 0 0;'>Yes! You may reapply after <strong>30 days</strong>. Please ensure all your documents are valid and meet our requirements before submitting a new application.</p>
            </div>
            
            <div class='card'>
                <h3 style='color: #059669; margin-top: 0;'>Requirements Reminder:</h3>
                <ul>
                    <li>Valid driver's license (at least 2 years)</li>
                    <li>Clean driving record</li>
                    <li>Valid government ID</li>
                    <li>Vehicle registration (OR/CR)</li>
                    <li>Valid franchise/TODA permit</li>
                    <li>Comprehensive insurance</li>
                    <li>Barangay clearance</li>
                    <li>2x2 ID photo</li>
                </ul>
            </div>
            
            <p>If you have any questions about your application or would like feedback on how to improve your chances for future applications, please don't hesitate to contact us.</p>
            
            <div class='card'>
                <p><strong>Contact Us:</strong></p>
                <p style='margin: 5px 0;'>📧 Email: <a href='mailto:" . EMAIL_DRIVERS_ADDRESS . "' style='color: #10b981;'>" . EMAIL_DRIVERS_ADDRESS . "</a></p>
                <p style='margin: 5px 0;'>📞 Phone: <a href='tel:" . str_replace(' ', '', EMAIL_CONTACT_PHONE) . "' style='color: #10b981;'>" . EMAIL_CONTACT_PHONE . "</a></p>
            </div>
            
            <p>We appreciate your interest in Routa and wish you all the best.</p>
            
            <p><strong>Best regards,</strong><br>
            The Routa Team</p>
        </div>
        
        <div class='footer'>
            <p>© " . date('Y') . " Routa. All rights reserved.</p>
            <p style='margin-top: 10px; font-size: 12px;'>
                Made with ❤️ in the Philippines
            </p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Send driver application status update email
 * 
 * @param string $email Driver's email address
 * @param string $firstName Driver's first name
 * @param string $status New status (approved, rejected, under_review)
 * @param string $message Additional message
 * @return bool Success status
 */
function sendDriverApplicationStatusUpdate($email, $firstName, $status, $message = '') {
    $statusTitles = [
        'approved' => 'Application Approved! 🎉',
        'rejected' => 'Application Update',
        'under_review' => 'Application Under Review'
    ];
    
    $subject = $statusTitles[$status] ?? 'Application Status Update';
    
    $emailMessage = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: #10b981;
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 8px 8px 0 0;
            }
            .content {
                background: #f9fafb;
                padding: 30px;
                border-radius: 0 0 8px 8px;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>{$subject}</h1>
        </div>
        <div class='content'>
            <p>Dear {$firstName},</p>
            <p>{$message}</p>
            <p>Best regards,<br>The Routa Team</p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, "Routa Driver Application - {$subject}", $emailMessage);
}
?>
