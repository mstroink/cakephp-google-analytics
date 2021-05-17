<?php

namespace Spatie\Analytics;

use DateTimeInterface;
use Google_Service_Analytics;
use Cake\Cache\CacheEngine;
use Closure;
use Google_Service_Analytics_GaData;

class AnalyticsClient
{
    protected int $cacheLifeTimeInMinutes = 0;

    public function __construct(
        protected Google_Service_Analytics $service,
        protected CacheEngine $cache,
    ) {
        //
    }

    public function setCacheLifeTimeInMinutes(int $cacheLifeTimeInMinutes): self
    {
        $this->cacheLifeTimeInMinutes = $cacheLifeTimeInMinutes * 60;

        return $this;
    }

    /**
     * Query the Google Analytics Service with given parameters.
     *
     * @param string $viewId
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param string $metrics
     * @param array $others
     *
     * @return Google_Service_Analytics_GaData|array|null
     */
    public function performQuery(string $viewId, DateTimeInterface $startDate, DateTimeInterface $endDate, string $metrics, array $others = []): Google_Service_Analytics_GaData | array | null
    {
        $cacheName = $this->determineCacheName(func_get_args());

        if ($this->cacheLifeTimeInMinutes === 0) {
            $this->cache->delete($cacheName);
        }

        return $this->remember($cacheName, $this->cacheLifeTimeInMinutes, function () use ($viewId, $startDate, $endDate, $metrics, $others) {
            $result = $this->service->data_ga->get(
                "ga:{$viewId}",
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $metrics,
                $others,
            );

            while ($nextLink = $result->getNextLink()) {
                if (isset($others['max-results']) && count($result->rows) >= $others['max-results']) {
                    break;
                }

                $options = [];

                parse_str(substr($nextLink, strpos($nextLink, '?') + 1), $options);

                $response = $this->service->data_ga->call('get', [$options], 'Google_Service_Analytics_GaData');

                if ($response->rows) {
                    $result->rows = array_merge($result->rows, $response->rows);
                }

                $result->nextLink = $response->nextLink;
            }

            return $result;
        });
    }

    public function getAnalyticsService(): Google_Service_Analytics
    {
        return $this->service;
    }

    /**
     * Determine the cache name for the set of query properties given.
     */
    protected function determineCacheName(array $properties): string
    {
        return 'cakephp-analytics_' . md5(serialize($properties));
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param  string  $key
     * @param  \DateTimeInterface|\DateInterval|int|null  $ttl
     * @param  \Closure  $callback
     * @return mixed
     */
    protected function remember(string $key, $ttl, Closure $callback): mixed
    {
        $value = $this->cache->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->cache->set($key, $value = $callback(), $ttl);

        return $value;
    }
}
