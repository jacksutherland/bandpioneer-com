<?php
namespace verbb\auth\clients\salesforce\provider;

use Exception;
use InvalidArgumentException;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use verbb\auth\clients\salesforce\token\SalesforceAccessToken;

class Salesforce extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string Key used in a token response to identify the resource owner.
     */
    public const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';

    /**
     * Base domain used for authentication
     *
     * @var string
     */
    protected string $domain = 'https://login.salesforce.com';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain . '/services/oauth2/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->domain . '/services/oauth2/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $token->getResourceOwnerId();
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
     * Retrives the currently configured provider domain.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
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
                $data[0]['message'] ?? $response->getReasonPhrase(),
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
     * @return SalesforceResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): SalesforceResourceOwner
    {
        return new SalesforceResourceOwner($response);
    }

    /**
     * Updates the provider domain with a given value.
     *
     * @param string $domain
     * @return  Salesforce
     *@throws  InvalidArgumentException
     */
    public function setDomain(string $domain): Salesforce
    {
        try {
            $this->domain = $domain;
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                'Value provided as domain is not a string'
            );
        }

        return $this;
    }
}
