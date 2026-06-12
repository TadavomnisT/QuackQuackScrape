<?php

require_once "UserAgent.php";

class DuckDuckGo 
{

    private $proxyConfig;
    private $useProxy;
    private $userAgent;
    
    public function __construct($proxyConfig = null, $useProxy = false) {
        $this->proxyConfig = $proxyConfig;
        $this->useProxy = $useProxy;
        $this->userAgent = new UserAgent(); // FIXED: Was "USerAgent"
    }
    
    /**
     * Configure proxy settings for requests
     * 
     * HTTP Proxy examples:
     *   $ddg->setProxy('http://proxy.example.com:8080');
     *   $ddg->setProxy('http://username:password@proxy.example.com:8080');
     * 
     * SOCKS5 Proxy examples:
     *   $ddg->setProxy('socks5://proxy.example.com:1080');
     *   $ddg->setProxy('socks5://username:password@proxy.example.com:1080');
     * 
     * @param string $proxyUrl Proxy URL with protocol
     * @param bool $useProxy Whether to actually use the proxy
     * @return $this
     */
    public function setProxy($proxyUrl, $useProxy = true)
    {
        $this->proxyConfig = $proxyUrl;
        $this->useProxy = $useProxy;
        return $this;
    }
    
    /**
     * Disable proxy usage
     * @return $this
     */
    public function disableProxy()
    {
        $this->useProxy = false;
        return $this;
    }
    
    /**
     * Enable proxy usage
     * @return $this
     */
    public function enableProxy()
    {
        $this->useProxy = !empty($this->proxyConfig);
        return $this;
    }
    
    /**
     * Configure cURL with proxy settings
     * @param resource $ch cURL handle
     */
    private function configureProxy($ch)
    {
        if (!$this->useProxy || empty($this->proxyConfig))
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
    
    public function searchUsingDuckduckgo($query, $useRandomDelay = true)
    {
        if ($useRandomDelay)
        {
            $delay = rand(3, 10);
            sleep($delay);
        }
        
        // Get consistent user agent data
        $userAgentData = $this->userAgent->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/";
        
        $postData = http_build_query([
            'q' => $query,
            's' => '0',
            'kl' => 'us-en'
        ]);
        
        $cookieFile = tempnam(sys_get_temp_dir(), 'cookie_' . md5(uniqid() . rand(1, 9999)));
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        
        // Random headers
        $acceptLanguages = ['en-US,en;q=0.9', 'en-GB,en;q=0.8', 'en;q=0.9', 'en-US,en;q=0.9,fr;q=0.8', 'en-US,en;q=0.9,es;q=0.8'];
        $accepts = [
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8'
        ];
        
        // Build headers that are CONSISTENT with the user agent
        $headers = [
            'User-Agent: ' . $userAgentData['ua'],
            'Accept: ' . $accepts[array_rand($accepts)],
            'Accept-Language: ' . $acceptLanguages[array_rand($acceptLanguages)],
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: application/x-www-form-urlencoded',
            'Origin: https://html.duckduckgo.com',
            'Referer: https://html.duckduckgo.com/',
            'DNT: 1',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0',
        ];
        
        // Add Sec-Ch-Ua headers ONLY for Chromium-based browsers
        if (strpos($userAgentData['ua'], 'Chrome') !== false || strpos($userAgentData['ua'], 'Edg') !== false)
        {
            $headers[] = 'Sec-Ch-Ua: ' . $userAgentData['brand'];
            $headers[] = 'Sec-Ch-Ua-Mobile: ' . $userAgentData['mobile'];
            $headers[] = 'Sec-Ch-Ua-Platform: "' . $userAgentData['platform'] . '"';
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $this->configureProxy($ch);
        
        $searchResult = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch))
        {
            $error = curl_error($ch);
            curl_close($ch);
            if (file_exists($cookieFile)) unlink($cookieFile);
            return ['error' => 'CURL Error: ' . $error];
        }
        
        curl_close($ch);
        
        if (file_exists($cookieFile))
        {
            unlink($cookieFile);
        }
        
        // DuckDuckGo sometimes returns 202 or 303, handle gracefully
        if ($httpCode === 202 || $httpCode === 303 || $httpCode === 200)
        {
            if (empty($searchResult))
            {
                // Try GET method as fallback
                return $this->searchUsingDuckduckgoGET($query);
            }
            
            // Parse results
            $results = $this->parseDuckDuckGoResults($searchResult);
            
            // If no results found, try GET
            if (empty($results))
            {
                $results = $this->searchUsingDuckduckgoGET($query);
            }
            
            return $results;
        }
        
        if ($httpCode !== 200 || empty($searchResult))
        {
            // Try GET method as fallback
            return $this->searchUsingDuckduckgoGET($query);
        }
        
        // Parse results
        $results = $this->parseDuckDuckGoResults($searchResult);
        
        // If no results found, try GET
        if (empty($results))
        {
            $results = $this->searchUsingDuckduckgoGET($query);
        }
        
        return $results;
    }
    
