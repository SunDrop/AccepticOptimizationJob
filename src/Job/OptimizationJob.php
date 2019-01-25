<?php

namespace Acceptic\Job;

use Acceptic\Campaign\CampaignAnalyser;
use Acceptic\Campaign\CampaignDataSource;
use Acceptic\Campaign\CampaignEventAggregator;
use Acceptic\Event\EventsDataSource;
use Psr\Log\LoggerInterface;

class OptimizationJob
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        $campaigns = (new CampaignDataSource())->getCampaignsAsKeyArray();
        $campaignEventAggregator = new CampaignEventAggregator();
        foreach ((new EventsDataSource())->getEventsSince("2 weeks ago") as $event) {
            if (!$event->isValid()) {
                $this->logger->error('VK20180125_01: Not valid event ({eventId})', [
                    'eventId' => $event->getId()
                ]);
                continue;
            }
            if (!isset($campaigns[$event->getCampaignId()])) {
                $this->logger->critical(
                    'VK20180125_02: Campaign ({campaignId}) in event ({eventId}) does not exist', [
                    'campaignId' => $event->getCampaignId(),
                    'eventId' => $event->getId(),
                ]);
                continue;
            }
            $campaignEventAggregator->add($event);
        }
        (new CampaignAnalyser($campaigns, $campaignEventAggregator))->run();
    }
}

