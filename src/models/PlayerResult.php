<?php

namespace Mako\Deadlockgametracker\Models;

class PlayerResult
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $heroId,
        public readonly int $team,
        public readonly int $kills,
        public readonly int $deaths,
        public readonly int $assists,
        public readonly int $souls,
    ) {}

    // build from one entry in match_info.players
    public static function fromArray(array $data): self
    {
        return new self(
            accountId: $data['account_id'],
            heroId: $data['hero_id'],
            team: $data['team'],
            kills: $data['kills'],
            deaths: $data['deaths'],
            assists: $data['assists'],
            souls: $data['net_worth'], // souls is called net_worth in the api
        );
    }
}