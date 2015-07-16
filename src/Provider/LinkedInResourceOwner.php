<?php namespace League\OAuth2\Client\Provider;

/**
 * @property array $response
 * @property string $uid
 */
class LinkedInResourceOwner extends GenericResourceOwner
{
    /**
     * Get user email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->response['emailAddress'] ?: null;
    }

    /**
     * Get user firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->response['firstName'] ?: null;
    }

    /**
     * Get user imageurl
     *
     * @return string
     */
    public function getImageurl()
    {
        return $this->response['pictureUrl'] ?: null;
    }

    /**
     * Get user lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->response['lastName'] ?: null;
    }

    /**
     * Get user userId
     *
     * @return string
     */
    public function getId()
    {
        return $this->resourceOwnerId;
    }

    /**
     * Get user location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->response['location']['name'] ?: null;
    }

    /**
     * Get user description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->response['headline'] ?: null;
    }

    /**
     * Get user url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->response['publicProfileUrl'] ?: null;
    }
}
