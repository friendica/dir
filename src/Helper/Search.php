<?php namespace Friendica\Directory\Helper;

use \BadMethodCallException;

/**
 * Provides easy access to common helpers, to add them to your views.
 */
class Search extends BaseHelper
{
    
    public function filterAllUrl($query)
    {
        return $this->app->get_baseurl() . '/search?query=' . urlencode($query);
    }
    
    public function filterPeopleUrl($query)
    {
        return $this->app->get_baseurl() . '/search/people?query=' . urlencode($query);
    }
    
    public function filterForumsUrl($query)
    {
        return $this->app->get_baseurl() . '/search/forums?query=' . urlencode($query);
    }
    
}