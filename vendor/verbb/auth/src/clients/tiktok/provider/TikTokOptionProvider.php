<?php

namespace verbb\auth\clients\tiktok\provider;

use League\OAuth2\Client\OptionProvider\OptionProviderInterface;

class TikTokOptionProvider implements OptionProviderInterface
{
    public function getAccessTokenOptions($method, array $params): array
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => json_encode($params),
        ];
    }
}
