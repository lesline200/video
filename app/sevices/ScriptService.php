<?php
// app/services/ScriptService.php
// ─── Génération de scripts vidéo via OpenAI GPT-4 ──────────────────

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/api_keys.php';

class ScriptService
{
    /**
     * Génère un script vidéo structuré via OpenAI.
     *
     * @param string $niche       Niche/thème de la vidéo
     * @param string $tone        Ton souhaité (motivational, dramatic, etc.)
     * @param int    $durationSec Durée cible en secondes
     * @param string $language    Langue (par défaut 'en')
     * @param string|null $topic  Sujet spécifique (optionnel)
     * @return array              Script structuré avec scènes
     * @throws Exception
     */
    public function generateScript(
        string $niche,
        string $tone = 'dramatic',
        int $durationSec = 60,
        string $language = 'en',
        ?string $topic = null
    ): array {
        $numScenes = max(3, intval($durationSec / 10));

        $topicInstruction = $topic
            ? "The specific topic is: \"$topic\"."
            : "Choose a compelling, viral-worthy topic within this niche.";

        $prompt = <<<PROMPT
You are an expert short-form video scriptwriter for platforms like TikTok, YouTube Shorts, and Instagram Reels.
 
Create a {$durationSec}-second faceless video script for the niche: "{$niche}".
Tone: {$tone}.
Language: {$language}.
{$topicInstruction}
 
Return a valid JSON object with this exact structure:
{
  "title": "Video title (catchy, under 60 chars)",
  "hook": "Opening hook sentence (first 3 seconds, attention-grabbing)",
  "scenes": [
    {
      "scene_number": 1,
      "narration": "The text to be spoken (1-2 sentences)",
      "image_prompt": "Detailed prompt for AI image generation (cinematic, specific, visual)",
      "duration_seconds": 8,
      "text_overlay": "Short text shown on screen (optional, max 8 words)"
    }
  ],
  "call_to_action": "Follow for more!",
  "hashtags": ["relevant", "hashtags", "for", "this", "video"],
  "description": "Short video description for social media posting"
}
 
Rules:
- Generate exactly {$numScenes} scenes
- Each scene's narration should be 1-2 sentences, clear and engaging
- Image prompts should be detailed, cinematic, and visually striking
- The total narration should fit within {$durationSec} seconds when spoken
- Make the hook extremely attention-grabbing (first 3 seconds are crucial)
- Include relevant trending hashtags
- Do NOT include any markdown, code blocks, or extra text — only valid JSON
PROMPT;
// echo  $prompt;
        // $response = $this->callOpenAI($prompt);

        // // Parse le JSON de la réponse  
        // $script = json_decode($response, true);
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     // Essayer d'extraire le JSON de la réponse
        //     if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
        //         $script = json_decode($matches[0], true);
        //     }
        //     if (json_last_error() !== JSON_ERROR_NONE) {
        //         throw new Exception('Failed to parse script JSON from OpenAI response');
        //     }
        // }

        // // Validation basique
        // if (empty($script['title']) || empty($script['scenes'])) {
        //     throw new Exception('Invalid script structure: missing title or scenes');
        // }
        $script = '{
            "title": "Start Before You\'re Ready",
            "hook": "What if everything you want is waiting on one bold move?",
            "scenes": [
            {
            "scene_number": 1,
            "narration": "You keep waiting for the perfect moment, but perfection is a myth. The truth is, success begins the second you decide to move.",
            "image_prompt": "A lone person standing at the edge of a cliff at sunrise, golden light breaking through clouds, dramatic cinematic lighting, wide-angle shot, high contrast, symbolizing decision and courage",
            "duration_seconds": 11,
            "text_overlay": "Start now"
            },
            {
            "scene_number": 2,
            "narration": "Fear will always be there, whispering doubts in your mind. But courage isn\'t the absence of fear, it\'s acting in spite of it.",
            "image_prompt": "Close-up of a determined face in the rain, water droplets on skin, intense eyes, dark moody background with soft light highlighting expression, cinematic realism",
            "duration_seconds": 11,
            "text_overlay": "Feel fear, act anyway"
            },
            {
            "scene_number": 3,
            "narration": "Every great story started with someone who refused to quit. Your story is no different if you choose to keep going.",
            "image_prompt": "A runner pushing forward on a long empty road at dusk, dramatic sky, motion blur, strong shadows, symbolizing perseverance and resilience",
            "duration_seconds": 12,
            "text_overlay": "Don\'t quit"
            },
            {
            "scene_number": 4,
            "narration": "Stop comparing your beginning to someone else\'s middle. Your pace is your power.",
            "image_prompt": "Split-screen visual: one side a beginner climbing stairs slowly, the other a successful person at the top, warm lighting, cinematic depth, symbolic contrast",
            "duration_seconds": 11,
            "text_overlay": "Your pace matters"
            },
            {
            "scene_number": 5,
            "narration": "Discipline will take you where motivation can\'t. Show up every day, even when it feels impossible.",
            "image_prompt": "Early morning gym scene, empty space with one person training alone, soft sunrise light through windows, sweat and effort visible, cinematic framing",
            "duration_seconds": 12,
            "text_overlay": "Stay consistent"
            },
            {
            "scene_number": 6,
            "narration": "One day, you\'ll look back and thank yourself for not giving up. Start today, and make that future real.",
            "image_prompt": "Person standing on a mountain peak overlooking vast landscape, sunrise horizon, arms raised in victory, epic cinematic view, high detail and vibrant colors",
            "duration_seconds": 13,
            "text_overlay": "Your future starts now"
            }
            ],
            "call_to_action": "Follow for more!",
            "hashtags": ["motivation", "successmindset", "nevergiveup", "discipline", "selfgrowth", "inspiration", "mindsetshift"],
            "description": "Stop waiting. Start now. Your future is built by what you do today."
            }';
        $script = json_decode($script, true);

        return $script;
    }

    /**
     * Appel à l'API OpenAI Chat Completions.
     */
    private function callOpenAI(string $prompt): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model'       => OPENAI_MODEL,
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a professional video scriptwriter. Always respond with valid JSON only, no markdown.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.8,
            'max_tokens'  => 2000,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY,
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT    => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("OpenAI API request failed: $error");
        }

        if ($httpCode !== 200) {
            $body = json_decode($response, true);
            $msg  = $body['error']['message'] ?? "HTTP $httpCode";
            throw new Exception("OpenAI API error: $msg");
        }

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
}
