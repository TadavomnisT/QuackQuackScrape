# QuackQuackScrape
Simple php scraper for DuckDuckGo web search.

<img width="2246" height="1254" alt="QuackQuackScrape" src="https://github.com/user-attachments/assets/ce2f6cb9-4831-4c53-9020-8b8ccc9f8ccd" />


## Disclaimer
Scraping search results from modern search engines is a challenging task due to `js protection`, `WAFs`, `CAPTCHAs` and limiting robot behaviors. This class fakes `User-Agent` or switches `IP` by using proxies (optional) to bypass limitations and fetches data from raw html. As I personally support and respect DuckDuckGo Project, I should make this clear that: 

**Please do not use this project to run heavy machine queries or scrape massive data that puts any pressure on ddg servers. If you have specific needs, you can follow their API guidlines.**

## Installation
1. Clone the repository:

   ```bash
   git clone https://github.com/TadavomnisT/QuackQuackScrape.git
   ```

2. Change to the project directory:

   ```bash
   cd QuackQuackScrape
   ```

3. run examples:

   ```bash
   php tests.php
   ```

## Usage

Example usage:

```php
<?php


// ============ USAGE EXAMPLES FOR DUCKDUCKGO CLASS ============

require_once "DuckDuckGo.php";

// Example 1: Basic usage without proxy
$ddg = new DuckDuckGo;
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);

// Example 2: Using HTTP proxy
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 3: Using HTTP proxy with authentication
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://username:password@proxy.example.com:8080')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 4: Using SOCKS5 proxy (Tor)
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('socks5://127.0.0.1:9050')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 5: Search multiple pages
/*
$ddg = new DuckDuckGo;
$results = $ddg->searchMultiplePages("quantum computing", 3);
var_dump($results);
*/

// Example 6: Search with offset
/*
$ddg = new DuckDuckGo;
$results = $ddg->searchWithOffset("php programming", 30);
var_dump($results);
*/

// Example 7: Temporarily disable proxy
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
$results1 = $ddg->searchUsingDuckduckgo("test query"); // uses proxy
$ddg->disableProxy();
$results2 = $ddg->searchUsingDuckduckgo("another query"); // no proxy
var_dump($results2);
*/

?>

```

Result:

```shell
$ php tests.php 
array(10) {
  [0]=>
  array(2) {
    ["title"]=>
    string(20) "#cutefemboy | TikTok"
    ["url"]=>
    string(37) "https://www.tiktok.com/tag/cutefemboy"
  }
  [1]=>
  array(2) {
    ["title"]=>
    string(29) "Cute Femboy Videos - Snapchat"
    ["url"]=>
    string(42) "https://www.snapchat.com/topic/cute-femboy"
  }
  [2]=>
  array(2) {
    ["title"]=>
    string(21) "CutestFemboy - Reddit"
    ["url"]=>
    string(38) "https://www.reddit.com/r/CutestFemboy/"
  }
  [3]=>
  array(2) {
    ["title"]=>
    string(45) "Femboy | Pictures and Videos | Scrolller NSFW"
    ["url"]=>
    string(31) "https://scrolller.com/c/feminin"
  }
  [4]=>
  array(2) {
    ["title"]=>
    string(49) "The 10 Most Adorable Cute Femboys on Social Media"
    ["url"]=>
    string(79) "https://www.roanyer.com/blog/the-10-most-adorable-cute-femboys-on-social-media/"
  }
  [5]=>
  array(2) {
    ["title"]=>
    string(46) "the cutest femboy tiktok compilation - YouTube"
    ["url"]=>
    string(43) "https://www.youtube.com/watch?v=DS9wcvy2YQM"
  }
  [6]=>
  array(2) {
    ["title"]=>
    string(38) "young, cute, original / Femboy - pixiv"
    ["url"]=>
    string(43) "https://www.pixiv.net/en/artworks/108364562"
  }
  [7]=>
  array(2) {
    ["title"]=>
    string(62) "Natural long hair crossdressers, femboys, transgender - Flickr"
    ["url"]=>
    string(72) "https://www.flickr.com/photos/167318454@N05/galleries/72157715410607091/"
  }
  [8]=>
  array(2) {
    ["title"]=>
    string(22) "Cute femboy - Facebook"
    ["url"]=>
    string(38) "https://www.facebook.com/cutefemboy01/"
  }
  [9]=>
  array(2) {
    ["title"]=>
    string(40) "Femboy TikToks Compilation #75 - YouTube"
    ["url"]=>
    string(43) "https://www.youtube.com/watch?v=_EhhkTk-2kM"
  }
}
```

## Contribution

Contributions are welcome! Feel free to submit issues, propose enhancements, and create pull requests. 


## License
This project is licensed under the GPL3 License. See the [LICENSE](LICENSE) file for details.
