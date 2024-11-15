<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Cache;
use App\Traits\ApiResponseTrait;
/**
 *     @OA\SecurityScheme(
 *         securityScheme="sanctumAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="Token",
 *         description="Sanctum Token Authentication"
 *     ),
 * @OA\Schema(
 *     schema="News",
 *     type="object",
 *     required={"source", "title", "content", "description", "published_at"},
 *     @OA\Property(property="id", type="integer", description="The ID of the news article"),
 *     @OA\Property(property="source", type="string", description="The source of the news article"),
 *     @OA\Property(property="author", type="string", description="The author of the news article", nullable=true),
 *     @OA\Property(property="title", type="string", description="The title of the news article"),
 *     @OA\Property(property="content", type="string", description="The content of the news article"),
 *     @OA\Property(property="description", type="string", description="The description of the news article"),
 *     @OA\Property(property="published_at", type="string", format="date-time", description="The publication date of the news article")
 * )
 */
class NewsController extends Controller
{ 

    use ApiResponseTrait;
    /**
     * @OA\Get(
     *     path="/api/news",
     *     summary="Get a list of news articles",
     *     description="Fetch a paginated list of news articles with optional filters (search by author, filter by date or source)",
     *     tags={"News"},
     *     security={{"sanctumAuth": {}}},
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Search news by author",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter news by publication date",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter news by source",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of news articles",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/News")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid query parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred while fetching news articles")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    { 
        $cacheKey = 'news_' . md5(json_encode($request->all()));
        $news = Cache::get($cacheKey);
        if (!$news) {
            $query = News::query();
            
            $query->when($request->filled('author'), function ($q) use ($request) {
                $q->where('author', $request->author);
            });
 
            $query->when($request->filled('date'), function ($q) use ($request) {
                $q->whereDate('published_at', $request->date);
            });
 
            $query->when($request->filled('source'), function ($q) use ($request) {
                $q->where('source', $request->source);
            });

            $news = $query->paginate(10);

            // Store the results in the cache for 10 minutes
            Cache::put($cacheKey, $news, now()->addMinutes(10));
        }
        return $this->successResponse($news, 'News fetched successfully');
    } 
    /**
     * @OA\Get(
     *     path="/api/news/{id}",
     *     summary="Get a single news article",
     *     description="Fetch a specific news article by its ID",
     *     tags={"News"},
     *     security={{"sanctumAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the news article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A single news article",
     *         @OA\JsonContent(ref="#/components/schemas/News")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="News article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="News article not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred while fetching the news article")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->errorResponse('News article not found', 404);
        } 
        return $this->successResponse($news, 'News article fetched successfully');
    }
    
}
