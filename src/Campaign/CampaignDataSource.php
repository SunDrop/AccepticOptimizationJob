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
    public function getCampaignsAsAssocArray(): ?iterable
    {
        return \array_reduce($this->getCampaigns(), function ($result, Campaign $campaign) {
            $result[$campaign->getId()] = $campaign;
            return $result;
        }, []);
    }

    /**
     * @return Campaign[]|null
     */
    public function getCampaigns(): ?iterable
    {
        return [];
    }
}
