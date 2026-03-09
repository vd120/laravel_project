<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Models\User;

class AiController extends Controller
{
    public function index()
    {
        return view('ai.index');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $userMessage = trim($request->message);
        $response = $this->generateResponse($userMessage);

        return response()->json([
            'success' => true,
            'response' => $response,
            'timestamp' => now()->format('H:i')
        ]);
    }

    private function generateResponse($message)
    {
        $user = Auth::user();

        // Only accept single digits 1-9
        if (preg_match('/^[1-9]$/', $message)) {
            return $this->handleMenuOption((int)$message, $user);
        }

        // If user types anything else, show the welcome menu
        return $this->showMainMenu();
    }

    private function handleMenuOption($option, $user)
    {
        switch ($option) {
            case 1:
                return $this->showMainMenu();

            case 2:
                return __('ai.writing_posts_content');

            case 3:
                $suggestions = $this->getFollowSuggestions($user);
                $content = __('ai.follow_suggestions_content');

                if (!empty($suggestions)) {
                    $suggestionsText = implode("\n", $suggestions);
                    return "**" . __('ai.follow_suggestions') . "**\n\n" .
                           $suggestionsText . "\n\n" . $content;
                }

                return "**" . __('ai.follow_suggestions') . "**\n\n" .
                       __('ai.no_suggestions') . "\n\n" . $content;

            case 4:
                $trends = $this->getTrendingTopics();
                $trendsText = implode("\n", $trends);
                return "**" . __('ai.trending_topics') . "**\n\n" .
                       $trendsText . "\n\n" . __('ai.trending_topics_content');

            case 5:
                return __('ai.privacy_guide_content');

            case 6:
                return __('ai.engagement_tips_content');

            case 7:
                return __('ai.stories_guide_content');

            case 8:
                return __('ai.profile_setup_content');

            case 9:
                return __('ai.search_discover_content');

            default:
                return $this->showMainMenu();
        }
    }

    private function showMainMenu()
    {
        return __('ai.welcome');
    }

    private function getFollowSuggestions($user)
    {
        $suggestions = User::where('id', '!=', $user->id)
            ->whereDoesntHave('blockedBy', function($query) use ($user) {
                $query->where('blocker_id', $user->id);
            })
            ->whereDoesntHave('blockedUsers', function($query) use ($user) {
                $query->where('blocked_id', $user->id);
            })
            ->whereDoesntHave('followers', function($query) use ($user) {
                $query->where('follower_id', $user->id);
            })
            ->withCount('posts')
            ->having('posts_count', '>', 0)
            ->inRandomOrder()
            ->take(3)
            ->get();

        if ($suggestions->isEmpty()) {
            return [];
        }

        $result = [];
        $currentLocale = App::getLocale();

        foreach ($suggestions as $suggestion) {
            if ($currentLocale === 'ar') {
                $result[] = "- @{$suggestion->username} ({$suggestion->posts_count} منشورات)";
            } else {
                $result[] = "- @{$suggestion->username} ({$suggestion->posts_count} posts)";
            }
        }

        return $result;
    }

    private function getTrendingTopics()
    {
        $currentLocale = App::getLocale();

        if ($currentLocale === 'ar') {
            return [
                "#تواصل_اجتماعي",
                "#تطوير_ويب",
                "#تقنية",
                "#برمجة",
                "#لارافيل"
            ];
        }

        return [
            "#LaravelSocial",
            "#WebDevelopment",
            "#SocialMedia",
            "#TechNews",
            "#Programming"
        ];
    }
}
