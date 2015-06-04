<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi API Sets Posts Controller
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application\Controllers
 * @copyright  2013 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

class Controller_API_SavedSearch_Posts extends Ushahidi_Rest {

	protected $_action_map =
	[
		Http_Request::GET => 'get'
	];

	protected $set_resource;

	protected function _scope()
	{
		return 'sets';
	}
	

	protected function _resource()
	{
		return 'saved_searches_posts';
	}

	/**
	 * Retrieve all posts attached to a set
	 *
	 * GET /api/saved-search/:set_id/posts
	 *
	 * @return void
	 */
	public function action_get_index_collection()
	{		
		parent::action_get_index_collection();

		$this->_usecase
			->setIdentifiers($this->_identifiers());
	}
}