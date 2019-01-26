<?php namespace Acceptic\Test;

use Acceptic\Event\Event;
use Acceptic\Event\EventType;

class EventTest extends \Codeception\Test\Unit
{
    public function trueEventsProvider()
    {
        foreach (EventType::getItems() as $eventType) {
            yield [new Event(rand(), $eventType, rand(), rand(), new \DateTime())];
        }
    }

    public function falseEventsProvider()
    {
        foreach (['FalseType', '', 0, false] as $eventType) {
            yield [new Event(rand(), $eventType, rand(), rand(), new \DateTime())];
        }
    }

    /**
     * @dataProvider trueEventsProvider
     * @param Event $event
     */
    public function testIsValid(Event $event)
    {
        $this->assertTrue($event->isValid());
    }

    /**
     * @dataProvider falseEventsProvider
     * @param Event $event
     */
    public function testIsNotValid(Event $event)
    {
        $this->assertFalse($event->isValid());
    }
}