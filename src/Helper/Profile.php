<?php namespace Friendica\Directory\Helper;

use \BadMethodCallException;

/**
 * Provides easy access to common helpers, to add them to your views.
 */
class Profile extends BaseHelper
{
    
    public function photoUrl($profileId)
    {
        return $this->app->get_baseurl() . '/photo/' . $profileId;
    }
    
}