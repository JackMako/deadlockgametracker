<?php

namespace Mako\Deadlockgametracker\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DeadlockClient
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.deadlock-api.com/',
            'timeout'  => 25,
        ]);
    }


    private function getMatchHistory(int $accountId): array
    {
        $response = $this->client->request('GET', "v1/players/{$accountId}/match-history");

        return json_decode($response->getBody(), true);
    }


    public function getRecentMatchIds(array $accountIds, int $count = 5): array
    {
        $ids = [];

        foreach ($accountIds as $id) {
            try {
                $data = $this->getMatchHistory($id);
            } catch (GuzzleException $e) {
                error_log("failed to fetch matches for {$id}: " . $e->getMessage());
                continue;
            }

            usort($data, fn($a, $b) => $b['start_time'] <=> $a['start_time']);
            $recent = array_slice($data, 0, $count);


            $ids = array_merge($ids, array_column($recent, 'match_id'));
        }

        return array_values(array_unique($ids));
    }

    public function getMatchMetadata($matchId) {
        $response = $this->client->request('GET', "v1/matches/{$matchId}/metadata");

        return json_decode($response->getBody(), true);
    }


    public function getHeroNames(): array
    {
        $response = $this->client->request('GET', 'https://api.deadlock-api.com/v1/assets/heroes');
        $heroes = json_decode($response->getBody(), true);

        return array_column($heroes, 'name', 'id');
    }

}