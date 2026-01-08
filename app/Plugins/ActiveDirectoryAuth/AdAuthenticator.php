<?php

namespace App\Plugins\ActiveDirectoryAuth;

use Adldap\Adldap;
use Adldap\Exceptions\AdldapException;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Plugins\ActiveDirectoryAuth\SettingsRepository;

class AdAuthenticator
{
    private ?Adldap $adldap;
    private SettingsRepository $settings;

    public function __construct(?Adldap $adldap = null, ?SettingsRepository $settings = null)
    {
        $this->adldap = $adldap;
        $this->settings = $settings ?? new SettingsRepository();
    }

    public function isEnabled(): bool
    {
        return $this->adldap instanceof Adldap
            && filter_var($this->settings->get('enabled', false), FILTER_VALIDATE_BOOLEAN);
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

        $loginAttribute = $this->settings->get('login_attribute', 'sAMAccountName');
        $baseFilter = $this->settings->get('user_filter', '(objectClass=user)');
        if (trim((string) $baseFilter) === '') {
            $baseFilter = '(objectClass=user)';
        }
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
            Log::warning('Active Directory authentication failed.', [
                'login' => $login,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            return null;
        } catch (\Throwable $exception) {
            Log::error('Unexpected Active Directory authentication error.', [
                'login' => $login,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);
            return null;
        }

        try {
            return $this->resolveLocalUser($adUser, $login);
        } catch (\Throwable $exception) {
            Log::error('Active Directory user resolution failed.', [
                'login' => $login,
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return null;
        }
    }

    public function connect()
    {
        if (!$this->adldap instanceof Adldap) {
            throw new \RuntimeException('Active Directory client is unavailable. Ensure the ActiveDirectoryAuth plugin is installed and enabled.');
        }

        $config = $this->buildConfig();
        if (!$config) {
            throw new \RuntimeException('Active Directory configuration is incomplete. Ensure host, base DN, bind DN, and bind password are set.');
        }

        $this->adldap->addProvider($config);

        return $this->adldap->connect();
    }

    public function settings(): array
    {
        return $this->settings->all();
    }

    public function syncAdUser($adUser): ?User
    {
        $loginAttribute = $this->settings->get('login_attribute', 'sAMAccountName');
        $login = $this->getAttributeValue($adUser, $loginAttribute) ?: '';

        if ($login === '') {
            return null;
        }

        return $this->resolveLocalUser($adUser, $login);
    }

    private function buildConfig(): ?array
    {
        $host = $this->settings->get('host');
        $baseDn = $this->settings->get('base_dn');
        $bindDn = $this->settings->get('bind_dn');
        $bindPassword = $this->settings->get('bind_password');
        $port = (int) $this->settings->get('port', 389);
        $encryption = strtolower((string) $this->settings->get('encryption', ''));
        $tlsVerify = filter_var($this->settings->get('tls_verify', true), FILTER_VALIDATE_BOOLEAN);
        $caCert = $this->settings->get('ca_cert');

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
        $mapping = $this->settings->get('map', []);
        $loginAttribute = $this->settings->get('login_attribute', 'sAMAccountName');
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
        $matchField = $this->settings->get('match_field', 'email');
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
        $user->role = $this->settings->get('default_role', 'user');
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
        return filter_var($this->settings->get('sync_attributes', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function autoProvision(): bool
    {
        return filter_var($this->settings->get('auto_provision', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function getAttributeValue($adUser, string $attribute): ?string
    {
        if (!$attribute) {
            return null;
        }

        return $adUser->getFirstAttribute($attribute);
    }
}
