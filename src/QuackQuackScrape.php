<?php
// QuackQuckScrape.php

require_once "UserAgent.php";
require_once "DuckDuckGo.php";
require_once "AdvancedWebFetch.php";

class QuackQuckScrape 
{
    private $duckDuckGo;
    private $webFetch;
    private $userAgent;
    private $initialized = false;
    private $defaultProxy = null;
    private $useProxyDefault = false;
    
    /**
     * Constructor - initializes all classes
     * 
     * @param string|null $proxyConfig Optional proxy configuration
     * @param bool $useProxy Whether to use proxy by default
     */
    public function __construct($proxyConfig = null, $useProxy = false)
    {
        $this->defaultProxy = $proxyConfig;
        $this->useProxyDefault = $useProxy;
        $this->initialize();
    }
    
    /**
     * Initialize all required classes
     */
    private function initialize()
    {
        if ($this->initialized === false)
        {
            $this->userAgent = new UserAgent();
            $this->duckDuckGo = new DuckDuckGo($this->defaultProxy, $this->useProxyDefault);
            $this->webFetch = new AdvancedWebFetch($this->defaultProxy, $this->useProxyDefault);
            $this->initialized = true;
        }
    }
    
    /**
     * Get random user agent data
     * 
     * @return array User agent data with 'ua', 'brand', 'mobile', 'platform'
     */
    public function getRandomUserAgent()
    {
        $this->initialize();
        return $this->userAgent->getRandomUserAgentData();
    }
    
    /**
     * Get all user agents
     * 
     * @return array List of all user agents
     */
    public function getAllUserAgents()
    {
        $this->initialize();
        return $this->userAgent->getUserAgents();
    }
    
    /**
     * Search using DuckDuckGo
     * 
     * @param string $query Search query
     * @param bool $useRandomDelay Whether to use random delay
     * @return array Search results
     */
    public function search($query, $useRandomDelay = true)
    {
        $this->initialize();
        return $this->duckDuckGo->searchUsingDuckduckgo($query, $useRandomDelay);
    }
    
    /**
     * Search multiple pages using DuckDuckGo
     * 
     * @param string $query Search query
     * @param int $pages Number of pages to fetch
     * @return array Combined results
     */
    public function searchMultiplePages($query, $pages = 3)
    {
        $this->initialize();
        return $this->duckDuckGo->searchMultiplePages($query, $pages);
    }
    
    /**
     * Search with offset/pagination
     * 
     * @param string $query Search query
     * @param int $offset Result offset
     * @return array Results
     */
    public function searchWithOffset($query, $offset = 0)
    {
        $this->initialize();
        return $this->duckDuckGo->searchWithOffset($query, $offset);
    }
    
    /**
     * Fetch a web page using GET method
     * 
     * @param string $url URL to fetch
     * @param array $customHeaders Custom headers to send
     * @return array Response with content, http_code, etc.
     */
    public function fetch($url, $customHeaders = [])
    {
        $this->initialize();
        return $this->webFetch->get($url, $customHeaders);
    }
    
    /**
     * Fetch a web page using POST method
     * 
     * @param string $url URL to fetch
     * @param mixed $postData Data to POST (array or string)
     * @param array $customHeaders Custom headers
     * @param bool $isJson Whether data is JSON
     * @return array Response
     */
    public function post($url, $postData, $customHeaders = [], $isJson = false)
    {
        $this->initialize();
        return $this->webFetch->post($url, $postData, $customHeaders, $isJson);
    }
    
    /**
     * Fetch with JavaScript simulation
     * 
     * @param string $url URL to fetch
     * @param array $customHeaders Custom headers
     * @return array Response with content
     */
    public function fetchWithJs($url, $customHeaders = [])
    {
        $this->initialize();
        return $this->webFetch->getWithJsSimulation($url, $customHeaders);
    }
    
    /**
     * Get only headers from a URL
     * 
     * @param string $url URL to check
     * @return array Headers
     */
    public function getHeaders($url)
    {
        $this->initialize();
        return $this->webFetch->getHeaders($url);
    }
    
    /**
     * Download a file
     * 
     * @param string $url File URL
     * @param string $destinationPath Local path to save
     * @return array Result with success/path/size or error
     */
    public function download($url, $destinationPath)
    {
        $this->initialize();
        return $this->webFetch->downloadFile($url, $destinationPath);
    }
    
    /**
     * Configure proxy for all classes
     * 
     * @param string $proxyUrl Proxy URL
     * @param bool $useProxy Whether to use proxy
     * @return $this
     */
    public function setProxy($proxyUrl, $useProxy = true)
    {
        $this->initialize();
        $this->defaultProxy = $proxyUrl;
        $this->useProxyDefault = $useProxy;
        
        if ($this->duckDuckGo !== null)
        {
            $this->duckDuckGo->setProxy($proxyUrl, $useProxy);
        }
        
        if ($this->webFetch !== null)
        {
            $this->webFetch->setProxy($proxyUrl, $useProxy);
        }
        
        return $this;
    }
    
