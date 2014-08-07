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
use Ushahidi\Entity\FormAttributeRepository;

use Ushahidi\Tool\Validator;

class Ushahidi_Validator_Post_Update implements Validator
{
	protected $repo;
	protected $valid;

	public function __construct(UpdatePostRepository $repo, FormAttributeRepository $attribute_repo)
	{
		$this->repo = $repo;
		$this->attribute_repo = $attribute_repo;
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
			->rules('locale', array(
					array('max_length', array(':value', 5)),
					array('alpha_dash', array(':value', TRUE)),
					// @todo check locale is valid
					array(array($this->repo, 'doesLocaleAlreadyExist'), array(':value', $input->parent_id, $input->type))
				))
			->rules('form_id', array(
					array('numeric'),
					array(array($this->repo, 'doesFormExist'), array(':value'))
				));

		// validate tags
		// Don't validate tags: we auto create missing tags so can't fail
		// $this->check_tags($input->tags);
		// validate values?
		$this->check_values($input->values, $input->form_id);

		// validate user changes
		$this->check_user($input->user);

		return $this->valid->check();
	}

	protected function check_tags($tags)
	{
		foreach ($tags as $tag)
		{
			if (is_numeric($value) AND intval($value) > 0)
			{
				$tag = ORM::factory('Tag')
				->where('id', '=', $value)
				->find();
			}
			// Tag or slug string
			else
			{
				$tag = ORM::factory('Tag')
				->where('slug', '=', $value)
				->or_where('tag', '=', $value)
				->find();
			}

			// Auto create tags if it doesn't exist
			if (! $tag->loaded())
			{
				$tag->tag = $value;
				$tag->type = 'category';
				$tag->check();
				$tag->save();
			}
		}
	}

	protected function check_values($values, $form_id)
	{
		foreach ($values as $key => $value)
		{
			// Check attribute exists
			$attribute = $this->attribute_repo->get($key, $form_id);
			if (! $attribute)
			{
				$this->valid->rule('values.'.$key,
					function(Validation $valid, $field, $value)
					{
						$this->valid->error('values.'. $key, 'attribute does not exist');
					},
					array(':validation', ':field', ':value')
				);
			}

			// Are there multiple values? Are they greater than cardinality limit?
			if (count($value) > $attribute->cardinality AND $attribute->cardinality != 0)
			{
				$this->valid->rule('values.'.$key,
					function(Validation $valid, $field, $value)
					{
						$valid->error($field, 'cardinality');
					},
					array(':validation', ':field', ':value')
				);
			}

			foreach($value as $k => $v)
			{
				// If id is specified, check post value entry exists
				if (! empty($v['id']))
				{
					$value_entity = $this->post_value_factory
						->getInstance($attribute->type)
						->get($id);

					// Add error if id specified by doesn't exist
					if (! $value_entity )
					{
						$this->valid->rule("values.$key.$k",
							function(Validation $valid, $field, $value)
							{
								$valid->error($field, 'value id does not exist');
							},
							array(':validation', ':field', ':value')
						);
					}
				}

				// Run any attribute type specific validation
				$this->valid->rule("values.$key.$k", [
					[$this->post_value_factory->getValidator($attribute->type), 'check'],
					[':value']
				]);
			}
		}

		// Validate required attributes
		$required_attributes = $this->attribute_repo->getRequired($form_id);
		foreach ($required_attributes as $attr)
		{
			$this->valid->rule('values.'.$attr->key, 'not_empty');
		}
	}

	public function errors($from = 'post')
	{
		return $this->valid->errors($from);
	}
}
