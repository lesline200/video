<?php
// app/services/VideoAssembler.php
// ─── Assemblage vidéo via FFmpeg (images + audio → MP4) ─────────────

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/api_keys.php';

class VideoAssembler
{
    // Encapsule un chemin pour Windows (guillemets doubles)
    private function q(string $path): string
    {
        return '"' . str_replace('"', '', $path) . '"';
    }

    public function assemble(
        array $images,
        string $audioFile,
        array $scenes,
        string $captionStyle = 'karaoke',
        string $outputFile = ''
    ): string {
        $this->ensureDir(VIDEOS_DIR);

        if (empty($outputFile)) {
            $outputFile = VIDEOS_DIR . '/' . uniqid('video_') . '.mp4';
        }

        $this->checkFFmpeg();

        $audioDuration = $this->getAudioDuration($audioFile);
        $numImages     = count($images);

        if ($numImages === 0) {
            throw new Exception('No images provided for video assembly');
        }

        $sceneDurations = $this->calculateSceneDurations($scenes, $audioDuration, $numImages);
        $videoPath      = $this->createSlideshowVideo($images, $audioFile, $sceneDurations, $outputFile);

        if (!empty($scenes) && $captionStyle !== 'none') {
            $subtitledPath = $this->addSubtitles($videoPath, $scenes, $sceneDurations, $captionStyle);
            if ($subtitledPath !== $videoPath) {
                unlink($videoPath);
                $videoPath = $subtitledPath;
            }
        }

        return $videoPath;
    }

    private function createSlideshowVideo(
        array $images,
        string $audioFile,
        array $durations,
        string $outputFile
    ): string {
        $concatFile = VIDEOS_DIR . '/' . uniqid('concat_') . '.txt';
        $content    = '';

        foreach ($images as $i => $imagePath) {
            $dur          = $durations[$i] ?? 5;
            $escapedPath  = str_replace('\\', '/', $imagePath);
            $content     .= "file '{$escapedPath}'\n";
            $content     .= "duration {$dur}\n";
        }

        $lastImage   = str_replace('\\', '/', end($images));
        $content    .= "file '{$lastImage}'\n";

        file_put_contents($concatFile, $content);

        $cmd = sprintf(
            '%s -y -f concat -safe 0 -i %s -i %s ' .
            '-filter_complex "[0:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,setsar=1,format=yuv420p,fps=30[v]" ' .
            '-map "[v]" -map 1:a ' .
            '-c:v libx264 -preset medium -crf 23 ' .
            '-c:a aac -b:a 192k ' .
            '-shortest -movflags +faststart ' .
            '%s 2>&1',
            $this->q(FFMPEG_PATH),
            $this->q($concatFile),
            $this->q($audioFile),
            $this->q($outputFile)
        );

        exec($cmd, $output, $returnCode);

        if (file_exists($concatFile)) {
            unlink($concatFile);
        }

        if ($returnCode !== 0) {
            throw new Exception('FFmpeg video assembly failed: ' . implode("\n", array_slice($output, -10)));
        }

        if (!file_exists($outputFile) || filesize($outputFile) < 1000) {
            throw new Exception('Generated video file is empty or corrupted');
        }

        return $outputFile;
    }

    private function addSubtitles(
        string $videoFile,
        array $scenes,
        array $durations,
        string $captionStyle
    ): string {
        $srtFile    = VIDEOS_DIR . '/' . uniqid('subs_') . '.srt';
        $srtContent = $this->generateSRT($scenes, $durations);
        file_put_contents($srtFile, $srtContent);

        $outputFile    = VIDEOS_DIR . '/' . uniqid('video_sub_') . '.mp4';
        $subtitleStyle = $this->getSubtitleStyle($captionStyle);

        // Sur Windows, les backslashes dans le filtre subtitles doivent être échappés
        $srtForFilter = str_replace(['\\', ':'], ['\\\\', '\\:'], $srtFile);

        $cmd = sprintf(
            '%s -y -i %s -vf "subtitles=%s:force_style=\'%s\'" ' .
            '-c:v libx264 -preset medium -crf 23 ' .
            '-c:a copy -movflags +faststart ' .
            '%s 2>&1',
            $this->q(FFMPEG_PATH),
            $this->q($videoFile),
            $srtForFilter,
            $subtitleStyle,
            $this->q($outputFile)
        );

        exec($cmd, $output, $returnCode);

        if (file_exists($srtFile)) {
            unlink($srtFile);
        }

        if ($returnCode !== 0) {
            error_log('Subtitle overlay failed: ' . implode("\n", array_slice($output, -5)));
            return $videoFile;
        }

        return $outputFile;
    }

