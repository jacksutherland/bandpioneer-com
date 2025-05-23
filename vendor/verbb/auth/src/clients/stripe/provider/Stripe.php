<?php

namespace verbb\auth\clients\stripe\provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

class Stripe extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://connect.stripe.com/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://connect.stripe.com/oauth/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.stripe.com/v1/account';
    }

    /**
     * Get deauthorization url to end OAuth flow
     *
     * @return string
     */
    public function getBaseDeauthorizationUrl(): string
    {
        return 'https://connect.stripe.com/oauth/deauthorize';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return ['read_only'];
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string $data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     *
     * @return StripeResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): StripeResourceOwner
    {
        return new StripeResourceOwner($response);
    }

    protected function createAccessToken(array $response, AbstractGrant $grant): AccessTokenInterface|AccessToken
    {
        $accessToken = parent::createAccessToken($response, $grant);

        // create the parent access token and add properties from response
        foreach ($response as $k => $v) {
            if (!property_exists($accessToken, $k)) {
                $accessToken->$k = $v;
            }
        }

        return $accessToken;
    }

    /**
     * @param string $stripeUserId stripe account ID
     *
     * @return mixed
     */
    public function deauthorize(string $stripeUserId): mixed
    {
        $request = $this->createRequest(
            self::METHOD_POST,
            $this->getBaseDeauthorizationUrl(),
            null,
            [
                'body' => $this->buildQueryString([
                    'stripe_user_id' => $stripeUserId,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]),
            ]
        );

        return $this->getParsedResponse($request);
    }
}
