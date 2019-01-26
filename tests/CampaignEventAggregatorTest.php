<?php namespace Acceptic\Test;

use Acceptic\Campaign\CampaignEventAggregator;
use Acceptic\Event\Event;
use Acceptic\Event\EventType;

class CampaignEventAggregatorTest extends \Codeception\Test\Unit
{
    private function getExpectedAggregatedStore()
    {
        return [
            // campaignId => 1
            1 => [
                // publisherId => 1
                1 => [
                    EventType::EVENT_TYPE_INSTALL => 5,
                    EventType::EVENT_TYPE_REGISTRATION => 5,
                ],
                // publisherId => 2
                2 => [
                    EventType::EVENT_TYPE_APP_OPEN => 5,
                ]
            ],
            // campaignId => 2
            2 => [
                // publisherId => 2
                2 => [
                    EventType::EVENT_TYPE_REGISTRATION => 5,
                ],
            ]
        ];
    }

    private function getEventsList()
    {
        for ($i = 1; $i <= 5; ++$i) {
            yield new Event($i, EventType::EVENT_TYPE_INSTALL, 1, 1, new \DateTime());
        }
        for ($i = 6; $i <= 10; ++$i) {
            yield new Event($i, EventType::EVENT_TYPE_REGISTRATION, 1, 1, new \DateTime());
        }
        for ($i = 11; $i <= 15; ++$i) {
            yield new Event($i, EventType::EVENT_TYPE_APP_OPEN, 1, 2, new \DateTime());
        }
        for ($i = 16; $i <= 20; ++$i) {
            yield new Event($i, EventType::EVENT_TYPE_REGISTRATION, 2, 2, new \DateTime());
        }
    }

    public function testAddEvent()
    {
        $campaignEventAggregator = new CampaignEventAggregator();
        foreach ($this->getEventsList() as $event) {
            $campaignEventAggregator->add($event);
        }
        $this->assertEquals($this->getExpectedAggregatedStore(), $campaignEventAggregator->getStore());
    }
}