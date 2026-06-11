<?php

class DuckDuckGo 
{
    private $proxyConfig = null;
    private $useProxy = false;
    
    /**
     * Generate a massive list of real user agents from different browsers, OS, and devices
     * @return array
     */
    private function getUserAgents()
    {
        $userAgents = [];
        
        // === CHROME (Windows, Mac, Linux) ===
        $chromeVersions = ['120', '119', '118', '117', '116', '115', '114', '113', '112', '111', '110', '109'];
        $windowsVersions = ['10.0', '11.0'];
        $macVersions = ['10_15_7', '11_0_0', '12_0_0', '13_0_0', '14_0_0'];
        $linuxVersions = ['X11; Linux x86_64', 'X11; Ubuntu; Linux x86_64'];
        
        // Windows Chrome
        foreach ($chromeVersions as $version)
        {
            foreach ($windowsVersions as $winVer)
            {
                $userAgents[] = [
                    'ua' => "Mozilla/5.0 (Windows NT $winVer; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version}.0.0.0 Safari/537.36",
                    'brand' => '"Google Chrome";v="' . $version . '", "Not?A_Brand";v="99"',
                    'mobile' => '?0',
                    'platform' => 'Windows'
                ];
            }
        }
        
        // Mac Chrome
        foreach ($chromeVersions as $version)
        {
            foreach ($macVersions as $macVer)
            {
                $userAgents[] = [
                    'ua' => "Mozilla/5.0 (Macintosh; Intel Mac OS X $macVer) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version}.0.0.0 Safari/537.36",
                    'brand' => '"Google Chrome";v="' . $version . '", "Not?A_Brand";v="99"',
                    'mobile' => '?0',
                    'platform' => 'macOS'
                ];
            }
        }
        
        // Linux Chrome
        foreach ($chromeVersions as $version)
        {
            foreach ($linuxVersions as $linuxVer)
            {
                $userAgents[] = [
                    'ua' => "Mozilla/5.0 ($linuxVer) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version}.0.0.0 Safari/537.36",
                    'brand' => '"Google Chrome";v="' . $version . '", "Not?A_Brand";v="99"',
                    'mobile' => '?0',
                    'platform' => 'Linux'
                ];
            }
        }
        
        // === FIREFOX ===
        $firefoxVersions = ['121', '120', '119', '118', '117', '116', '115'];
        
        foreach ($firefoxVersions as $version)
        {
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:{$version}.0) Gecko/20100101 Firefox/{$version}.0",
                'brand' => '"Firefox";v="' . $version . '"',
                'mobile' => '?0',
                'platform' => 'Windows'
            ];
            
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:{$version}.0) Gecko/20100101 Firefox/{$version}.0",
                'brand' => '"Firefox";v="' . $version . '"',
                'mobile' => '?0',
                'platform' => 'macOS'
            ];
            
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (X11; Linux x86_64; rv:{$version}.0) Gecko/20100101 Firefox/{$version}.0",
                'brand' => '"Firefox";v="' . $version . '"',
                'mobile' => '?0',
                'platform' => 'Linux'
            ];
        }
        
        // === SAFARI ===
        $safariVersions = ['17.1', '17.0', '16.6', '16.5', '16.4'];
        $webkitVersions = ['605.1.15', '605.1.14'];
        
        foreach ($safariVersions as $safariVer)
        {
            foreach ($webkitVersions as $webkitVer)
            {
                $userAgents[] = [
                    'ua' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/$webkitVer (KHTML, like Gecko) Version/$safariVer Safari/$webkitVer",
                    'brand' => '"Safari";v="' . $safariVer . '"',
                    'mobile' => '?0',
                    'platform' => 'macOS'
                ];
            }
        }
        
        // === EDGE ===
        $edgeVersions = ['120', '119', '118'];
        
        foreach ($edgeVersions as $version)
        {
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version}.0.0.0 Safari/537.36 Edg/{$version}.0.0.0",
                'brand' => '"Microsoft Edge";v="' . $version . '", "Not?A_Brand";v="99"',
                'mobile' => '?0',
                'platform' => 'Windows'
            ];
            
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{$version}.0.0.0 Safari/537.36 Edg/{$version}.0.0.0",
                'brand' => '"Microsoft Edge";v="' . $version . '", "Not?A_Brand";v="99"',
                'mobile' => '?0',
                'platform' => 'macOS'
            ];
        }
        
        // === MOBILE (iOS) ===
        $iOSVersions = ['17_1', '17_0', '16_6', '16_5'];
        
        foreach ($iOSVersions as $iosVer)
        {
            $versionNum = str_replace('_', '.', $iosVer);
            
            $userAgents[] = [
                'ua' => "Mozilla/5.0 (iPhone; CPU iPhone OS $iosVer like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/$versionNum Mobile/15E148 Safari/604.1",
                'brand' => '"Mobile Safari";v="' . $versionNum . '"',
                'mobile' => '?1',
                'platform' => 'iOS'
            ];
        }
        
        // === MOBILE (Android) ===
        $androidVersions = ['14', '13', '12'];
        $androidModels = ['SM-S911B', 'Pixel 7 Pro', 'OnePlus 11'];
        
        foreach ($androidVersions as $androidVer)
        {
            foreach ($androidModels as $model)
            {
                $userAgents[] = [
                    'ua' => "Mozilla/5.0 (Linux; Android $androidVer; $model) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36",
                    'brand' => '"Google Chrome";v="120", "Not?A_Brand";v="99"',
                    'mobile' => '?1',
                    'platform' => 'Android'
                ];
            }
        }
        
        return $userAgents;
    }
    
    /**
     * Get a random user agent with all associated headers
     * @return array Returns associative array with 'ua', 'brand', 'mobile', 'platform'
     */
    private function getRandomUserAgentData()
    {
        $userAgents = $this->getUserAgents();
        return $userAgents[array_rand($userAgents)];
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
        $userAgentData = $this->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/";
        
        $postData = http_build_query([
            'q' => $query,
            's' => '0',
            'o' => 'json',
            'kl' => 'us-en',
            'df' => ''
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
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
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
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-User: ?1',
            'Cache-Control: max-age=0',
        ];
        
        // Add Sec-Ch-Ua headers ONLY for Chromium-based browsers
        // (Firefox doesn't send these headers)
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
            unlink($cookieFile);
            return ['error' => 'CURL Error: ' . $error];
        }
        
        curl_close($ch);
        
        if (file_exists($cookieFile))
        {
            unlink($cookieFile);
        }
        
        if ($httpCode !== 200 || empty($searchResult))
        {
            return ['error' => "HTTP $httpCode, Empty response"];
        }
        
        // Check if blocked or no results
        if (strpos($searchResult, 'no-results') !== false && strpos($searchResult, 'No results found') !== false)
        {
            return $this->searchUsingDuckduckgoGET($query);
        }
        
        // Parse results
        $results = $this->parseDuckDuckGoResults($searchResult);
        
        // If no results found with POST, try GET
        if (empty($results))
        {
            $results = $this->searchUsingDuckduckgoGET($query);
        }
        
        return $results;
    }
    
    private function searchUsingDuckduckgoGET($query)
    {
        $userAgentData = $this->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/?q=" . urlencode($query) . "&s=0&kl=us-en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = [
            'User-Agent: ' . $userAgentData['ua'],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
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
        curl_close($ch);
        
        if (empty($searchResult))
        {
            return [];
        }
        
        return $this->parseDuckDuckGoResults($searchResult);
    }
    
    private function parseDuckDuckGoResults($html)
    {
        $results = [];
        
        $patterns = [
            '/<div class="[^"]*result[^"]*"[^>]*>.*?<a[^>]+class="[^"]*result__a[^"]*"[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>/is',
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
                    
                    // Validate URL
                    if (filter_var($url, FILTER_VALIDATE_URL) && !empty($title))
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
            $offset = $page * 30; // DuckDuckGo shows ~30 results per page
            $results = $this->searchWithOffset($query, $offset);
            
            if (isset($results['error']))
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
        $userAgentData = $this->getRandomUserAgentData();
        
        $searchUrl = "https://html.duckduckgo.com/html/?q=" . urlencode($query) . "&s=" . $offset . "&kl=us-en";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgentData['ua']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
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
        curl_close($ch);
        
        if (empty($result))
        {
            return [];
        }
        
        return $this->parseDuckDuckGoResults($result);
    }
}

?>