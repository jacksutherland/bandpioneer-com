# Custom Provider
You can register your own Provider to add support for other social media platforms, or even extend an existing Provider.

```php
namespace modules\sitemodule;

use craft\events\RegisterComponentTypesEvent;
use modules\sitemodule\MyProvider;
use verbb\sociallogin\services\Providers;
use yii\base\Event;

Event::on(Providers::class, Providers::EVENT_REGISTER_PROVIDER_TYPES, function(RegisterComponentTypesEvent $event) {
    $event->types[] = MyProvider::class;
});
```

## Examples
There are 3 methods to creating a custom provider, and which one you choose depends on your needs - whether simple or complex.

### Auth Provider
Social Login makes use of the [Auth](https://github.com/verbb/auth) module to handle OAuth authorization and token handling. While Social Login providers have their own logic, we build on top of an [Auth](https://github.com/verbb/auth) provider. These in turn build off a [league/oauth2-client](https://github.com/thephpleague/oauth2-client) provider.

To create a custom provider, you must specify a [Auth](https://github.com/verbb/auth)-compatible provider. Have a look at the list of providers in the [Auth](https://github.com/verbb/auth) module, and if one is supported there already, you should use that.


```php
<?php
namespace modules\sitemodule;

use Craft;
use verbb\sociallogin\base\OAuthProvider;

use verbb\auth\providers\Facebook as FacebookProvider;

class Example extends OAuthProvider
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return 'Example Provider';
    }

    public static function getOAuthProviderClass(): string
    {
        return FacebookProvider::class;
    }


    // Properties
    // =========================================================================

    public static string $handle = 'example';


    // Public Methods
    // =========================================================================

    public function getPrimaryColor(): ?string
    {
        return '#000000';
    }

    public function getIcon(): ?string
    {
        return '<svg>...</svg>';
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('social-module/example/settings', [
            'provider' => $this,
        ]);
    }
}
```

### Generic Provider
As mentioned, you're required to provide a [Auth](https://github.com/verbb/auth) provider with your Social Login provider class. If Auth doesn't have a provider for your needs, you can use the `GenericProvider` class. This abstracts all things OAuth away from your class, and you just need to provide some basic settings like the authorization and access token endpoint URLs, any scopes, and more.

Create the following class to house your Provider logic.

```php
<?php
namespace modules\sitemodule;

use Craft;
use verbb\sociallogin\base\OAuthProvider;

use verbb\auth\providers\Generic as GenericProvider;

class Example1 extends OAuthProvider
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return 'Example 1 Provider';
    }

    public static function getOAuthProviderClass(): string
    {
        return GenericProvider::class;
    }


    // Properties
    // =========================================================================

    public static string $handle = 'example1';


    // Public Methods
    // =========================================================================

    public function getOAuthProviderConfig(): array
    {
        $config = parent::getOAuthProviderConfig();
        $config['urlAuthorize'] = 'https://accounts.zoho.com/oauth/v2/auth';
        $config['urlAccessToken'] = 'https://accounts.zoho.com/oauth/v2/token';
        $config['urlResourceOwnerDetails'] = 'https://accounts.zoho.com/oauth/user/info';
        $config['scopes'] = ['aaaserver.profile.READ'];
        $config['scopeSeparator'] = ' ';

        return $config;
    }

    public function getPrimaryColor(): ?string
    {
        return '#000000';
    }

    public function getIcon(): ?string
    {
        return '<svg>...</svg>';
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('social-module/example-1/settings', [
            'provider' => $this,
        ]);
    }
}
```

Here, you can see we're providing the `verbb\auth\providers\Generic` class to our `getOAuthProviderClass()` class. This will tell the integration which [Auth](https://github.com/verbb/auth) provider to use. We can then provide settings in the `getOAuthProviderConfig()` that are used in the initialization of the provider.

The settings here follow the [league/oauth2-client](https://github.com/thephpleague/oauth2-client) configuration. In our example above, we're using [Zoho CRM](https://www.zoho.com/en-au/crm/) for a real-world example.

### Custom Auth Provider
While using the `GenericProvider` is a great way to get start, you may find the need to have more control over the authorization flow. This could be for special handling of the access tokens, and lots more - it all depends on your provider.

So, instead of using the `GenericProvider`, we can create our own [Auth](https://github.com/verbb/auth) provider to use.

```php
<?php
namespace modules\sitemodule;

use Craft;
use verbb\sociallogin\base\OAuthProvider;

use modules\sitemodule\auth\Example2 as Example2Provider;

class Example1 extends OAuthProvider
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return 'Example 2 Provider';
    }

    public static function getOAuthProviderClass(): string
    {
        return Example2Provider::class;
    }


    // Properties
    // =========================================================================

    public static string $handle = 'example2';


    // Public Methods
    // =========================================================================

    public function getPrimaryColor(): ?string
    {
        return '#000000';
    }

    public function getIcon(): ?string
    {
        return '<svg>...</svg>';
    }

    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('social-module/example-2/settings', [
            'provider' => $this,
        ]);
    }
}
```

While almost exactly the same, we have removed the `getOAuthProviderConfig()` method and now return a new `modules\sitemodule\auth\Example2` class. 

Let's take a look at what the `Example2` class looks like.

```php
<?php
namespace modules\sitemodule\auth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use verbb\auth\base\ProviderTrait;

class Example2 extends AbstractProvider
{
    // Traits
    // =========================================================================

    use BearerAuthorizationTrait;
    use ProviderTrait;


    // Public Methods
    // =========================================================================

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.zoho.com/oauth/v2/auth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://accounts.zoho.com/oauth/v2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://accounts.zoho.com/oauth/user/info';
    }

    public function getApiUrl(): string
    {
        return 'https://www.zohoapis.com';
    }

    public function getBaseApiUrl(): ?string
    {
        return $this->getApiUrl();
    }


    // Protected Methods
    // =========================================================================

    protected function getDefaultScopes(): array
    {
        return ['aaaserver.profile.READ'];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                ($data['error']['message'] ?? $response->getReasonPhrase()),
                $response->getStatusCode(),
                $response
            );
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }
}
```

If you're familiar with [league/oauth2-client](https://github.com/thephpleague/oauth2-client) providers, this will look familiar. In fact an [Auth](https://github.com/verbb/auth) provider really is an extension of a [league/oauth2-client](https://github.com/thephpleague/oauth2-client) provider. The main inclusion will be `verbb\auth\base\ProviderTrait`, otherwise you can utilise any implementation details from [league/oauth2-client](https://github.com/thephpleague/oauth2-client).

In our example above, we're using [Zoho CRM](https://www.zoho.com/en-au/crm/) for a real-world example.


### Additional Settings
You can of course extend and create as many additional settings as you like. The Apple provider is a good example of this, where we're required to send more settings than a typical OAuth2 request.

```php
use craft\helpers\App;

public ?string $extraSetting = null;

public function getExtraSetting(): ?string
{
    return App::parseEnv($this->extraSetting);
}

protected function defineRules(): array
{
    $rules = parent::defineRules();
    $rules[] = [['extraSetting'], 'required'];

    return $rules;
}

public function isConfigured(): bool
{
    return parent::isConfigured() && $this->extraSetting;
}

public function getOAuthProviderConfig(): array
{
    $config = parent::getOAuthProviderConfig();
    $config['extraSetting'] = 'someValue';

    return $config;
}
```

### Adding Profile Fields
You can add a list of available fields that users can map to from the provider. This will be different for each provider. The `id` and `email` attribute are already included by default as this is the minimum requirement to map a social media provider user profile to a Craft user. `response` is also included which is the raw response from the user profile request.

To add more fields, include them according to what they are called in the provider API.

```php
public function getUserProfileFields(): array
{
    return [
        'birthday',
        'postalCode',
    ];
}
```