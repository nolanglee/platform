<?php

namespace spec\Ushahidi\Usecase\Post;

use Ushahidi\Tool\Validator;
use Ushahidi\Tool\Authorizer;
use Ushahidi\Entity\Post;
use Ushahidi\Usecase\Post\UpdatePostRepository;
use Ushahidi\Usecase\Post\PostData;

use PhpSpec\ObjectBehavior;

class UpdateSpec extends ObjectBehavior
{
	function let(UpdatePostRepository $repo, Validator $valid, Authorizer $auth)
	{
		$this->beConstructedWith($repo, $valid, $auth);
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('Ushahidi\Usecase\Post\Update');
	}

	function it_can_update_a_post_with_valid_input($valid, $repo, $auth, Post $post, PostData $input, PostData $update)
	{
		$raw_post   = ['title' => 'Before Update', 'content' => 'Some content'];
		$raw_input  = ['title' => 'After Update', 'content' => 'Some content'];
		$raw_update = ['title' => 'After Update'];

		$user_id = 1;

		$post->asArray()->willReturn($raw_post);
		$input->asArray()->willReturn($raw_input);
		$update->asArray()->willReturn($raw_update);

		// the update will be what is different in the input, as compared to the post
		$input->getDifferent($raw_post)->willReturn($update);

		// only the changed values will be validated
		$valid->check($update)->willReturn(true);

		// auth check
		$auth->isAllowed($post, 'update', $user_id)->willReturn(true);
		$auth->isAllowed($post, 'change_user', $user_id)->willReturn(true);

		// the repo will only receive changed values
		$repo->updatePost($post->id, $raw_update)->shouldBeCalled();

		// the persisted changes will be applied to the post
		$post->setData($raw_update)->shouldBeCalled();

		// after being updated, the same post will be returned
		$this->interact($post, $input, $user_id)->shouldReturn($post);
	}
}
