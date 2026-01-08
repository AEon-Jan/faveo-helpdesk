<?php

namespace App\Console\Commands;

use Adldap\Exceptions\AdldapException;
use App\Plugins\ActiveDirectoryAuth\AdAuthenticator;
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
    public function handle(AdAuthenticator $authenticator)
    {
        $settings = $authenticator->settings();

        $host = $settings['host'] ?? null;
        $baseDn = $settings['base_dn'] ?? null;
        $bindDn = $settings['bind_dn'] ?? null;
        $bindPassword = $settings['bind_password'] ?? null;
        $tlsVerify = filter_var($settings['tls_verify'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $caCert = $settings['ca_cert'] ?? null;

        if (!$host || !$baseDn || !$bindDn || $bindPassword === null) {
            $this->error('Missing AD configuration. Please set Active Directory settings in the panel.');

            return 1;
        }

        if ($caCert) {
            putenv("LDAPTLS_CACERT={$caCert}");
        }

        if (!$tlsVerify) {
            putenv('LDAPTLS_REQCERT=never');
        }

        $username = $this->argument('username');
        $loginAttribute = $settings['login_attribute'] ?? 'sAMAccountName';
        $baseFilter = $settings['user_filter'] ?? '(objectClass=user)';
        $filter = sprintf(
            '(&%s(%s=%s)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))',
            $baseFilter,
            $loginAttribute,
            $username
        );

        try {
            $provider = $authenticator->connect();
            $user = $provider->search()->rawFilter($filter)->first();
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return 1;
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
