<?php namespace League\OAuth2\Client\Provider;

/**
 * @property array $response
 * @property string $uid
 */
class LinkedInResourceOwner extends GenericResourceOwner
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['emailAddress'] ?: null;
    }

    /**
     * Get user firstname
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->response['firstName'] ?: null;
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl()
    {
        return $this->response['pictureUrl'] ?: null;
    }

    /**
     * Get user lastname
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['lastName'] ?: null;
    }

    /**
     * Get user userId
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['id'] ?: null;
    }

    /**
     * Get user location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->response['location']['name'] ?: null;
    }

    /**
     * Get user description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->response['headline'] ?: null;
    }

    /**
     * Get user url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->response['publicProfileUrl'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
