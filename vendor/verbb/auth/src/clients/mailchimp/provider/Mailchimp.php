<?php 

namespace verbb\auth\clients\mailchimp\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Mailchimp extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, `httpClient`, and `randomFactory`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://login.mailchimp.com/oauth2/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://login.mailchimp.com/oauth2/token';
    }

    /**
     * Get provider url to fetch account metadata
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://login.mailchimp.com/oauth2/metadata';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Scope separator, defaults to ','
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            throw new IdentityProviderException(
                $data['description'] ?? $response->getReasonPhrase(),
                $statusCode,
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return MailchimpResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): MailchimpResourceOwner
    {
        return new MailchimpResourceOwner($response);
    }
}
