<?php

/**
 * Search Posts in Collection Use Case
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
	 * Override to filter posts to just this collection
	 *
	 * @return SearchData
	 */
	protected function getSearch()
	{
		$set_id = $this->getIdentifier('set_id');
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		// Include set_id identifier in filters to ensure
		// we only get posts from in this collection
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

		// Skip the formatter->setSearch step here
		// @todo why? this should be getting skipped

		// ... and return the formatted results.
		return $this->formatter->__invoke($results);
	}
}
