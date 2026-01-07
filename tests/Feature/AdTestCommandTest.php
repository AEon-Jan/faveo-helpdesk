<?php

namespace Tests\Feature;

use Adldap\Adldap;
use Mockery;
use Tests\TestCase;

class AdTestCommandTest extends TestCase
{
    public function test_ad_test_command_outputs_user_data_without_real_ad()
    {
        putenv('AD_HOST=ad.example.local');
        putenv('AD_BASE_DN=DC=example,DC=local');
        putenv('AD_BIND_DN=CN=svc_faveo,OU=Service,DC=example,DC=local');
        putenv('AD_BIND_PASSWORD=secret');
        putenv('AD_ENCRYPTION=starttls');
        putenv('AD_TLS_VERIFY=true');
        putenv('AD_LOGIN_ATTR=sAMAccountName');
        putenv('AD_USER_FILTER=(objectClass=user)');

        $user = new class {
            public function getDn()
            {
                return 'CN=Jane Doe,OU=Users,DC=example,DC=local';
            }

            public function getFirstAttribute($attribute)
            {
                $values = [
                    'mail' => 'jane.doe@example.local',
                    'givenName' => 'Jane',
                    'sn' => 'Doe',
                    'displayName' => 'Jane Doe',
                ];

                return $values[$attribute] ?? null;
            }
        };

        $search = Mockery::mock();
        $search->shouldReceive('rawFilter')->andReturnSelf();
        $search->shouldReceive('first')->andReturn($user);

        $provider = Mockery::mock();
        $provider->shouldReceive('search')->andReturn($search);

        $adldap = Mockery::mock(Adldap::class);
        $adldap->shouldReceive('addProvider')->once();
        $adldap->shouldReceive('connect')->andReturn($provider);

        $this->app->instance(Adldap::class, $adldap);

        $this->artisan('ad:test', ['username' => 'jane.doe'])
            ->expectsOutput('AD user found:')
            ->expectsOutput('DN: CN=Jane Doe,OU=Users,DC=example,DC=local')
            ->expectsOutput('mail: jane.doe@example.local')
            ->expectsOutput('givenName: Jane')
            ->expectsOutput('sn: Doe')
            ->expectsOutput('displayName: Jane Doe')
            ->assertExitCode(0);
    }
}
