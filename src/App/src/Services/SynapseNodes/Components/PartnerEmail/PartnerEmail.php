<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\PartnerEmail;

use PHPMailer\PHPMailer\PHPMailer;
use Qore\App\SynapseNodes\Components\DemandMessage\DemandMessage;
use Qore\App\SynapseNodes\Components\User\User;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: PartnerEmail
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class PartnerEmail extends SynapseBaseEntity
{
    /**
     * Send mail
     *
     * @param DemandMessage|array $_message 
     *
     * @throws Exception 
     *
     * @return array 
     */
    public function send($_message, User $_user): array
    {
        if ($_message instanceof DemandMessage) {
            $_message['to'] = Qore::collection($_message['to'] ?? [])
                ->extract('email')
                ->toList();
        }

        if (! isset($_message['to']) || ! $_message['to'] || ! is_array($_message['to'])) {
            throw new Exception('Unknown element `to` in message structure');
        }

        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        $replyMessage = null;
        if (isset($_message['replyMessageId']) && $_message['replyMessageId']) {
            $replyMessage = $mm('SM:DemandMessage')->where(['@this.messageId' => $_message['replyMessageId']])->one();
        }

        $replace = [
            '{{ user.firstname }}' => $_user->getFirstname(),
            '{{ user.lastname }}' => $_user->getLastname(),
            '{{ user.fullname }}' => $_user->fullname,
            '{{ content }}' => $_message['body'],
            '{{ from }}' => $this->email,
            '{{ reply.body }}' => $replyMessage['data']['body'] ?? '',
        ];

        if (isset($this['emailTemplate'])) {
            $_message['body'] = str_replace(array_keys($replace), array_values($replace), $this['emailTemplate']);
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Host = gethostbyname($this->smtpHost);
        // $mail->Host = 'ssl://' . $this->smtpHost;
        // $mail->Port = (int)$this->smtpPort;
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

        foreach ($_message['to'] as $address) {
            $mail->addAddress($address);
        }

        if (isset($_message['replyMessageId']) && $_message['replyMessageId']) {
            $mail->addCustomHeader('In-Reply-To', $_message['replyMessageId']);
            $mail->addCustomHeader('References', $_message['replyMessageId']);
            $mail->Subject = str_replace('Re: ', '',$mail->Subject);
        }

        $mail->isHTML(true);
        $mail->Subject = strip_tags($_message['subject'] ?? '');
        $mail->Body = $_message['body'];
        $mail->AltBody = strip_tags($_message['body'] ?? '');

        if ($_message instanceof DemandMessage && $_message->attachments()->count()) {
            foreach ($_message->attachments() as $attachment) {
                $mail->addAttachment(
                    $attachment->file()->getPath(), 
                    $attachment['filename'],
                    PHPMailer::ENCODING_BASE64,
                    $attachment['type']
                );
            }
        }
        return [$mail->send(), $mail];
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
