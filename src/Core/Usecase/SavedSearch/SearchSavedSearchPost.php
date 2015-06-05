<?php

/**
 * Search Posts in SavedSearch Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\SavedSearch;

use Ushahidi\Core\Usecase\Post\SearchPost;
use Ushahidi_Repository;

class SearchSavedSearchPost extends SearchPost
{
	protected $post_repository;

	public function setPostRepository(Ushahidi_Repository $repo)
	{
		$this->post_repository = $repo;
		return $this;
	}

	protected function getSearch()
	{
		$fields = $this->repo->getSearchFields();
		$paging = $this->getPagingFields();

		$filters = $this->getFilters(array_merge($fields, array_keys($paging)));

		$this->search->setFilters(array_merge($paging, $this->repo->get($this->getIdentifier('set_id'))->filter));
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
		$this->post_repository->setSearchParams($search);

		// ... get the results of the search
		$results = $this->post_repository->getSearchResults();

		// ... get the total count for the search
		$total = $this->post_repository->getSearchTotal();

		// ... remove any entities that cannot be seen
		$priv = 'read';
		foreach ($results as $idx => $entity) {
			if (!$this->auth->isAllowed($entity, $priv)) {
				unset($results[$idx]);
			}
		}

		// ... pass the search information to the formatter, for paging
		$this->formatter->setSearch($search, $total);

		// ... and return the formatted results.
		return $this->formatter->__invoke($results);
	}

	/**
	 * Get an empty entity.
	 *
	 * @return Entity
	 */
	protected function getEntity()
	{
		return $this->post_repository->getEntity();
	}

	public function getSearchTotal()
	{
		return $this->post_repository->getSearchTotal();
	}
}
