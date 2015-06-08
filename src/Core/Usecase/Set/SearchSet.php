<?php

/**
 * Ushahidi Platform Set Search Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Set;

use Ushahidi\Core\Usecase\SearchUsecase;
use Ushahidi_Repository;

class SearchSet extends SearchUsecase
{
	/**
	 * Get filter parameters as search data.
	 *
	 * Override this to ensure we always search by
	 * for sets with search=false.
	 *
	 * @return SearchData
	 */
	protected function getSearch()
	{
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		// Force search=false as a filter. This ensures we always
		// get sets, not saved searches.
		// @todo just add this and then call parent?
		$filters['search'] = false;

		$this->search->setFilters(array_merge($paging, $filters));
		$this->search->setSortingKeys(array_keys($paging));

		return $this->search;
	}
}
