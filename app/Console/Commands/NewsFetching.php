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
        
        $newsSources = app('news.fetchers');

        foreach ($newsSources as $source) {
            $news = $source->fetchNews(); 
            $data = array_merge($data, $news);
        } 
        News::upsert($data, ['title'], ['source', 'author', 'content', 'description']);

        $this->info('News fetched and saved successfully.');
    }
}
