<?php

$viewsDir = 'd:/laragon/www/qpos/resources/js/views/admin';

// Load dictionary
$jsonPath = 'd:/laragon/www/qpos/lang/bn.json';
$dict = json_decode(file_get_contents($jsonPath), true) ?: [];

$allFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($it as $f) {
    if (!$f->isDir() && $f->getExtension() === 'vue') {
        $allFiles[] = $f->getPathname();
    }
}

$modified = 0;
foreach ($allFiles as $filePath) {
    $orig = file_get_contents($filePath);
    $content = $orig;

    // 1. Clean ONLY static plain text attributes: e.g. title="Something (বাংলা)"
    // We explicitly avoid :title or any attribute starting with : or @ or containing JS operators
    $content = preg_replace_callback('/(?<!:)\b(title|placeholder|header)\s*=\s*"([^"\'{}:?]+)\s*\(([\x{0980}-\x{09FF}\s\/\-_.,]+)\)"/u', function($m) use (&$dict) {
        $attr = $m[1];
        $en = trim($m[2]);
        $bn = trim($m[3]);
        if (!empty($en) && !empty($bn)) {
            $dict[$en] = $bn;
        }
        return $attr . '="' . $en . '"';
    }, $content);

    // 2. Clean plain text inside tags: e.g. >Some English (বাংলা)<
    $content = preg_replace_callback('/>\s*([a-zA-Z0-9\s\/\-_&.,\':+]+)\s*\(([\x{0980}-\x{09FF}\s\/\-_.,]+)\)\s*</u', function($m) use (&$dict) {
        $en = trim($m[1]);
        $bn = trim($m[2]);
        if (!empty($en) && !empty($bn)) {
            $dict[$en] = $bn;
        }
        return '>{{ $t(\'' . addslashes($en) . '\') }}<';
    }, $content);

    // 3. Clean options: <option value="xyz">Some Text (বাংলা)</option>
    $content = preg_replace_callback('/<option([^>]*)>\s*([a-zA-Z0-9\s\/\-_&.,\':+]+)\s*\(([\x{0980}-\x{09FF}\s\/\-_.,]+)\)\s*<\/option>/u', function($m) use (&$dict) {
        $attr = $m[1];
        $en = trim($m[2]);
        $bn = trim($m[3]);
        if (!empty($en) && !empty($bn)) {
            $dict[$en] = $bn;
        }
        return '<option' . $attr . '>{{ $t(\'' . addslashes($en) . '\') }}</option>';
    }, $content);

    // 4. Wrap plain table headers: <th ...>Plain Header</th>
    $content = preg_replace_callback('/<th([^>]*)>\s*([A-Za-z0-9\s\/\-_#&.:]+)\s*<\/th>/', function($m) use (&$dict) {
        $attrs = $m[1];
        $text = trim($m[2]);
        if ($text === '' || $text === ':' || str_contains($text, '$t(') || str_contains($text, '{{') || str_contains($text, '<')) {
            return $m[0];
        }
        if (!isset($dict[$text])) {
            $dict[$text] = $text;
        }
        return '<th' . $attrs . '>{{ $t(\'' . addslashes($text) . '\') }}</th>';
    }, $content);

    if ($content !== $orig) {
        file_put_contents($filePath, $content);
        $modified++;
    }
}

echo "Safely modified {$modified} Vue files.\n";

// Save dictionaries
ksort($dict, SORT_NATURAL | SORT_FLAG_CASE);

file_put_contents($jsonPath, json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Updated {$jsonPath} with " . count($dict) . " keys.\n";

$jsPath = 'd:/laragon/www/qpos/resources/js/lang/bn.js';
$jsContent = "export default " . json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ";\n";
file_put_contents($jsPath, $jsContent);
echo "Updated {$jsPath} with " . count($dict) . " keys.\n";

