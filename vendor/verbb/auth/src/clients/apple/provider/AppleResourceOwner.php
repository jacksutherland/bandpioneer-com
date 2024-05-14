<?php namespace verbb\auth\clients\apple\provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * @property array $response
 * @property string $uid
 */
use League\OAuth2\Client\Provider\GenericResourceOwner;

class AppleResourceOwner extends GenericResourceOwner
{
    use ArrayAccessorTrait;

    /**
     * Raw response
     *
     * @var array
     */
    protected $response = [];

    /**
     * @var string|null
     */
    private ?string $email;

    /**
     * @var boolean true when its private relay from apple else the user mail address
     */
    private bool $isPrivateEmail;

    /**
     * Gets resource owner attribute by key. The key supports dot notation.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        return $this->getValueByKey($this->response, $key);
    }

    /**
     * Get user first name
     *
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        $name = $this->getAttribute('name');
        if (isset($name)) {
            return $name['firstName'];
        }
        return null;
    }

    /**
     * Get user user id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->resourceOwnerId;
    }

    /**
     * Get user last name
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        $name = $this->getAttribute('name');
        if (isset($name)) {
            return $name['lastName'];
        }
        return null;
    }

    /**
     * Get user email, if available
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->getAttribute('email');
    }

    /**
     * @return bool
     */
    public function isPrivateEmail(): bool
    {
        return (bool) $this->getAttribute('isPrivateEmail');
    }

}
