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
     * @param array $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * A unique identifying value for the member.
     *
     * This value is linked to your specific application.
     * Any attempts to use it with a different application will
     * result in a "404 - Invalid member id" error.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }

    /**
     * The member's first name.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getValueByKey($this->response, 'firstName');
    }

    /**
     * The member's last name.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getValueByKey($this->response, 'lastName');
    }

    /**
     * The member's maiden name.
     *
     * @return string|null
     */
    public function getMaidenName()
    {
        return $this->getValueByKey($this->response, 'maidenName');
    }

    /**
     * The member's name, formatted based on language.
     *
     * @return string|null
     */
    public function getFormattedName()
    {
        return $this->getValueByKey($this->response, 'formattedName');
    }

    /**
     * The member's first name, spelled phonetically.
     *
     * @return string|null
     */
    public function getPhoneticFirstName()
    {
        return $this->getValueByKey($this->response, 'phoneticFirstName');
    }

    /**
     * The member's last name, spelled phonetically.
     *
     * @return string|null
     */
    public function getPhoneticLastName()
    {
        return $this->getValueByKey($this->response, 'phoneticLastName');
    }

    /**
     * The member's name, spelled phonetically and formatted based on language.
     *
     * @return string|null
     */
    public function getPhoneticFormattedName()
    {
        return $this->getValueByKey($this->response, 'phoneticFormattedName');
    }

    /**
     * The member's headline.
     *
     * @return string|null
     */
    public function getHeadline()
    {
        return $this->getValueByKey($this->response, 'headline');
    }

    /**
     * An object representing the user's physical location.
     * Available fields: name, country, country.code
     * Defaults to "name" for compatibility reasons
     *
     * @return string|array|null
     */
    public function getLocation($field = "name")
    {
        if (is_null($field)) {
            return $this->getValueByKey($this->response, 'location');
        } else {
            return $this->getValueByKey($this->response, 'location.' . $field);
        }
    }

    /**
     * The industry the member belongs to.
     * Available fields: name, country, country.code
     *
     * @return string|null
     */
    public function getIndustry()
    {
        return $this->getValueByKey($this->response, 'industry');
    }

    /**
     * The most recent item the member has shared on LinkedIn.
     * If the member has not shared anything, their 'status' is returned instead.
     *
     * @return string|null
     */
    public function getCurrentShare()
    {
        return $this->getValueByKey($this->response, 'currentShare');
    }

    /**
     * The number of LinkedIn connections the member has, capped at 500.
     * See 'num-connections-capped' to determine if the value returned has been capped.
     *
     * @return int|null
     */
    public function getNumConnections()
    {
        return $this->getValueByKey($this->response, 'numConnections');
    }

    /**
     * Returns 'true' if the member's 'num-connections' value has been capped at 500',
     * or 'false' if 'num-connections' represents the user's true value..
     *
     * @return bool|null
     */
    public function getNumConnectionsCapped()
    {
        return $this->getValueByKey($this->response, 'numConnectionsCapped');
    }

    /**
     * A long-form text area describing the member's professional profile.
     *
     * @return string|null
     */
    public function getSummary()
    {
        return $this->getValueByKey($this->response, 'summary');
    }

    /**
     * A short-form text area describing the member's specialties.
     *
     * @return string|null
     */
    public function getSpecialties()
    {
        return $this->getValueByKey($this->response, 'specialties');
    }

    /**
     * An object representing the member's current position.
     *
     * @return string|null
     */
    public function getPositions($field = null)
    {
        if (is_null($field)) {
            return $this->getValueByKey($this->response, 'positions');
        } else {
            return $this->getValueByKey($this->response, 'positions.' . $field);
        }
    }

    /**
     * A URL to the member's formatted profile picture, if one has been provided.
     *
     * @return string|null
     */
    public function getPictureUrl()
    {
        return $this->getValueByKey($this->response, 'pictureUrl');
    }

    /**
     * A URL to the member's original unformatted profile picture.
     * This image is usually larger than the picture-url value above.
     *
     * @return string|null
     */
    public function getPictureUrls()
    {
        return $this->getValueByKey($this->response, 'pictureUrls');
    }

    /**
     * The URL to the member's authenticated profile on LinkedIn.
     * You must be logged into LinkedIn to view this URL.
     *
     * @return string|array|null
     */
    public function getSiteStandardProfileRequest($field = 'url')
    {
        if (is_null($field)) {
            return $this->getValueByKey($this->response, 'siteStandardProfileRequest');
        } else {
            return $this->getValueByKey($this->response, 'siteStandardProfileRequest.' . $field);
        }
    }

    /**
     * A URL representing the resource you would request for
     * programmatic access to the member's profile.
     *
     * @return string|null
     */
    public function getApiStandardProfileRequest()
    {
        return $this->getValueByKey($this->response, 'apiStandardProfileRequest');
    }

    /**
     * A URL representing the resource you would request for
     * programmatic access to the member's profile.
     *
     * @return string|null
     */
    public function getPublicProfileUrl()
    {
        return $this->getValueByKey($this->response, 'publicProfileUrl');
    }

    /**
     * The LinkedIn member's primary email address.
     * Secondary email addresses associated with the member
     * are not available via the API.
     *
     * @return string|null
     */
    public function getEmailAddress()
    {
        return $this->getValueByKey($this->response, 'emailAddress');
    }

    /**
     * Obsolete, left for compatibility reasons
     * Get user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getEmailAddress();
    }

    /**
     * Obsolete, left for compatibiliy reasons.
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageurl()
    {
        return $this->getPictureUrl();
    }


    /**
     * Obsolete, left for compatibility reasons.
     * Get user description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getHeadline();
    }

    /**
     * Obsolete, left for compatibility reasons.
     * Get user url
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->getPublicProfileUrl();
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
