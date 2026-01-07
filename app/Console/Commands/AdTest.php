<?php

namespace App\Console\Commands;

use Adldap\Adldap;
use Adldap\Exceptions\AdldapException;
use Illuminate\Console\Command;

class AdTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ad:test {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Active Directory connectivity and look up a user.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Adldap $adldap)
    {
        $host = env('AD_HOST');
        $baseDn = env('AD_BASE_DN');
        $bindDn = env('AD_BIND_DN');
        $bindPassword = env('AD_BIND_PASSWORD');
        $port = (int) env('AD_PORT', 389);
        $encryption = strtolower((string) env('AD_ENCRYPTION', ''));
        $tlsVerify = filter_var(env('AD_TLS_VERIFY', true), FILTER_VALIDATE_BOOLEAN);
        $caCert = env('AD_CA_CERT');

        if (!$host || !$baseDn || !$bindDn || $bindPassword === null) {
            $this->error('Missing AD configuration. Please set AD_HOST, AD_BASE_DN, AD_BIND_DN, and AD_BIND_PASSWORD.');

            return 1;
        }

        if ($caCert) {
            putenv("LDAPTLS_CACERT={$caCert}");
        }

        if (!$tlsVerify) {
            putenv('LDAPTLS_REQCERT=never');
        }

        $config = [
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

        $username = $this->argument('username');
        $loginAttribute = env('AD_LOGIN_ATTR', 'sAMAccountName');
        $baseFilter = env('AD_USER_FILTER', '(objectClass=user)');
        $filter = sprintf(
            '(&%s(%s=%s)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
            $baseFilter,
            $loginAttribute,
            $username
        );

        try {
            $adldap->addProvider($config);
            $provider = $adldap->connect();

            $user = $provider->search()->rawFilter($filter)->first();
        } catch (AdldapException $exception) {
            $this->error('AD connection or search failed: '.$exception->getMessage());

            return 1;
        }

        if (!$user) {
            $this->warn('No matching AD user found.');

            return 1;
        }

        $this->info('AD user found:');
        $this->line('DN: '.$user->getDn());
        $this->line('mail: '.$user->getFirstAttribute('mail'));
        $this->line('givenName: '.$user->getFirstAttribute('givenName'));
        $this->line('sn: '.$user->getFirstAttribute('sn'));
        $this->line('displayName: '.$user->getFirstAttribute('displayName'));

        return 0;
    }
}
