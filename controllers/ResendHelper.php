<?php
require_once __DIR__ . '/../config/config.php';

class ResendHelper {
    private $apiKey;

    public function __construct() {
        $this->apiKey = RESEND_API_KEY;
    }

    public function sendPasswordReset($to, $resetLink) {
        $url = 'https://api.resend.com/emails';
        
        $subject = 'Reset Password - ' . APP_NAME;
        $html = "
        <div style='font-family: sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; rounded: 12px;'>
            <h2 style='color: #093320;'>Halo!</h2>
            <p>Anda menerima email ini karena kami menerima permintaan reset password untuk akun CattlePro Anda.</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' style='background-color: #00A166; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Reset Password</a>
            </div>
            <p>Link ini akan kadaluarsa dalam 1 jam.</p>
            <p>Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 12px; color: #64748B;'>Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ke browser Anda:</p>
            <p style='font-size: 12px; color: #64748B;'>{$resetLink}</p>
        </div>";

        $payload = [
            'from' => EMAIL_FROM,
            'to' => [$to],
            'subject' => $subject,
            'html' => $html
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 || $httpCode === 201;
    }
}
?>
