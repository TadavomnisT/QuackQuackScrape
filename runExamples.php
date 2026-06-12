<?php

require_once "./src/QuackQuackScrape.php";

// Initialize the main scraper
$scraper = new QuackQuckScrape();

// Search DuckDuckGo
$results = $scraper->search('femboy dance');
var_dump($results);

// // Fetch a web page
// $page = $scraper->fetch('https://en.wikipedia.org/wiki/Femboy/');
// var_dump($page);

// // Enable smart discovery features
// $scraper->setSmartDiscovery(true, 3)
//         ->setFollowMetaRefresh(true)
//         ->setFollowJsRedirects(true)
//         ->setTryCommonVariations(true)
//         ->setRetryAttempts(3, 2);

// // Test various URL scenarios

// echo "=== Test 1: Wikipedia with trailing slash (auto-corrects) ===\n";
// $result = $scraper->fetch('https://en.wikipedia.org/wiki/Quantum/');
// if (!isset($result['error'])) {
//     echo "✓ Success! Found at: " . ($result['discovered_url'] ?? $result['effective_url'] ?? 'N/A') . "\n";
// } else {
//     echo "✗ Failed: " . $result['error'] . "\n";
// }

// echo "\n=== Test 2: Missing www subdomain ===\n";
// $result = $scraper->fetch('http://google.com');
// if (!isset($result['error'])) {
//     echo "✓ Success! Redirected to: " . $result['effective_url'] . "\n";
// }

// echo "\n=== Test 3: Case-insensitive path (should find) ===\n";
// $result = $scraper->fetch('https://github.com/QUANTUM');
// if (!isset($result['error'])) {
//     echo "✓ Success! Found at: " . $result['effective_url'] . "\n";
// }

// echo "\n=== Test 4: Missing index file ===\n";
// $result = $scraper->fetch('https://httpbin.org/');
// if (!isset($result['error'])) {
//     echo "✓ Success! Content length: " . strlen($result['content']) . " bytes\n";
// }

// echo "\n=== Test 5: URL with spaces (auto-encoded) ===\n";
// $result = $scraper->fetch('https://en.wikipedia.org/wiki/Quantum mechanics');
// if (!isset($result['error'])) {
//     echo "✓ Success! Found at: " . $result['effective_url'] . "\n";
// }

// echo "\n=== Test 6: 301/302 redirect handling ===\n";
// $result = $scraper->fetch('http://en.wikipedia.org/wiki/Quantum');
// if (!isset($result['error'])) {
//     echo "✓ Success! Redirected from HTTP to HTTPS: " . $result['effective_url'] . "\n";
// }

// echo "\n=== Test 7: Meta refresh redirect ===\n";
// // Create a test page with meta refresh (if you have a test server)
// // $result = $scraper->fetch('http://localhost/test-meta-refresh.php');
// // echo $result['effective_url'] . "\n";

// echo "\n=== Test 8: URL with common variation ===\n";
// $result = $scraper->fetch('https://stackoverflow.com/questions/tagged/php');
// if (!isset($result['error'])) {
//     echo "✓ Success! Page loaded\n";
// }

// echo "\n=== Test 9: Non-existent URL with smart discovery ===\n";
// $result = $scraper->fetch('https://en.wikipedia.org/wiki/ThisPageDoesNotExist12345');
// if (isset($result['error'])) {
//     echo "✗ Correctly failed: " . $result['error'] . "\n";
// }

// echo "\n=== Test 10: JavaScript redirect simulation ===\n";
// // This would handle pages that use window.location.href
// $result = $scraper->fetchWithJs('https://example.com/js-redirect-page');
// if (!isset($result['error'])) {
//     echo "✓ JS redirect handled\n";
// }

// // Advanced: Get statistics
// $stats = $scraper->getStats();
// echo "\n=== Statistics ===\n";
// print_r($stats);


// // Fetch with JavaScript simulation
// $jsPage = $scraper->fetchWithJs('https://example.com/dynamic-page');

// // Download a file
// $scraper->download('https://example.com/file.pdf', '/path/to/save.pdf');

// // Extract all links from a page
// $links = $scraper->extractLinks('https://example.com');

// // Batch operations
// $batchResults = $scraper->batch([
//     ['type' => 'search', 'query' => 'artificial intelligence'],
//     ['type' => 'fetch', 'url' => 'https://example.com'],
//     ['type' => 'headers', 'url' => 'https://example.com']
// ]);

// // Configure proxy
// $scraper->setProxy('http://proxy.example.com:8080')->enableProxy();

// // Set custom settings
// $scraper->setTimeout(45)
//         ->setRetryAttempts(5, 2)
//         ->enableRandomDelay(2, 5);



// ============ Or you can manually just use DuckDuckGo class ============

require_once "./src/DuckDuckGo.php";

// Example 1: Basic usage without proxy
$ddg = new DuckDuckGo;
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);

// Example 2: Using HTTP proxy

// $ddg = new DuckDuckGo;
// $ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
// $results = $ddg->searchUsingDuckduckgo("cute femboys");
// var_dump($results);


// // Example 3: Using HTTP proxy with authentication

// $ddg = new DuckDuckGo;
// $ddg->setProxy('http://username:password@proxy.example.com:8080')->enableProxy();
// $results = $ddg->searchUsingDuckduckgo("cute femboys");
// var_dump($results);


// // Example 4: Using SOCKS5 proxy (Tor)

// $ddg = new DuckDuckGo;
// $ddg->setProxy('socks5://127.0.0.1:9050')->enableProxy();
// $results = $ddg->searchUsingDuckduckgo("cute femboys");
// var_dump($results);


// // Example 5: Search multiple pages

// $ddg = new DuckDuckGo;
// $results = $ddg->searchMultiplePages("quantum computing", 3);
// var_dump($results);


// // Example 6: Search with offset

// $ddg = new DuckDuckGo;
// $results = $ddg->searchWithOffset("php programming", 30);
// var_dump($results);


// // Example 7: Temporarily disable proxy

// $ddg = new DuckDuckGo;
// $ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
// $results1 = $ddg->searchUsingDuckduckgo("test query"); // uses proxy
// $ddg->disableProxy();
// $results2 = $ddg->searchUsingDuckduckgo("another query"); // no proxy
// var_dump($results2);


?>
