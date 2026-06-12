# QuackQuackScrape

A powerful PHP web scraping library with built-in DuckDuckGo search capabilities, intelligent URL discovery, rotating user agents, proxy support, and advanced request handling.

<img width="2246" height="1254" alt="QuackQuackScrape" src="./docs/img/QuackQuckScrape.webp" />

## Features

- 🔍 **DuckDuckGo Search** - HTML-based search with pagination and offset support
- 🕵️ **Smart URL Discovery** - Auto-detects working URLs, redirects, meta refresh, and JavaScript redirects
- 🔄 **Faking User Agents** - Hundreds of real user agents from Chrome, Firefox, Safari, Edge across Windows, macOS, Linux, iOS, and Android
- 🌐 **Proxy Support** - HTTP, HTTPS, and SOCKS5 proxies with authentication
- 📡 **Advanced Web Fetching** - Retry logic, random delays, cookie persistence, and SSL configuration
- 📄 **JavaScript Simulation** - Extracts API calls and handles client-side redirects
- 📥 **File Download** - Download files directly to disk
- 🔗 **Link Extraction** - Parse and extract all links from any webpage

## Installation

1. Clone the repository:

```bash
git clone https://github.com/TadavomnisT/QuackQuackScrape.git
```

2. Change to the project directory:

```bash
cd QuackQuackScrape
```

3. simply run the example test:

```php
php runExamples.php;
```

## Requirements

- PHP 7.0+
- cURL extension (usually pre-installed)

## Quick Start

```php
<?php
require_once "QuackQuackScrape.php";

// Initialize the scraper
$scraper = new QuackQuckScrape();

// Enable smart discovery features (optional)
$scraper->setSmartDiscovery(true, 3)
        ->setFollowMetaRefresh(true)
        ->setFollowJsRedirects(true)
        ->setRetryAttempts(3, 2);

// Search DuckDuckGo
$results = $scraper->search('cute femboys');
var_dump($results);

// Fetch a web page
$page = $scraper->fetch('https://en.wikipedia.org/wiki/Femboy');
var_dump($page);

```

## Advanced Usage

### Configuration Methods

```php
$scraper = new QuackQuckScrape();

// Request settings
$scraper->setTimeout(45);                          // Timeout in seconds
$scraper->setMaxRedirects(10);                     // Max redirects to follow
$scraper->setRetryAttempts(5, 2);                  // 5 retries with 2s delay
$scraper->enableRandomDelay(2, 5);                 // Random delay between requests

// Headers and cookies
$scraper->setCustomHeaders(['X-Custom: Value']);   // Custom HTTP headers
$scraper->enableCookies();                         // Persist cookies across requests

// SSL (disable for scraping)
$scraper->setSSLVerification(false);

// Smart discovery
$scraper->setSmartDiscovery(true, 3);              // Enable with depth 3
$scraper->setFollowMetaRefresh(true);              // Follow meta refresh redirects
$scraper->setFollowJsRedirects(true);              // Follow JavaScript redirects
$scraper->setTryCommonVariations(true);            // Try http/https, www/no-www, index files
$scraper->setUrlNormalization(true);               // Normalize URLs automatically
```

### Proxy Configuration

```php
// HTTP proxy with authentication
$scraper->setProxy('http://username:password@proxy.example.com:8080')->enableProxy();

// SOCKS5 proxy
$scraper->setProxy('socks5://127.0.0.1:9050')->enableProxy();

// Disable proxy
$scraper->disableProxy();
```

### Search Methods

```php
// Basic search
$results = $scraper->search('artificial intelligence');

// Multiple pages
$results = $scraper->searchMultiplePages('machine learning', 5);

// Search with offset (pagination)
$results = $scraper->searchWithOffset('php programming', 30); // Page 2
```

### Web Fetching Methods

