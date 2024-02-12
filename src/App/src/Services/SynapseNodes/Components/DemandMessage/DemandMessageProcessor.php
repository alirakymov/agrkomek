<?php

namespace Qore\App\SynapseNodes\Components\DemandMessage;

use DateTime;
use Ddeboer\Imap\Message;
use Ddeboer\Imap\Message\EmailAddress;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFile;
use Qore\App\Services\Tracking\TrackingInterface;
use Qore\App\SynapseNodes\Components\DemandAttachment\DemandAttachment;
use Qore\App\SynapseNodes\Components\Partner\Partner;
use Qore\App\SynapseNodes\Components\PartnerEmail\PartnerEmail;
use Qore\Collection\CollectionInterface;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\Sanitizer\SanitizerInterface;
use Qore\SynapseManager\SynapseManager;
use Ramsey\Uuid\Uuid;
use Throwable;
use const UPLOAD_ERR_OK;

class DemandMessageProcessor
{

    /**
     * @var \Qore\App\SynapseNodes\Components\PartnerEmail\PartnerEmail
     */
    private PartnerEmail $_partnerEmail;

    /**
     * Constructor
     *
     * @param \Qore\App\SynapseNodes\Components\PartnerEmail\PartnerEmail $_partnerEmail
     */
    public function __construct(PartnerEmail $_partnerEmail)
    {
        $this->_partnerEmail = $_partnerEmail;
    }

    /**
     * Direction
     *
     * @param \Ddeboer\Imap\Message $_message 
     * @param string $_direction 
     * @param \Qore\Collection\CollectionInterface<Partner> $_partners
     * @return void
     */ 
    public function process(Message $_message, string $_direction, CollectionInterface $_partners): void
    {
        /** @var SynapseManager */
        $sm = Qore::service(SynapseManager::class);
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);

        # - Get connection instance
        $connection = $mm->getAdapter()->getDriver()->getConnection();

        $messageId = trim($_message->getId()) ?: sha1($_message->getBodyHtml() ?: $_message->getBodyText());

        $demandMessage = $mm('SM:DemandMessage')
            ->where(['@this.messageId' => $messageId])
            ->one();

        # - Break if current message was processed
        if (! is_null($demandMessage)) {
            return;
        }

        $parentMessage = null;
        if (! empty($_message->getReferences())) {
            $parentMessage = $mm('SM:DemandMessage')->where(function($_where) use ($_message) {
                $_where([
                    '@this.messageId' => $_message->getReferences(),
                    '@this.__idparent' => 0
                ]);
            })->with('demand')->one();
        }

        $emailsMatch = [];

        # - Матчим партнера по родительскому сообщению
        if (! is_null($parentMessage)) {
            if ($parentMessage['direction'] == DemandMessage::OUTBOX) {
                $emailsMatch = Qore::collection($parentMessage['to'])->map(
                    fn($_email) => is_array($_email) ? $_email['email'] : $_email 
                )->toList();
            } else {
                $emailsMatch = [$parentMessage['from']['email']];
            }
        # - Матчим партнера по текущему сообщению
        } else {
            if ($_direction == DemandMessage::OUTBOX) {
                $emailsMatch = Qore::collection($_message->getTo())->map(
                    fn($_email) => $_email->getAddress()
                )->toList();
            } else {
                $emailsMatch = [$_message->getFrom()->getAddress()];
            }
        }

        $_partner = null;
        foreach ($_partners as $partner) {
            foreach ($emailsMatch as $emailMatch) {
                if ($this->applyCriterias($partner, $emailMatch)) {
                    $_partner = $partner;
                    break;
                }
            }
        }

        if (is_null($_partner)) {
            return;
        }

        # - Break if it's outbox email and not in references chain
        if ($_direction == DemandMessage::OUTBOX 
            && (is_null($parentMessage) || ! in_array($parentMessage->messageId, $_message->getReferences()))) {
            return;
        }

        $mm($_partner)->with('outboxEmails')->one();

        # - Update contact information
        if (! empty($_message->getFrom())) {
            $outboxEmail = $_partner->outboxEmails()->firstMatch(['email' => $_message->getFrom()->getAddress()]) 
                ?? $mm('SM:OutboxPartnerEmail', ['email' => $_message->getFrom()->getAddress()]);

            $mm($_partner)->with('outboxEmails')->one();
            $outboxEmail = $_partner->outboxEmails()->firstMatch(['email' => $_message->getFrom()->getAddress()]) 
                ?? $mm('SM:OutboxPartnerEmail', ['email' => $_message->getFrom()->getAddress()]);

            $outboxEmail->name = $_message->getFrom()->getName();

            if ($outboxEmail->isNew()) {
                $outboxEmail->link('partner', $_partner);
            }
            $mm($outboxEmail)->save();
        }

        # - Update contact information from to
        $emailsTo = Qore::collection(array_merge($_message->getTo(), $_message->getCc()));

