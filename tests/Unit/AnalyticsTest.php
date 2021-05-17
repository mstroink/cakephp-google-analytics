<?php

namespace MStroink\Analytics\Tests;

use Cake\Chronos\Chronos;
use Cake\Collection\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use MStroink\Analytics\Analytics;
use MStroink\Analytics\AnalyticsClient;
use MStroink\Analytics\Period;

class AnalyticsTest extends TestCase
{
    /** @var \MStroink\Analytics\AnalyticsClient|\Mockery\Mock */
    protected $analyticsClient;

    /** @var string */
    protected $viewId;

    /** @var \MStroink\Analytics\Analytics */
    protected $analytics;

    /** @var \Cake\Chronos\Chronos */
    protected $startDate;

    /** @var \Cake\Chronos\Chronos */
    protected $endDate;

    public function setUp(): void
    {
        $this->analyticsClient = Mockery::mock(AnalyticsClient::class);

        $this->viewId = '1234567';

        $this->analytics = new Analytics($this->analyticsClient, $this->viewId);

        $this->startDate = Chronos::now()->subDays(7);

        $this->endDate = Chronos::now();
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_can_fetch_the_visitor_and_page_views()
    {
        $expectedArguments = [
            $this->viewId,
            $this->expectChronos($this->startDate),
            $this->expectChronos($this->endDate),
            'ga:users,ga:pageviews',
            ['dimensions' => 'ga:date,ga:pageTitle'],
        ];

        $this->analyticsClient
            ->shouldReceive('performQuery')->withArgs($expectedArguments)
            ->once()
            ->andReturn([
                'rows' => [['20160101', 'pageTitle', '1', '2']],
            ]);

        $response = $this->analytics->fetchVisitorsAndPageViews(Period::create($this->startDate, $this->endDate));

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertEquals('2016-01-01', $response->first()['date']->format('Y-m-d'));
        $this->assertEquals('pageTitle', $response->first()['pageTitle']);
        $this->assertEquals(1, $response->first()['visitors']);
        $this->assertEquals(2, $response->first()['pageViews']);
    }

    /** @test */
    public function it_can_fetch_the_total_visitor_and_page_views()
    {
        $expectedArguments = [
            $this->viewId,
            $this->expectChronos($this->startDate),
            $this->expectChronos($this->endDate),
            'ga:users,ga:pageviews',
            ['dimensions' => 'ga:date'],
        ];

        $this->analyticsClient
            ->shouldReceive('performQuery')->withArgs($expectedArguments)
            ->once()
            ->andReturn([
                'rows' => [['20160101', '1', '2']],
            ]);

        $response = $this->analytics->fetchTotalVisitorsAndPageViews(Period::create($this->startDate, $this->endDate));

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertEquals('2016-01-01', $response->first()['date']->format('Y-m-d'));
        $this->assertEquals(1, $response->first()['visitors']);
        $this->assertEquals(2, $response->first()['pageViews']);
    }

    /** @test */
    public function it_can_fetch_the_most_visited_pages()
    {
        $maxResults = 10;

        $expectedArguments = [
            $this->viewId,
            $this->expectChronos($this->startDate),
            $this->expectChronos($this->endDate),
            'ga:pageviews',
            ['dimensions' => 'ga:pagePath,ga:pageTitle', 'sort' => '-ga:pageviews', 'max-results' => $maxResults],
        ];

        $this->analyticsClient
            ->shouldReceive('performQuery')->withArgs($expectedArguments)
            ->once()
            ->andReturn([
                'rows' => [['https://test.com', 'Page title', '123']],
            ]);

        $response = $this->analytics->fetchMostVisitedPages(Period::create($this->startDate, $this->endDate), $maxResults);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertEquals('https://test.com', $response->first()['url']);
        $this->assertEquals('Page title', $response->first()['pageTitle']);
        $this->assertEquals(123, $response->first()['pageViews']);
    }

    /** @test */
    public function it_can_fetch_the_top_referrers()
    {
        $maxResults = 10;

        $expectedArguments = [
            $this->viewId,
            $this->expectChronos($this->startDate),
            $this->expectChronos($this->endDate),
            'ga:pageviews',
            ['dimensions' => 'ga:fullReferrer', 'sort' => '-ga:pageviews', 'max-results' => $maxResults],
        ];

        $this->analyticsClient
            ->shouldReceive('performQuery')->withArgs($expectedArguments)
            ->once()
            ->andReturn([
                'rows' => [['https://referrer.com', '123']],
            ]);

        $response = $this->analytics->fetchTopReferrers(Period::create($this->startDate, $this->endDate), $maxResults);

        $this->assertInstanceOf(Collection::class, $response);
        $this->assertEquals('https://referrer.com', $response->first()['url']);
        $this->assertEquals(123, $response->first()['pageViews']);
    }

    /** @test */
    public function it_can_fetch_the_top_browsers()
    {
        $expectedArguments = [
            $this->viewId,
            $this->expectChronos($this->startDate),
            $this->expectChronos($this->endDate),
            'ga:sessions',
            ['dimensions' => 'ga:browser', 'sort' => '-ga:sessions'],
        ];

        $this->analyticsClient
            ->shouldReceive('performQuery')->withArgs($expectedArguments)
            ->once()
            ->andReturn([
                'rows' => [
                    ['Browser 1', '100'],
                    ['Browser 2', '90'],
                    ['Browser 3', '30'],
                    ['Browser 4', '20'],
                    ['Browser 1', '10'],
                ],
            ]);

        $response = $this->analytics->fetchTopBrowsers(Period::create($this->startDate, $this->endDate), 3);
        $this->assertInstanceOf(Collection::class, $response);
        $this->assertEquals([
            ['browser' => 'Browser 1', 'sessions' => 100],
            ['browser' => 'Browser 2', 'sessions' => 90],
            ['browser' => 'Others', 'sessions' => 60],
        ], $response->toArray());
    }

    protected function expectChronos(Chronos $chronos)
    {
        return Mockery::on(function (Chronos $argument) use ($chronos) {
            return $argument->format('Y-m-d') == $chronos->format('Y-m-d');
        });
    }
}