```php
// Standard GET request
$response = $scraper->fetch('https://example.com');

// POST request
$postData = ['key' => 'value'];
$response = $scraper->post('https://api.example.com', $postData);

// JSON POST request
$jsonData = ['query' => 'test'];
$response = $scraper->post('https://api.example.com', $jsonData, [], true);

// JavaScript simulation (handles dynamic content)
$response = $scraper->fetchWithJs('https://example.com/spa-page');

// Get only headers
$headers = $scraper->getHeaders('https://example.com');

// Download a file
$result = $scraper->download('https://example.com/file.pdf', '/path/to/save.pdf');

// Check if URL is accessible
if ($scraper->isAccessible('https://example.com')) {
    echo "URL is reachable!\n";
}

// Extract all links from a page
$links = $scraper->extractLinks('https://example.com');
```

### User Agent Management

```php
// Get random user agent with full data
$uaData = $scraper->getRandomUserAgent();
echo "UA: " . $uaData['ua'] . "\n";
echo "Brand: " . $uaData['brand'] . "\n";
echo "Mobile: " . $uaData['mobile'] . "\n";
echo "Platform: " . $uaData['platform'] . "\n";

// Get all available user agents
$allUAs = $scraper->getAllUserAgents();
echo "Total user agents: " . count($allUAs) . "\n";
```

### Batch Operations

```php
$batchResults = $scraper->batch([
    ['type' => 'search', 'query' => 'open source'],
    ['type' => 'fetch', 'url' => 'https://github.com'],
    ['type' => 'headers', 'url' => 'https://github.com']
]);
```

### Complete Example with Error Handling

```php
<?php
require_once "QuackQuackScrape.php";

// Initialize with proxy (optional)
$scraper = new QuackQuckScrape('socks5://127.0.0.1:9050', false);

// Configure for best results
$scraper->setTimeout(30)
        ->setRetryAttempts(3, 2)
        ->enableRandomDelay(2, 5)
        ->setSmartDiscovery(true, 2)
        ->setFollowMetaRefresh(true)
        ->setFollowJsRedirects(true);

// Perform search
$results = $scraper->search('web scraping best practices', true);

if (isset($results['error'])) {
    die("Search error: " . $results['error']);
}

foreach ($results as $result) {
    echo "\n---\n";
    echo "Title: " . $result['title'] . "\n";
    echo "URL: " . $result['url'] . "\n";
    
    // Fetch each result
    $page = $scraper->fetch($result['url']);
    
    if (!isset($page['error'])) {
        echo "Content length: " . strlen($page['content']) . " bytes\n";
        echo "HTTP Code: " . ($page['http_code'] ?? 'N/A') . "\n";
    } else {
        echo "Error fetching: " . $page['error'] . "\n";
    }
    
    // Be respectful - delay between requests
    sleep(rand(2, 5));
}

// Get scraper statistics
$stats = $scraper->getStats();
print_r($stats);
```

## Response Structure

### Search Response
```php
[
    [
        'title' => 'Result Title',
        'url'   => 'https://example.com/page'
    ],
    // ...
]
```

### Fetch Response (GET/POST)
```php
[
    'content'       => 'HTML content or response body',
    'raw_content'   => 'Raw/uncompressed content',
    'http_code'     => 200,
    'content_type'  => 'text/html; charset=UTF-8',
    'total_time'    => 1.234,
    'effective_url' => 'https://final-url.example.com/page',
    'headers'       => ['Set-Cookie: ...'],
    // Available when redirects/corrections occurred:
    'original_url'   => 'https://original-url.example.com',
    'discovered_url' => 'https://discovered-url.example.com',
    'redirects_followed' => 2
]
```

### Error Response
```php
[
    'error' => 'Error description'
]
```

## Disclaimer

Scraping search results from modern search engines is a challenging task due to JavaScript protection, WAFs, CAPTCHAs, and bot detection mechanisms. This library fakes User-Agents and supports IP rotation through proxies to bypass limitations, fetching data from raw HTML.

As I personally support and respect duckduckgo project, I should point out that:

**Please do not use this project to run heavy machine queries or scrape massive amounts of data that puts any pressure on DuckDuckGo servers.** If you have specific needs, follow their API guidelines.

## License

This project is licensed under the GPL3 License. See the [LICENSE](LICENSE) file for details.

## Contribution

Contributions are welcome! Feel free to submit issues, propose enhancements, and create pull requests.
