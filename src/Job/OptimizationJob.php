<?php

namespace Acceptic\Job;

use Acceptic\Campaign\CampaignAnalyser;
use Acceptic\Campaign\CampaignDataSource;
use Acceptic\Campaign\CampaignEventAggregator;
use Acceptic\Event\EventsDataSource;
use Psr\Log\LoggerInterface;

class OptimizationJob
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

    /**
     * OptimizationJob constructor (for test).
     * @param CampaignDataSource $campaignDataSource
     * @param EventsDataSource $eventsDataSource
     * @param CampaignEventAggregator $campaignEventAggregator
     * @param CampaignAnalyser $campaignAnalyser
     * @param LoggerInterface $logger
     */
    public function __construct(
        CampaignDataSource $campaignDataSource,
        EventsDataSource $eventsDataSource,
        CampaignEventAggregator $campaignEventAggregator,
        CampaignAnalyser $campaignAnalyser,
        LoggerInterface $logger
    ) {
        $this->campaignDataSource = $campaignDataSource;
        $this->eventsDataSource = $eventsDataSource;
        $this->campaignEventAggregator = $campaignEventAggregator;
        $this->campaignAnalyser = $campaignAnalyser;
        $this->logger = $logger;
    }

    public function run()
    {
        $campaigns = $this->campaignDataSource->getCampaignsAsAssocArray();
        foreach ($this->eventsDataSource->getEventsSince("2 weeks ago") as $event) {
            if (!$event->isValid()) {
                $this->logger->error('VK20180125_01: Not valid event ({eventId})', [
                    'eventId' => $event->getId(),
                ]);
                continue;
            }
            if (!isset($campaigns[$event->getCampaignId()])) {
                $this->logger->critical(
                    'VK20180125_02: Campaign ({campaignId}) for event ({eventId}) does not exist', [
                    'campaignId' => $event->getCampaignId(),
                    'eventId' => $event->getId(),
                ]);
                continue;
            }
            $this->campaignEventAggregator->add($event);
        }
        $this->campaignAnalyser
            ->setCampaigns($campaigns)
            ->setCampaignEventAggregator($this->campaignEventAggregator)
            ->run();
    }
}