        try {

            $emailsTo = $emailsTo->filter(function($_emailTo) {
                return $_emailTo->getAddress() !== $this->_partnerEmail['email'];
            })->compile();

            foreach ($emailsTo as $emailTo) {
                $outboxEmail = $_partner->outboxEmails()->firstMatch(['email' => $emailTo->getAddress()]) 
                    ?? $mm('SM:OutboxPartnerEmail', ['email' => $emailTo->getAddress()]);

                if ($outboxEmail->isNew()) {
                    $outboxEmail->link('partner', $_partner);
                }

                if ($outboxEmail->name !== $emailTo->getName() || $outboxEmail->isNew()) {
                    $outboxEmail->name = $emailTo->getName();
                    $mm($outboxEmail)->save();
                }
            }

        } catch (Throwable $e) {
            dump('-------------INCORRECT Message---------------');
            dump([
                'message' => $_message->getRawMessage(),
                'partner' => $_partner,
                'messeage-id' => $_message->getId(),
            ]);
            $emailsTo = [];
        }

        # - Try get demand from parent message
        $demand = ! is_null($parentMessage) ? $parentMessage->demand() : null;
        # - Create new demand if it's null
        if (is_null($demand)) {
            $demand = $mm('SM:Demand', [
                'title' => $_message->getSubject(),
            ]);
            $demand->setPartner($_partner);
            ! is_null($_partner->group()) && $demand->setGroup($_partner->group());
        }

        $attachmentsHash = [];
        foreach ($_message->getAttachments() as $attachment) {
            $attachmentsHash[sha1($demand->unique . $attachment->getDecodedContent())] = $attachment;
        }

        $existsAttachments = $mm('SM:DemandAttachment')->where(['@this.hash' => array_keys($attachmentsHash)])->all();
        foreach ($existsAttachments as $attachment) {
            unset($attachmentsHash[$attachment->hash]);
        }

        $attachments = [];
        foreach ($attachmentsHash as $hash => $attachment) {
            $stream = (new StreamFactory)->createStream($attachment->getDecodedContent());
            $file = new UploadedFile(
                $stream, 
                $stream->getSize(),
                UPLOAD_ERR_OK, 
                $attachment->getFilename(),
                mb_strtolower(sprintf('%s/%s', $attachment->getType(), $attachment->getSubtype()))
            );

            /** @var DemandAttachment */
            $demandAttachment = $mm('SM:DemandAttachment', [
                'hash' => $hash
            ]);

            try {
                $demandAttachment->fromUploadedFile($file);
                $_direction == DemandMessage::INBOX
                    ? $demandAttachment->setInboxInitiator($messageId)
                    : $demandAttachment->setOutboxInitiator($messageId);

                $attachments[] = $demandAttachment;
            } catch (\Throwable $exception) {
                dump($exception);
            }
        }

        $demand->link('attachments', Qore::collection($attachments));

        $messageDate = DateTime::createFromFormat('U', (string)((int)$_message->getHeaders()->get('udate')));
        $messageDate = $messageDate->setTimezone(new \DateTimeZone("UTC"));

        $pattern = '/<blockquote([\s\S]+)?>([\s\S]+)?<\/blockquote>/ism';
        $text = preg_replace($pattern, "", $_message->getBodyHtml() ?: $_message->getBodyText());
        $demandMessage = $mm('SM:DemandMessage', [
            'subject' => $_message->getSubject(),
            'body' => $text,
            'messageDate' => $messageDate,
            'data' => [
                'body' => $_message->getBodyHtml() ?: $_message->getBodyText()
            ], //$_message,
            'messageId' => $messageId,
            'from' => ['name' => $_message->getFrom()->getName(), 'email' => $_message->getFrom()->getAddress()],
            'to' => Qore::collection($emailsTo)->map(
                fn($_email) => ['name' => $_email->getName(), 'email' => $_email->getAddress()]
            )->toList(),
            'replyToMessageId' => $_message->getInReplyTo(),
            'references' => $_message->getReferences(),
            'direction' => $_direction,
            '__idparent' => $parentMessage->id ?? 0
        ]);

        $connection->execute('BEGIN');

        $demandMessage->link('attachments', Qore::collection($attachments));
        $mm($demandMessage)->save();

        $demand->setMessage($demandMessage);
        $demand['message-id'] = $_message->getId();
        $mm($demand)->save();

        $connection->execute('COMMIT');
    }

    /**
     * Check for criterias
     *
     * @param \Qore\App\SynapseNodes\Components\Partner\Partner $_partner 
     * @param string $_email
     *
     * @return bool
     */
    public function applyCriterias(Partner $_partner, string $_email): bool
    {
        $criterias = $_partner->criterias();

        foreach ($criterias as $criteria) {
            if (preg_match($criteria['pattern'], $_email)) {
                return true;
            }
        }

        return false;
    }

}
