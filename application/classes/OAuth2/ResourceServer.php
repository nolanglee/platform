<?php

/**
 * OAuth2 Resource Server
 *
 * License is MIT, to be more compatible with PHP League.
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\OAuth2
 * @copyright  2014 Ushahidi
 * @license    http://mit-license.org/
 * @link       http://github.com/php-loep/oauth2-server
 */

use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Storage\SessionInterface;
/**
 * @todo  This is still in the works - be gentle 
 */
class OAuth2_ResourceServer extends ResourceServer 
{
	protected $currentSession;

	public function getCurrentSession() 
	{
		if ($this->currentSession === null)
		{
			$this->setCurrentSession();	
		}

		return $this->currentSession; // maybe also check for correct instance ?
	}

	private function setCurrentSession()
	{
		$this->currentSession = $this->getSessionStorage()
									->getByAccessToken($this->getAccessToken());

		return $this;
	}

	public function getCurrentSessionScopes()
	{
		return $this->getCurrentSession()
					->getScopes($this->getCurrentSession());
	}

	public function getOwnerId()
	{
		return $this->getCurrentSession()
					->getOwnerId();
	}	

	public function hasScope($scope = null)
	{ 
		$scopes = $this->getCurrentSessionScopes();

		if (!$scope === null and in_array($scope, $scopes))
		{
			return true;
		}

		return false; // I do not like to have "has" method throw exceptions - it should just answer a question
	}

}