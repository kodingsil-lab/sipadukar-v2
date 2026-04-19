<?php
$viewPath = __DIR__ . '/app/Views';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewPath));

// Pattern: <?= $var ... ?> where the output is NOT wrapped in esc(), htmlspecialchars(), htmlentities()
// Also NOT purely numeric/utility functions
$rawPattern = '/<\?=\s*(?!esc\s*\()(?!htmlspecialchars\s*\()(?!htmlentities\s*\()(?!number_format)(?!count\s*\()(?!round\s*\()(?!intval\s*\()(?!date\s*\()(?!implode\s*\()(?!isset)(?!empty)(?!true)(?!false)(?!null)(?!\()(\$[a-zA-Z_][^\n?]{0,150})\?>/';

// Also check echo with no escaping
$echoPattern = '/\becho\s+(?!esc\s*\()(?!htmlspecialchars\s*\()(\$[a-zA-Z_\'\"(][^\n;]{0,120})/';

$findings = [];

foreach ($files as $f) {
    if ($f->isDir() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    $rel = str_replace($viewPath . DIRECTORY_SEPARATOR, '', $f->getPathname());

    preg_match_all($rawPattern, $content, $m1, PREG_OFFSET_CAPTURE);
    preg_match_all($echoPattern, $content, $m2, PREG_OFFSET_CAPTURE);

    $hits = [];
    foreach ($m1[0] as $m) {
        $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        $hits[] = ['type' => 'RAW_ECHO', 'line' => $line, 'code' => substr($m[0], 0, 120)];
    }
    foreach ($m2[0] as $m) {
        $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
        $hits[] = ['type' => 'ECHO_STMT', 'line' => $line, 'code' => substr($m[0], 0, 120)];
    }

    if (!empty($hits)) {
        $findings[$rel] = $hits;
    }
}

ksort($findings);
foreach ($findings as $file => $hits) {
    echo "=== $file ===\n";
    foreach ($hits as $h) {
        echo "  [{$h['type']}] L{$h['line']}: {$h['code']}\n";
    }
    echo "\n";
}

echo "Total files with findings: " . count($findings) . "\n";
