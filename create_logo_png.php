<?php
/**
 * Generate PNG version of WorkNexus logo for email use.
 * Run once: php create_logo_png.php
 * Then delete this file.
 */

$size = 128;
$img = imagecreatetruecolor($size, $size);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);

// Blue gradient colors
$blue1 = imagecolorallocate($img, 91, 141, 239);   // #5B8DEF
$blue2 = imagecolorallocate($img, 74, 124, 240);    // #4A7CF0
$white = imagecolorallocate($img, 255, 255, 255);
$white90 = imagecolorallocatealpha($img, 255, 255, 255, 20); // ~0.9 opacity
$white70 = imagecolorallocatealpha($img, 255, 255, 250, 38); // ~0.7 opacity
$white50 = imagecolorallocatealpha($img, 255, 255, 250, 64); // ~0.5 opacity

$cx = $size / 2;
$cy = $size / 2;
$r = 55; // outer circle radius

// Draw outer blue circle with slight gradient effect
imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $blue1);

// Add a subtle highlight (lighter top-left)
$highlight = imagecolorallocatealpha($img, 120, 170, 255, 50);
imagefilledellipse($img, $cx - 10, $cy - 10, $r * 1.6, $r * 1.6, $highlight);

// Re-draw main circle to blend
imagefilledellipse($img, $cx, $cy, $r * 2 - 4, $r * 2 - 4, $blue1);

// White dots (3 nodes) - scaled from 48px viewBox to 128px
// Original positions: (24,14), (13,30), (35,30) in 48x48
$scale = $size / 48;
$dotR1 = round(5.5 * $scale); // top dot radius
$dotR2 = round(4.5 * $scale); // bottom dots radius

$topX = round(24 * $scale);
$topY = round(14 * $scale);
$leftX = round(13 * $scale);
$leftY = round(30 * $scale);
$rightX = round(35 * $scale);
$rightY = round(30 * $scale);

// Draw connecting lines first (behind dots)
imagesetthickness($img, round(2 * $scale));

imageline($img, $topX, $topY, $leftX, $leftY, $white70);
imageline($img, $topX, $topY, $rightX, $rightY, $white70);
imageline($img, $leftX, $leftY, $rightX, $rightY, $white50);

// Draw white dots
imagefilledellipse($img, $topX, $topY, $dotR1 * 2, $dotR1 * 2, $white);
imagefilledellipse($img, $leftX, $leftY, $dotR2 * 2, $dotR2 * 2, $white90);
imagefilledellipse($img, $rightX, $rightY, $dotR2 * 2, $dotR2 * 2, $white90);

// Save as PNG
$outputPath = __DIR__ . '/assets/images/logo-email.png';
imagepng($img, $outputPath);
imagedestroy($img);

echo "Logo PNG created at: {$outputPath}\n";
echo "Size: " . filesize($outputPath) . " bytes\n";
