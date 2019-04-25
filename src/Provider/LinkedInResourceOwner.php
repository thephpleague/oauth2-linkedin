<?php namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class LinkedInResourceOwner implements ResourceOwnerInterface
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
     * Gets resource owner attribute by key. The key supports dot notation.
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->getValueByKey($this->response, (string) $key);
    }

    /**
     * Get user firstname
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getAttribute('profile.localizedFirstName');
    }

    /**
     * Get user lastname
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getAttribute('profile.localizedLastName');
    }

    /**
     * Get user imageurl
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->getBiggestProfilePictureUrl();
    }

    /**
     * @return string|null
     */
    public function getBiggestProfilePictureUrl()
    {
        $pictures = $this->getSortedProfilePictures();
        $picture = array_pop($pictures);

        return $picture ? $picture['url'] : null;
    }

    /**
     * Get user userId
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getAttribute('profile.id');
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        $emailResponse = $this->getAttribute('email.elements');

        if (is_array($emailResponse)) {
            $emailResponse = array_filter($emailResponse, function ($element) {
                return
                    strtoupper($element['type']) === 'EMAIL'
                    && strtoupper($element['state']) === 'CONFIRMED'
                    && $element['primary'] === true
                    && isset($element['handle~']['emailAddress'])
                ;
            });
            $emailResponse = array_pop($emailResponse);
            $emailResponse = $emailResponse ? $emailResponse['handle~']['emailAddress'] : null;
        }

        return $emailResponse;
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
     * @return array|mixed
     */
    private function getSortedProfilePictures()
    {
        $pictures = $this->getAttribute('profile.profilePicture.displayImage~.elements');

        if ($pictures) {
            $pictures = array_filter($pictures, function ($element) {
                // filter to public images only
                return
                    isset($element['data']['com.linkedin.digitalmedia.mediaartifact.StillImage'])
                    && strtoupper($element['authorizationMethod']) === 'PUBLIC'
                    && isset($element['identifiers'][0]['identifier'])
                ;
            });

            // order images by width, LinkedIn profile pictures are always squares, so that should be good enough
            usort($pictures, function ($elementA, $elementB) {
                $wA = $elementA['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];
                $wB = $elementB['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];

                return $wA - $wB;
            });

            $pictures = array_map(function ($element) {
                // this is an URL, no idea how many of identifiers there can be, so take the first one.
                $url = $element['identifiers'][0]['identifier'];
                $type = $element['identifiers'][0]['mediaType'];
                $width = $element['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'];

                return [
                    'width' => $width,
                    'url' => $url,
                    'contentType' => $type,
                ];
            }, $pictures);
        } else {
            $pictures = [];
        }

        return $pictures;
    }
}
