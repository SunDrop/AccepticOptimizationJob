<?php namespace Acceptic\Test;

use Acceptic\Campaign\Campaign;
use Acceptic\Campaign\CampaignAnalyser;
use Acceptic\Campaign\CampaignEventAggregator;
use Acceptic\Campaign\OptimizationProps;
use Acceptic\Event\Event;
use Acceptic\Event\EventType;
use Acceptic\Publisher\NotificationType;
use Acceptic\Publisher\PublisherNotifier;
use Psr\Log\NullLogger;

class CampaignAnalyserTest extends \Codeception\Test\Unit
{
    /** @var array */
    private $campaigns;

    /** @var CampaignEventAggregator */
    private $campaignEventAggregator;

    /** @var PublisherNotifier */
    private $publisherNotifier;

    /** @var CampaignAnalyser */
    private $campaignAnalyser;

    public function testCampaignsNotInitializedException()
    {
        $campaignAnalyser = new CampaignAnalyser();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp(sprintf('/%s/i', 'campaigns'));
        $campaignAnalyser->run();
    }

    public function testCampaignEventAggregatorNotInitializedException()
    {
        $campaignAnalyser = new CampaignAnalyser();
        $this->expectException(\LogicException::class);
        $campaignAnalyser->setCampaigns($this->prepareCampaign());
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp(sprintf('/%s/i', 'CampaignEventAggregator'));
        $campaignAnalyser->run();
    }

    public function testPublisherNotifierNotInitializedException()
    {
        $campaignAnalyser = new CampaignAnalyser();
        $this->expectException(\LogicException::class);
        $campaignAnalyser->setCampaigns($this->prepareCampaign());
        $campaignAnalyser->setCampaignEventAggregator($this->prepareCampaignEventAggregator());
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageRegExp(sprintf('/%s/i', 'PublisherNotifier'));
        $campaignAnalyser->run();
    }

    public function testCheckNotifications()
    {
        $this->campaignAnalyser
            ->setPublisherNotifier($this->getPublisherNotifierStub())
            ->run();
    }

    public function testCheckBlacklist()
    {
        $this->campaignAnalyser
            ->setCampaigns($this->getCampaignStub())
            ->setPublisherNotifier($this->getPublisherNotifierStub())
            ->run();
    }

    protected function _before()
    {
        $this->campaigns = $this->prepareCampaign();
        $this->campaignEventAggregator = $this->prepareCampaignEventAggregator();
        $this->publisherNotifier = $this->preparePublisherNotifier();
        $this->campaignAnalyser = (new CampaignAnalyser())
            ->setCampaigns($this->campaigns)
            ->setCampaignEventAggregator($this->campaignEventAggregator)
            ->setPublisherNotifier($this->publisherNotifier);
    }

    private function prepareCampaign()
    {
        $optimizationProps = new OptimizationProps();
        $optimizationProps->sourceEvent = EventType::EVENT_TYPE_INSTALL;
        $optimizationProps->measuredEvent = EventType::EVENT_TYPE_PURCHASE;
        $optimizationProps->threshold = 1;
        $optimizationProps->ratioThreshold = .10;

        return [
            1 => new Campaign(1, $optimizationProps, [1, 2, 10]),
        ];
    }

    private function getEventsList()
    {
        /**
         * publisher(1) => [open => 1, install => 100, purchase => 12] -> test unblocked
         * publisher(2) => [install => 2] -> test stay blocked
         * publisher(3) => [open => 2] -> test not banned
         * publisher(4) => [install => 100, purchase => 9] -> test blocked
         * publisher(10) => [] -> test stay blocked
         */
        // Publisher Id = 1
        yield new Event(rand(), EventType::EVENT_TYPE_APP_OPEN, 1, 1, new \DateTime());
        for ($i = 0; $i < 100; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_INSTALL, 1, 1, new \DateTime());
        }
        for ($i = 0; $i < 12; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_PURCHASE, 1, 1, new \DateTime());
        }

        // Publisher Id = 2
        for ($i = 0; $i < 2; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_INSTALL, 1, 2, new \DateTime());
        }

        // Publisher Id = 3
        for ($i = 0; $i < 2; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_APP_OPEN, 1, 3, new \DateTime());
        }

        // Publisher Id = 4
        for ($i = 0; $i < 100; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_INSTALL, 1, 4, new \DateTime());
        }
        for ($i = 0; $i < 9; ++$i) {
            yield new Event(rand(), EventType::EVENT_TYPE_PURCHASE, 1, 4, new \DateTime());
        }
    }

    private function preparePublisherNotifier()
    {
        return new PublisherNotifier(new NullLogger());
    }

    /**
     * @return PublisherNotifier
     */
    private function getPublisherNotifierStub()
    {
        $mock = $this->getMockBuilder(PublisherNotifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock
            ->expects($this->exactly(2))
            ->method('notify')
            ->willReturnCallback(function () {
                $args = \func_get_args();
                $notificationType = \array_shift($args);
                /**
                 * 1) Test blocked publisher 4
                 * 2) Test unblocked publisher 1
                 */
                switch ($notificationType) {
                    case NotificationType::TYPE_BLOCKED:
                        $this->assertEquals(1, \count($args));
                        $this->assertEquals([4], $args);
                        break;
                    case NotificationType::TYPE_UNBLOCKED:
                        $this->assertEquals(1, \count($args));
                        $this->assertEquals([1], $args);
                        break;
                    default:
                        $this->assertFalse(true);
                        break;
                }
            });
        /** @var PublisherNotifier $mock */
        return $mock;
    }

    private function getCampaignStub()
    {
        $optimizationProps = new OptimizationProps();
        $optimizationProps->sourceEvent = EventType::EVENT_TYPE_INSTALL;
        $optimizationProps->measuredEvent = EventType::EVENT_TYPE_PURCHASE;
        $optimizationProps->threshold = 1;
        $optimizationProps->ratioThreshold = .10;

        $campaignMock = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveBlacklist'])
            ->getMock();
        // now call the constructor
        $reflectedClass = new \ReflectionClass(Campaign::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($campaignMock, ...[1, $optimizationProps, [1, 2, 10]]);

        /**
         * Test in blacklist publishers [2, 4, 10]
         * Publisher 1 will move to whitelist
         * Publisher 4 will add to blacklist
         * Publisher 2 and 10 will still stay in blacklist
         */
        $campaignMock->expects($this->once())->method('saveBlacklist')->willReturnCallback(function () {
            $args = \func_get_args();
            $this->assertIsArray($args);
            $this->assertCount(1, $args);
            $blacklist = $args[0];
            $this->assertIsArray($blacklist);
            $this->assertEquals([2, 4, 10], $blacklist);
        });

        return [
            1 => $campaignMock,
        ];
    }

    private function prepareCampaignEventAggregator()
    {
        $campaignEventAggregator = new CampaignEventAggregator();
        foreach ($this->getEventsList() as $event) {
            $campaignEventAggregator->add($event);
        }

        return $campaignEventAggregator;
    }
}
