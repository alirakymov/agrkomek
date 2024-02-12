<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\DemandAttachment;

use Laminas\Diactoros\UploadedFile;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Qore\SynapseManager\SynapseManager;
use Qore\UploadManager\UploadedFile as UploadManagerUploadedFile;
use Qore\UploadManager\UploadManager;

/**
 * Class: DemandAttachments
 *
 * @see SynapseBaseEntity
 */
class DemandAttachment extends SynapseBaseEntity
{
    /**
     * @var string - initiator type for inbox messages
     */
    const INITIATOR_INBOX = 'inbox';

    /**
     * @var string - initiator type for outbox messages
     */
    const INITIATOR_OUTBOX = 'outbox';

    /**
     * Build prive routes to attachment
     *
     * @return void
     */
    public function buildRoutes(): void
    {
        if ($this->isNew()) {
            return;
        }

        /** @var SynapseManager */
        $sm = Qore::service(SynapseManager::class);

        $agentService = $sm('DemandAttachment:Agent');

        $this['routes'] = [
            'download' => Qore::url($agentService->getRouteName('download'), [
                'id' => $this['id'],
            ]),
        ];
    }

    /**
     * From uploaded file
     *
     * @param \Laminas\Diactoros\UploadedFile $_file 
     *
     * @return
     */
    public function fromUploadedFile(UploadedFile $_file)
    {
        $this['attachment'] = Qore::service(UploadManager::class)->saveFile($_file, false);
        $this['filename'] = $_file->getClientFilename();
        $this['type'] = $_file->getClientMediaType();
        $this['size'] = $_file->getSize();
    }

    /**
     * Set inbox initiator type and identifier
     *
     * @param string $_messageId 
     *
     * @return DemandAttachment
     */
    public function setInboxInitiator(string $_messageId): DemandAttachment
    {
        $this['initiator'] = static::INITIATOR_INBOX;
        $this['initiatorId'] = $_messageId;
        return $this;
    }

    /**
     * Set outbox initiator type and identifier
     *
     * @param string $_messageId 
     *
     * @return DemandAttachment
     */
    public function setOutboxInitiator(string $_messageId): DemandAttachment
    {
        $this['initiator'] = static::INITIATOR_OUTBOX;
        $this['initiatorId'] = $_messageId;
        return $this;
    }

    /**
     * Get file abstract layer object
     *
     * @return UploadManagerUploadedFile|null
     */
    public function file(): ?UploadManagerUploadedFile
    {
        return isset($this['attachment']) 
            ? Qore::service(UploadManager::class)->getFile($this['attachment']) 
            : null;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        $f = function($_event) {
            $_event->getTarget()->buildRoutes();
            return true;
        };

        static::after('initialize', $f);
        static::after('save', $f);

        parent::subscribe();
    }

}
