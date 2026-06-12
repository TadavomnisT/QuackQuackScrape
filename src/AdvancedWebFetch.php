<?php

class AdvancedWebFetch 
{
    private $timeout = 30;
    private $maxRedirects = 10;
    private $userAgent;
    private $proxyConfig = null;
    private $useProxy = false;
    private $cookieFile = null;
    private $headers = [];
    private $followLocation = true;
    private $sslVerify = false;
    private $retryAttempts = 3;
    private $retryDelay = 1;
    private $useRandomDelay = false;
    private $minDelay = 1;
    private $maxDelay = 3;
    private $keepCookies = false;
    private $sessionCookies = [];
    private $lastUrl = '';
    private $enableSmartDiscovery = true;
    private $discoveryDepth = 2;
    private $followMetaRefresh = true;
    private $followJsRedirects = true;
    private $normalizeUrls = true;
    private $tryCommonVariations = true;
    private $respectRobotsTxt = false;
    private $maxUrlCorrections = 5;
    
    public function __construct($proxyConfig = null, $useProxy = false)
    {
        $this->userAgent = new UserAgent();
        $this->proxyConfig = $proxyConfig;
        $this->useProxy = $useProxy;
        
        if ($this->keepCookies === false)
        {
            $this->cookieFile = tempnam(sys_get_temp_dir(), 'cookie_' . md5(uniqid() . rand(1, 9999)));
        }
    }
    
    /**
     * Enable or disable smart URL discovery
     */
    public function setSmartDiscovery($enabled, $depth = 2)
    {
        $this->enableSmartDiscovery = $enabled;
        $this->discoveryDepth = $depth;
        return $this;
    }
    
    /**
     * Enable or disable following meta refresh redirects
     */
    public function setFollowMetaRefresh($enabled)
    {
        $this->followMetaRefresh = $enabled;
        return $this;
    }
    
    /**
     * Enable or disable following JavaScript redirects
     */
    public function setFollowJsRedirects($enabled)
    {
        $this->followJsRedirects = $enabled;
        return $this;
    }
    
    /**
     * Enable or disable trying common URL variations
     */
    public function setTryCommonVariations($enabled)
    {
        $this->tryCommonVariations = $enabled;
        return $this;
    }
    
    /**
     * Normalize URL for better compatibility
     */
    private function normalizeUrl($url)
    {
        if ($this->normalizeUrls === false)
        {
            return $url;
        }
        
        // Decode URL first
        $url = urldecode($url);
        
        // Remove fragments (#) as they're client-side only
        $url = preg_replace('/#.*$/', '', $url);
        
        // Fix multiple slashes in path
        $parsed = parse_url($url);
        if (isset($parsed['path']))
        {
            $parsed['path'] = preg_replace('/\/+/', '/', $parsed['path']);
            
            // Remove trailing slash from file extensions
            if (preg_match('/\.[a-z]+$/', $parsed['path']))
            {
                $parsed['path'] = rtrim($parsed['path'], '/');
            }
        }
        
        // Rebuild URL
        $url = $this->buildUrlFromParts($parsed);
        
        // Encode spaces and special characters
        $url = str_replace(' ', '%20', $url);
        $url = str_replace('|', '%7C', $url);
        $url = str_replace('^', '%5E', $url);
        $url = str_replace('`', '%60', $url);
        
        return $url;
    }
    
    /**
     * Build URL from parsed parts
     */
    private function buildUrlFromParts($parts)
    {
        if (!isset($parts['scheme']) || !isset($parts['host']))
        {
            return '';
        }
        
        $url = $parts['scheme'] . '://';
        
        if (isset($parts['user']))
        {
            $url .= $parts['user'];
            if (isset($parts['pass']))
            {
                $url .= ':' . $parts['pass'];
            }
            $url .= '@';
        }
        
        $url .= $parts['host'];
        
        if (isset($parts['port']))
        {
            $url .= ':' . $parts['port'];
        }
        
        if (isset($parts['path']))
        {
            $url .= $parts['path'];
        }
        
        if (isset($parts['query']))
        {
            $url .= '?' . $parts['query'];
        }
        
        return $url;
    }
    
