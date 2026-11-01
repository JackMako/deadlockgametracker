<?php
namespace Mako\Deadlockgametracker\Util;

use Mako\Deadlockgametracker\Models\MatchResult;

class MatchFormatter
{
    public function __construct(
        private array $heroNames,   // [heroId => name]
        private array $playerNames, // [accountId => name]
    ) {}

    public function format(MatchResult $match): string
    {
//        $me = $match->findPlayer($trackedAccountId);
//        $result = $match->didWin($me) ? 'WIN' : 'LOSS';
        $duration = gmdate('i:s', $match->durationSeconds);

        $lines = [];
        $lines[] = "```";

        foreach ([0, 1] as $team) {
            $lines[] = $team === $match->winningTeam ? "-- Team {$team} (won) --" : "-- Team {$team} --";

            foreach ($match->players as $p) {
                if ($p->team !== $team) continue;

                $lines[] = sprintf(
                    '%-16s %-12s %2d/%2d/%2d  %6s souls',
                    mb_strimwidth($this->name($p->accountId), 0, 16, '…'),
                    $this->hero($p->heroId),
                    $p->kills, $p->deaths, $p->assists,
                    number_format($p->souls)
                );
            }
        }

        $lines[] = "```";
        return implode("\n", $lines);
    }

    private function hero(int $id): string
    {
        return $this->heroNames[$id] ?? "Hero #{$id}";
    }

    private function name(int $id): string
    {
        return $this->playerNames[$id] ?? "Player #{$id}";
    }
}