    private function generateSRT(array $scenes, array $durations): string
    {
        $srt         = '';
        $currentTime = 0.0;

        foreach ($scenes as $i => $scene) {
            $narration = $scene['text_overlay'] ?? $scene['narration'] ?? '';
            if (empty($narration)) continue;

            if (strlen($narration) > 80) {
                $narration = $scene['text_overlay'] ?? substr($narration, 0, 80) . '...';
            }

            $dur   = $durations[$i] ?? 5;
            $start = $this->formatSRTTime($currentTime);
            $end   = $this->formatSRTTime($currentTime + $dur);

            $srt .= ($i + 1) . "\n";
            $srt .= "{$start} --> {$end}\n";
            $srt .= $narration . "\n\n";

            $currentTime += $dur;
        }

        return $srt;
    }

    private function formatSRTTime(float $seconds): string
    {
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = floor($seconds % 60);
        $millis  = round(($seconds - floor($seconds)) * 1000);

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $millis);
    }

    private function getSubtitleStyle(string $style): string
    {
        $styles = [
            'karaoke'     => 'FontName=Arial,FontSize=22,PrimaryColour=&H00FFFFFF,OutlineColour=&H00000000,BackColour=&H80000000,Bold=1,Outline=2,Shadow=1,MarginV=80,Alignment=2',
            'tiktok-bold' => 'FontName=Impact,FontSize=26,PrimaryColour=&H00FFFFFF,OutlineColour=&H00000000,BackColour=&H00000000,Bold=1,Outline=3,Shadow=0,MarginV=100,Alignment=2',
            'minimal'     => 'FontName=Helvetica,FontSize=18,PrimaryColour=&H00FFFFFF,OutlineColour=&H00000000,BackColour=&H00000000,Bold=0,Outline=1,Shadow=0,MarginV=60,Alignment=2',
            'cinematic'   => 'FontName=Georgia,FontSize=20,PrimaryColour=&H00E0E0E0,OutlineColour=&H00000000,BackColour=&H40000000,Bold=0,Outline=2,Shadow=2,MarginV=70,Alignment=2',
        ];

        return $styles[$style] ?? $styles['karaoke'];
    }

    private function calculateSceneDurations(array $scenes, float $totalDuration, int $numImages): array
    {
        $durations    = [];
        $totalDefined = 0;

        foreach ($scenes as $scene) {
            $totalDefined += $scene['duration_seconds'] ?? 0;
        }

        if ($totalDefined > 0) {
            $ratio = $totalDuration / $totalDefined;
            foreach ($scenes as $scene) {
                $durations[] = ($scene['duration_seconds'] ?? 5) * $ratio;
            }
        } else {
            $perScene = $totalDuration / $numImages;
            for ($i = 0; $i < $numImages; $i++) {
                $durations[] = $perScene;
            }
        }

        return $durations;
    }

    private function getAudioDuration(string $audioFile): float
    {
        $cmd      = '"' . FFPROBE_PATH . '" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "' . $audioFile . '" 2>&1';
        $output   = trim(shell_exec($cmd) ?? '0');
        $duration = (float) $output;

        if ($duration <= 0) {
            $duration = 30.0;
        }

        return $duration;
    }

    public function getVideoInfo(string $videoFile): array
    {
        $cmd    = '"' . FFPROBE_PATH . '" -v error -show_entries format=duration,size -show_entries stream=width,height -of json "' . $videoFile . '" 2>&1';
        $output = shell_exec($cmd);
        $info   = json_decode($output ?? '{}', true);

        return [
            'duration_seconds' => (int) round((float)($info['format']['duration'] ?? 0)),
            'file_size_bytes'  => (int) ($info['format']['size'] ?? filesize($videoFile)),
            'width'            => (int) ($info['streams'][0]['width'] ?? 1080),
            'height'           => (int) ($info['streams'][0]['height'] ?? 1920),
        ];
    }

  private function checkFFmpeg(): void
{
    $cmd = '"' . FFMPEG_PATH . '" -version 2>&1';
    exec($cmd, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception('FFmpeg is not installed or not found at: ' . FFMPEG_PATH);
    }
}


    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}