    /**
     * Disable proxy for all classes
     * 
     * @return $this
     */
    public function disableProxy()
    {
        $this->initialize();
        $this->useProxyDefault = false;
        
        if ($this->duckDuckGo !== null)
        {
            $this->duckDuckGo->disableProxy();
        }
        
        if ($this->webFetch !== null)
        {
            $this->webFetch->setProxy($this->defaultProxy, false);
        }
        
        return $this;
    }
    
    /**
     * Enable proxy for all classes
     * 
     * @return $this
     */
    public function enableProxy()
    {
        $this->initialize();
        $this->useProxyDefault = true;
        
        if ($this->duckDuckGo !== null)
        {
            $this->duckDuckGo->enableProxy();
        }
        
        if ($this->webFetch !== null)
        {
            $this->webFetch->setProxy($this->defaultProxy, true);
        }
        
        return $this;
    }
    
    /**
     * Set timeout for web requests
     * 
     * @param int $seconds Timeout in seconds
     * @return $this
     */
    public function setTimeout($seconds)
    {
        $this->initialize();
        $this->webFetch->setTimeout($seconds);
        return $this;
    }
    
    /**
     * Set maximum number of redirects to follow
     * 
     * @param int $count Maximum redirects
     * @return $this
     */
    public function setMaxRedirects($count)
    {
        $this->initialize();
        $this->webFetch->setMaxRedirects($count);
        return $this;
    }
    
    /**
     * Set retry attempts for failed requests
     * 
     * @param int $attempts Number of retry attempts
     * @param int $delaySeconds Delay between retries
     * @return $this
     */
    public function setRetryAttempts($attempts, $delaySeconds = 1)
    {
        $this->initialize();
        $this->webFetch->setRetryAttempts($attempts, $delaySeconds);
        return $this;
    }
    
    /**
     * Enable random delay between requests
     * 
     * @param int $minSeconds Minimum delay
     * @param int $maxSeconds Maximum delay
     * @return $this
     */
    public function enableRandomDelay($minSeconds = 1, $maxSeconds = 3)
    {
        $this->initialize();
        $this->webFetch->enableRandomDelay($minSeconds, $maxSeconds);
        return $this;
    }
    
    /**
     * Disable random delay
     * 
     * @return $this
     */
    public function disableRandomDelay()
    {
        $this->initialize();
        $this->webFetch->disableRandomDelay();
        return $this;
    }
    
    /**
     * Set custom headers for all requests
     * 
     * @param array $headers Headers to add
     * @return $this
     */
    public function setCustomHeaders($headers)
    {
        $this->initialize();
        $this->webFetch->setHeaders($headers);
        return $this;
    }
    
    /**
     * Enable SSL verification (not recommended for scraping)
     * 
     * @param bool $enabled Whether to verify SSL
     * @return $this
     */
    public function setSSLVerification($enabled)
    {
        $this->initialize();
        $this->webFetch->setSSLVerification($enabled);
        return $this;
    }
    
    /**
     * Enable cookie persistence across requests
     * 
     * @return $this
     */
    public function enableCookies()
    {
        $this->initialize();
        $this->webFetch->enableCookiePersistence();
        return $this;
    }
    
    /**
     * Disable cookie persistence
     * 
     * @return $this
     */
    public function disableCookies()
    {
        $this->initialize();
        $this->webFetch->disableCookiePersistence();
        return $this;
    }
    
    /**
     * Enable or disable smart URL discovery
     * 
     * @param bool $enabled Enable smart discovery
     * @param int $depth Discovery depth
     * @return $this
     */
    public function setSmartDiscovery($enabled, $depth = 2)
    {
        $this->initialize();
        $this->webFetch->setSmartDiscovery($enabled, $depth);
        return $this;
    }
    
    /**
     * Enable or disable following meta refresh redirects
     * 
     * @param bool $enabled Follow meta refresh
     * @return $this
     */
    public function setFollowMetaRefresh($enabled)
    {
        $this->initialize();
        $this->webFetch->setFollowMetaRefresh($enabled);
        return $this;
    }
    
    /**
     * Enable or disable following JavaScript redirects
     * 
     * @param bool $enabled Follow JS redirects
     * @return $this
     */
    public function setFollowJsRedirects($enabled)
    {
        $this->initialize();
        $this->webFetch->setFollowJsRedirects($enabled);
        return $this;
    }
    
