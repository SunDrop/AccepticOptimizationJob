<?php

namespace Acceptic\Campaign;

use Acceptic\Log\SomeLogger;
use Acceptic\Publisher\NotificationType;
use Acceptic\Publisher\PublisherNotifier;

class CampaignAnalyser
{
    /** @var Campaign[] */
    private $campaigns;

    /** @var CampaignEventAggregator */
    private $campaignEventAggregator;

    /**
     * @param Campaign[] $campaigns
     * @param CampaignEventAggregator $campaignEventAggregator
     */
    public function __construct(iterable $campaigns, CampaignEventAggregator $campaignEventAggregator)
    {
        $this->campaigns = $campaigns;
        $this->campaignEventAggregator = $campaignEventAggregator;
    }

    public function run(): void
    {
        foreach ($this->campaignEventAggregator->getStore() as $campaignId => $campaignAggregator) {
            $blacklist = [];
            $campaign = $this->campaigns[$campaignId];
            $optimizationProps = $campaign->getOptimizationProps();
            foreach ($campaignAggregator as $publisherId => $publisherEvents) {
                if ($this->isThresholdDone($optimizationProps, $publisherEvents)
                    && !$this->isRatioDone($optimizationProps, $publisherEvents)) {
                    $blacklist[] = $publisherId;
                }
            }
            $this->notifyPublishers($campaign, $blacklist);
            $campaign->saveBlacklist($blacklist);
        }
    }

    private function isThresholdDone(OptimizationProps $optimizationProps, array $publisherEvents): bool
    {
        $sourceEvent = $optimizationProps->sourceEvent;
        $threshold = $optimizationProps->threshold;
        if ($publisherEvents[$sourceEvent] > $threshold) {
            return true;
        }

        return false;
    }

    private function isRatioDone(OptimizationProps $optimizationProps, $publisherEvents): bool
    {
        $sourceEvent = $optimizationProps->sourceEvent;
        $measuredEvent = $optimizationProps->measuredEvent;
        $ratioThreshold = $optimizationProps->ratioThreshold;
        if (!$publisherEvents[$sourceEvent]) {
            return false;
        }
        if ($publisherEvents[$measuredEvent] / $publisherEvents[$sourceEvent] < $ratioThreshold) {
            return false;
        }

        return true;
    }

    private function notifyPublishers(Campaign $campaign, array $blacklist): void
    {
        $publisherNotifier = new PublisherNotifier(new SomeLogger());

        $newBlockedPublisherList = array_diff($blacklist, $campaign->getBlackList());
        $publisherNotifier->notify(NotificationType::TYPE_BLOCKED, $newBlockedPublisherList);

        $newUnblockedPublisherList = array_diff($campaign->getBlackList(), $blacklist);
        $publisherNotifier->notify(NotificationType::TYPE_UNBLOCKED, $newUnblockedPublisherList);
    }
}
