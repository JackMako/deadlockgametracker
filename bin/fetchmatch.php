<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Mako\Deadlockgametracker\Database;
use Mako\Deadlockgametracker\Api\DeadlockClient;
use Mako\Deadlockgametracker\Api\SteamClient;
use Mako\Deadlockgametracker\Models\MatchResult;
use Mako\Deadlockgametracker\Repositories\MatchRepo;
use Mako\Deadlockgametracker\Util\MatchFormatter;
use Mako\Deadlockgametracker\Util\SteamId;
use Mako\Deadlockgametracker\Api\DiscordWebhook;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required('STEAM_API_KEY')->notEmpty();

$api = new DeadlockClient();
$steam = new SteamClient($_ENV['STEAM_API_KEY']);
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


$trackedPlayers = array_map(
    [SteamId::class, 'toAccountId'],
    explode(',', $_ENV['TRACKED_PLAYERS'])
);

$recent = $api->getRecentMatchIds($trackedPlayers);

$matchesToSend = [] ;
foreach ($recent as $match) {
    if ($matchRepo->hasSeen($match)) {
        echo "already seen";
    } else {
        $matchRepo->markSeen($match);
        $matchesToSend[] = MatchResult::fromArray($api->getMatchMetadata($match));
    }
}

$webhook = new DiscordWebhook($_ENV['WEBHOOK_URL']);

foreach ($matchesToSend as $match) {
    $heroNames = $api->getHeroNames();
    $playerNames = $steam->getPlayerNames(array_map(fn($p) => $p->accountId, $match->players));
    $formatter = new MatchFormatter($heroNames, $playerNames);
    $webhook->send($formatter->format($match));
}




