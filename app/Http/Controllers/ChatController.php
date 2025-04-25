<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Show the chat page
     */
    public function index()
    {
        return view('chat.index');
    }

    /**
     * Process user messages
     */
    public function sendMessage(Request $request)
    {
        $userMessage = $request->input('message');
        $mode = $request->input('mode', 'normal'); // normal, trip-plan, travel-tip

        try {
            switch ($mode) {
                case 'trip-plan':
                    $response = $this->tripPlanMode();
                    break;
                case 'travel-tip':
                    $response = $this->travelTipMode();
                    break;
                default:
                    $response = $this->normalMode($userMessage);
            }

            return response()->json(['reply' => $response]);
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function normalMode($message)
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are a travel assistant, specialized in helping users solve travel-related issues. Provide friendly, professional answers. Your replies will be parsed as Markdown format, so you can use headings, lists, bold, italic, and other formatting.'],
            ['role' => 'user', 'content' => $message]
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini-2025-04-14',
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }

    private function tripPlanMode()
    {
        // Randomly select a popular travel destination
        $destinations = ['Tokyo', 'Paris', 'New York', 'London', 'Beijing', 'Bangkok', 'Sydney', 'Rome', 'Hong Kong', 'Singapore', 'Dubai', 'Seoul'];
        $destination = $destinations[array_rand($destinations)];

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4.1-mini-2025-04-14',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a travel planning expert. Please create a detailed 7-day travel itinerary for the user. Use Markdown format with appropriate headings, lists, and highlighted points.'],
                    ['role' => 'user', 'content' => "Please create a detailed 7-day travel itinerary for {$destination}, including daily activities, recommended attractions, dining suggestions, and transportation arrangements. Use Markdown format to make the content clear and readable."]
                ],
                'temperature' => 0.8,
                'max_tokens' => 5000,
            ]);

            return "# {$destination} 7-Day Travel Itinerary ✨\n\n" . $result->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('Travel plan generation error: ' . $e->getMessage());
            return "Sorry, I couldn't generate a travel plan for you. Please try again later or ask me about any travel destination.";
        }
    }

    private function travelTipMode()
    {
        // Randomly select a travel tip category
        $tipCategories = [
            'Luggage packing tips',
            'Money-saving travel hacks',
            'Travel safety advice',
            'Preventing motion sickness',
            'Healthy eating while traveling',
            'Hotel selection tips',
            'Communicating with locals',
            'Travel photography tips',
            'Handling travel delays',
            'Eco-friendly travel practices'
        ];

        $category = $tipCategories[array_rand($tipCategories)];

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4.1-mini-2025-04-14',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a travel expert, sharing practical travel tips. Please use Markdown format to make your content clear.'],
                    ['role' => 'user', 'content' => "Please share a practical travel tip about {$category}, short and easy to understand. Use Markdown format."]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            return "# Travel Tip: {$category} 💡\n\n" . $result->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('Travel tip generation error: ' . $e->getMessage());
            return "Sorry, I couldn't generate a travel tip for you. Please try again later or ask me about any travel-related question.";
        }
    }
}
