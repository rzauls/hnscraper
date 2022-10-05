<?php

namespace Tests\Feature;

use App\Interfaces\HNClient;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class FetchPostsTest extends TestCase
{

    // do not persist mock post data
    use DatabaseTransactions;

    protected function generateFakePosts($i = 5)
    {
        $posts = Post::factory()->count($i)->make();
        $col = new Collection();
        foreach ($posts as $p) {
            $col->add($p);
        }
        return $col;
    }


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_command_calls_data_fetcher_and_saves_data_to_db()
    {
        $fakePosts = $this->generateFakePosts();
        $this->instance(
            HNClient::class,
            Mockery::mock(HNClient::class, function (MockInterface $mock) use ($fakePosts) {
                $mock->shouldReceive('GetPosts')
                    ->andReturn($fakePosts);
            })
        );
        $this->artisan('fetch:posts')->assertExitCode(0);


        foreach ($fakePosts as $p) {
            // check if each model was saved
            $dbPost = Post::find($p->id);
            $this->assertTrue($dbPost->exists());
        }
    }

    public function test_command_doesnt_update_deleted_posts()
    {
        $fakePosts = $this->generateFakePosts(5);
        $deletedPost = Post::factory()->create();
        $deletedPost->points = 123; // update points to some known value
        $deletedPost->save();
        $deletedPost->delete();
        $deletedPost->points = 321; // set "fetched" post back to a different value
        $deletedPostID = $deletedPost->id;
        $fakePosts->add($deletedPost);

        $this->instance(
            HNClient::class,
            Mockery::mock(HNClient::class, function (MockInterface $mock) use ($fakePosts) {
                $mock->shouldReceive('GetPosts')
                    ->andReturn($fakePosts);
            })
        );
        $this->artisan('fetch:posts')->assertExitCode(0);

        $this->assertDatabaseHas('posts', [
            'id' => $deletedPostID,
            'points' => 123, // assert that a row with 123 points exists (row was not updated since it was soft-deleted)
        ]);
        $this->assertDatabaseMissing('posts', [
            'id' => $deletedPostID,
            'points' => 321, // assert that the updated row did not get persisted
        ]);
    }
}
