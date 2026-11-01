<?php
namespace Mako\Deadlockgametracker\Api;

use GuzzleHttp\Client;

class DiscordWebhook
{
    private Client $client;

    public function __construct(private string $webhookUrl)
    {
        $this->client = new Client(['timeout' => 10]);
    }

    // post a plain text message to the channel
    public function send(string $message): void
    {
        $this->client->post($this->webhookUrl, [
            'json' => ['content' => $message],
        ]);
    }
}