<?php
namespace Mako\Deadlockgametracker\Models;

class MatchResult
{
    /**
     * @param PlayerResult[] $players
     */
    public function __construct(
        public readonly int $matchId,
        public readonly int $startTime,
        public readonly int $durationSeconds,
        public readonly int $winningTeam,
        public readonly array $players,
    ) {}

    // takes the whole metadata response from the api
    public static function fromArray(array $data): self
    {
        $info = $data['match_info'];

        return new self(
            matchId: $info['match_id'],
            startTime: $info['start_time'],
            durationSeconds: $info['duration_s'],
            winningTeam: $info['winning_team'],
            players: array_map(
                fn(array $p) => PlayerResult::fromArray($p),
                $info['players']
            ),
        );
    }

    public function didWin(PlayerResult $player): bool
    {
        return $player->team === $this->winningTeam;
    }

    public function findPlayer(int $accountId): ?PlayerResult
    {
        foreach ($this->players as $player) {
            if ($player->accountId === $accountId) {
                return $player;
            }
        }

        return null;
    }


    public function toString() {

    }
}