<?php namespace Friendica\Directory\Helper;

use \BadMethodCallException;

/**
 * Provides easy access to common helpers, to add them to your views.
 */
class Search extends BaseHelper
{

    public function filterAllUrl($query = '')
    {
		if (empty($query)) {
			$url = $this->app->get_baseurl() . '/directory';
		} else {
			$url = $this->app->get_baseurl() . '/search?query=' . urlencode($query);
		}
		return $url;
    }

    public function filterPeopleUrl($query = '')
    {
        if (empty($query)) {
			$url = $this->app->get_baseurl() . '/directory/people';
		} else {
			$url = $this->app->get_baseurl() . '/search/people?query=' . urlencode($query);
		}
		return $url;
    }

    public function filterForumsUrl($query = '')
    {
		if (empty($query)) {
			$url = $this->app->get_baseurl() . '/directory/forums';
		} else {
			$url = $this->app->get_baseurl() . '/search/forums?query=' . urlencode($query);
		}
		return $url;
    }

}