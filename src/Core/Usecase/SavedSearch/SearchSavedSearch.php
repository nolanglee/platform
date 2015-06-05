<?php

/**
 * Ushahidi Platform Saved Search Search Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\SavedSearch;

use Ushahidi\Core\Usecase\SearchUsecase;
use Ushahidi_Repository;

class SearchSavedSearch extends SearchUsecase
{
	/**
	 * Get filter parameters as search data.
	 *
	 * Override this to ensure we always search by
	 * for saved searches with search=true.
	 *
	 * @return SearchData
	 */
	protected function getSearch()
	{
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		// Force search=true as a filter. This ensures we always
		// get saved search, not collections.
		// @todo just add this and then call parent?
		$filters['search'] = true;

		$this->search->setFilters(array_merge($paging, $filters));
		$this->search->setSortingKeys(array_keys($paging));

		return $this->search;
	}
}
