<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\User;

use Qore\App\SynapseNodes\Components\User\Authorization\MentorRole;
use Qore\App\SynapseNodes\Components\User\Authorization\UserRole;
use Qore\Helper\Helper;
use Qore\Qore;
use Qore\Rbac\RbacSubjectInterface;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Ramsey\Uuid\Uuid;

/**
 * Class: User
 *
 * @see SynapseBaseEntity
 */
class User extends SynapseBaseEntity implements UserInterface, RbacSubjectInterface
{
    /**
     * @var string - название роли для агент
     */
    const ROLE_AGENT = 'agent';

    /**
     * @var string - название роли для супервайзера
     */
    const ROLE_SUPERVISOR = 'supervisor';

    /**
     * Get roles of user
     *
     * @return array
     */
    public static function getRolesList(): array
    {
        return [
            [ 'id' => static::ROLE_AGENT, 'label' => 'Агент', ],
            [ 'id' => static::ROLE_SUPERVISOR, 'label' => 'Супервайзер', ],
        ];
    }

    /**
     * Is agent
     *
     * @return bool
     */
    public function isAgent(): bool
    {
        return $this['role'] == static::ROLE_AGENT;
    }

    /**
     * Is supervisor
     *
     * @return bool
     */
    public function isSupervisor(): bool
    {
        return $this['role'] == static::ROLE_SUPERVISOR;
    }

    /**
     * @inheritdoc
     */
    public function getLastname(): string
    {
        return $this->splitFullname()['lastname'];
    }

    /**
     * @inheritdoc
     */
    public function getFirstname(): string
    {
        return $this->splitFullname()['firstname'];
    }

    /**
     * @inheritdoc
     */
    public function splitFullname(): array
    {
        $splitKeys = ['lastname', 'firstname'];
        if (! $this->fullname) {
            return array_combine($splitKeys, ['', '']);
        }

        return array_combine($splitKeys, preg_split('/\s+/', $this->fullname));
    }

    /**
     * @inheritdoc
     */
    public function getMeetMemberIdentity(): string
    {
        return sha1('MeetMember-' . $this['id']);
    }

    /**
     * @inheritdoc
     */
    public function generateToken(): string
    {
        return $this->token = sha1(Uuid::uuid4()->toString());
    }

    /**
     * @inheritdoc
     */
    public function getIdentity(): string
    {
        return $this->phone;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): iterable
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->fullname ?: $default;
    }

    /**
     * @inheritdoc
     */
    public function getDetails(): array
    {
        return [];
    }

    /**
     * Reset password
     *
     * @return UserInterface
     */
    public function resetPassword(): UserInterface
    {
        $this['password'] = '';
        return $this;
    }

    /**
     * Generate OTP
     *
     * @return UserInterface
     */
    public function generateOtp(): UserInterface
    {
        $this['otp'] = rand(100000, 999999);
        return $this;
    }

    /**
     * Is mentor
     *
     * @return bool
     */
    public function isMentor(): bool
    {
        return isset($this['mentor']) && (int)$this['mentor'];
    }

    /**
     * @inheritdoc
     */
    public function getRole()
    {
        return $this->isMentor() ? MentorRole::class : UserRole::class;
    }

    /**
     * Decorate user
     *
     * @return array 
     */
    public function decorate(): array
    {
        return $this->extract([ 
            'id', 'fullname', 'username',
            'splitted-name' => fn() => $this->splitFullname(),
        ]);
    }

    /**
     * Initialize events
     *
     * @return void
     */
    public static function subscribe()
    {
        # - Generate password hash and username for user entity
        static::before('save', function($_event) {
            $user = $_event->getTarget();
            $user['phone'] = preg_replace('/[^0-9]/', '', $user['phone']);

            if (mb_strlen($user['phone']) > 10) {
                $user['phone'] = mb_substr($user['phone'], -10);
            }

            # - Generate password hash
            if ($user->password) {
                $passwordDetails = password_get_info($user->password);
                if (is_null($passwordDetails['algo'])) {
                    $user->password = password_hash($user->password, PASSWORD_DEFAULT);
                    $user['otp'] = null;
                }
            } elseif ($user->isNew()) {
                $user->generateOtp();
            }

            # - Genearate username
            if ($user->fullname) {
                $user->fullname = trim(preg_replace('/\s+/', ' ', $user->fullname));
                list($name1, $name2) = explode(' ', $user->fullname, 2);
                $user->username = sprintf(
                    '%s.%s',
                    mb_strtolower(Qore::service(Helper::class)->translit($name2)),
                    mb_strtolower(Qore::service(Helper::class)->translit($name1))
                );
            }
        });

        parent::subscribe();
    }

}
