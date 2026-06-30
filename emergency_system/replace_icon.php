<?php
$svg = '<svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg>';

function walk($dir) {
    $results = [];
    $files = scandir($dir);
    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $results[] = $path;
            }
        } else if ($value != "." && $value != "..") {
            $results = array_merge($results, walk($path));
        }
    }
    return $results;
}

$files = walk(__DIR__);
$count = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $newContent = preg_replace('/<span class="siren">.*?<\/span>/s', $svg, $content);
    
    // Replace old SVG in index.php, login.php, register.php, admin/dashboard.php
    $newContent = preg_replace('/<svg class="brand-icon".*?<\/svg>/s', $svg, $newContent);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Replaced icon in " . basename($file) . "\n";
        $count++;
    }
}
echo "Updated $count files.\n";
?>
