<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi API Collections Posts Controller
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application\Controllers
 * @copyright  2013 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

class Controller_API_Collections_Posts extends Ushahidi_Rest {

	protected function _scope()
	{
		// @todo rename scope
		return 'sets';
	}

	protected function _resource()
	{
		return 'collections_posts';
	}

	public function action_get_index_collection()
	{
		parent::action_get_index_collection();

		$this->_usecase
			// Send through parent collection id
			->setIdentifiers($this->_identifiers())
			// And add parent collection id to the filters
			->setFilters($this->request->query() + [
				'set_id' => $this->request->param('set_id')
			]);
	}

	public function action_post_index_collection()
	{
		parent::action_post_index_collection();

		// Merge IDs and payload so that set id appears
		// in the payload.
		// @todo use setIdentifiers and handle this properly in the usecase
		$this->_usecase
			->setPayload(array_merge($this->_payload(), $this->_identifiers()));
	}
}
