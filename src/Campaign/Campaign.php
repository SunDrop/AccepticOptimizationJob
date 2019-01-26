<?php

namespace Acceptic\Campaign;

class Campaign
{
    /** @var int */
    private $id;

    /** @var OptimizationProps $optProps */
    private $optProps;

    /** @var array */
    private $publisherBlacklist;

    /**
     * Campaign constructor (for test).
     * @param int $id
     * @param OptimizationProps $optProps
     * @param array $publisherBlacklist
     */
    public function __construct(int $id, OptimizationProps $optProps, array $publisherBlacklist)
    {
        $this->id = $id;
        $this->optProps = $optProps;
        $this->publisherBlacklist = $publisherBlacklist;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOptimizationProps()
    {
        return $this->optProps;
    }

    public function getBlackList()
    {
        return $this->publisherBlacklist;
    }

    public function saveBlacklist($blacklist)
    {
        // dont implement
    }
}
