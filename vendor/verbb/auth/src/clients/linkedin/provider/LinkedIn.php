<?php

namespace verbb\auth\clients\linkedin\provider;

use verbb\auth\clients\linkedin\token\LinkedInAccessToken;
use verbb\auth\clients\linkedin\provider\exception\LinkedInAccessDeniedException;

use Exception;
use InvalidArgumentException;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class LinkedIn extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = ['openid', 'email', 'profile'];

    /**
     * Requested fields in scope, seeded with default values
     *
     * @var array
     * @see https://developer.linkedin.com/docs/fields/basic-profile
     */
    protected $fields = [
        'sub', 'name', 'given_name', 'family_name', 'picture', 'locale', 'email', 'email_verified'
    ];

    protected $restProtocolVersion;
    protected $restVersion;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, and `httpClient`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['fields']) && !is_array($options['fields'])) {
            throw new InvalidArgumentException('The fields option must be an array');
        }

        $this->restProtocolVersion = $options['restProtocolVersion'] ?? '';
        $this->restVersion = $options['restVersion'] ?? '';

        // Some APIs need to override the default scopes
        $this->defaultScopes = $options['defaultScopes'] ?? $this->defaultScopes;

        parent::__construct($options, $collaborators);
    }

    protected function getDefaultHeaders()
    {
        $headers = [];

        if ($this->restProtocolVersion) {
            $headers['X-Restli-Protocol-Version'] = $this->restProtocolVersion;
        }

        if ($this->restVersion) {
            $headers['LinkedIn-Version'] = $this->restVersion;
        }

        return $headers;
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param array $response
     * @param AbstractGrant $grant
     * @return AccessTokenInterface
     */
    protected function createAccessToken(array $response, AbstractGrant $grant): LinkedInAccessToken
    {
        return new LinkedInAccessToken($response);
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return [
            'Authorization' => "Bearer {$token}",
        ];
    }

    /**
     * Get the string used to separate scopes.
     *
     * @return string
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/authorization';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
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
        return 'https://api.linkedin.com/v2/userinfo';
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
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     * @return void
     * @throws IdentityProviderException
     * @see https://developer.linkedin.com/docs/guide/v2/error-handling
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        $this->checkResponseUnauthorized($response, $data);

        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['message'] ?? $response->getReasonPhrase(),
                $data['status'] ?? $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Check a provider response for unauthorized errors.
     *
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     * @return void
     * @throws LinkedInAccessDeniedException
     * @see https://developer.linkedin.com/docs/guide/v2/error-handling
     */
    protected function checkResponseUnauthorized(ResponseInterface $response, array $data): void
    {
        if (isset($data['status']) && $data['status'] === 403) {
            throw new LinkedInAccessDeniedException(
                $data['message'] ?? $response->getReasonPhrase(),
                $data['status'],
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return LinkedInResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): LinkedInResourceOwner
    {
        return new LinkedInResourceOwner($response);
    }

    /**
     * Returns the requested fields in scope.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Updates the requested fields in scope.
     *
     * @param array $fields
     *
     * @return LinkedIn
     */
    public function withFields(array $fields): LinkedIn
    {
        $this->fields = $fields;

        return $this;
    }
}