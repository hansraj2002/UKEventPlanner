<?php
require 'vendor/autoload.php';

use Algolia\AlgoliaSearch\SearchClient;

if (class_exists('Algolia\AlgoliaSearch\SearchClient')) {
    echo "Algolia SearchClient loaded successfully!";
} else {
    echo "Failed to load Algolia SearchClient!";
}
