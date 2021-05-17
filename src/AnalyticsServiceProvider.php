<?php

namespace MStroink\Analytics;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\ServiceProvider;
use MStroink\Analytics\Exceptions\InvalidConfiguration;

class AnalyticsServiceProvider extends ServiceProvider
{
    protected $provides = [
        AnalyticsClient::class,
        Analytics::class,
    ];
 
    public function services(ContainerInterface $container): void
    {
        $container->add(AnalyticsClient::class, function () {
            $analyticsConfig = Configure::read('Analytics');

            return AnalyticsClientFactory::createForConfig($analyticsConfig);
        });

        $container->add(Analytics::class, function () use ($container) {
            $analyticsConfig = Configure::read('Analytics');

            $this->guardAgainstInvalidConfiguration($analyticsConfig);

            $client = $container->get(AnalyticsClient::class);

            return new Analytics($client, $analyticsConfig['view_id']);
        });
    }

    protected function guardAgainstInvalidConfiguration(array $analyticsConfig = null): void
    {
        if (empty($analyticsConfig['view_id'])) {
            throw InvalidConfiguration::viewIdNotSpecified();
        }

        if (is_array($analyticsConfig['service_account_credentials_json'])) {
            return;
        }

        if (! file_exists($analyticsConfig['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($analyticsConfig['service_account_credentials_json']);
        }
    }
}
