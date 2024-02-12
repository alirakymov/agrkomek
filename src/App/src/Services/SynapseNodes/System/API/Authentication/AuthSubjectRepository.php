<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\System\API\Authentication;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Qore\SynapseManager\SynapseManager;
use Qore\Qore;

class AuthSubjectRepository implements UserRepositoryInterface
{
    /**
     * @var \Qant\SynapseManager\SynapseManager
     */
    private SynapseManager $_sm;

    /**
     * __construct
     *
     * @param SynapseManager $_sm
     */
    public function __construct(SynapseManager $_sm)
    {
        $this->_sm = $_sm;
    }

    /**
     * [TODO:description]
     *
     * @param string $_credential [TODO:description]
     * @param string|null $_password (optional) [TODO:description]
     *
     * @return ?UserInterface [TODO:description]
     */
    public function authenticate(string $_credential, ?string $_password = null): ?UserInterface
    {
        $config = Qore::config('catalog.api');
        if ($_credential === $config['username'] && password_verify($_password, $config['password'])) {
            $subject= new class($_credential, $_password) implements UserInterface {
                /** @var array */
                private array $roles = [];

                /** @var array */
                private array $details = [];

                public function __construct ($_username, $_password)
                {
                    $this->details = ['username' => $_username, 'password' => $_password];
                }

                /**
                 * Get the unique user identity (id, username, email address or ...)
                 */
                public function getIdentity(): string
                {
                    return $this->details['username'];
                }

                /**
                 * Get all user roles
                 *
                 * @psalm-return iterable<int|string, string>
                 */
                public function getRoles(): iterable
                {
                    return $this->roles;
                }

                /**
                 * Get a detail $name if present, $default otherwise
                 *
                 * @param null|mixed $default
                 * @return mixed
                 */
                public function getDetail(string $name, $default = null)
                {
                    return $this->details[$name];
                }

                /**
                 * Get all the details, if any
                 *
                 * @psalm-return array<string, mixed>
                 */
                public function getDetails(): array
                {
                    return $this->details;
                }
            };

            return $subject;
        }

        return null;
    }

}
