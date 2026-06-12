<?php

class UserAgent 
{
    
    /**
     * Generate a massive list of real user agents from different browsers, OS, and devices
     * @return array
     */
    public function getUserAgents()
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
    public function getRandomUserAgentData()
    {
        $userAgents = $this->getUserAgents();
        return $userAgents[array_rand($userAgents)];
    }

}

?>