<?php

namespace Spatie\Analytics;

use Cake\Cache\Cache;
use Cake\Cache\CacheEngine;
use Cake\Cache\Engine\FileEngine;
use Google_Client;
use Google_Service_Analytics;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

class AnalyticsClientFactory
{
    public static function createForConfig(array $analyticsConfig): AnalyticsClient
    {
        $authenticatedClient = self::createAuthenticatedGoogleClient($analyticsConfig);

        $googleService = new Google_Service_Analytics($authenticatedClient);

        return self::createAnalyticsClient($analyticsConfig, $googleService);
    }

    public static function createAuthenticatedGoogleClient(array $config): Google_Client
    {
        $client = new Google_Client();

        $client->setScopes([
            Google_Service_Analytics::ANALYTICS_READONLY,
        ]);

        $client->setAuthConfig($config['service_account_credentials_json']);

        self::configureClientCache($client, $config['cache']);

        return $client;
    }

    protected static function configureClientCache(Google_Client $client, array $config): void
    {
        $engine = self::createCacheEngine($config);

        $cache = new Psr16Adapter($engine);

        $client->setCache($cache);
    }

    protected static function createAnalyticsClient(array $config, Google_Service_Analytics $googleService): AnalyticsClient
    {
        $cache = self::createCacheEngine($config['cache']);

        $client = new AnalyticsClient($googleService, $cache);

        $client->setCacheLifeTimeInMinutes($config['cache_lifetime_in_minutes']);

        return $client;
    }

    protected static function createCacheEngine(array $cacheConfig): CacheEngine
    {
        $name = $cacheConfig['config'] ?? 'analytics';

        if (Cache::getConfig($name) === null) {
            Cache::setConfig($name, [
                'className' => FileEngine::class,
                'duration' => '+1 year',
            ]);
        }

        return Cache::pool($name);
    }
}
