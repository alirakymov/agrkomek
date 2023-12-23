<?php

namespace Qore\App\Services\SmsService;

use Closure;
use GuzzleHttp\Client;
use SplStack;

class SmsService
{
    /**
     * @var SplStack<T>
     */
    private SplStack $stack;

    /**
     * Consctructor
     *
     * @param array $_users 
     */
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly string $password,
        private readonly Client $client,
    ) {

    }

    public function send(string $_recipient, string $_text): void
    {
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'action' => 'sendmessage',
            'recipient' => $_recipient,
            'messagetype' => 'SMS:TEXT',
            'originator' => 'Agro',
            'messagedata' => $_text,
            'reporturl' => 'https://agro.tops.kz/callback/sms?statusmessage=$statusmessage%26statuscode=$statuscode%26messageid=$messageid%26recipient=$recipient%26originator=$originator%26messagetype=$messagetype%26status=$status%26deliveredtonetworkdate=$deliveredtonetworkdate%26deliveredtohandsetdate=$deliveredtohandsetdate'
        ];

        $response = $this->client->request('GET', $this->host, ['query' => $params]);
        dump($response);
    }

}
