<?php

namespace Acceptic\Campaign;

use Acceptic\Event\Event;

class CampaignEventAggregator
{
    /**
     * @var array
     * [
     *   'campaignId' => [
     *     'publisherId' => [
     *          'eventType' => sum,
     *          'eventType' => sum,
     *          'eventType' => sum,
     *      ],
     *   ],
     * ]
     */
    private $store;

    public function getStore(): ?iterable
    {
        return $this->store;
    }

    public function add(Event $event): void
    {
        $this->sumEvent($event->getCampaignId(), $event->getPublisherId(), $event->getType());
    }

    private function sumEvent(int $campaignId, int $publisherId, string $eventType): void
    {
        if (!isset($this->store[$campaignId][$publisherId][$eventType])) {
            $this->store[$campaignId][$publisherId][$eventType] = 1;
        } else {
            $this->store[$campaignId][$publisherId][$eventType] += 1;
        }
    }
}
