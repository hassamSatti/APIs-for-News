<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;

class NewsFetching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from different sources and save in database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    { 
        $data = [];
        try {
            $newsSources = app('news.fetchers');

            foreach ($newsSources as $source) {
                try {
                    $news = $source->fetchNews(); 
                    $data = array_merge($data, $news);
                } catch (\Exception $e) {
                    \Log::error("Error fetching news from source: " . get_class($source) . ". Error: " . $e->getMessage());
                    $this->error("Error fetching news from source: " . get_class($source));
                }
            } 
            if (!empty($data)) {
                try {
                    News::upsert($data, ['title'], ['source', 'author', 'content', 'description']);
                    $this->info('News fetched and saved successfully.');
                } catch (\Exception $e) {
                    \Log::error("Error saving news to the database. Error: " . $e->getMessage());
                    $this->error("Error saving news to the database. Please try again.");
                }
            } else {
                $this->info('No news fetched to save.');
            }
        } catch (\Exception $e) { 
            \Log::error("An unexpected error occurred while fetching news. Error: " . $e->getMessage());
            $this->error("An unexpected error occurred while fetching the news.");
        }
    }
}
