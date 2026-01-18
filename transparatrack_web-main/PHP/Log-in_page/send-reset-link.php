<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
include('db_connect.php'); // Uses PDO ($pdo)

// PHPMailer setup
require __DIR__ . '/../../phpmailer/src/Exception.php';
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // ==========================
    // RECIPIENT CHECK (Only RTU)
    // ==========================
    // 'i' modifier makes it case-insensitive (accepts @RTU.EDU.PH or @rtu.edu.ph)
    if (!preg_match("/@rtu\.edu\.ph$/i", $email)) {
        header("Location: forgot-password.php?error=invalid_email");
        exit;
    }

    // Verify user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {

        // Generate secure token + expiry 15 mins
        $token = bin2hex(random_bytes(50));

        $update = $pdo->prepare("
            UPDATE users 
            SET reset_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            WHERE Email = ?
        ");
        $update->execute([$token, $email]);

        $resetLink = "http://localhost/transparatrack_web/PHP/Log-in_page/reset-password.php?token=" . urlencode($token);

        // PHPMailer
        $mail = new PHPMailer(true);

        try {
            // ==========================
            // SENDER SETTINGS (Gmail)
            // ==========================
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'transparatrack@gmail.com'; 
            $mail->Password   = 'lpmc sbvp ytqq sfse'; // Spaces removed for better compatibility
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('transparatrack@gmail.com', 'TransparaTrack Support');
            $mail->addAddress($email);

            // ==========================
            // EMBEDDED LOGO FIX
            // ==========================
            $logoPath = __DIR__ . '/../media/logo.jpg'; 
            
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'headerlogo', 'logo.jpg');
            } else {
                error_log("CRITICAL PHPMailer Error: Logo file not found at: " . $logoPath);
            }

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - TransparaTrack';

            // ==========================
            // YOUR ORIGINAL HTML DESIGN
            // ==========================
            $mail->Body = "
            <table width='100%' border='0' cellpadding='0' cellspacing='0' style='font-family: Arial, sans-serif;'>
            <tr>
                <td align='center'>
                    <table width='100%' border='0' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); border: 1px solid #ddd;'>
                        <tr>
                            <td style='background-color: #EBE9E9; padding: 25px 40px 15px 40px; border-radius: 10px 10px 0 0;'>
                                <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td width='50' style='width: 50px; vertical-align: top;'>
                                            <img src='cid:headerlogo' width='50' height='50' alt='TransparaTrack Logo' style='display:block;'>
                                        </td>
                                        <td width='10' style='width: 10px;'>&nbsp;</td>
                                        <td style='vertical-align: top;'>
                                            <span style='color:#CE2323; font-size:30px; font-weight:700; display:block; line-height:1.2; font-family: Arial, sans-serif;'>
                                                TransparaTrack
                                            </span>
                                            <span style='color:#27294A; font-size:16px; font-weight:500; display:block; font-family: Arial, sans-serif; margin-top:0;'>
                                                See the process, Start the progress
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <div style='height:4px; background: linear-gradient(to right, #4149B7, #CE2323, #F3B900); border-radius:4px; margin-top:15px;'></div>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 40px;'>
                                <table width='100%' border='0' cellpadding='0' cellspacing='0'>
                                    <tr>
                                        <td style='font-size:16px; color:#333;'>
                                            <h2 style='color:#4149B7; margin-top:0; margin-bottom:20px; font-weight:bold; font-size:24px;'>Password Reset Request</h2>
                                            <p>Greetings,</p>
                                            <p>We have received a request to reset the password associated with your <b>TransparaTrack</b> account.</p>
                                            <p>To proceed with the password reset process, please click the secure link below:</p>
                                            <p style='text-align:center; margin:25px 0;'>
                                                <a href='$resetLink' style='background:#4149B7;color:white;padding:12px 25px;text-decoration:none;border-radius:5px; display:inline-block; font-weight:bold; font-size:16px;'>Reset Password</a>
                                            </p>
                                            <p>For security purposes, this link will expire in <b>15 minutes</b>.</p>
                                            <hr style='margin:30px 0; border:none; border-top:1px solid #eaeaea;'>
                                            <p style='font-size:12px;color:#777;'>If you did not initiate this request, please disregard this email. No changes will be made to your account.</p>
                                            <p style='font-size:14px;color:#333;'>Sincerely,<br><b>The TransparaTrack Support Team</b></p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </table>
            ";

            $mail->send();
            header("Location: check-email.php");
            exit;

        } catch (Exception $e) {
            // =========================================================
            // DEBUG MODE (TEMPORARY)
            // I have commented out the redirect so you can see the ERROR.
            // Once it works, you can uncomment the header() line.
            // =========================================================
            
            echo "<h1>Message could not be sent.</h1>";
            echo "<p>Mailer Error: <b>" . $mail->ErrorInfo . "</b></p>";
            
            // header("Location: forgot-password.php?error=mailer_error"); // UNCOMMENT THIS ONLY WHEN IT WORKS
            exit;
        }

    } else {
        header("Location: forgot-password.php?error=email_not_found");
        exit;
    }

} else {
    header("Location: forgot-password.php?error=invalid_request");
    exit;
}
?>