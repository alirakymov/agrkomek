<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\OutboxPartnerEmail;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: OutboxPartnerEmail
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class OutboxPartnerEmail extends SynapseBaseEntity
{
    public function send(DemandMessage $_message): bool
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Host = gethostbyname($this->smtpHost);
        $mail->SMTPAuth = true; 
        $mail->Username = $this->email;
        $mail->Password = $this->password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom($this->email, $this->name);

        foreach ($_message->to as $address) {
            $mail->addAddress($address['email']);
        }

        $mail->isHTML(true);
        $mail->Subject = strip_tags($_message->subject);
        $mail->Body = $_message->body;
        $mail->AltBody = strip_tags($_message->body);

        return $mail->send();
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        parent::subscribe();
    }

}
