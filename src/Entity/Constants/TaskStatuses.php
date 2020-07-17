<?php

namespace App\Entity\Constants;

/**
 * Class TaskStatuses
 */
class TaskStatuses
{
    public const STATUS_CREATED   = 'created';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED  = 'canceled';

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_SCHEDULED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELED,
        ];
    }
}
