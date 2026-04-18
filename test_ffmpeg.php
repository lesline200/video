<?php
// test_ffmpeg2.php
define('FFMPEG_PATH', 'C:/ffmpeg/bin/ffmpeg.exe');

$cmd = '"' . FFMPEG_PATH . '" -version 2>&1';
echo "CMD: " . $cmd . "<br>";

exec($cmd, $output, $returnCode);

echo "Return code: " . $returnCode . "<br>";
echo "Output: <pre>" . implode("\n", $output) . "</pre>";