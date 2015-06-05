<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Set Repository
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Core\Entity;
use Ushahidi\Core\Entity\Set;
use Ushahidi\Core\Entity\SetRepository;
use Ushahidi\Core\SearchData;
use Ushahidi\Core\Tool\JsonTranscode;

class Ushahidi_Repository_Set extends Ushahidi_Repository implements SetRepository
{
	protected $search_data;
	protected $post_repo;
	protected $json_transcoder;
	protected $json_properties = ['filter', 'view_options', 'visible_to'];

	public function setTranscoder(JsonTranscode $transcoder)
	{
		$this->json_transcoder = $transcoder;
	}

	// Ushahidi_Repository
	protected function getTable()
	{
		return 'sets';
	}

	// Ushahidi_Repository
	public function getEntity(Array $data = null)
	{
		return new Set($data);
	}

	public function setSearchData(SearchData $search_data)
	{
		$this->search_data = $search_data;
		return $this;
	}

	// CreateRepository
	public function create(Entity $entity) {
		$record = array_filter($this->json_transcoder->encode(
			$entity->asArray(),
			$this->json_properties
		));
		$record['created'] = time();
		return $this->executeInsert($this->removeNullValues($record));

	}

	// UpdateRepository
	public function update(Entity $entity) {
		return parent::update($entity->setState(['updated' => time()]));
	}

	// SearchRepository
	public function getSearchFields()
	{
		return [
			'user_id',
			'q', /* LIKE name */
			'search',
			'featured',
		];
	}

	// SearchRepository
	public function setSearchParams(SearchData $search)
	{
		// Overriding so we can alter sorting logic
		// @todo make it easier to override just sorting

		$this->search_query = $this->selectQuery();

		$sorting = $search->getSorting();

		// Always return featured sets first
		// @todo make this optional
		$this->search_query->order_by('sets.featured', 'DESC');

		if (!empty($sorting['orderby'])) {
			$this->search_query->order_by(
				$this->getTable() . '.' . $sorting['orderby'],
				Arr::get($sorting, 'order')
			);
		}

		if (!empty($sorting['offset'])) {
			$this->search_query->offset($sorting['offset']);
		}

		if (!empty($sorting['limit'])) {
			$this->search_query->limit($sorting['limit']);
		}

		// apply the unique conditions of the search
		$this->setSearchConditions($search);
	}

	// Ushahidi_Repository
	protected function setSearchConditions(SearchData $search)
	{
		$sets_query = $this->search_query;

		if ($search->q)
		{
			$sets_query->where('name', 'LIKE', "%{$search->q}%");
		}

		if ($search->search !== null)
		{
			$sets_query->where('search', '=', (int)$search->search);
		}

		if ($search->featured !== null)
		{
			$sets_query->where('featured', '=', (int)$search->featured);
		}

		if ($search->user_id)
		{
			$sets_query->where('user_id', '=', $search->user_id);
		}

		if (isset($search->search))
		{
			$sets_query->where('search', '=', (int)$search->search);
		}

		if ($search->id)
		{
			$sets_query->where('id', '=', $search->id);
		}
	}

	public function getSmartSetFilters($id)
	{
		$filters = $this->get($id)->filter;
		foreach($filters as $key=>$value)
		{
			if (property_exists($this->search_data, $key))
			{
				$this->search_data->$key = $value;
			}
		}

		return $this->search_data;
	}

	public function deleteSetPost($set_id, $post_id)
	{
		DB::delete('posts_sets')
			->where('post_id', '=', $post_id)
			->where('set_id', '=', $set_id)
			->execute($this->db);

		return $post_id;
	}

	public function setPostExists($set_id, $post_id)
	{
		$result =
		DB::select('posts_sets.*')
			->from('posts_sets')
			->where('post_id', '=', $post_id)
			->where('set_id', '=', $set_id)
			->execute($this->db)
			->as_array();

		return (bool) count($result);
	}

	public function addPostToSet($set_id, $post_id)
	{
		list($id, $rows) = DB::insert('posts_sets')
			->columns(array_keys(compact('post_id', 'set_id')))
			->values(array_values(compact('post_id', 'set_id')))
			->execute($this->db);

		return $id;
	}

}
