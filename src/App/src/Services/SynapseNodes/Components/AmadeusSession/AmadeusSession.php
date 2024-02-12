<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusSession;

use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class: AmadeusSession
 *
 * @see SynapseBaseEntity
 */
class AmadeusSession extends SynapseBaseEntity
{
    private static $_requiredCookies = ['lss_loc_id', 'um_jst', 'prxCookie'];

    /**
     * getParsedCookies
     *
     * @return array
     */
    public function getParsedCookies(): array
    {
        $cookies = [];
        $cookiesString = $this->cookies . ';';
        foreach (static::$_requiredCookies as $name) {
            $matches = [];
            $pattern = "/$name=(.+?);/";
            preg_match_all($pattern, $cookiesString, $matches);
            $cookies[$name] = isset($matches[1][0]) ? $matches[1][0] : false;
        }
        return $cookies;
    }

    /**
     * getAuthority
     *
     * @return string
     */
    public function getAuthority(): string
    {
        return explode('//', $this->initiator)[1];
    }

    /**
     * getLowerCaseOfficeId
     *
     * @return string
     */
    public function getLowerCaseOfficeId(): string
    {
        return strtolower($this->officeId);
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            if (! isset($entity['token']) || ! $entity['token']) {
                $entity['token'] = sha1(Uuid::uuid4()->toString());
            }
        });

        parent::subscribe();
    }
}
