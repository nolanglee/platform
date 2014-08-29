<?php

/**
 * Ushahidi Platform Admin Post Update Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Usecase\Post;

use Ushahidi\Entity\Post;
use Ushahidi\Tool\Validator;
use Ushahidi\Exception\ValidatorException;

class Update
{
	private $repo;
	private $valid;

	private $updated = [];

	public function __construct(UpdatePostRepository $repo, Validator $valid)
	{
		$this->repo  = $repo;
		$this->valid = $valid;
	}

	public function interact(Post $post, PostData $input)
	{
		// We only want to work with values that have been changed
		// @todo figure out what to do about this.. something are always different
		// because input data isn't an entity, and shouldn't have to be.
		$update = $input->getDifferent($post->asArray());

		// Include parent and type for use in validation
		// These are never updated, but needed for some checks
		// @todo figure out a better way to include these
		$update->parent_id = $post->parent_id;
		$update->type = $post->type;

		if (!$this->valid->check($update)) {
			throw new ValidatorException("Failed to validate post", $this->valid->errors());
		}

		// Determine what changes to make in the post
		$this->updated = $update->asArray();

		// Update the post and get the updated post back
		$post = $this->repo->updatePost($post->id, $this->updated);

		// @todo make tag/values entites arrays before they go into Post
		// and make sure $post->setData($updated) actually works.

		return $post;
	}

	public function getUpdated()
	{
		return $this->updated;
	}
}
