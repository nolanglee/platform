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

	/**
	 * Construct
	 *
	 * @param UpdatePostRepository                  $repo
	 * @param FormAttributeRepository               $form_attribute_repo
	 */
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
		//$this->check_user($input->user_id, $input->user_email, $input->user_realname);

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
				/*$this->valid->rule("values.$key.$k", [
					[$this->post_value_factory->getValidator($attribute->type), 'check'],
					[':value']
				]);*/
			}
		}

		// Validate required attributes
		$required_attributes = $this->attribute_repo->getRequired($form_id);
		foreach ($required_attributes as $attr)
		{
			$this->valid->rule('values.'.$attr->key, 'not_empty');
		}
	}

	protected function check_user($id, $email, $realname)
	{

		// Do we have a user id?
		if ($id)
		{
			if (
					// New post and current user id
					(! $post->loaded() AND $id == $user_data['id'])
					// Allowed to manually set user info
					OR $this->acl->is_allowed($this->user, $post, 'change_user')
				)
			{
				$user = ORM::factory('User', $user_data['id']);
				if (! $user->loaded())
				{
					$validation->rule('user',
						function(Validation $validation, $field, $value)
						{
							$validation->error($field, 'user_exists');
						},
						array(':validation', ':field', ':value')
					);
					return FALSE;
				}
			}
			else
			{
				$validation->rule('user',
					function(Validation $validation, $field, $value)
					{
						$validation->error($field, 'change_user_permission');
					},
					array(':validation', ':field', ':value')
				);
				return FALSE;
			}
		}
		// Do we have an email or name?
		elseif (
			! empty($email)
			OR ! empty($username)
		)
		{
			if (
					// New post and anonymous user
					(! $post->loaded() AND ! $this->user->loaded())
					// Allowed to manually set user info
					OR $this->acl->is_allowed($this->user, $post, 'change_user')
				)
			{
				// Save new user
				// Make sure email is set to something
				$user_data['email'] = (! empty($user_data['email'])) ? $user_data['email'] : NULL;

				// Check if user was loaded
				// Note: if the email was used before but not registered (no username) we're going to overwrite name details
				if ($post->user_id)
				{
					$user = $post->user;
				}
				else
				{
					$user = ORM::factory('User')
						->where('email', '=', $user_data['email'])
						->find();
				}

				// If user is registered, throw error telling them to log in
				if ($user->loaded() AND $user->username)
				{
					$validation->rule('user',
						function(Validation $validation, $field, $value)
						{
							$validation->error($field, 'user_already_registered');
						},
						array(':validation', ':field', ':value')
					);
					return FALSE;
				}

				$user->values($user_data, array('email', 'realname'));

				// @todo add a setting for requiring email or not
				// $user_validation = Validation::factory($post_data['user']);
				// $user_validation->rule('email', 'not_empty');

				$user->check(/* $user_validation */);
			}
			else
			{
				// @todo fix the case where we end up here but submission actually included same values as before
				// Error
				$validation->rule('user',
					function(Validation $validation, $field, $value)
					{
						$validation->error($field, 'change_user_permission');
					},
					array(':validation', ':field', ':value')
				);
				return FALSE;
			}
		}
	}

	public function errors($from = 'post')
	{
		return $this->valid->errors($from);
	}
}
