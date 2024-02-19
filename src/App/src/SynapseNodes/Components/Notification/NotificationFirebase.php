<?php

namespace Qore\App\SynapseNodes\Components\Notification;

use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\ServerError;
use Kreait\Firebase\Exception\Messaging\ServerUnavailable;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Qore\ORM\ModelManager;
use Qore\Qore;
use Qore\QueueManager\JobAbstract;
use Qore\QueueManager\JobInterface;
use Qore\QueueManager\JobTrait;
use Qore\SynapseManager\SynapseManager;

/**
 * Class: NotificationFirebase
 *
 * @see JobInterface
 * @see JobAbstract
 */
class NotificationFirebase extends JobAbstract implements JobInterface
{
    use JobTrait;

    protected static $name = null;
    protected static $persistence = false;
    protected static $acknowledgement = true;
    protected static $workersNumber = 1;

    /**
     * process task
     *
     * @return bool 
     */
    public function process() : bool
    {
        /** @var ModelManager */
        $mm = Qore::service(ModelManager::class);
        /** @var SynapseManager */
        $sm = Qore::service(SynapseManager::class);

        # - Reconnect to mysql server
        $connection = $mm->getAdapter()->getDriver()->getConnection();
        $connection->connect();

        $notification = $mm('SM:Notification')->where(['@this.id' => $this->task['id']])->one();
        if (! $notification) {
            return true;
        }

        $user = $mm('SM:User')->with('devices')->where(['@this.id' => $notification->idUser])->one();

        try {
            $serviceAccount = PROJECT_DATA_PATH . '/firebase/google-services.json';
            $factory = (new Factory)->withServiceAccount($serviceAccount);
            $firebase = $factory->createMessaging();

            $notify = Notification::create($notification->title, $notification->message);

            foreach ($user->devices() as $device) {
                $message = CloudMessage::withTarget('token', $device['token'])
                    ->withNotification($notify)
                    ->withData(array_merge($notification['data'], ['event' => $notification['event']]))
                    ->withHighestPossiblePriority();
                $result = $firebase->send($message);
            }
        } catch (\Throwable $t) {
            dump($t);
        }

        $connection->disconnect();

        return true;
    }

}
