<?php

namespace Acceptic\Event;

class EventType
{
    public const EVENT_TYPE_INSTALL = 'install';
    public const EVENT_TYPE_APP_OPEN = 'app_open';
    public const EVENT_TYPE_REGISTRATION = 'registration';
    public const EVENT_TYPE_PURCHASE = 'purchase';

    public static function getItems(): array
    {
        return [
            self::EVENT_TYPE_INSTALL,
            self::EVENT_TYPE_APP_OPEN,
            self::EVENT_TYPE_REGISTRATION,
            self::EVENT_TYPE_PURCHASE,
        ];
    }
}
