<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Post;

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

        $userMessage = strtolower($request->message);
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
        $message = trim(strtolower($message));

        // Check for invalid numeric inputs (multi-digit, > 9, or negative)
        if (is_numeric($message)) {
            $num = (int)$message;
            if ($num < 0 || $num > 9 || strlen($message) > 1) {
                return "⚠️ **Please type from 0-9 only**\n\nChoose an option by typing a single number (1-9).";
            }
        }

        switch ($message) {
            case '1':
            case 'help':
            case 'menu':
                return $this->showMainMenu();

            case '2':
            case 'write':
            case 'post':
            case 'writing':
                return "📝 **Writing Better Posts**\n\n" .
                       "Here are some tips for creating engaging content:\n\n" .
                       "🎯 **Start with a hook**\n" .
                       "• Ask a thought-provoking question\n" .
                       "• Share a surprising fact\n" .
                       "• Tell a short personal story\n\n" .
                       "💬 **Make it conversational**\n" .
                       "• Use everyday language\n" .
                       "• Ask readers questions\n" .
                       "• Share your genuine opinions\n\n" .
                       "📸 **Add visuals**\n" .
                       "• Images get 2x more engagement\n" .
                       "• Use relevant photos or graphics\n" .
                       "• Create eye-catching thumbnails\n\n" .
                       "⏰ **Post timing**\n" .
                       "• Peak hours: 7-9 AM, 12-2 PM, 5-7 PM\n" .
                       "• Post consistently for best results\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '3':
            case 'follow':
            case 'people':
            case 'suggestions':
                $suggestions = $this->getFollowSuggestions($user);
                return "👥 **Follow Suggestions**\n\n" . $suggestions . "\n\n" .
                       "💡 **Pro Tips:**\n" .
                       "• Follow people whose content you enjoy\n" .
                       "• Engage with their posts to build connections\n" .
                       "• Use the Explore page to discover more users\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '4':
            case 'trend':
            case 'trending':
            case 'popular':
                $trends = $this->getTrendingTopics();
                return "🔥 **Trending Topics**\n\n" . $trends . "\n\n" .
                       "📊 **How trends work:**\n" .
                       "• Based on recent posts and engagement\n" .
                       "• Updated frequently throughout the day\n" .
                       "• Use trending hashtags in your posts\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '5':
            case 'privacy':
            case 'private':
            case 'security':
                return "🔒 **Privacy & Security Guide**\n\n" .
                       "📝 **Post Privacy:**\n" .
                       "• Public: Everyone can see\n" .
                       "• Private: Only followers can see\n\n" .
                       "👤 **Account Privacy:**\n" .
                       "• Public: Anyone can follow you\n" .
                       "• Private: You approve follow requests\n\n" .
                       "🚫 **Blocking & Safety:**\n" .
                       "• Block unwanted users\n" .
                       "• Report inappropriate content\n" .
                       "• Your data is secure and private\n\n" .
                       "⚙️ **Manage Settings:**\n" .
                       "Go to Profile → Edit to change privacy settings.\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '6':
            case 'engagement':
            case 'like':
            case 'comment':
            case 'interact':
                return "💝 **Engagement Tips**\n\n" .
                       "👍 **Liking Posts:**\n" .
                       "• Shows appreciation for content\n" .
                       "• Helps content get discovered\n" .
                       "• Builds positive connections\n\n" .
                       "💬 **Commenting:**\n" .
                       "• Share your thoughts respectfully\n" .
                       "• Ask questions to start discussions\n" .
                       "• Be genuine and authentic\n\n" .
                       "🔄 **Replying:**\n" .
                       "• Continue conversations in threads\n" .
                       "• Show you're engaged with the community\n" .
                       "• Build relationships through dialogue\n\n" .
                       "📌 **Saving Posts:**\n" .
                       "• Bookmark content for later\n" .
                       "• Create personal collections\n" .
                       "• Reference important information\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '7':
            case 'stories':
            case 'story':
                return "📱 **Stories Guide**\n\n" .
                       "✨ **What are Stories?**\n" .
                       "• Temporary posts that disappear after 24 hours\n" .
                       "• Perfect for sharing moments and updates\n" .
                       "• Great for behind-the-scenes content\n\n" .
                       "🎨 **Story Features:**\n" .
                       "• Add text, stickers, and emojis\n" .
                       "• Create polls and questions\n" .
                       "• Share multiple images/videos\n\n" .
                       "👁️ **View Tracking:**\n" .
                       "• See who viewed your stories\n" .
                       "• Get insights on engagement\n" .
                       "• Understand your audience better\n\n" .
                       "📍 **How to Create:**\n" .
                       "Go to Stories → Create Story\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '8':
            case 'profile':
            case 'bio':
            case 'account':
                return "🌟 **Profile Optimization**\n\n" .
                       "🖼️ **Profile Picture:**\n" .
                       "• Use a clear, friendly photo\n" .
                       "• Shows your personality\n" .
                       "• Helps others recognize you\n\n" .
                       "📝 **Bio (160 characters max):**\n" .
                       "• Tell people who you are\n" .
                       "• Include your interests\n" .
                       "• Add emojis to make it fun\n" .
                       "• Include a call-to-action\n\n" .
                       "📍 **Location & Links:**\n" .
                       "• Add your location for local connections\n" .
                       "• Link to your website or other profiles\n" .
                       "• Makes your profile more complete\n\n" .
                       "🎯 **Why it matters:**\n" .
                       "Complete profiles get 3x more engagement!\n\n" .
                       "Type 'menu' to go back to main menu.";

            case '9':
            case 'search':
            case 'find':
            case 'discover':
                return "🔍 **Finding Content & People**\n\n" .
                       "🔎 **Search Bar:**\n" .
                       "• Search for posts, people, or topics\n" .
                       "• Use keywords or usernames\n" .
                       "• Find specific content quickly\n\n" .
                       "🏷️ **Hashtags:**\n" .
                       "• Use #topic to find related posts\n" .
                       "• Popular: #LaravelSocial, #WebDev\n" .
                       "• Create your own hashtags\n\n" .
                       "👥 **Explore Page:**\n" .
                       "• Discover trending posts\n" .
                       "• Find new people to follow\n" .
                       "• Get personalized recommendations\n\n" .
                       "📍 **Location Search:**\n" .
                       "• Find local users and content\n" .
                       "• Connect with people nearby\n" .
                       "• Discover location-based trends\n\n" .
                       "Type 'menu' to go back to main menu.";

            default:
                return $this->showMainMenu();
        }
    }

    private function showMainMenu()
    {
        return "🤖 **Laravel Social AI Assistant**\n\n" .
               "Welcome! Choose an option by typing the number:\n\n" .
               "📋 **Main Menu:**\n" .
               "1️⃣ **Help & Menu** - Show this menu again\n" .
               "2️⃣ **Writing Posts** - Tips for better content\n" .
               "3️⃣ **Follow Suggestions** - People to follow\n" .
               "4️⃣ **Trending Topics** - What's hot now\n" .
               "5️⃣ **Privacy Guide** - Security & privacy settings\n" .
               "6️⃣ **Engagement Tips** - How to interact better\n" .
               "7️⃣ **Stories Guide** - How to use stories\n" .
               "8️⃣ **Profile Setup** - Optimize your profile\n" .
               "9️⃣ **Search & Discover** - Finding content & people\n\n" .
               "💡 **Quick Tips:**\n" .
               "• Type the number (1-9) or keyword\n" .
               "• Get personalized help for each topic\n" .
               "• Type 'menu' anytime to return here\n\n" .
               "What would you like help with? Just type a number! 🎯";
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
            return "• Try exploring the platform - there are many interesting people to discover!\n" .
                   "• Search for topics you're passionate about\n" .
                   "• Check out trending posts to find like-minded users";
        }

        $result = "";
        foreach ($suggestions as $suggestion) {
            $result .= "• @" . $suggestion->name . " (" . $suggestion->posts_count . " posts)\n";
        }

        return $result;
    }

    private function getTrendingTopics()
    {
        
        
        $trends = [
            "#LaravelSocial",
            "#WebDevelopment",
            "#SocialMedia",
            "#TechNews",
            "#Programming"
        ];

        $result = "";
        foreach ($trends as $trend) {
            $result .= "• " . $trend . "\n";
        }

        return $result;
    }
}
