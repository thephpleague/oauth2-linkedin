<?php namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\UserInterface;

class User implements UserInterface
{
    /**
     * User email
     *
     * @var string
     */
    protected $email;

    /**
     * User firstname
     *
     * @var string
     */
    protected $firstname;

    /**
     * User imageurl
     *
     * @var string
     */
    protected $imageurl;

    /**
     * User lastname
     *
     * @var string
     */
    protected $lastname;

    /**
     * User userId
     *
     * @var string
     */
    protected $userId;

    /**
     * User location
     *
     * @var string
     */
    protected $location;

    /**
     * User description
     *
     * @var string
     */
    protected $description;

    /**
     * User url
     *
     * @var string
     */
    protected $url;

    /**
     * Create new user
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        array_walk($attributes, [$this, 'mergeAttribute']);
    }

    /**
     * Attempt to merge individual attributes with user properties
     *
     * @param  mixed   $value
     * @param  string  $key
     *
     * @return void
     */
    private function mergeAttribute($value, $key)
    {
        $method = 'set'.ucfirst($key);

        if (method_exists($this, $method)) {
            $this->$method($value);
        }
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user email
     *
     * @param  string $email
     *
     * @return this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get user firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set user firstname
     *
     * @param  string $firstname
     *
     * @return this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get user imageurl
     *
     * @return string
     */
    public function getImageurl()
    {
        return $this->imageurl;
    }

    /**
     * Set user imageurl
     *
     * @param  string $imageurl
     *
     * @return this
     */
    public function setImageurl($imageurl)
    {
        $this->imageurl = $imageurl;

        return $this;
    }

    /**
     * Get user lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set user lastname
     *
     * @param  string $lastname
     *
     * @return this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get user userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user userId
     *
     * @param  string $userId
     *
     * @return this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get user location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set user location
     *
     * @param  string $location
     *
     * @return this
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get user description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set user description
     *
     * @param  string $description
     *
     * @return this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get user url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set user url
     *
     * @param  string $url
     *
     * @return this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
