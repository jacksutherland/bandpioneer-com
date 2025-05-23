<?php

/*
 * GitLab OAuth2 Provider
 * (c) Omines Internetbureau B.V. - https://omines.nl/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace verbb\auth\clients\gitlab\provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use verbb\auth\clients\gitlab\provider\exception\GitLabIdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * GitLab.
 *
 * @author Niels Keurentjes <niels.keurentjes@omines.com>
 */
class GitLab extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public const PATH_API_USER = '/api/v4/user';
    public const PATH_AUTHORIZE = '/oauth/authorize';
    public const PATH_TOKEN = '/oauth/token';
    public const DEFAULT_SCOPE = 'api';
    public const SCOPE_SEPARATOR = ' ';

    public string $domain = 'https://gitlab.com';

    /**
     * GitLab constructor.
     */
    public function __construct(array $options, array $collaborators = [])
    {
        if (isset($options['domain'])) {
            $this->domain = $options['domain'];
        }
        parent::__construct($options, $collaborators);
    }

    /**
     * Get authorization url to begin OAuth flow.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return $this->domain . self::PATH_AUTHORIZE;
    }

    /**
     * Get access token url to retrieve token.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->domain . self::PATH_TOKEN;
    }

    /**
     * Get provider url to fetch user details.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->domain . self::PATH_API_USER;
    }

    /**
     * Get the default scopes used by GitLab.
     * Current scopes are 'api', 'read_user', 'openid'.
     *
     * This returns an array with 'api' scope as default.
     */
    protected function getDefaultScopes(): array
    {
        return [self::DEFAULT_SCOPE];
    }

    /**
     * GitLab uses a space to separate scopes.
     */
    protected function getScopeSeparator(): string
    {
        return self::SCOPE_SEPARATOR;
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response Parsed response data
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, mixed $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw GitLabIdentityProviderException::clientException($response, $data);
        }

        if (isset($data['error'])) {
            throw GitLabIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     */
    protected function createResourceOwner(array $response, AccessToken $token): ResourceOwnerInterface
    {
        $user = new GitLabResourceOwner($response, $token);

        return $user->setDomain($this->domain);
    }
}