    /**
     * Enable or disable trying common URL variations
     * 
     * @param bool $enabled Try common variations
     * @return $this
     */
    public function setTryCommonVariations($enabled)
    {
        $this->initialize();
        $this->webFetch->setTryCommonVariations($enabled);
        return $this;
    }
    
    /**
     * Enable or disable URL normalization
     * 
     * @param bool $enabled Normalize URLs
     * @return $this
     */
    public function setUrlNormalization($enabled)
    {
        $this->initialize();
        $this->webFetch->setUrlNormalization($enabled);
        return $this;
    }
    
    /**
     * Perform multiple operations in sequence
     * 
     * @param array $operations Array of operations to perform
     * @return array Results of all operations
     */
    public function batch($operations)
    {
        $results = [];
        
        if (is_array($operations))
        {
            foreach ($operations as $op)
            {
                if (isset($op['type']))
                {
                    switch ($op['type'])
                    {
                        case 'search':
                            $results[] = $this->search($op['query'], $op['delay'] ?? true);
                            break;
                        
                        case 'fetch':
                            $results[] = $this->fetch($op['url'], $op['headers'] ?? []);
                            break;
                        
                        case 'post':
                            $results[] = $this->post($op['url'], $op['data'] ?? [], $op['headers'] ?? [], $op['isJson'] ?? false);
                            break;
                        
                        case 'headers':
                            $results[] = $this->getHeaders($op['url']);
                            break;
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Check if a URL is accessible
     * 
     * @param string $url URL to check
     * @return bool True if accessible (HTTP 200)
     */
    public function isAccessible($url)
    {
        $this->initialize();
        $headers = $this->webFetch->getHeaders($url);
        
        if (isset($headers['error']))
        {
            return false;
        }
        
        // Try to get status code from headers
        foreach ($headers as $key => $value)
        {
            if (strpos($key, 'HTTP') !== false && strpos($value, '200') !== false)
            {
                return true;
            }
        }
        
        // Fallback to actual fetch
        $result = $this->webFetch->get($url);
        return !isset($result['error']) && ($result['http_code'] ?? 0) === 200;
    }
    
    /**
     * Extract all links from a fetched page
     * 
     * @param string $url URL to fetch and parse
     * @return array List of links
     */
    public function extractLinks($url)
    {
        $result = $this->fetch($url);
        
        if (isset($result['error']))
        {
            return ['error' => $result['error']];
        }
        
        $links = [];
        $content = $result['content'];
        
        if (preg_match_all('/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/i', $content, $matches))
        {
            foreach ($matches[1] as $link)
            {
                if (filter_var($link, FILTER_VALIDATE_URL))
                {
                    $links[] = $link;
                }
                elseif (strpos($link, '/') === 0)
                {
                    // Parse relative URL
                    $parsed = parse_url($url);
                    $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
                    $links[] = $baseUrl . $link;
                }
            }
        }
        
        return array_unique($links);
    }
    
    /**
     * Get statistics about the scraper
     * 
     * @return array Statistics
     */
    public function getStats()
    {
        return [
            'user_agents_count' => count($this->getAllUserAgents()),
            'proxy_enabled' => $this->useProxyDefault,
            'proxy_config' => $this->defaultProxy,
            'classes_initialized' => $this->initialized
        ];
    }
}

// Usage example:
/*
// Initialize the main scraper
$scraper = new QuackQuckScrape();

// Enable smart discovery features
$scraper->setSmartDiscovery(true, 3)
        ->setFollowMetaRefresh(true)
        ->setFollowJsRedirects(true)
        ->setTryCommonVariations(true)
        ->setRetryAttempts(3, 2);

// Search DuckDuckGo
$results = $scraper->search('quantum physics');
print_r($results);

// Fetch a web page
$page = $scraper->fetch('https://en.wikipedia.org/wiki/Quantum');
if (!isset($page['error'])) {
    echo "Success! Page length: " . strlen($page['content']) . " bytes\n";
    echo "Effective URL: " . ($page['effective_url'] ?? 'N/A') . "\n";
}

// Fetch with JavaScript simulation
$jsPage = $scraper->fetchWithJs('https://example.com/dynamic-page');

// Download a file
$scraper->download('https://example.com/file.pdf', '/path/to/save.pdf');

// Extract all links from a page
$links = $scraper->extractLinks('https://example.com');

// Batch operations
$batchResults = $scraper->batch([
    ['type' => 'search', 'query' => 'artificial intelligence'],
    ['type' => 'fetch', 'url' => 'https://example.com'],
    ['type' => 'headers', 'url' => 'https://example.com']
]);

// Configure proxy
$scraper->setProxy('http://proxy.example.com:8080')->enableProxy();

// Set custom settings
$scraper->setTimeout(45)
        ->setRetryAttempts(5, 2)
        ->enableRandomDelay(2, 5);
*/
?>