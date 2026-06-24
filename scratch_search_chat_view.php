<?php
$content = file_get_contents('c:/Users/Raamiz/Downloads/slack/resources/views/chat/index.blade.php');
$lines = explode("\n", $content);

echo "Searching for chat-form submit:\n";
foreach ($lines as $i => $line) {
    if (strpos($line, 'chat-form') !== false && (strpos($line, 'submit') !== false || strpos($line, 'addEventListener') !== false)) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