    /**
     * Generate common URL variations for discovery
     */
    private function generateUrlVariations($url)
    {
        $variations = [];
        $parsed = parse_url($url);
        
        if (!isset($parsed['host']))
        {
            return $variations;
        }
        
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];
        $path = $parsed['path'] ?? '';
        
        // Original URL
        $variations[] = $url;
        
        // Switch between http and https
        if ($scheme === 'https')
        {
            $variations[] = 'http://' . $host . $path;
        }
        else
        {
            $variations[] = 'https://' . $host . $path;
        }
        
        // Add/remove trailing slash
        if (substr($path, -1) === '/')
        {
            $variations[] = $scheme . '://' . $host . rtrim($path, '/');
        }
        else
        {
            $variations[] = $scheme . '://' . $host . $path . '/';
        }
        
        // Try with www and without www
        if (strpos($host, 'www.') === 0)
        {
            $variations[] = $scheme . '://' . substr($host, 4) . $path;
        }
        else
        {
            $variations[] = $scheme . '://www.' . $host . $path;
        }
        
        // Try common index files
        $indexFiles = ['index.html', 'index.htm', 'index.php', 'default.html', 'home.html'];
        foreach ($indexFiles as $index)
        {
            if (substr($path, -1) === '/')
            {
                $variations[] = $scheme . '://' . $host . $path . $index;
            }
            else
            {
                $variations[] = $scheme . '://' . $host . rtrim($path, '/') . '/' . $index;
            }
        }
        
        // Remove duplicate variations
        $variations = array_unique($variations);
        
