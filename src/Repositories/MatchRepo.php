<?php
namespace Mako\Deadlockgametracker\repositories;

use Mako\Deadlockgametracker\Database;

class MatchRepo{

    private database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function hasSeen(int $matchId) : bool {
        $result = $this->db->query(
            'SELECT 1 FROM seen_matches WHERE match_id = ?',
            [$matchId]
        )->fetchColumn();

        return $result !== false;
    }

    public function markSeen(int $matchId)
    {
        $this->db->query(
            'INSERT IGNORE INTO seen_matches (match_id) VALUES (?)',
            [$matchId]
        );
    }

}
