<?php namespace Acceptic\Test;

use Acceptic\Campaign\Campaign;
use Acceptic\Campaign\CampaignAnalyser;
use Acceptic\Campaign\CampaignDataSource;
use Acceptic\Campaign\CampaignEventAggregator;
use Acceptic\Campaign\OptimizationProps;
use Acceptic\Event\Event;
use Acceptic\Event\EventsDataSource;
use Acceptic\Event\EventType;
use Acceptic\Job\OptimizationJob;
use Acceptic\Log\SomeLogger;
use Psr\Log\LoggerInterface;

class OptimizationJobTest extends \Codeception\Test\Unit
{
    /** @var CampaignDataSource */
    private $campaignDataSource;

    /** @var EventsDataSource */
    private $eventsDataSource;

    /** @var CampaignEventAggregator */
    private $campaignEventAggregator;

    /** @var CampaignAnalyser */
    private $campaignAnalyser;

    /** @var LoggerInterface */
    private $logger;

    protected function _before()
    {
        $this->campaignDataSource = $this->getCampaignDataSourceMock();
        $this->eventsDataSource = $this->getEventsDataSourceMock();
        $this->campaignEventAggregator = $this->getCampaignEventAggregatorMock();
        $this->campaignAnalyser = $this->getCampaignAnalyserMock();
        $this->logger = $this->getLoggerMock();
    }

    private function getCampaignDataSourceMock()
    {
        $mock = $this->createMock(CampaignDataSource::class);
        $mock->method('getCampaignsAsAssocArray')->willReturn([
            1 => new Campaign(1, new OptimizationProps(), [1, 2, 3]),
        ]);

        return $mock;
    }

    private function getEventsDataSourceMock()
    {
        $mock = $this->createMock(EventsDataSource::class);
        $mock->method('getEventsSince')->willReturn(
            [
                new Event(1, EventType::EVENT_TYPE_INSTALL, 1, 1, new \DateTime()),
                new Event(2, EventType::EVENT_TYPE_PURCHASE, 1, 1, new \DateTime()),
                new Event(3, EventType::EVENT_TYPE_APP_OPEN, 1, 1, new \DateTime('tomorrow')),
                new Event(4, 'InvalidType', 1, 1, new \DateTime()),
                new Event(5, EventType::EVENT_TYPE_REGISTRATION, 2, 1, new \DateTime()),
            ]
        );

        return $mock;
    }

    private function getCampaignEventAggregatorMock()
    {
        $mock = $this->createMock(CampaignEventAggregator::class);
        /**
         * Expects events with ids [1,2] are valid and added to CampaignEventAggregator
         */
        $mock->expects($this->exactly(2))->method('add');

        return $mock;
    }

    private function getCampaignAnalyserMock()
    {
        return $this->createMock(CampaignAnalyser::class);
    }

    private function getLoggerMock()
    {
        $mock = $this->createMock(SomeLogger::class);
        /**
         * Expects events with ids [3,4] are invalid that logged as error
         */
        $mock->expects($this->exactly(2))->method('error');
        /**
         * Expects for event with id [5] campaign doesn't exist that logged as critical
         */
        $mock->expects($this->once())->method('critical');

        return $mock;
    }

    public function testRun()
    {
        $optimizationJob = new OptimizationJob(
            $this->campaignDataSource,
            $this->eventsDataSource,
            $this->campaignEventAggregator,
            $this->campaignAnalyser,
            $this->logger
        );
        $optimizationJob->run();
    }
}