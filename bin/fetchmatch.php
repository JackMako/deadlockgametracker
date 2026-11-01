<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Mako\Deadlockgametracker\Database;
use Mako\Deadlockgametracker\Api\DeadlockClient;
use Mako\Deadlockgametracker\Api\SteamClient;
use Mako\Deadlockgametracker\Api\DiscordWebhook;
use Mako\Deadlockgametracker\Models\MatchResult;
use Mako\Deadlockgametracker\Repositories\MatchRepo;
use Mako\Deadlockgametracker\Util\MatchFormatter;
use Mako\Deadlockgametracker\Util\SteamId;

// load config and fail early if anything is missing
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required([
    'STEAM_API_KEY',
    'TRACKED_PLAYERS',
    'WEBHOOK_URL',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASSWORD',
])->notEmpty();

// build everything once
$api = new DeadlockClient();
$steam = new SteamClient($_ENV['STEAM_API_KEY']);
$webhook = new DiscordWebhook($_ENV['WEBHOOK_URL']);

$db = new Database(
    [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'dbname' => $_ENV['DB_NAME'],
    ],
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD']
);
$matchRepo = new MatchRepo($db);

// steamid64s from env into account ids
$trackedPlayers = array_map(
    [SteamId::class, 'toAccountId'],
    array_filter(array_map('trim', explode(',', $_ENV['TRACKED_PLAYERS'])))
);

// unique match ids across all tracked players
$recent = $api->getRecentMatchIds($trackedPlayers);

// hero names never change mid run so fetch once
$heroNames = $api->getHeroNames();

$posted = 0;

foreach ($recent as $matchId) {
    if ($matchRepo->hasSeen($matchId)) {
        continue;
    }

    try {
        $match = MatchResult::fromArray($api->getMatchMetadata($matchId));
        $playerNames = $steam->getPlayerNames(array_map(fn($p) => $p->accountId, $match->players));
        $formatter = new MatchFormatter($heroNames, $playerNames);

        // one post per match
        $webhook->send($formatter->format($match));
        $posted++;
        usleep(500000); // stay under discords rate limit

        // only mark seen once its actually posted
        $matchRepo->markSeen($matchId);
    } catch (Throwable $e) {
        // one bad match shouldnt kill the run and it retries next time
        echo "[" . date('Y-m-d H:i:s') . "] match {$matchId} failed: {$e->getMessage()}" . PHP_EOL;
    }
}

echo "[" . date('Y-m-d H:i:s') . "] checked " . count($recent) . ", posted {$posted}" . PHP_EOL;