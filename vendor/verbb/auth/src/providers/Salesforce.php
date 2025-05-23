<?php
namespace verbb\auth\providers;

use verbb\auth\base\ProviderTrait;
use verbb\auth\clients\salesforce\provider\Salesforce as SalesforceProvider;
use verbb\auth\models\Token;

class Salesforce extends SalesforceProvider
{
    // Traits
    // =========================================================================

    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseApiUrl(?Token $token): ?string
    {
        return null;
    }
}