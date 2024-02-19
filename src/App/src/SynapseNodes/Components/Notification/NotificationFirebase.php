<?php

namespace Qore\App\SynapseNodes\Components\Notification;

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
        $mm = Qore::service(ModelManager::class);
        $sm = Qore::service(SynapseManager::class);

        $notification = $mm('SM:Notification')->where(['@this.id' => $this->task['id']])->one();
        if (! $notification) {
            return true;
        }

        $user = $mm('SM:User')->with('devices')->where(['@this.id' => $notification->idUser])->one();

        $serviceAccount = PROJECT_DATA_PATH . '/firebase/google-services.json';
        $firebase = (new Factory)->withServiceAccount($serviceAccount)->createMessaging();

        $notify = Notification::create($notification->title, $notification->message);

        try {
            foreach ($user->devices() as $device) {
                $message = CloudMessage::new()
                    ->withNotification($notify)
                    ->withData(array_merge($notification['data'], ['event' => $notification['event']]))
                    ->withTarget('token', $device['token']);
                $firebase->send($message);
            }
        } catch (\Throwable $t) {
            dump($t);
        }

        return true;
    }

}