        return $variations;
    }
    
    /**
     * Intelligent URL discovery with multiple strategies
     */
    private function discoverUrl($url, $depth = 0)
    {
        if ($depth >= $this->discoveryDepth)
        {
            return $url;
        }
        
        // First, try the original URL
        $result = $this->executeBasicRequest($url);
        
        if ($result['success'] && $result['http_code'] === 200)
        {
            return $result['effective_url'] ?? $url;
        }
        
        // Handle redirects
        if ($result['http_code'] >= 300 && $result['http_code'] < 400 && isset($result['location']))
        {
            $redirectUrl = $this->resolveRelativeUrl($url, $result['location']);
            return $this->discoverUrl($redirectUrl, $depth + 1);
        }
        
        // Try common URL variations
        if ($this->tryCommonVariations)
        {
            $variations = $this->generateUrlVariations($url);
            
            foreach ($variations as $variation)
            {
                if ($variation === $url)
                {
                    continue;
                }
                
                $result = $this->executeBasicRequest($variation);
                
                if ($result['success'] && $result['http_code'] === 200)
                {
                    return $result['effective_url'] ?? $variation;
                }
                
                // Follow redirects from variation
                if ($result['http_code'] >= 300 && $result['http_code'] < 400 && isset($result['location']))
                {
                    $redirectUrl = $this->resolveRelativeUrl($variation, $result['location']);
                    $discovered = $this->discoverUrl($redirectUrl, $depth + 1);
                    if ($discovered !== $redirectUrl)
                    {
                        return $discovered;
                    }
                }
            }
        }
        
        // Try to find the page through sitemap
        $sitemapUrl = $this->findInSitemap($url);
        if ($sitemapUrl)
        {
            return $sitemapUrl;
        }
        
        // Try to find through search (if URL is specific enough)
        $searchResult = $this->findThroughSearch($url);
        if ($searchResult)
        {
            return $searchResult;
        }
        
        return $url;
    }
    
    /**
     * Execute basic request without retries for discovery
     */
    private function executeBasicRequest($url)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't auto-follow for discovery
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request for discovery
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $userAgentData = $this->userAgent->getRandomUserAgentData();
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgentData['ua']);
        
        $this->configureProxy($ch);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
        // Extract Location header for redirects
        $location = null;
        if (preg_match('/Location:\s*([^\r\n]+)/i', $response, $matches))
        {
            $location = trim($matches[1]);
        }
        
        curl_close($ch);
        
        return [
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'effective_url' => $effectiveUrl,
            'location' => $location
        ];
    }
    
    /**
     * Resolve relative URL
     */
    private function resolveRelativeUrl($baseUrl, $relativeUrl)
    {
        if (filter_var($relativeUrl, FILTER_VALIDATE_URL))
        {
            return $relativeUrl;
        }
        
        $baseParsed = parse_url($baseUrl);
        $basePath = $baseParsed['path'] ?? '';
        
        if (strpos($relativeUrl, '/') === 0)
        {
            // Absolute path
            return $baseParsed['scheme'] . '://' . $baseParsed['host'] . $relativeUrl;
        }
        
        // Relative path
        $baseDir = dirname($basePath);
        if ($baseDir === '/')
        {
            $baseDir = '';
        }
        
        return $baseParsed['scheme'] . '://' . $baseParsed['host'] . $baseDir . '/' . $relativeUrl;
    }
    
    /**
     * Try to find URL in sitemap
     */
    private function findInSitemap($url)
    {
        $parsed = parse_url($url);
        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
        
        $sitemapUrls = [
            $baseUrl . '/sitemap.xml',
            $baseUrl . '/sitemap_index.xml',
            $baseUrl . '/sitemap/sitemap.xml',
            $baseUrl . '/sitemap/sitemap_index.xml',
            $baseUrl . '/sitemap.php',
            $baseUrl . '/sitemap/sitemap.php'
        ];
        
        $pathLower = strtolower($parsed['path'] ?? '');
        $targetPath = $parsed['path'] ?? '';
        
        foreach ($sitemapUrls as $sitemapUrl)
        {
            $result = $this->executeBasicRequest($sitemapUrl);
            
            if ($result['success'])
            {
                // Fetch and parse sitemap
                $content = $this->get($sitemapUrl);
                if (!isset($content['error']) && !empty($content['content']))
                {
                    // Look for the URL in sitemap
                    if (preg_match('/<loc>([^<]+' . preg_quote($targetPath, '/') . '[^<]*)<\/loc>/i', $content['content'], $matches))
                    {
                        return $matches[1];
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Try to find URL through search
     */
    private function findThroughSearch($url)
    {
        $parsed = parse_url($url);
        $host = $parsed['host'];
        $path = $parsed['path'] ?? '';
        
        // Extract keywords from path
        $keywords = str_replace(['/', '-', '_'], ' ', $path);
        $keywords = trim($keywords);
        
        if (strlen($keywords) < 10)
        {
            return null;
        }
        
        // Try DuckDuckGo search (if available)
        $searchUrl = "https://api.duckduckgo.com/?q=" . urlencode('site:' . $host . ' ' . $keywords) . "&format=json&no_html=1&skip_disambig=1";
        
        $result = $this->executeBasicRequest($searchUrl);
        
        if ($result['success'])
        {
            $content = $this->get($searchUrl);
            if (!isset($content['error']))
            {
                $data = json_decode($content['content'], true);
                if (isset($data['Results'][0]['FirstURL']))
                {
                    return $data['Results'][0]['FirstURL'];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract meta refresh redirect
     */
    private function extractMetaRefresh($html)
    {
        if (preg_match('/<meta\s+http-equiv=["\']refresh["\']\s+content=["\']\d+;\s*url=([^"\']+)["\']/i', $html, $matches))
        {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Extract JavaScript redirects
     */
    private function extractJsRedirect($html)
    {
        $patterns = [
            '/window\.location\.(?:href|replace)\s*=\s*["\']([^"\']+)["\']/i',
            '/window\.location\.assign\s*\(\s*["\']([^"\']+)["\']\s*\)/i',
            '/location\.(?:href|replace)\s*=\s*["\']([^"\']+)["\']/i',
            '/self\.location\s*=\s*["\']([^"\']+)["\']/i',
            '/top\.location\s*=\s*["\']([^"\']+)["\']/i'
        ];
        
        foreach ($patterns as $pattern)
        {
            if (preg_match($pattern, $html, $matches))
            {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Extract canonical URL
     */
    private function extractCanonicalUrl($html)
    {
        if (preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\']([^"\']+)["\']/i', $html, $matches))
        {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Set timeout for requests
     */
    public function setTimeout($seconds)
    {
        $this->timeout = intval($seconds);
        return $this;
    }
    
    /**
     * Set maximum number of redirects to follow
     */
    public function setMaxRedirects($count)
    {
        $this->maxRedirects = intval($count);
        return $this;
    }
    
    /**
     * Set custom headers
     */
    public function setHeaders($headers)
    {
        if (is_array($headers))
        {
            $this->headers = array_merge($this->headers, $headers);
        }
        return $this;
    }
    
    /**
     * Set proxy configuration
     */
    public function setProxy($proxyUrl, $useProxy = true)
    {
        $this->proxyConfig = $proxyUrl;
        $this->useProxy = $useProxy;
        return $this;
    }
    
    /**
     * Enable or disable SSL verification
     */
    public function setSSLVerification($enabled)
    {
        $this->sslVerify = $enabled;
        return $this;
    }
    
    /**
     * Set retry attempts and delay
     */
    public function setRetryAttempts($attempts, $delaySeconds = 1)
    {
        $this->retryAttempts = intval($attempts);
        $this->retryDelay = intval($delaySeconds);
        return $this;
    }
    
    /**
     * Enable random delay between requests
     */
    public function enableRandomDelay($minSeconds = 1, $maxSeconds = 3)
    {
        $this->useRandomDelay = true;
        $this->minDelay = $minSeconds;
        $this->maxDelay = $maxSeconds;
        return $this;
    }
    
    /**
     * Disable random delay
     */
    public function disableRandomDelay()
    {
        $this->useRandomDelay = false;
        return $this;
    }
    
    /**
     * Enable cookie persistence across requests
     */
    public function enableCookiePersistence()
    {
        $this->keepCookies = true;
        return $this;
    }
    
    /**
     * Disable cookie persistence
     */
    public function disableCookiePersistence()
    {
        $this->keepCookies = false;
        if ($this->cookieFile && file_exists($this->cookieFile))
        {
            unlink($this->cookieFile);
        }
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cookie_' . md5(uniqid() . rand(1, 9999)));
        return $this;
    }
    
    /**
     * Configure proxy for cURL
     */
    private function configureProxy($ch)
    {
        if ($this->useProxy === false || empty($this->proxyConfig))
        {
            return;
        }
        
        $proxyUrl = $this->proxyConfig;
        
        if (preg_match('/^(socks[45a]?):\/\//', $proxyUrl))
        {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXY, preg_replace('/^socks[45a]?:\/\//', '', $proxyUrl));
        }
        else
        {
            curl_setopt($ch, CURLOPT_PROXY, preg_replace('/^https?:\/\//', '', $proxyUrl));
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }
        
        if (preg_match('/\/\/(.+?):(.+?)@/', $proxyUrl, $matches))
        {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $matches[1] . ':' . $matches[2]);
        }
        
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    }
    
    /**
     * Build comprehensive headers for request
     */
    private function buildHeaders($url, $customHeaders = [])
    {
        $userAgentData = $this->userAgent->getRandomUserAgentData();
        
        $acceptLanguages = [
            'en-US,en;q=0.9', 
            'en-GB,en;q=0.8', 
            'en;q=0.9', 
            'en-US,en;q=0.9,fr;q=0.8', 
            'en-US,en;q=0.9,es;q=0.8',
            'en-US,en;q=0.9,de;q=0.8',
            'en-US,en;q=0.9,ja;q=0.8'
        ];
        
        $accepts = [
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ];
        
        $headers = [
            'User-Agent: ' . $userAgentData['ua'],
            'Accept: ' . $accepts[array_rand($accepts)],
            'Accept-Language: ' . $acceptLanguages[array_rand($acceptLanguages)],
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0',
            'DNT: 1',
            'Sec-Fetch-Dest: ' . (strpos($url, 'api') !== false ? 'empty' : 'document'),
            'Sec-Fetch-Mode: ' . (strpos($url, 'api') !== false ? 'cors' : 'navigate'),
            'Sec-Fetch-Site: cross-site',
            'Pragma: no-cache',
        ];
        
        // Add Chromium-specific headers for Chrome/Edge
        if (strpos($userAgentData['ua'], 'Chrome') !== false || strpos($userAgentData['ua'], 'Edg') !== false)
        {
            $headers[] = 'Sec-Ch-Ua: ' . $userAgentData['brand'];
            $headers[] = 'Sec-Ch-Ua-Mobile: ' . $userAgentData['mobile'];
            $headers[] = 'Sec-Ch-Ua-Platform: "' . $userAgentData['platform'] . '"';
        }
        
        // Add referer if not already set
        $hasReferer = false;
        foreach ($customHeaders as $header)
        {
            if (stripos($header, 'Referer:') !== false)
            {
                $hasReferer = true;
                break;
            }
        }
        
        if (!$hasReferer)
        {
            $parsedUrl = parse_url($url);
            if (isset($parsedUrl['scheme']) && isset($parsedUrl['host']))
            {
                $headers[] = 'Referer: ' . $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/';
            }
        }
        
        // Merge with custom headers
        $headers = array_merge($headers, $customHeaders);
        
        // Merge with instance headers
        $headers = array_merge($headers, $this->headers);
        
        return $headers;
    }
    
    /**
     * Apply random delay if enabled
     */
    private function applyRandomDelay()
    {
        if ($this->useRandomDelay)
        {
            $delay = rand($this->minDelay, $this->maxDelay);
            usleep($delay * 1000000);
        }
    }
    
    /**
     * Fetch URL content with intelligent discovery
     */
    public function get($url, $customHeaders = [])
    {
        $this->applyRandomDelay();
        
        // Normalize URL first
        $url = $this->normalizeUrl($url);
        
        // Smart URL discovery
        if ($this->enableSmartDiscovery)
        {
            $discoveredUrl = $this->discoverUrl($url);
            if ($discoveredUrl !== $url)
            {
                $url = $discoveredUrl;
            }
        }
        
        $this->lastUrl = $url;
        
        $attempt = 0;
        $lastError = null;
        $redirectCount = 0;
        $currentUrl = $url;
        
        while ($redirectCount <= $this->maxRedirects)
        {
            $attempt = 0;
            
            while ($attempt < $this->retryAttempts)
            {
                if ($attempt > 0)
                {
                    sleep($this->retryDelay);
                }
                
                $result = $this->executeRequest($currentUrl, 'GET', null, $customHeaders);
                
                if (isset($result['error']) === false)
                {
                    // Check for meta refresh redirect
                    if ($this->followMetaRefresh && isset($result['content']))
                    {
                        $metaRefresh = $this->extractMetaRefresh($result['content']);
                        if ($metaRefresh)
                        {
                            $redirectCount++;
                            $currentUrl = $this->resolveRelativeUrl($currentUrl, $metaRefresh);
                            $result = null;
                            break;
                        }
                    }
                    
                    // Check for JavaScript redirect
                    if ($this->followJsRedirects && isset($result['content']))
                    {
                        $jsRedirect = $this->extractJsRedirect($result['content']);
                        if ($jsRedirect)
                        {
                            $redirectCount++;
                            $currentUrl = $this->resolveRelativeUrl($currentUrl, $jsRedirect);
                            $result = null;
                            break;
                        }
                    }
                    
                    // Check for canonical URL
                    if (isset($result['content']))
                    {
                        $canonical = $this->extractCanonicalUrl($result['content']);
                        if ($canonical && $canonical !== $currentUrl)
                        {
                            $currentUrl = $this->resolveRelativeUrl($currentUrl, $canonical);
                            $result = null;
                            break;
                        }
                    }
                    
                    // Add discovered URL to result
                    if ($currentUrl !== $url)
                    {
                        $result['original_url'] = $url;
                        $result['discovered_url'] = $currentUrl;
                        $result['redirects_followed'] = $redirectCount;
                    }
                    
                    return $result;
                }
                
                $lastError = $result['error'];
                $attempt++;
            }
            
            if ($result !== null)
            {
                return $result;
            }
            
            $redirectCount++;
        }
        
        return ['error' => 'Too many redirects or corrections after ' . $redirectCount . ' attempts'];
    }
    
    /**
     * Fetch URL content with POST method
     */
    public function post($url, $postData, $customHeaders = [], $isJson = false)
    {
        $this->applyRandomDelay();
        
        $url = $this->normalizeUrl($url);
        $this->lastUrl = $url;
        
        if ($isJson && is_array($postData))
        {
            $postData = json_encode($postData);
            $customHeaders[] = 'Content-Type: application/json';
        }
        elseif (is_array($postData))
        {
            $postData = http_build_query($postData);
            $customHeaders[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $this->retryAttempts)
        {
            if ($attempt > 0)
            {
                sleep($this->retryDelay);
            }
            
            $result = $this->executeRequest($url, 'POST', $postData, $customHeaders);
            
            if (isset($result['error']) === false)
            {
                return $result;
            }
            
            $lastError = $result['error'];
            $attempt++;
        }
        
        return ['error' => 'Failed after ' . $this->retryAttempts . ' attempts: ' . $lastError];
    }
    
    /**
     * Execute cURL request
     */
    private function executeRequest($url, $method = 'GET', $postData = null, $customHeaders = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerify ? 2 : 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'curlHeaderCallback']);
        
        // Set method specific options
        if ($method === 'POST')
        {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData !== null)
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
        }
        elseif ($method === 'HEAD')
        {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        
        // Set headers
        $headers = $this->buildHeaders($url, $customHeaders);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Configure proxy
        $this->configureProxy($ch);
        
        // Execute request
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
        if (curl_error($ch))
        {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => 'CURL Error: ' . $error];
        }
        
        curl_close($ch);
        
        // Handle different HTTP status codes
        if ($httpCode === 403)
        {
            return $this->handleForbidden($url);
        }
        
        if ($httpCode === 429)
        {
            return ['error' => 'Rate limited (429)', 'retry_after' => $this->getRetryAfter($content)];
        }
        
        if ($httpCode === 404)
        {
            return ['error' => 'Page not found (404)'];
        }
        
        if ($httpCode >= 500 && $httpCode < 600)
        {
            return ['error' => 'Server error (HTTP ' . $httpCode . ')'];
        }
        
        if ($httpCode !== 200 && $httpCode !== 201 && $httpCode !== 202 && $httpCode !== 204)
        {
            return ['error' => 'HTTP ' . $httpCode];
        }
        
        // Process content based on encoding
        $decodedContent = $this->decodeContent($content, $contentType);
        
        return [
            'content' => $decodedContent,
            'raw_content' => $content,
            'http_code' => $httpCode,
            'content_type' => $contentType,
            'total_time' => $totalTime,
            'effective_url' => $effectiveUrl,
            'headers' => $this->sessionCookies
        ];
    }
    
    /**
     * Handle 403 Forbidden responses with alternative strategies
     */
    private function handleForbidden($url)
    {
        // Try with different user agent patterns
        $userAgentsToTry = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; Bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0'
        ];
        
        foreach ($userAgentsToTry as $userAgent)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            
            $headers = [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive'
            ];
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $this->configureProxy($ch);
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && !empty($content))
            {
                return [
                    'content' => $content,
                    'raw_content' => $content,
                    'http_code' => 200,
                    'content_type' => 'text/html',
                    'bypassed' => true,
                    'used_user_agent' => $userAgent
                ];
            }
        }
        
        return ['error' => 'Access forbidden (403) - all bypass attempts failed'];
    }
    
    /**
     * Get retry-after value from response
     */
    private function getRetryAfter($content)
    {
        if (preg_match('/Retry-After:\s*(\d+)/i', $content, $matches))
        {
            return intval($matches[1]);
        }
        return 60;
    }
    
    /**
     * Decode compressed content
     */
    private function decodeContent($content, $contentType)
    {
        if (empty($content))
        {
            return '';
        }
        
        // Check if content is gzipped
        if (substr($content, 0, 2) === "\x1f\x8b")
        {
            $decoded = @gzdecode($content);
            if ($decoded !== false)
            {
                return $decoded;
            }
        }
        
        // Check if content is brotli compressed
        if (strpos($contentType, 'br') !== false || substr($content, 0, 3) === "\xce\xb2\xcf")
        {
            if (function_exists('brotli_uncompress'))
            {
                $decoded = @brotli_uncompress($content);
                if ($decoded !== false)
                {
                    return $decoded;
                }
            }
        }
        
        return $content;
    }
    
    /**
     * Callback for processing response headers
     */
    private function curlHeaderCallback($ch, $header)
    {
        if (strpos($header, 'Set-Cookie:') === 0)
        {
            $this->sessionCookies[] = trim($header);
        }
        return strlen($header);
    }
    
    /**
     * Fetch with JavaScript rendering simulation
     */
    public function getWithJsSimulation($url, $customHeaders = [])
    {
        $result = $this->get($url, $customHeaders);
        
        if (isset($result['error']))
        {
            return $result;
        }
        
        $content = $result['content'];
        
        // Simulate common JavaScript redirects
        $jsRedirect = $this->extractJsRedirect($content);
        if ($jsRedirect)
        {
            return $this->get($this->resolveRelativeUrl($url, $jsRedirect), $customHeaders);
        }
        
        // Simulate meta refresh redirects
        $metaRefresh = $this->extractMetaRefresh($content);
        if ($metaRefresh)
        {
            return $this->get($this->resolveRelativeUrl($url, $metaRefresh), $customHeaders);
        }
        
        // Extract dynamically loaded content patterns
        if (preg_match_all('/<script[^>]*>([^<]+)<\/script>/is', $content, $matches))
        {
            foreach ($matches[1] as $script)
            {
                // Look for API calls in JavaScript
                if (preg_match_all('/(?:fetch|\.get|\.post)\s*\(\s*["\']([^"\']+)["\']/', $script, $apiMatches))
                {
                    foreach ($apiMatches[1] as $apiUrl)
                    {
                        if (filter_var($apiUrl, FILTER_VALIDATE_URL))
                        {
                            $apiResult = $this->get($apiUrl);
                            if (isset($apiResult['content']))
                            {
                                $result['content'] .= "\n<!-- Dynamically loaded content from: $apiUrl -->\n" . $apiResult['content'];
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get all headers from a URL
     */
    public function getHeaders($url)
    {
        $url = $this->normalizeUrl($url);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $this->configureProxy($ch);
        
        $response = curl_exec($ch);
        $headers = [];
        
        if (!curl_error($ch))
        {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerText = substr($response, 0, $headerSize);
            $headers = $this->parseHeaderText($headerText);
            $headers['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        
        curl_close($ch);
        return $headers;
    }
    
    /**
     * Parse header text into associative array
     */
    private function parseHeaderText($headerText)
    {
        $headers = [];
        $lines = explode("\n", $headerText);
        
        foreach ($lines as $line)
        {
            $line = trim($line);
            if (strpos($line, ':') !== false)
            {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
    
    /**
     * Download file to local path
     */
    public function downloadFile($url, $destinationPath)
    {
        $result = $this->get($url);
        
        if (isset($result['error']))
        {
            return ['error' => $result['error']];
        }
        
        if (file_put_contents($destinationPath, $result['content']))
        {
            return [
                'success' => true,
                'path' => $destinationPath,
                'size' => strlen($result['content'])
            ];
        }
        
        return ['error' => 'Failed to write file'];
    }
    
    /**
     * Clean up temporary files
     */
    public function __destruct()
    {
        if (!$this->keepCookies && $this->cookieFile && file_exists($this->cookieFile))
        {
            unlink($this->cookieFile);
        }
    }
}