    private function searchUsingDuckduckgoGET($query)
    {
        $userAgentData = $this->userAgent->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/?q=" . urlencode($query) . "&s=0&kl=us-en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = [
            'User-Agent: ' . $userAgentData['ua'],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ];
        
        // Add Sec-Ch-Ua headers only for Chromium-based browsers
        if (strpos($userAgentData['ua'], 'Chrome') !== false || strpos($userAgentData['ua'], 'Edg') !== false)
        {
            $headers[] = 'Sec-Ch-Ua: ' . $userAgentData['brand'];
            $headers[] = 'Sec-Ch-Ua-Mobile: ' . $userAgentData['mobile'];
            $headers[] = 'Sec-Ch-Ua-Platform: "' . $userAgentData['platform'] . '"';
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $this->configureProxy($ch);
        
        $searchResult = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch))
        {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => 'CURL Error: ' . $error];
        }
        
        curl_close($ch);
        
        if (empty($searchResult) || $httpCode !== 200)
        {
            return [];
        }
        
        return $this->parseDuckDuckGoResults($searchResult);
    }
    
    private function parseDuckDuckGoResults($html)
    {
        $results = [];
        
        $patterns = [
            '/<a[^>]+class="result__a"[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/i',
            '/<a[^>]+href="(https?:\/\/[^"]+)"[^>]+class="[^"]*result__a[^"]*"[^>]*>([^<]+)<\/a>/i',
            '/<a rel="nofollow" class="result__a" href="([^"]+)">([^<]+)<\/a>/i',
        ];
        
        foreach ($patterns as $pattern)
        {
            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER))
            {
                foreach ($matches as $match)
                {
                    $url = $match[1];
                    $title = strip_tags($match[2]);
                    
                    // Clean DuckDuckGo redirect URLs
                    if (preg_match('/uddg=([^&]+)/', $url, $urlMatch))
                    {
                        $url = urldecode($urlMatch[1]);
                    }
                    elseif (preg_match('/\/l\/\?uddg=([^&]+)/', $url, $urlMatch))
                    {
                        $url = urldecode($urlMatch[1]);
                    }
                    elseif (preg_match('/\/l\?uddg=([^&]+)/', $url, $urlMatch))
                    {
                        $url = urldecode($urlMatch[1]);
                    }
                    elseif (preg_match('/\/redirect\?uddg=([^&]+)/', $url, $urlMatch))
                    {
                        $url = urldecode($urlMatch[1]);
                    }
                    
                    // Validate URL
                    if (filter_var($url, FILTER_VALIDATE_URL) && !empty($title) && strpos($url, 'duckduckgo.com') === false)
                    {
                        $results[] = [
                            'title' => html_entity_decode(trim($title), ENT_QUOTES | ENT_HTML5),
                            'url' => $url
                        ];
                    }
                }
                
                if (!empty($results))
                {
                    break;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Search with multiple pages
     * @param string $query Search query
     * @param int $pages Number of pages to fetch
     * @return array Combined results
     */
    public function searchMultiplePages($query, $pages = 3)
    {
        $allResults = [];
        
        for ($page = 0; $page < $pages; $page++)
        {
            $offset = $page * 30;
            $results = $this->searchWithOffset($query, $offset);
            
            if (isset($results['error']) || empty($results))
            {
                break;
            }
            
            $allResults = array_merge($allResults, $results);
            
            // Delay between pages
            if ($page < $pages - 1)
            {
                sleep(rand(3, 8));
            }
        }
        
        return $allResults;
    }
    
    /**
     * Search with offset/pagination
     * @param string $query Search query
     * @param int $offset Result offset
     * @return array Results
     */
    public function searchWithOffset($query, $offset = 0)
    {
        $userAgentData = $this->userAgent->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/?q=" . urlencode($query) . "&s=" . $offset . "&kl=us-en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = [
            'User-Agent: ' . $userAgentData['ua'],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ];
        
        // Add Sec-Ch-Ua headers only for Chromium-based browsers
        if (strpos($userAgentData['ua'], 'Chrome') !== false || strpos($userAgentData['ua'], 'Edg') !== false)
        {
            $headers[] = 'Sec-Ch-Ua: ' . $userAgentData['brand'];
            $headers[] = 'Sec-Ch-Ua-Mobile: ' . $userAgentData['mobile'];
            $headers[] = 'Sec-Ch-Ua-Platform: "' . $userAgentData['platform'] . '"';
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $this->configureProxy($ch);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch))
        {
            curl_close($ch);
            return [];
        }
        
        curl_close($ch);
        
        if (empty($result) || $httpCode !== 200)
        {
            return [];
        }
        
        return $this->parseDuckDuckGoResults($result);
    }
}
?>