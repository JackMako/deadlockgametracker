<?php
namespace Mako\Deadlockgametracker\Api;

use GuzzleHttp\Client;

class SteamClient
{
    private const STEAM64_OFFSET = 76561197960265728;
    private Client $client;

    public function __construct(private string $apiKey)
    {
        $this->client = new Client(['base_uri' => 'https://api.steampowered.com/', 'timeout' => 10]);
    }

    // takes deadlock account ids, returns [accountId => 'steam name']
    public function getPlayerNames(array $accountIds): array
    {
        $steamIds = array_map(fn($id) => $id + self::STEAM64_OFFSET, $accountIds);

        $response = $this->client->request('GET', 'ISteamUser/GetPlayerSummaries/v2/', [
            'query' => ['key' => $this->apiKey, 'steamids' => implode(',', $steamIds)],
        ]);
        $players = json_decode($response->getBody(), true)['response']['players'];

        $names = [];
        foreach ($players as $p) {
            $names[(int) $p['steamid'] - self::STEAM64_OFFSET] = $p['personaname'];
        }
        return $names;
    }
}
