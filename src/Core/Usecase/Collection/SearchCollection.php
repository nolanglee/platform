<?php

/**
 * Ushahidi Platform Entity Search Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Usecase\SearchUsecase;
use Ushahidi_Repository;

class SearchCollection extends SearchUsecase
{
	/**
	 * Get filter parameters as search data.
	 *
	 * @return SearchData
	 */
	protected function getSearch()
	{
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		$filters['search'] = false;

		$this->search->setFilters(array_merge($paging, $filters));
		$this->search->setSortingKeys(array_keys($paging));

		return $this->search;
	}
}
