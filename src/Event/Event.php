<?php

namespace Acceptic\Event;

class Event
{
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var int */
    private $campaignId;

    /** @var int */
    private $publisherId;

    /** @var \DateTimeInterface */
    private $ts;

    /**
     * Event constructor (for test).
     * @param int $id
     * @param string $type
     * @param int $campaignId
     * @param int $publisherId
     * @param \DateTimeInterface $ts
     */
    public function __construct(int $id, string $type, int $campaignId, int $publisherId, \DateTimeInterface $ts)
    {
        $this->id = $id;
        $this->type = $type;
        $this->campaignId = $campaignId;
        $this->publisherId = $publisherId;
        $this->ts = $ts;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaignId(): int
    {
        return $this->campaignId;
    }

    public function getPublisherId(): int
    {
        return $this->publisherId;
    }

    public function isValid(): bool
    {
        if (!in_array($this->getType(), EventType::getItems(), true)) {
            return false;
        }

        try {
            if ($this->getTs() > new \DateTime('now')) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTs(): ?\DateTimeInterface
    {
        return $this->ts;
    }
}
