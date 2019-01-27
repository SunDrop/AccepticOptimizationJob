<?php

namespace Acceptic\Campaign;

use Acceptic\Publisher\NotificationType;
use Acceptic\Publisher\PublisherNotifier;
use LogicException;

class CampaignAnalyser
{
    /** @var Campaign[] */
    private $campaigns;

    /** @var CampaignEventAggregator */
    private $campaignEventAggregator;

    /** @var PublisherNotifier */
    private $publisherNotifier;

    /**
     * @param PublisherNotifier $publisherNotifier
     * @return CampaignAnalyser
     */
    public function setPublisherNotifier(PublisherNotifier $publisherNotifier): CampaignAnalyser
    {
        $this->publisherNotifier = $publisherNotifier;
        return $this;
    }

    /**
     * @param iterable $campaigns
     * @return CampaignAnalyser
     */
    public function setCampaigns(iterable $campaigns): CampaignAnalyser
    {
        $this->campaigns = $campaigns;
        return $this;
    }

    /**
     * @param CampaignEventAggregator $campaignEventAggregator
     * @return CampaignAnalyser
     */
    public function setCampaignEventAggregator(CampaignEventAggregator $campaignEventAggregator): CampaignAnalyser
    {
        $this->campaignEventAggregator = $campaignEventAggregator;
        return $this;
    }

    /**
     * @throws LogicException
     */
    public function run(): void
    {
        if ($this->isInitialized()) {
            foreach ($this->campaignEventAggregator->getStore() as $campaignId => $campaignAggregatedData) {
                $blacklist = [];
                $whitelist = [];
                $campaign = $this->campaigns[$campaignId];
                $optimizationProps = $campaign->getOptimizationProps();
                foreach ($campaignAggregatedData as $publisherId => $publisherEvents) {
                    if ($this->isThresholdDone($optimizationProps, $publisherEvents)
                        && !$this->isRatioDone($optimizationProps, $publisherEvents)) {
                        $blacklist[] = $publisherId;
                    } else {
                        $whitelist[] = $publisherId;
                    }
                }
                $this->notifyPublishers($campaign, $blacklist, $whitelist);
                $campaign->saveBlacklist($this->makeBlacklist($campaign, $blacklist, $whitelist));
            }
        }
    }

    /**
     * @throws LogicException
     * @return bool
     */
    private function isInitialized(): bool
    {
        if (!$this->campaigns) {
            throw new LogicException('campaigns is not initialized');
        }
        if (!$this->campaignEventAggregator) {
            throw new LogicException('campaignEventAggregator is not initialized');
        }
        if (!$this->publisherNotifier) {
            throw new LogicException('publisherNotifier is not initialized');
        }

        return true;
    }

    private function isThresholdDone(OptimizationProps $optimizationProps, array $publisherEvents): bool
    {
        $sourceEvent = $optimizationProps->sourceEvent;
        $threshold = $optimizationProps->threshold;
        $sourceEventSum = $publisherEvents[$sourceEvent] ?? 0;
        if ($sourceEventSum > $threshold) {
            return true;
        }

        return false;
    }

    private function isRatioDone(OptimizationProps $optimizationProps, $publisherEvents): bool
    {
        $sourceEvent = $optimizationProps->sourceEvent;
        $measuredEvent = $optimizationProps->measuredEvent;
        $ratioThreshold = $optimizationProps->ratioThreshold;
        $sourceEventSum = $publisherEvents[$sourceEvent] ?? 0;
        $measuredEventSum = $publisherEvents[$measuredEvent] ?? 0;
        if (!$sourceEventSum) {
            return false;
        }
        if ($measuredEventSum / $sourceEventSum < $ratioThreshold) {
            return false;
        }

        return true;
    }

    private function makeBlacklist(Campaign $campaign, array $blacklist, array $whitelist): array
    {
        $newBlacklist = array_unique(array_merge(array_diff($campaign->getBlackList(), $whitelist), $blacklist));
        sort($newBlacklist);
        return $newBlacklist;
    }

    private function notifyPublishers(Campaign $campaign, array $blacklist, array $whitelist): void
    {
        $newBlockedPublisherList = array_diff($blacklist, $campaign->getBlackList());
        $this->publisherNotifier->notify(NotificationType::TYPE_BLOCKED, ...$newBlockedPublisherList);

        $newUnblockedPublisherList = array_intersect($campaign->getBlackList(), $whitelist);
        $this->publisherNotifier->notify(NotificationType::TYPE_UNBLOCKED, ...$newUnblockedPublisherList);
    }
}
