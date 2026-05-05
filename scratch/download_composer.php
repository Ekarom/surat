<?php
echo "Downloading composer.phar...\n";
$url = 'https://getcomposer.org/composer.phar';
$file = 'composer.phar';
if (copy($url, $file)) {
    echo "Successfully downloaded composer.phar\n";
} else {
    echo "Failed to download composer.phar\n";
}
?>
