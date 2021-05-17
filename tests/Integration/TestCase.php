<?php

namespace Spatie\Analytics\Tests\Integration;

use Cake\TestSuite\TestCase as CakeTestCase;
use Spatie\Analytics\AnalyticsFacade;
use Spatie\Analytics\AnalyticsServiceProvider;

abstract class TestCase extends CakeTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AnalyticsServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Analytics' => AnalyticsFacade::class,
        ];
    }
}
