<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\vimeo\provider\Vimeo as VimeoProvider;
use verbb\auth\models\Token;

class Vimeo extends VimeoProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return 'https://api.vimeo.com/';
    }

    public function getApiRequestQueryParams(?Token $token): array
    {
        return [
            'access_token' => (string)($token?->getToken() ?? ''),
        ];
    }
}