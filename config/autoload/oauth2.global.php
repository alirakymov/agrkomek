<?php

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\OAuth2\Repository\Pdo\UserRepository;
use Qore\App\SynapseNodes\Components\User\UserRepositoryFactory;

return [
    'authentication' => [
        'private_key'    => PROJECT_DATA_PATH . '/oauth/private.key',
        'public_key'     => PROJECT_DATA_PATH . '/oauth/public.key',
        'encryption_key' => require PROJECT_DATA_PATH . '/oauth/encryption.key',
        'access_token_expire'  => 'P1D',
        'refresh_token_expire' => 'P1M',
        'auth_code_expire'     => 'PT10M',
        'pdo' => [
            'dsn'      => 'mysql:dbname=agro;host=mps.mdb',
            'username' => 'root',
            'password' => 'qore2_password'
        ],

        'grants' => [
            // ClientCredentialsGrant::class => ClientCredentialsGrant::class,
            PasswordGrant::class          => PasswordGrant::class,
            // AuthCodeGrant::class          => AuthCodeGrant::class,
            // ImplicitGrant::class          => ImplicitGrant::class,
            RefreshTokenGrant::class      => RefreshTokenGrant::class
        ],
    ],
    'dependencies' => [
        'aliases' => [
            AuthenticationInterface::class => OAuth2Adapter::class
        ],
        'factories' => [
            UserRepository::class => UserRepositoryFactory::class,
        ],
    ],
];
