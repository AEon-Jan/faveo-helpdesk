<?php

namespace App\Plugins\ActiveDirectoryAuth;

use Adldap\Adldap;
use Adldap\Exceptions\AdldapException;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdAuthenticator
{
    private ?Adldap $adldap;

    public function __construct(?Adldap $adldap = null)
    {
        $this->adldap = $adldap;
    }

    public function isEnabled(): bool
    {
        return $this->adldap instanceof Adldap
            && filter_var(config('active_directory_auth.enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function attempt(string $login, string $password): ?User
    {
        if (!$this->isEnabled() || $password === '') {
            return null;
        }

        $config = $this->buildConfig();
        if (!$config) {
            return null;
        }

        $loginAttribute = config('active_directory_auth.login_attribute', 'sAMAccountName');
        $baseFilter = config('active_directory_auth.user_filter', '(objectClass=user)');
        $filter = sprintf(
            '(&%s(%s=%s)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
            $baseFilter,
            $loginAttribute,
            $login
        );

        try {
            $this->adldap->addProvider($config);
            $provider = $this->adldap->connect();

            $adUser = $provider->search()->rawFilter($filter)->first();
            if (!$adUser) {
                return null;
            }

            if (!$provider->auth()->attempt($adUser->getDn(), $password, true)) {
                return null;
            }
        } catch (AdldapException $exception) {
            return null;
        }

        return $this->resolveLocalUser($adUser, $login);
    }

    private function buildConfig(): ?array
    {
        $host = config('active_directory_auth.host');
        $baseDn = config('active_directory_auth.base_dn');
        $bindDn = config('active_directory_auth.bind_dn');
        $bindPassword = config('active_directory_auth.bind_password');
        $port = (int) config('active_directory_auth.port', 389);
        $encryption = strtolower((string) config('active_directory_auth.encryption', ''));
        $tlsVerify = filter_var(config('active_directory_auth.tls_verify', true), FILTER_VALIDATE_BOOLEAN);
        $caCert = config('active_directory_auth.ca_cert');

        if (!$host || !$baseDn || !$bindDn || $bindPassword === null) {
            return null;
        }

        if ($caCert) {
            putenv("LDAPTLS_CACERT={$caCert}");
        }

        if (!$tlsVerify) {
            putenv('LDAPTLS_REQCERT=never');
        }

        return [
            'hosts' => [$host],
            'base_dn' => $baseDn,
            'username' => $bindDn,
            'password' => $bindPassword,
            'port' => $port,
            'use_ssl' => $encryption === 'ldaps',
            'use_tls' => $encryption === 'starttls',
            'version' => 3,
            'follow_referrals' => false,
        ];
    }

    private function resolveLocalUser($adUser, string $login): ?User
    {
        $mapping = config('active_directory_auth.map', []);
        $loginAttribute = config('active_directory_auth.login_attribute', 'sAMAccountName');
        $userNameAttr = $mapping['user_name'] ?? $loginAttribute;
        $emailAttr = $mapping['email'] ?? 'mail';
        $firstNameAttr = $mapping['first_name'] ?? 'givenName';
        $lastNameAttr = $mapping['last_name'] ?? 'sn';

        $userName = $this->getAttributeValue($adUser, $userNameAttr) ?: $login;
        $email = $this->getAttributeValue($adUser, $emailAttr);
        $firstName = $this->getAttributeValue($adUser, $firstNameAttr) ?: $login;
        $lastName = $this->getAttributeValue($adUser, $lastNameAttr) ?: '';

        [$lookupField, $lookupValue] = $this->resolveLookup($userName, $email);
        if (!$lookupValue) {
            return null;
        }

        $user = User::where($lookupField, $lookupValue)->first();
        if (!$user && $this->autoProvision()) {
            $user = $this->createUser($userName, $email, $firstName, $lastName);
        }

        if (!$user) {
            return null;
        }

        if ($this->shouldSync()) {
            $this->syncUser($user, $userName, $email, $firstName, $lastName);
        }

        return $user;
    }

    private function resolveLookup(string $userName, ?string $email): array
    {
        $matchField = config('active_directory_auth.match_field', 'email');
        if ($matchField === 'user_name') {
            if ($userName) {
                return ['user_name', $userName];
            }

            return ['email', $email];
        }

        if ($email) {
            return ['email', $email];
        }

        return ['user_name', $userName];
    }

    private function createUser(string $userName, ?string $email, string $firstName, string $lastName): User
    {
        $user = new User();
        $user->user_name = $userName;
        $user->email = $email;
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->password = Hash::make(Str::random(32));
        $user->role = config('active_directory_auth.default_role', 'user');
        $user->active = 1;
        $user->ban = 0;
        $user->phone_number = '';
        $user->ext = '';
        $user->save();

        return $user;
    }

    private function syncUser(User $user, string $userName, ?string $email, string $firstName, string $lastName): void
    {
        $user->user_name = $userName;
        if ($email) {
            $user->email = $email;
        }
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->save();
    }

    private function shouldSync(): bool
    {
        return filter_var(config('active_directory_auth.sync_attributes', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function autoProvision(): bool
    {
        return filter_var(config('active_directory_auth.auto_provision', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function getAttributeValue($adUser, string $attribute): ?string
    {
        if (!$attribute) {
            return null;
        }

        return $adUser->getFirstAttribute($attribute);
    }
}
