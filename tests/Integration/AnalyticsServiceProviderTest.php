<?php

namespace Spatie\Analytics\Tests\Integration;

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Core\Container;
use Cake\Core\ContainerInterface;
use Cake\TestSuite\TestCase;
use Carbon\Carbon;
use Spatie\Analytics\Analytics;
use Spatie\Analytics\AnalyticsServiceProvider;
use Spatie\Analytics\Exceptions\InvalidConfiguration;
use Storage;

class AnalyticsServiceProviderTest extends TestCase
{
    protected ContainerInterface $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->addServiceProvider(new AnalyticsServiceProvider());

        Configure::write('Analytics', [
            'cache' => ['config' => 'analytics'],
            'cache_lifetime_in_minutes' => 0,
            'view_id' => '123',
        ]);
    }
    
    /** @test */
    public function it_will_throw_an_exception_if_the_view_id_is_not_set()
    {
        Configure::write('Analytics.view_id', '');
    
        $this->expectException(InvalidConfiguration::class);

        $analytics = $this->container->get(Analytics::class);

        $analytics->fetchVisitorsAndPageViews(Chronos::now()->subDay(), Chronos::now());
    }

    /** @test */
    public function it_allows_credentials_json_file()
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'analytics');

        file_put_contents($tmpPath, json_encode($this->get_credentials()));

        Configure::write('Analytics.service_account_credentials_json', $tmpPath);

        $analytics = $this->container->get(Analytics::class);

        $this->assertInstanceOf(\Spatie\Analytics\Analytics::class, $analytics);
    }

    /** @test */
    public function it_will_throw_an_exception_if_the_credentials_json_does_not_exist()
    {
        Configure::write('Analytics.service_account_credentials_json', 'bogus.json');

        $this->expectException(InvalidConfiguration::class);

        $analytics = $this->container->get(Analytics::class);

        $analytics->fetchVisitorsAndPageViews(Chronos::now()->subDay(), Chronos::now());
    }

    /** @test */
    public function it_allows_credentials_json_to_be_array()
    {
        Configure::write('Analytics.service_account_credentials_json', $this->get_credentials());

        $analytics = $this->container->get(Analytics::class);

        $this->assertInstanceOf(\Spatie\Analytics\Analytics::class, $analytics);
    }

    protected function get_credentials()
    {
        return [
            'type' => 'service_account',
            'project_id' => 'bogus-project',
            'private_key_id' => 'bogus-id',
            'private_key' => 'bogus-key',
            'client_email' => 'bogus-user@bogus-app.iam.gserviceaccount.com',
            'client_id' => 'bogus-id',
            'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri' => 'https://accounts.google.com/o/oauth2/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
            'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/bogus-ser%40bogus-app.iam.gserviceaccount.com',
        ];
    }
}
