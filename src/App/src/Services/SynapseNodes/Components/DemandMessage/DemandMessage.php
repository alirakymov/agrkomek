<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\DemandMessage;

use Qore\Qore;
use Qore\Sanitizer\SanitizerInterface;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;

/**
 * Class: DemandMessage
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class DemandMessage extends SynapseBaseEntity
{
    /*
     * @var string - type of direction 
     */
    const INBOX = 'inbox';

    /*
     * @var string - type of direction 
     */
    const OUTBOX = 'outbox';

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();

            $entity->data = serialize($entity->data);

            if (isset($entity['messageDate']) && is_object($entity->messageDate)) {
                $entity->messageDate = $entity->messageDate->format('Y-m-d H:i:s');
            }

            /** @var SanitizerInterface */
            $sanitizer = Qore::service(SanitizerInterface::class);

            $entity->subject = $sanitizer->sanitize($entity->subject ?? '');
            $entity->body = $sanitizer->sanitize($entity->body ?? '');

            $entity->replyToMessageId ??= [];
            $entity->replyToMessageId = json_encode($entity->replyToMessageId, JSON_UNESCAPED_UNICODE);

            $entity->from ??= [];
            $entity->from = json_encode($entity->from, JSON_UNESCAPED_UNICODE);

            $entity->to ??= [];
            $entity->to = json_encode($entity->to, JSON_UNESCAPED_UNICODE);

            $entity->references ??= [];
            $entity->references = json_encode($entity->references, JSON_UNESCAPED_UNICODE);
        });

        static::after('save', $func = function($_event) {
            $entity = $_event->getTarget();

            if (is_string($entity->data)) {
                $entity->data = @unserialize($entity->data);
            }

            if (isset($entity['messageDate']) && is_string($entity['messageDate'])) {
                $entity['messageDate'] = new \DateTime($entity['messageDate']);
            }

            $entity->replyToMessageId = is_string($entity->replyToMessageId) 
                ? json_decode($entity->replyToMessageId, true) 
                : $entity->replyToMessageId;

            $entity->from = is_string($entity->from) 
                ? json_decode($entity->from, true) 
                : $entity->from;

            $entity->to = is_string($entity->to) 
                ? json_decode($entity->to, true) 
                : $entity->to;

            $entity->references = is_string($entity->references) 
                ? json_decode($entity->references, true) 
                : $entity->references;
        });

        static::after('initialize', $func);

        parent::subscribe();
    }

}
