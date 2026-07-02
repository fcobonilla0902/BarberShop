<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    private function configurarMailer() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = trim($_ENV['EMAIL_PASS'], "'\"");
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME'] ?? 'BarberShop');
        $mail->addAddress($this->email, $this->nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    public function enviarConfirmacion() {
        $mail = $this->configurarMailer();
        $mail->Subject = 'Confirma tu cuenta en BarberShop';

        $url = $_ENV['APP_URL'] . "/confirmar-cuenta?token=" . $this->token;

        $contenido = "<html>";
        $contenido .= "<p><strong>Hola " . htmlspecialchars($this->nombre) . "</strong>, has creado tu cuenta en BarberShop.</p>";
        $contenido .= "<p>Para confirmar tu cuenta presiona el siguiente enlace:</p>";
        $contenido .= "<p><a href='" . $url . "'>Confirmar cuenta</a></p>";
        $contenido .= "<p>Si no solicitaste esta cuenta, puedes ignorar este mensaje.</p>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }

    public function enviarInstrucciones() {
        $mail = $this->configurarMailer();
        $mail->Subject = 'Recupera tu contraseña en BarberShop';

        $contenido = "<html>";
        $contenido .= "<p><strong>Hola " . htmlspecialchars($this->nombre) . "</strong>, solicitaste recuperar tu contraseña.</p>";
        $contenido .= "<p>Esta funcionalidad queda preparada para una siguiente etapa.</p>";
        $contenido .= "</html>";

        $mail->Body = $contenido;
        $mail->send();
    }
}
