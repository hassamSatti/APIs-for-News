<?php 

namespace App\Services;

use App\Contracts\NewsInterface;
use Illuminate\Support\Facades\Http;

class NewsAPIService implements NewsInterface
{
    public function fetchNews(): array
    {
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => env('NEWS_API_KEY'),
            'country' => 'us',
        ]);

        $news = $response->json()['articles'] ?? [];
        return $this->formatNews($news);
    }

    private function formatNews(array $news): array
    {
        return array_map(function ($new) {
            return [
                'source' => isset($new['source']['name']) && is_string($new['source']['name']) ? $new['source']['name'] : 'NewsAPI',  
                'author' => isset($new['author']) && is_string($new['author']) ? $new['author'] : null,  
                'title' => isset($new['title']) && is_string($new['title']) ? $new['title'] : 'Untitled',
                'content' => isset($new['content']) && is_string($new['content']) ? $new['content'] : '', 
                'description' => isset($new['description']) && is_string($new['description']) ? $new['description'] : '', 
                'published_at' => isset($new['publishedAt']) && !empty($new['publishedAt']) 
                    ? \Carbon\Carbon::parse($new['publishedAt'])->format('Y-m-d H:i:s') 
                    : now()->format('Y-m-d H:i:s'),  
            ];
        }, $news);
    }
}
