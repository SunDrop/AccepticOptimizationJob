<?php

namespace Acceptic\Publisher;

class NotificationType
{
    public const TYPE_BLOCKED = 'blocked';

    public const TYPE_UNBLOCKED = 'unblocked';

    public static function getItems(): array
    {
        return [
            self::TYPE_BLOCKED,
            self::TYPE_UNBLOCKED,
        ];
    }
}