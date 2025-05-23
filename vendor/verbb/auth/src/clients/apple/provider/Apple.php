<?php

namespace verbb\auth\clients\apple\provider;

use Exception;
use Firebase\JWT\JWK;
use InvalidArgumentException;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use League\OAuth2\Client\Grant\AbstractGrant;
use verbb\auth\clients\apple\provider\exception\AppleAccessDeniedException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use verbb\auth\clients\apple\token\AppleAccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

use League\OAuth2\Client\Provider\AbstractProvider;
use DateTimeImmutable;
use Psr\Http\Message\RequestInterface;

class Apple extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Default scopes
     *
     * @var array
     */
    public array $defaultScopes = ['name', 'email'];

    /**
     * @var string the team id
     */
    protected string $teamId = '';

    /**
     * @var string the key file id
     */
    protected string $keyFileId = '';

    /**
     * @var string the key file path
     */
    protected string $keyFilePath = '';

    /**
     * Constructs Apple's OAuth 2.0 service provider.
     *
     * @param array $options
     * @param array $collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (empty($options['teamId'])) {
            throw new InvalidArgumentException('Required option not passed: "teamId"');
        }

        if (empty($options['keyFileId'])) {
            throw new InvalidArgumentException('Required option not passed: "keyFileId"');
        }

        if (empty($options['keyFilePath'])) {
            throw new InvalidArgumentException('Required option not passed: "keyFilePath"');
        }

        parent::__construct($options, $collaborators);
    }

    /**
     * Creates an access token from a response.
     *
     * The grant that was used to fetch the response can be used to provide
     * additional context.
     *
     * @param array $response
     * @param AbstractGrant $grant
     * @return AccessTokenInterface|AppleAccessToken
     * @throws Exception
     */
    protected function createAccessToken(array $response, AbstractGrant $grant): AccessTokenInterface|AppleAccessToken
    {
        return new AppleAccessToken($this->getAppleKeys(), $response);
    }

    /**
     * @return string[] Apple's JSON Web Keys
     */
    private function getAppleKeys(): array
    {
        $response = $this->httpClient->request('GET', 'https://appleid.apple.com/auth/keys');

        if ($response->getStatusCode() === 200) {
            return JWK::parseKeySet(json_decode($response->getBody()->__toString(), true));
        }

        return [];
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
     * Change response mode when scope requires it
     *
     * @param array $options
     *
     * @return array
     */
    protected function getAuthorizationParameters(array $options): array
    {
        $options = parent::getAuthorizationParameters($options);
        if (str_contains($options['scope'], 'name') || str_contains($options['scope'], 'email')) {
            $options['response_mode'] = 'form_post';
        }
        return $options;
    }

    /**
     * @param AccessToken $token
     *
     * @return mixed
     */
    protected function fetchResourceOwnerDetails(AccessToken $token): mixed
    {
        return json_decode(array_key_exists('user', $_GET) ? $_GET['user']
            : (array_key_exists('user', $_POST) ? $_POST['user'] : '[]'), true) ?: [];
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://appleid.apple.com/auth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://appleid.apple.com/auth/token';
    }

    /**
     * Get revoke token url to revoke token
     *
     */
    public function getBaseRevokeTokenUrl(array $params): string
    {
        return 'https://appleid.apple.com/auth/revoke';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     * @throws Exception
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        throw new Exception('No Apple ID REST API available yet!');
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
     * @param  ResponseInterface $response
     * @param  array             $data     Parsed response data
     * @return void
     * @throws AppleAccessDeniedException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new AppleAccessDeniedException(
                array_key_exists('error', $data) ? $data['error'] : $response->getReasonPhrase(),
                array_key_exists('code', $data) ? $data['code'] : $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return AppleResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): AppleResourceOwner
    {
        return new AppleResourceOwner(
            array_merge(
                ['sub' => $token->getResourceOwnerId()],
                $response,
                [
                    'email' => $token->getValues()['email'] ?? ($response['email'] ?? null),
                    'isPrivateEmail' => $token instanceof AppleAccessToken ? $token->isPrivateEmail() : null
                ]
            ),
            $token->getResourceOwnerId()
        );
    }

    public function getAccessToken($grant, array $options = []): AccessTokenInterface|AccessToken
    {
        $configuration = $this->getConfiguration();
        $time = new DateTimeImmutable();
        $time = $time->setTime($time->format('H'), $time->format('i'), $time->format('s'));
        $expiresAt = $time->modify('+1 Hour');
        $expiresAt = $expiresAt->setTime($expiresAt->format('H'), $expiresAt->format('i'), $expiresAt->format('s'));

        $token = $configuration->builder()
            ->issuedBy($this->teamId)
            ->permittedFor('https://appleid.apple.com')
            ->issuedAt($time)
            ->expiresAt($expiresAt)
            ->relatedTo($this->clientId)
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $this->keyFileId)
            ->getToken($configuration->signer(), $configuration->signingKey());

        $options += [
            'client_secret' => $token->toString()
        ];

        return parent::getAccessToken($grant, $options);
    }

    /**
     * Revokes an access or refresh token using a specified token.
     *
     * @param string $token
     * @param string|null $tokenTypeHint
     * @return RequestInterface
     */
    public function revokeAccessToken(string $token, string $tokenTypeHint = null): RequestInterface
    {
        $configuration = $this->getConfiguration();
        $time = new DateTimeImmutable();
        $time = $time->setTime($time->format('H'), $time->format('i'), $time->format('s'));
        $expiresAt = $time->modify('+1 Hour');
        $expiresAt = $expiresAt->setTime($expiresAt->format('H'), $expiresAt->format('i'), $expiresAt->format('s'));

        $clientSecret = $configuration->builder()
            ->issuedBy($this->teamId)
            ->permittedFor('https://appleid.apple.com')
            ->issuedAt($time)
            ->expiresAt($expiresAt)
            ->relatedTo($this->clientId)
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $this->keyFileId)
            ->getToken($configuration->signer(), $configuration->signingKey());

        $params = [
            'client_id'     => $this->clientId,
            'client_secret' => $clientSecret->toString(),
            'token'         => $token
        ];
        if ($tokenTypeHint !== null) {
            $params += [
                'token_type_hint' => $tokenTypeHint
            ];
        }

        $method  = $this->getAccessTokenMethod();
        $url     = $this->getBaseRevokeTokenUrl($params);
        if (property_exists($this, 'optionProvider')) {
            $options = $this->optionProvider->getAccessTokenOptions(self::METHOD_POST, $params);
        } else {
            $options = $this->getAccessTokenOptions($params);
        }
        $request = $this->getRequest($method, $url, $options);

        return $this->getParsedResponse($request);
    }

    /**
     * @return Configuration|null
     */
    public function getConfiguration(): ?Configuration
    {
        if (method_exists(Signer\Ecdsa\Sha256::class, 'create')) {
            return Configuration::forSymmetricSigner(
                Signer\Ecdsa\Sha256::create(),
                $this->getLocalKey()
            );
        }

        return Configuration::forSymmetricSigner(
            new Signer\Ecdsa\Sha256(),
            $this->getLocalKey()
        );
    }

    /**
     * @return Key
     */
    public function getLocalKey(): Key
    {
        return InMemory::file($this->keyFilePath);
    }
}
