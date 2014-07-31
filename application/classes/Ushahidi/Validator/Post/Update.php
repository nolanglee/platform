<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Post Validator
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Data;
use Ushahidi\Usecase\Post\UpdatePostRepository;

use Ushahidi\Tool\Validator;

class Ushahidi_Validator_Post_Update implements Validator
{
	protected $repo;
	protected $valid;

	public function __construct(UpdatePostRepository $repo)
	{
		$this->repo = $repo;
	}

	public function check(Data $input)
	{
		$this->valid = Validation::factory($input->asArray())
			->rules('title', array(
					array('max_length', array(':value', 150)),
				))
			->rules('slug', array(
					array('min_length', array(':value', 2)),
					array('max_length', array(':value', 150)),
					array('alpha_dash', array(':value', TRUE)),
					array([$this->repo, 'isSlugAvailable'], array(':value')),
				))
			->rules('type', array(
					// not empty?
					array('in_array', array(':value', array(
						'report',
						'revision',
						'comment',
						'translation',
						'alert'
					))),
				))
			->rules('locale', array(
					//array('not_empty'),
					array('max_length', array(':value', 5)),
					array('alpha_dash', array(':value', TRUE)),
					// @todo check locale is valid
					array(array($this->repo, 'unique_locale'), array(':value'))
				))
			->rules('form_id', array(
					array('numeric'),
					array(array($this->repo, 'fk_exists'), array(':value'))
				))
			->rules('parent_id', array(
					array('numeric'),
					array(array($this->repo, 'parent_exists'), array(':value'))
				));

		// validate values?
		// validate tags?

		return $this->valid->check();
	}

	public function errors($from = 'post')
	{
		return $this->valid->errors($from);
	}
}
