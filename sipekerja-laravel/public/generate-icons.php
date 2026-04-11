<?php
/**
 * SIPEKERJA PWA Icon Generator
 * Run once: php public/generate-icons.php
 * Requires PHP GD extension (standard on most servers)
 */

if (!extension_loaded('gd')) {
    die("Error: PHP GD extension is required. Install with: sudo apt-get install php-gd\n");
}

$outputDir = __DIR__ . '/icons';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$sizes = [192, 512];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);

    // Background: BPS Blue #003366
    $bg = imagecolorallocate($img, 0, 51, 102);
    imagefill($img, 0, 0, $bg);

    // Rounded corners via antialiased arc trick (4-corner fill)
    $radius = (int)($size * 0.22);
    $corner = imagecolorallocate($img, 255, 255, 255);
    // Mask corners with white, then recolor back — simpler: just use circle crop approach
    // For simplicity, draw filled rectangle then round corners by overdrawing white arcs
    imagecolortransparent($img, $corner);

    // Draw amber accent bar at bottom — #FFC107 amber
    $amber = imagecolorallocate($img, 255, 193, 7);
    $barH = (int)($size * 0.08);
    imagefilledrectangle($img, 0, $size - $barH, $size, $size, $amber);

    // Draw "S" letter centered
    $white = imagecolorallocate($img, 255, 255, 255);
    $fontFactor = $size >= 512 ? 12 : 5;

    // Use built-in font for compatibility (no TTF needed)
    // GD built-in fonts: 1-5, size 5 is largest (9x15px)
    // For larger icons, tile multiple characters to simulate big letter
    $builtinSize = 5; // 9px wide x 15px tall per char
    $charW = imagefontwidth($builtinSize);
    $charH = imagefontheight($builtinSize);

    // Scale factor: how many times to tile
    $scale = (int)($size / 48);
    if ($scale < 1) $scale = 1;

    // Draw scaled "S" using imagestring at multiple positions
    $letter = 'S';
    $textW = $charW * $scale;
    $textH = $charH * $scale;
    $x = (int)(($size - $textW) / 2);
    $y = (int)(($size - $textH) / 2) - (int)($size * 0.04);

    // Render letter at normal size then tile (workaround for no TTF)
    // Use imagecopyresized to scale up a small "S"
    $tmp = imagecreatetruecolor($charW, $charH);
    $tmpBg = imagecolorallocate($tmp, 0, 51, 102);
    $tmpWhite = imagecolorallocate($tmp, 255, 255, 255);
    imagefill($tmp, 0, 0, $tmpBg);
    imagestring($tmp, $builtinSize, 0, 0, $letter, $tmpWhite);

    imagecopyresized($img, $tmp, $x, $y, 0, 0, $textW, $textH, $charW, $charH);
    imagedestroy($tmp);

    $filename = $outputDir . "/icon-{$size}.png";
    imagepng($img, $filename);
    imagedestroy($img);

    echo "Created: {$filename}\n";
}

echo "Done! Icons generated in public/icons/\n";
