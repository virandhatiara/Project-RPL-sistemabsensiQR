<?php
function processDirectory($dir) {
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            processDirectory($file);
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($file);
            $newContent = $content;
            
            // CSRF Injection
            if (preg_match_all('/<form[^>]+method=["\']POST["\'][^>]*>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $offsetAdjust = 0;
                foreach ($matches[0] as $match) {
                    $formTag = $match[0];
                    $pos = $match[1] + $offsetAdjust;
                    $insertStr = "\n<input type=\"hidden\" name=\"csrf_token\" value=\"<?= \$_SESSION['csrf_token'] ?>\">\n";
                    
                    $subContent = substr($newContent, $pos, 300);
                    if (strpos($subContent, 'name="csrf_token"') === false && strpos($subContent, "name='csrf_token'") === false) {
                        $insertPos = $pos + strlen($formTag);
                        $newContent = substr_replace($newContent, $insertStr, $insertPos, 0);
                        $offsetAdjust += strlen($insertStr);
                    }
                }
            }
            
            if ($newContent !== $content) {
                file_put_contents($file, $newContent);
                echo "Injected CSRF token into $file\n";
            }
        }
    }
}

processDirectory(realpath(__DIR__ . '/../views'));
echo "Done CSRF injection.\n";
