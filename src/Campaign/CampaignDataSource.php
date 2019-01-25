<?php

namespace Acceptic\Campaign;

/**
 * Class CampaignDataSource
 * @package Campaign
 *
 * CampaignDataSource stub
 */
class CampaignDataSource
{
    /**
     * Get an associative array like [
     *     'campaignId1' => campaign1,
     *     'campaignId2' => campaign2,
     *     ...
     *     'campaignIdN' => campaignN,
     * ]
     * @return iterable|null
     */
    public function getCampaignsAsKeyArray(): ?\iterable
    {
        return \array_map(function (Campaign $campaign) {
            return [$campaign->getId() => $campaign];
        }, $this->getCampaigns());
    }

    /**
     * @return Campaign[]|null
     */
    public function getCampaigns(): ?\iterable
    {
        return [];
    }
}