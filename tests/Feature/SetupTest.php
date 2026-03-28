<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SetupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function application_key_is_set()
    {
        $this->assertNotEmpty(config('app.key'));
    }

    /** @test */
    public function database_connection_works()
    {
        $this->assertTrue(\DB::connection()->getPdo() !== null);
    }

    /** @test */
    public function admin_user_exists()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        
        if ($admin) {
            $this->assertTrue($admin->is_admin);
        } else {
            $this->markTestIncomplete('Admin user not created yet. Run setup script.');
        }
    }

    /** @test */
    public function storage_directory_exists()
    {
        $this->assertDirectoryExists(storage_path());
        $this->assertDirectoryExists(storage_path('app/public'));
        $this->assertDirectoryExists(storage_path('logs'));
        $this->assertDirectoryExists(storage_path('framework/cache'));
        $this->assertDirectoryExists(storage_path('framework/sessions'));
        $this->assertDirectoryExists(storage_path('framework/views'));
    }

    /** @test */
    public function bootstrap_cache_directory_exists()
    {
        $this->assertDirectoryExists(base_path('bootstrap/cache'));
    }

    /** @test */
    public function env_file_exists()
    {
        $this->assertFileExists(base_path('.env'));
    }

    /** @test */
    public function required_php_extensions_are_loaded()
    {
        $required = ['mbstring', 'xml', 'curl', 'zip', 'openssl', 'pdo', 'json', 'tokenizer', 'bcmath'];
        
        foreach ($required as $ext) {
            $this->assertTrue(
                extension_loaded($ext),
                "Required PHP extension '{$ext}' is not loaded"
            );
        }
    }

    /** @test */
    public function migrations_have_been_run()
    {
        $this->assertDatabaseHas('migrations', [
            'migration' => '0001_01_01_000000_create_users_table.php'
        ]);
    }

    /** @test */
    public function users_table_exists()
    {
        $this->assertTrue(\Schema::hasTable('users'));
    }

    /** @test */
    public function posts_table_exists()
    {
        $this->assertTrue(\Schema::hasTable('posts'));
    }

    /** @test */
    public function can_create_user()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    /** @test */
    public function can_create_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    /** @test */
    public function home_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_loads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function register_page_loads()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test */
    public function storage_link_exists()
    {
        $this->assertDirectoryExists(public_path('storage'));
    }
}
