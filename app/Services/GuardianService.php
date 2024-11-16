<?php
namespace App\Services;

use App\Contracts\NewsInterface;
use Illuminate\Support\Facades\Http;

class GuardianService implements NewsInterface
{
    public function fetchNews(): array
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => env('GUARDIAN_API_KEY'),
            'show-fields' => 'all',
        ]);

        $news = $response->json()['response']['results'] ?? [];
        return $this->formatNews($news);
    }
    private function formatNews(array $news): array
    {
        return array_map(function ($new) {
            return [
                'source' => 'The Guardian',
                'author' => $new['fields']['byline'] ?? null,
                'title' => isset($new['webTitle']) ? (string)$new['webTitle'] : 'Untitled',
                'content' => isset($new['fields']['body']) ? (string)$new['fields']['body'] : '',
                'description' => is_array($new['fields']['trailText'] ?? null) ? ($new['fields']['trailText'][0] ?? 'No Description') : (string)($new['fields']['trailText'] ?? 'No Description'),
                'published_at' => isset($new['webPublicationDate']) 
                    ? \Carbon\Carbon::parse($new['webPublicationDate'])->format('Y-m-d H:i:s') 
                    : now()->format('Y-m-d H:i:s'),
            ];
        }, $news);
    }
}