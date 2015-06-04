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

use Ushahidi\Core\Usecase\Post\SearchPost;

class SearchCollectionPost extends SearchPost
{
	/**
	 * Get filter parameters as search data.
	 *
	 * @return SearchData
	 */
	protected function getSearch()
	{
		$set_id = $this->getIdentifier('set_id');
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		$this->search->setFilters(array_merge($paging, $filters, ['set'=>$set_id]));
		$this->search->setSortingKeys(array_keys($paging));

		return $this->search;
	}

	// Usecase
	public function interact()
	{
		// Fetch an empty entity...
		$entity = $this->getEntity();

		// ... verify that the entity can be searched by the current user
		$this->verifySearchAuth($entity);

		// ... and get the search filters for this entity
		$search = $this->getSearch();

		// ... pass the search information to the repo
		$this->repo->setSearchParams($search);

		// ... get the results of the search
		$results = $this->repo->getSearchResults();

		// ... get the total count for the search
		$total = $this->repo->getSearchTotal();

		// ... remove any entities that cannot be seen
		$priv = 'read';
		foreach ($results as $idx => $entity) {
			if (!$this->auth->isAllowed($entity, $priv)) {
				unset($results[$idx]);
			}
		}

		// ... and return the formatted results.
		return $this->formatter->__invoke($results);
	}
}
