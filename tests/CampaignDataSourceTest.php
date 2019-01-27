<?php

namespace Acceptic\Test;

use Acceptic\Campaign\Campaign;
use Acceptic\Campaign\CampaignDataSource;
use Acceptic\Campaign\OptimizationProps;
use Acceptic\Event\EventType;

class CampaignDataSourceTest extends \Codeception\Test\Unit
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $campaignDataSourceStub;

    public function testGetCampaignsAsKeyArray()
    {
        /** @var CampaignDataSource $campaignDataSourceStub */
        $campaignDataSourceStub = $this->setCampaignDataSourceStub([$this, 'getCampaignList']);
        $campaignsAssocArray = $campaignDataSourceStub->getCampaignsAsAssocArray();
        $campaignList = $this->getCampaignList();
        foreach ($campaignsAssocArray as $campaignId => $campaignActual) {
            /** @var Campaign $campaignExpected */
            $campaignExpected = current($campaignList);
            next($campaignList);
            $this->assertEquals($campaignExpected->getId(), $campaignId);
            $this->assertEquals($campaignExpected, $campaignActual);
        }
    }

    public function testGetCampaignsAsKeyArraySameIds()
    {
        /** @var CampaignDataSource $campaignDataSourceStub */
        $campaignDataSourceStub = $this->setCampaignDataSourceStub([$this, 'getCampaignSameIdsList']);
        $campaignsAssocArray = $campaignDataSourceStub->getCampaignsAsAssocArray();
        $campaignSameIdsList = $this->getCampaignSameIdsList();
        $campaignExpected = end($campaignSameIdsList);

        foreach ($campaignsAssocArray as $campaignId => $campaignActual) {
            $this->assertEquals($campaignExpected, $campaignActual);
        }
        $this->assertCount(1, $campaignsAssocArray);
    }

    protected function _before()
    {
        $this->campaignDataSourceStub = $this->getMockBuilder(CampaignDataSource::class)
            ->setMethods(['getCampaigns'])
            ->getMock();
    }

    private function getCampaignList()
    {
        $optimizationProps = new OptimizationProps();
        $optimizationProps->sourceEvent = EventType::EVENT_TYPE_INSTALL;
        $optimizationProps->measuredEvent = EventType::EVENT_TYPE_PURCHASE;
        $optimizationProps->threshold = 0;
        $optimizationProps->ratioThreshold = .11;

        return [
            new Campaign(3, $optimizationProps, [1, 2, 3]),
            new Campaign(5, $optimizationProps, [1, 2, 3]),
            new Campaign(8, $optimizationProps, [1, 2, 3]),
        ];
    }

    private function getCampaignSameIdsList()
    {
        $optimizationProps = new OptimizationProps();
        $optimizationProps->sourceEvent = EventType::EVENT_TYPE_INSTALL;
        $optimizationProps->measuredEvent = EventType::EVENT_TYPE_PURCHASE;
        $optimizationProps->threshold = 0;
        $optimizationProps->ratioThreshold = .11;

        return [
            new Campaign(1, $optimizationProps, [1, 2, 3]),
            new Campaign(1, $optimizationProps, [4, 5, 6]),
            new Campaign(1, $optimizationProps, [7, 8, 9]),
        ];
    }

    private function setCampaignDataSourceStub(callable $getCampaignList)
    {
        $this->campaignDataSourceStub
            ->method('getCampaigns')
            ->willReturn($getCampaignList());

        return $this->campaignDataSourceStub;
    }
}
