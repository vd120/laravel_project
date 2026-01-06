<?php

namespace Tests\Feature;

use App\Events\CommentAdded;
use App\Events\NotificationReceived;
use App\Events\PostUpdated;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Services\RealtimeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();

        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        
        $this->actingAs($this->user, 'web');
    }

    /** @test */
    public function it_can_like_a_post_and_broadcast_update()
    {
        Event::fake();

        
        $this->assertEquals(0, $this->post->likes()->count());

        
        $response = $this->post(route('posts.like', $this->post));

        
        $response->assertJson([
            'success' => true,
            'liked' => true,
            'likes_count' => 1
        ]);

        
        $this->assertEquals(1, $this->post->fresh()->likes()->count());

        
        Event::assertDispatched(PostUpdated::class, function ($event) {
            return $event->post->id === $this->post->id &&
                   $event->action === 'like' &&
                   $event->userId === $this->user->id;
        });
    }

    /** @test */
    public function it_can_unlike_a_post_and_broadcast_update()
    {
        Event::fake();

        
        $this->post->likes()->create(['user_id' => $this->user->id]);
        $this->assertEquals(1, $this->post->fresh()->likes()->count());

        
        $response = $this->post(route('posts.like', $this->post));

        
        $response->assertJson([
            'success' => true,
            'liked' => false,
            'likes_count' => 0
        ]);

        
        $this->assertEquals(0, $this->post->fresh()->likes()->count());

        
        Event::assertDispatched(PostUpdated::class, function ($event) {
            return $event->post->id === $this->post->id &&
                   $event->action === 'unlike' &&
                   $event->userId === $this->user->id;
        });
    }

    /** @test */
    public function it_can_add_comment_and_broadcast_update()
    {
        Event::fake();

        $commentData = [
            'content' => 'This is a test comment',
            'post_id' => $this->post->id,
        ];

        
        $this->assertEquals(0, $this->post->comments()->count());

        
        $response = $this->post(route('comments.store'), $commentData);

        
        $response->assertJson([
            'success' => true,
            'message' => 'Comment posted successfully'
        ]);

        
        $this->assertEquals(1, $this->post->fresh()->comments()->count());

        
        Event::assertDispatched(CommentAdded::class, function ($event) {
            return $event->postId === $this->post->id;
        });
    }

    /** @test */
    public function it_can_get_realtime_updates_via_api()
    {
        
        $this->post->likes()->create(['user_id' => $this->user->id]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        
        $response = $this->get('/api/user/realtime-updates');

        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'notifications',
                'posts'
            ],
            'timestamp'
        ]);

        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function realtime_service_can_cache_data()
    {
        $service = new RealtimeService();

        
        $testData = ['test' => 'data'];
        $service->updateCache('test_key', $testData, 60);

        $cachedData = $service->getCache('test_key');
        $this->assertEquals($testData, $cachedData);
    }

    /** @test */
    public function realtime_service_can_update_post_data()
    {
        $service = new RealtimeService();

        
        $this->post->likes()->create(['user_id' => $this->user->id]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $data = $service->updatePostData($this->post->id);

        
        $this->assertArrayHasKey('likes_count', $data);
        $this->assertArrayHasKey('comments_count', $data);
        $this->assertArrayHasKey('latest_comments', $data);

        $this->assertEquals(1, $data['likes_count']);
        $this->assertEquals(1, $data['comments_count']);
    }

    /** @test */
    public function broadcasting_events_have_correct_structure()
    {
        Event::fake();

        
        $this->post->likes()->create(['user_id' => $this->user->id]);

        broadcast(new PostUpdated($this->post, 'like', $this->user->id));

        Event::assertDispatched(PostUpdated::class, function ($event) {
            $broadcastData = $event->broadcastWith();

            return isset($broadcastData['post_id']) &&
                   isset($broadcastData['action']) &&
                   isset($broadcastData['user_id']) &&
                   isset($broadcastData['likes_count']) &&
                   isset($broadcastData['comments_count']);
        });
    }

    /** @test */
    public function comment_broadcasting_includes_mention_processing()
    {
        Event::fake();

        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'content' => 'Hello @' . $this->otherUser->name,
        ]);

        broadcast(new CommentAdded($comment, $this->post->id));

        Event::assertDispatched(CommentAdded::class, function ($event) {
            $broadcastData = $event->broadcastWith();

            return isset($broadcastData['comment']['content']) &&
                   isset($broadcastData['comment']['user']) &&
                   isset($broadcastData['post_id']);
        });
    }

    /** @test */
    public function notification_broadcasting_works()
    {
        Event::fake();

        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'like',
            'message' => 'Someone liked your post',
        ]);

        broadcast(new NotificationReceived($notification, $this->user->id));

        Event::assertDispatched(NotificationReceived::class, function ($event) {
            $broadcastData = $event->broadcastWith();

            return isset($broadcastData['notification']) &&
                   isset($broadcastData['unread_count']);
        });
    }

    /** @test */
    public function api_endpoints_require_authentication()
    {
        
        auth()->logout();

        
        $response = $this->get('/api/user/realtime-updates');

        
        $response->assertStatus(401);
    }

    /** @test */
    public function post_like_requires_post_ownership_or_following()
    {
        
        $privatePost = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_private' => true,
        ]);

        
        $response = $this->post(route('posts.like', $privatePost));

        
        $response->assertStatus(403);
    }

    /** @test */
    public function realtime_service_handles_nonexistent_posts()
    {
        $service = new RealtimeService();

        $data = $service->updatePostData(99999); 

        $this->assertEmpty($data);
    }
}
