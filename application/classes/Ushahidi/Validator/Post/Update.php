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
use Ushahidi\Entity\TagRepository;
use Ushahidi\Entity\UserRepository;

use Ushahidi\Tool\Validator;

class Ushahidi_Validator_Post_Update implements Validator
{
	protected $repo;
	protected $valid;

	protected $attribute_repo;
	protected $tag_repo;
	protected $post_value_factory;
	protected $post_value_validator_factory;

	/**
	 * Construct
	 *
	 * @param UpdatePostRepository                  $repo
	 * @param FormAttributeRepository               $form_attribute_repo
	 * @param TagRepository                         $tag_repo
	 * @param UserRepository                        $user_repo
	 * @param Ushahidi_Repository_PostValueFactory  $post_value_factory
	 * @param Ushahidi_Validator_Post_ValueFactory  $post_value_validator_factory
	 */
	public function __construct(
			UpdatePostRepository $repo,
			FormAttributeRepository $attribute_repo,
			TagRepository $tag_repo,
			UserRepository $user_repo,
			Ushahidi_Repository_PostValueFactory $post_value_factory,
			Ushahidi_Validator_Post_ValueFactory $post_value_validator_factory)
	{
		$this->repo = $repo;
		$this->attribute_repo = $attribute_repo;
		$this->tag_repo = $tag_repo;
		$this->user_repo = $user_repo;
		$this->post_value_factory = $post_value_factory;
		$this->post_value_validator_factory = $post_value_validator_factory;
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
				))
			->rules('values', [
					[[$this, 'check_values'], [':validation', ':value', ':data']]
				])
			->rules('tags', [
					[[$this, 'check_tags'], [':validation', ':value']]
				])
			->rules('user_id', [
					// check the user exists
					// @todo better error message
					[[$this->user_repo, 'get'], [':value']]
				])
			// @todo move user email/realname validation to somewhere shared between various use validators?
			// @todo check user isn't registered (has username) if we have email/realname info
			->rules('user_email', [
					['Valid::email'],
					// confirm email not already used (OR not registered)
					[[$this->user_repo, 'isUniqueOrUnregisteredEmail'], [':value']]
				])
			->rules('user_realname', [
					['max_length', [':value', 150]],
				]);

		return $this->valid->check();
	}

	public function check_tags(Validation $valid, $tags)
	{
		foreach ($tags as $key => $tag)
		{
			if (! ($tag_entity = $this->tag_repo->get($tag) OR $tag_entity = $this->tag_repo->getByTag($tag)))
			{
				$valid->error('tags.'. $key, 'tag ":tag" does not exist', [':tag' => $tag]);
			}
		}
	}

	public function check_values(Validation $valid, $values, $data)
	{
		foreach ($values as $key => $value)
		{
			// Check attribute exists
			$attribute = $this->attribute_repo->getByKey($key, $data['form_id']);
			if (! $attribute)
			{
				$valid->error('values', 'attribute ":key" does not exist', [':key' => $key]);
				return;
			}

			// Are there multiple values? Are they greater than cardinality limit?
			if (count($value) > $attribute->cardinality AND $attribute->cardinality != 0)
			{
				$valid->error('values', 'Too many values for :key (max: :cardinality)', [
					':key' => $key,
					':cardinality' => $attribute->cardinality
				]);
			}

			foreach($value as $k => $v)
			{
				// If id is specified, check post value entry exists
				if (! empty($v['id']))
				{
					$value_entity = $this->post_value_factory
						->getInstance($attribute->type)
						->get($v['id']);

					// Add error if id specified by doesn't exist
					if (! $value_entity)
					{
						$valid->error("values", 'value id :id for field :key does not exist', [':key' => $key, ':id' => $v['id']]);
					}
				}

				// Run checks on individual values type specific validation
				if ($validator = $this->post_value_validator_factory->getValidator($attribute->type))
				{
					if (! $validator->check($v))
					{
						foreach($validator->errors() as $error)
						{
							$valid->error("values", $error, [':key' => $key]);
						}
					}
				}
			}
		}

		// Validate required attributes
		$required_attributes = $this->attribute_repo->getRequired($data['form_id']);
		foreach ($required_attributes as $attr)
		{
			// @todo this doesn't actually work..
			$this->valid->rule('values.'.$attr->key, 'not_empty');
		}
	}

	public function errors($from = 'post')
	{
		return $this->valid->errors($from);
	}
}
