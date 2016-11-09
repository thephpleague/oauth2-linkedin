<?php namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;

/**
 * @property array $response
 * @property string $uid
 */
class LinkedInResourceOwner extends GenericResourceOwner
{

    use ArrayAccessorTrait;

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
        return $this->getValueByKey($this->response, 'emailAddress');
    }

    /**
     * Get user firstname
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'firstName');
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl()
    {
        return $this->getValueByKey($this->response, 'pictureUrl');
    }

    /**
     * Get user lastname
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'lastName');
    }

    /**
     * Get user userId
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * Get user location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->getValueByKey($this->response, 'location.name');
    }

    /**
     * Get user description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getValueByKey($this->response, 'headline');
    }

    /**
     * Get user url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getValueByKey($this->response, 'publicProfileUrl');
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
