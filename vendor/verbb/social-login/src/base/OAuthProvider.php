<?php
namespace verbb\sociallogin\base;

use verbb\sociallogin\SocialLogin;

use Craft;
use craft\helpers\UrlHelper;

use verbb\auth\base\OAuthProviderInterface;
use verbb\auth\base\OAuthProviderTrait;
use verbb\auth\models\Token;
use verbb\auth\models\UserProfile;

abstract class OAuthProvider extends Provider implements OAuthProviderInterface
{
    // Traits
    // =========================================================================

    use OAuthProviderTrait;
    

    // Public Methods
    // =========================================================================

    public function settingsAttributes(): array
    {
        // These won't be picked up in a Trait
        $attributes = parent::settingsAttributes();
        $attributes[] = 'clientId';
        $attributes[] = 'clientSecret';

        return $attributes;
    }

    public function isConfigured(): bool
    {
        return $this->clientId && $this->clientSecret;
    }

    public function getRedirectUri(): ?string
    {
        // Use the current or primary site for the redirect
        $siteId = Craft::$app->getSites()->getCurrentSite()->id ?? Craft::$app->getSites()->getPrimarySite()->id;

        // Special-case for when `cpTrigger` is empty to signify split front/back end Craft installs
        if (!Craft::$app->getConfig()->getGeneral()->cpTrigger) {
            return UrlHelper::actionUrl('social-login/auth/callback');
        }

        return UrlHelper::siteUrl('social-login/auth/callback', null, null, $siteId);
    }

    public function getAuthorizationUrlOptions(): array
    {
        // Use any auth options defined in config files
        $options = $this->authorizationOptions;

        // Combine default scopes at the provider level, with account level ones, and any in the config.
        $defaultScopes = $this->getOAuthProvider()->defaultScopes();
        $options['scope'] = array_values(array_unique(array_merge($defaultScopes, $this->scopes)));

        return $options;
    }

    public function getUserProfile(Token $token): UserProfile
    {
        if ($this->getIsOAuth1()) {
            $resource = $this->getOAuthProvider()->getUserDetails($token->getToken());
        } else {
            $resource = $this->getOAuthProvider()->getResourceOwner($token->getToken());
        }

        return new UserProfile($resource);
    }

    public function getToken(): ?Token
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if ($currentUser) {
            if ($connection = SocialLogin::$plugin->getConnections()->getConnectionByUserAndProvider($currentUser->id, $this->handle)) {
                return $connection->getToken();
            }
        }

        return null; 
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [
            ['clientId', 'clientSecret'], 'required', 'when' => function($model) {
                return $model->enabled;
            },
        ];

        return $rules;
    }
}