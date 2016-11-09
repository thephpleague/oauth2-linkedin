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
        return $this->getField('emailAddress');
    }

    /**
     * Get user firstname
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getField('firstName');
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl()
    {
        return $this->getField('pictureUrl');
    }

    /**
     * Get user lastname
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getField('lastName');
    }

    /**
     * Get user userId
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('id');
    }

    /**
     * Get user location
     *
     * @return string|null
     */
    public function getLocation()
    {
        if (isset($this->response['location']['name'])) {
            return $this->response['location']['name'];
        }
        return null;
    }

    /**
     * Get user description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getField('headline');
    }

    /**
     * Get user url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getField('publicProfileUrl');
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

    /**
     * Returns a field from the response data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->response[$key]) ? $this->response[$key] : null;
    }
}
