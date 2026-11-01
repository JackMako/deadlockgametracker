<?php
namespace Mako\Deadlockgametracker\Util;

class SteamID {
    private const STEAM64_OFFSET = 76561197960265728;

    // accepts steamid64 or account id and always returns the account id
    public static function toAccountId(int|string $id): int
    {
        $id = (int) $id;
        return $id > self::STEAM64_OFFSET ? $id - self::STEAM64_OFFSET : $id;
    }
}