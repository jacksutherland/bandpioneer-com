<?php

/*
 * Gumroad OAuth2 Provider
 * (c) alofoxx
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace verbb\auth\clients\gumroad\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use verbb\auth\clients\gumroad\provider\exception\GumroadIdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * Gumroad provider.
 *
 * @author alofoxx <alofoxx@gmail.com>
 */
class Gumroad extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * API Domain
     *
     * @var string
     */
    public string $apiDomain = 'https://gumroad.com';

    /**
     * Get authorization URL to begin OAuth flow
     * Note: Gumroad does not use /oauth2/ for url path of OAuth2!
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->apiDomain.'/oauth/authorize';
    }

    /**
     * Get access token URL to retrieve token
     * Note: Gumroad does not use /oauth2/ for url path of OAuth2!
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->apiDomain.'/oauth/token';
    }

    /**
     * Get provider URL to retrieve user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->apiDomain.'/api/v2/user';
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * Discord's scope separator is space (%20)
     *
     * @return string Scope separator
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
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
        return ['view_sales'];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw GumroadIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw GumroadIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return GumroadResourceOwner|ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token): GumroadResourceOwner|ResourceOwnerInterface
    {
        return new GumroadResourceOwner($response);
    }
}
