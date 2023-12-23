<?php

declare(strict_types=1);

namespace Qore\App\Services\SmsService;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Qore\ORM\ModelManager;
use Qore\SynapseManager\SynapseManager;
use Ramsey\Uuid\Uuid;

class SmsServiceFactory
{
    /**
     * __invoke
     *
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container): SmsService 
    {
        $config = $container->get('config');
        
        $smsConfig = array_merge($config['sms-service'] ?? [], [
            'host' => 'http://212.124.121.186:9507/api',
            'login' => 'agrokomek1',
            'password' => 'QJB5zKuTl',
        ]);

        return new SmsService($smsConfig['host'], $smsConfig['login'], $smsConfig['password'], new Client());
    }

}
