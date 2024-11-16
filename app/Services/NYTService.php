<?php 

namespace App\Services;

use App\Contracts\NewsInterface;
use Illuminate\Support\Facades\Http;

class NYTService implements NewsInterface
{
    public function fetchNews(): array
    {
        $response = Http::get('https://api.nytimes.com/svc/topstories/v2/home.json', [
            'api-key' => env('NYT_API_KEY'),
        ]);

        $news = $response->json()['results'] ?? [];
        return $this->formatNews($news);
    }

    private function formatNews(array $news): array
    {
        return array_map(function ($new) {
            return [
                'source' => 'New York Times',
                'author' => $new['byline'] ?? null,
                'title' => isset($new['title']) ? (string)$new['title'] : 'Untitled',
                'content' => isset($new['abstract']) ? (string)$new['abstract'] : '',
                'description' => is_array($new['des_facet'] ?? null) ? ($new['des_facet'][0] ?? 'No Description') : (string)($new['des_facet'] ?? 'No Description'),
                'published_at' => isset($new['publishedAt']) 
                    ? \Carbon\Carbon::parse($new['publishedAt'])->format('Y-m-d H:i:s') 
                    : now()->format('Y-m-d H:i:s'),
            ];
        }, $news);
    }
}