<?php
// app/services/ImageService.php
// ─── Génération d'images via OpenAI DALL-E 3 ────────────────────────
 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/api_keys.php';
 
class ImageService
{
    /**
     * Génère une image à partir d'un prompt via DALL-E 3.
     *
     * @param string $prompt     Description de l'image à générer
     * @param string $style      Style visuel (cinematic, anime, realistic, etc.)
     * @param string $size       Taille de l'image (1024x1024, 1024x1792, 1792x1024)
     * @param string $outputFile Chemin du fichier de sortie (optionnel)
     * @return string            Chemin du fichier image sauvegardé
     * @throws Exception
     */
    public function generateImage(
        string $prompt,
        string $style = 'cinematic',
        string $size = '1024x1792',
        string $outputFile = ''
    ): string {
        // $this->ensureDir(IMAGES_DIR);
 
        // if (empty($outputFile)) {
        //     $outputFile = IMAGES_DIR . '/' . uniqid('img_') . '.png';
        // }
 
        // // Enrichir le prompt avec le style
        // $styledPrompt = $this->buildStyledPrompt($prompt, $style);
 
        // $url  = 'https://api.openai.com/v1/images/generations';
        // $data = [
        //     'model'           => OPENAI_IMAGE_MODEL,
        //     'prompt'          => $styledPrompt,
        //     'n'               => 1,
        //     'size'            => $size,
        //     'quality'         => 'standard',
        //     'response_format' => 'url',
        // ];
 
        // $ch = curl_init($url);
        // curl_setopt_array($ch, [
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_POST           => true,
        //     CURLOPT_HTTPHEADER     => [
        //         'Content-Type: application/json',
        //         'Authorization: Bearer ' . OPENAI_API_KEY,
        //     ],
        //     CURLOPT_POSTFIELDS => json_encode($data),
        //     CURLOPT_TIMEOUT    => 120,
        // ]);
 
        // $response = curl_exec($ch);
        // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $error    = curl_error($ch);
        // curl_close($ch);
 
        // if ($error) {
        //     throw new Exception("DALL-E API request failed: $error");
        // }
 
        // if ($httpCode !== 200) {
        //     $body = json_decode($response, true);
        //     $msg  = $body['error']['message'] ?? "HTTP $httpCode";
        //     throw new Exception("DALL-E API error: $msg");
        // }
 
        // $result   = json_decode($response, true);
        // $imageUrl = $result['data'][0]['url'] ?? null;
 
        // if (!$imageUrl) {
        //     throw new Exception('No image URL returned by DALL-E');
        // }
 
        // // Télécharger l'image
        // $imageData = file_get_contents($imageUrl);
        // if ($imageData === false) {
        //     throw new Exception('Failed to download generated image');
        // }
 
        // file_put_contents($outputFile, $imageData);
 
        // if (!file_exists($outputFile) || filesize($outputFile) < 1000) {
        //     throw new Exception('Downloaded image is empty or corrupted');
        // }

        // S'assurer que le dossier de sortie existe et qu'on a un chemin de fichier valide
        if (!is_dir(IMAGES_DIR)) {
            mkdir(IMAGES_DIR, 0755, true);
        }
        if (empty($outputFile)) {
            $outputFile = IMAGES_DIR . '/' . uniqid('img_') . '.jpeg';
        }

        // Utiliser l'image d'exemple fournie à la racine du projet (portable Windows/Linux)
        $sampleImage = __DIR__ . '/../../image.jpeg';
        if (!file_exists($sampleImage)) {
            throw new Exception('Sample image file not found at ' . $sampleImage);
        }

        $imageData = file_get_contents($sampleImage);
        if ($imageData === false) {
            throw new Exception('Failed to read sample image file');
        }

        if (file_put_contents($outputFile, $imageData) === false) {
            throw new Exception('Failed to write image file to ' . $outputFile);
        }

        return $outputFile;
    }
 
    /**
     * Génère plusieurs images pour un ensemble de scènes.
     *
     * @param array  $scenes Liste des scènes avec 'image_prompt'
     * @param string $style  Style visuel global
     * @return array         Liste des chemins de fichiers d'images générées
     * @throws Exception
     */
    public function generateSceneImages(array $scenes, string $style = 'cinematic'): array
{
    if (!is_dir(IMAGES_DIR)) mkdir(IMAGES_DIR, 0755, true);
    $images = [];
 
        foreach ($scenes as $index => $scene) {
            $prompt = $scene['image_prompt'] ?? $scene['narration'] ?? '';
            if (empty($prompt)) {
                throw new Exception("Scene {$index} has no image prompt");
            }
 
            $outputFile = IMAGES_DIR . '/' . uniqid("scene_{$index}_") . '.jpeg';
 
            try {
                $images[] = $this->generateImage($prompt, $style, '1024x1792', $outputFile);
            } catch (Exception $e) {
                // Log l'erreur mais continue avec les autres scènes
                error_log("Image generation failed for scene {$index}: " . $e->getMessage());
                // Générer une image placeholder
                $images[] = $this->createPlaceholderImage($outputFile, $scene['text_overlay'] ?? "Scene " . ($index + 1));
            }
        }
 
        return $images;
    }
 
    /**
     * Construit un prompt enrichi avec le style visuel.
     */
    private function buildStyledPrompt(string $prompt, string $style): string
    {
        $styleDescriptions = [
            'cinematic'   => 'Cinematic lighting, dramatic composition, movie-quality, 4K, detailed, professional photography',
            'anime'       => 'High-quality anime art style, vibrant colors, detailed anime illustration, Studio Ghibli inspired',
            'realistic'   => 'Photorealistic, ultra-detailed, professional photography, natural lighting, 8K resolution',
            'dark'        => 'Dark and moody atmosphere, dramatic shadows, noir style, cinematic, high contrast',
            'vibrant'     => 'Bright vibrant colors, energetic, pop art inspired, bold and eye-catching',
            'minimal'     => 'Minimalist design, clean lines, simple composition, modern aesthetic',
            'watercolor'  => 'Watercolor painting style, soft colors, artistic, hand-painted feel',
            'digital-art' => 'Digital art, concept art style, detailed illustration, fantasy art',
        ];
 
        $styleDesc = $styleDescriptions[$style] ?? $styleDescriptions['cinematic'];
 
        return "{$prompt}. Style: {$styleDesc}. Vertical format (9:16 aspect ratio), suitable for TikTok/Reels/Shorts. No text or watermarks in the image.";
    }
 
    /**
     * Crée une image placeholder simple si la génération échoue.
     */
    private function createPlaceholderImage(string $outputFile, string $text): string
    {
        // Créer une image simple avec GD
        $width  = 1024;
        $height = 1792;
        $img    = imagecreatetruecolor($width, $height);
 
        // Dégradé de fond
        $colorTop    = imagecolorallocate($img, 30, 41, 59);
        $colorBottom = imagecolorallocate($img, 15, 23, 42);
 
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)(30 + (15 - 30) * $ratio);
            $g = (int)(41 + (23 - 41) * $ratio);
            $b = (int)(59 + (42 - 59) * $ratio);
            $lineColor = imagecolorallocate($img, $r, $g, $b);
            imageline($img, 0, $y, $width, $y, $lineColor);
        }
 
        // Texte centré
        $white = imagecolorallocate($img, 255, 255, 255);
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $x = ($width - $textWidth) / 2;
        $y = $height / 2;
        imagestring($img, $fontSize, (int)$x, (int)$y, $text, $white);
 
        imagepng($img, $outputFile);
        imagedestroy($img);
 
        return $outputFile;
    }
 
    /**
     * S'assure que le répertoire existe.
     */
    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}