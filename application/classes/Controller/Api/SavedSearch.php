<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi API Sets Controller
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application\Controllers
 * @copyright  2013 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

class Controller_Api_SavedSearch extends Ushahidi_Rest {

	protected function _scope()
	{
		return 'sets';
	}

	protected function _resource()
	{
		return 'saved_searches';
	}

	/**
	 * Delete An Entity
	 *
	 * DELETE /api/foo/:id
	 *
	 * @return void
	 */
	public function action_delete_index()
	{
		$this->_usecase = service('factory.usecase')
			->get($this->_resource(), 'delete')
			->setIdentifiers($this->_identifiers());
	}
}
