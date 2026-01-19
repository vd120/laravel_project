<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class GeneratePostSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:generate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate slugs for existing posts that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postsWithoutSlugs = Post::whereNull('slug')->get();

        if ($postsWithoutSlugs->isEmpty()) {
            $this->info('All posts already have slugs!');
            return;
        }

        $this->info("Generating slugs for {$postsWithoutSlugs->count()} posts...");

        $bar = $this->output->createProgressBar($postsWithoutSlugs->count());

        foreach ($postsWithoutSlugs as $post) {
            $post->slug = Post::generateUniqueSlug();
            $post->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All slugs generated successfully!');
    }
}