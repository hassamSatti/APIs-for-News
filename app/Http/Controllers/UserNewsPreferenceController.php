<?php

namespace App\Http\Controllers;

use App\Models\News; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
class UserNewsPreferenceController extends Controller
{
    use ApiResponseTrait;
    // This code add User News Preference if already exists then Update 
    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     summary="Add or update user news preferences",
     *     description="This endpoint allows a user to add or update their news preferences, including authors and sources. If both 'authors' and 'sources' are missing from the request, a 404 error will be returned.",
     *     tags={"User News Preferences"},
     *     security={{"sanctumAuth":{}}},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         description="The news preferences to be set for the authenticated user.",
     *         @OA\JsonContent(
     *             required={"authors", "sources"},
     *             @OA\Property(property="authors", type="array", items=@OA\Items(type="string"), description="List of authors to follow", example={"Author1", "Author2"}),
     *             @OA\Property(property="sources", type="array", items=@OA\Items(type="string"), description="List of sources to follow", example={"Source1", "Source2"})
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="User news preference has been successfully set or updated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="authors", type="array", items=@OA\Items(type="string"), example={"Author1", "Author2"}),
     *                 @OA\Property(property="sources", type="array", items=@OA\Items(type="string"), example={"Source1", "Source2"})
     *             ),
     *             @OA\Property(property="message", type="string", example="User News Preference set successfully")
     *         )
     *     ),
     *      @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The Authors and Source must be Array.")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Authors and Sources are not found in the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Authors and Sources are not Found in Request")
     *         )
     *     )
     * )
     */
    public function addOrUpdate(Request $request)
    {
        $request->validate([
            'authors' => 'array',
            'sources' => 'array',
        ]);

        if(!$request->input('authors') && !$request->input('sources'))
        {
            return $this->errorResponse('Authors and Sources are not Found in Request', 404);
        }

        $user = Auth::user(); 
        $authors = $request->input('authors', []);
        $sources = $request->input('sources', []);
        $preference = $user->preference()->updateOrCreate(
            [],
            [
                'authors' => $authors,
                'sources' => $sources,
            ]
        );
        $preferenceResponse['authors'] = $preference->authors;
        $preferenceResponse['sources'] = $preference->sources;
        return $this->successResponse($preferenceResponse, 'User News Preference set successfully');
    }
    /**
     * @OA\Get(
     *     path="/api/preferences",
     *     summary="Get user news preferences",
     *     description="Fetches the news preferences (authors and sources) for the authenticated user. Returns 404 if no preferences are found.",
     *     tags={"User News Preferences"},
     *     security={{"sanctumAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="User news preferences fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="authors", type="array", items=@OA\Items(type="string"), example={"Author1", "Author2"}),
     *                 @OA\Property(property="sources", type="array", items=@OA\Items(type="string"), example={"Source1", "Source2"})
     *             ),
     *             @OA\Property(property="message", type="string", example="User News Preference fetched successfully")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found for the user.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No preferences found for this user")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getPreferences()
    {
        $user = Auth::user(); 

        $preference = $user->preference;

        if (!$preference) {
            return $this->errorResponse('No preferences found for this user', 404);
        }
        $preferenceResponse['authors'] = $preference->authors;
        $preferenceResponse['sources'] = $preference->sources;
        return $this->successResponse($preferenceResponse, 'User News Preference fetched successfully');
    }
    /**
     * @OA\Get(
     *     path="/api/personalizedNews",
     *     summary="Get news based on user preferences",
     *     description="Retrieve news articles filtered by the user's preferences for authors and sources.",
     *     tags={"User News Preferences"},
     *     security={{"sanctumAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="News fetched based on user preferences",
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
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No preferences found for this user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No preferences found for this user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="No news found based on user preferences",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No news found based on user preferences")
     *         )
     *     )
     * )
     */
    public function getNewsBasedOnPreferences()
    {
        $user = Auth::user();
        $preference = $user->preference;
          
        if (!$preference) {
            return $this->errorResponse('No preferences found for this user', 404);
        }

        $authors = $preference->authors;
        $sources = $preference->sources;

        $newsQuery = News::query();

        if ($authors) {
            $newsQuery->whereIn('author', $authors);
        }
     
        if ($sources) {
            $newsQuery->orWhereIn('source', $sources);
        }
        $news = $newsQuery->paginate(10);
        if ($news->isEmpty()) {
            return $this->errorResponse('No news found based on user preferences', 405);
        }

        return $this->successResponse($news, 'News fetched based on user preferences');
    }
}
