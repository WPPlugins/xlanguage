<?php
/*
Plugin Name: xLanguage
Plugin URI: http://hellosam.net/project/xlanguage
Description: Allows you to blog in different language, and allows user to select which version to read.  Please see the <a href="options-general.php?page=xlanguage.php">Options Page</a> to configure this plugin.
Author: Sam Wong, Huizhe Xiao
Version: 2.0.3
Author URI: http://hellosam.net/

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General 
Public License as published by the Free Software Foundation, either version 2 of the License, or (at your 
option) any later version.

This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage. See the GNU General Public License for
more details.

For full license details see license.txt
============================================================================================================ */

include(dirname(__FILE__).'/plugin.php');
include(dirname(__FILE__).'/parser.php');
include(dirname(__FILE__).'/widget.php');
include(dirname(__FILE__).'/template.php');

/**
 * xLanguagePlugin class
 *
 * @package xLanguage
 * @author Sam Wong
 * @copyright Copyright (C) 2008 Sam Wong
 **/

define('xLanguageTagQuery', 'lang');
define('xLanguageTagPermalink', 'lang');
define('xLanguageTagPermalinkMode', 'xLpm');
define('xLanguageTagCookie', 'xLanguage_' . $cookiehash);

// Controlling the structure version
define('xLanguageOptionsStructVersion', 20000); 
// Controlling the Javascript version (to avoid caching)
define('xLanguageJavascriptVersion', 5);

// Constant Declaration
define('xLanguageRedirectNever', 0);
define('xLanguageRedirectAuto', 1);
define('xLanguageRedirectAlways', 2);

define('xLanguagePrefDetectionDefault', 255);
define('xLanguagePrefDetectionLink', 1);
define('xLanguagePrefDetectionCookie', 2);
define('xLanguagePrefDetectionBrowser', 4);

define('xLanguageLangAvailabilityUser', 1);
define('xLanguageLangAvailabilityBot', 2);

define('xLanguageParserXHtml', 1);
define('xLanguageParserSB', 2);
define('xLanguageParserLogSizeLimit', 1024 * 128);

define('xLanguagePermalinkPostfix', 1);
define('xLanguagePermalinkPrefix', 2);

define('xLanguagePostMetaAvailableLanguageListSpliter', ',');

define('xLanguageQueryMetadataKey', 'xLanguage_Available');

class xLanguagePlugin extends xLanguagePluginBase
{
    /**
    
    Options Structure -

    options[
        contribution => false, // Does user donate? :)
        default     => 'zh', // Kept for compatibility issue: default = default2[1]
        default2    => [ 1 => 'zh', 2 => 'zh' ], // Array Key = Mode (User, Bot)

        split => '|',   // The splitter used in single line mode
        redirect => 1,  // 0 = never, 1 = auto (if WP-Cache enabled, as if wp_cache_meta_object defined), 2 = always
        pref_detection => -1,           // Language Preference Detection: -1 = Default (All On, or just Link if WP-SuperCache is on), Bitwise: 1 = Link (Permalink/Get), 2 = Cookie, 4 = Browser
        feedback => [
            enable => false,            // Feedback enabled?
            expose => false,            // Expose your Wordpress Blog URL?
            last => epoch,              // Last feedback time
            last_status =>              // Last feedback result
            next => epoch               // Planned next feedback contact
        ],
        query = > array(                // Query option, whether and how xLanguage effect the post query
            enable => false,            // Global switch
            enable_for = > array(       // Fine-tuning switches to turn off specific filtering
                feed => true,               // Feed List
                search => true,             // Serach Result
                post => true,               // Post List (Any other non search, non feed list such as By Date, By Category, etc)
                page => true,               // Page - such as Widget's page list, Theme's page list
            )
        ),
        parser => [     // Parser option
            mode => array(1, 2),            // 1 = XHTML, 2 = Square Bracket
            default => 1,                   // Default mode: affecting the admin Edit interface
            option_sb_prefix => 'lang_',    // Prefix string used in Square Bracket mode
            log => '',                      // Version 12: Empty = Disable, Otherwise is a path relative to ABSPATH
            log => '',                      // Version 13: Empty = Disable, Otherwise is a path from wp_upload_dir(), or could also be a broken path
            log2 => '',                     // Version 14+: Empty = Disable, Otherwise is a path from wp_upload_dir()
        ],
        language => [
            'en-us' => [ // The key should be the same as the 'code'
                code => 'en-us',        // Locale code. Used for tagging the Data/HTML/post an such. 
                                        // Language preference matching is also done with this
                name => 'English|英語',     // The name of this language. Used when the language name is shown to user
                timef => '...',             // Time format
                datef => '...',             // Date format
                pos => 0,                   // Position of the text element in single line mode
                shownas => 'en-us',         // The code appear to user. All lang tag of that is replace with lang tag of this.
                availability => 1,          // Bitwise: 1 = User, 2 = Bot
                show => array('en-us')      // An array of locale code that if this language is the preferred one, all data tagged with this will be shown in multiline mode.
                availas => array('en-us'),  // When determining what's available in a data, data tagged with this lang with reported as available under lang "availas".
                    Normally 'show' and 'availas' is a symmetric mapping.
                    i.e.        Show  Availas
                        Lang A  A,B   A
                        Lang B  B     A,B
                        
                theme => '',            // If present, use another theme
                // The fallback language and corresponding missing string shown
                fallback => array('en' => '', 'zh' => '...', '_missing' => '...') 
            ],
            'zh' =>    [ code => 'zh', 'name' => 'Chinese|中文', ... ],
            ....
        ],

        // As of 2.0.0, only PrefixMode is supported
        permalink_mode => xLanguagePermalink*, // Where should the /lang/xx placed in permalink
        permalink_support => set of (xLanguagePermalink*), // The mode that supported
        permalink_redirect => true, // If user reach the page with non primary mode, use 301 to redirect

        hook => [
            textlink => [...],
            text => [...],
            link => [...],
            ...
        ],
        hookpriority => [
            textlink => 18,
            text => 18,
            ...
        ],        
        structversion => xLanguageOptionsStructVersion
    ]
    */

    /**
     * Constructor hook the plugins_loaded event, to defer the initilization by then
     *
     * @return void
     **/
    function xLanguagePlugin()
    {
        $this->register_plugin('xlanguage', __FILE__);
        $this->widget_list_langs = new xLanguageWidgetListLangs('xLanguage List');
        $this->add_action('plugins_loaded');

        // And hooks to modify the permalinks. Do it early as activate will call this.
        $this->add_filter('rewrite_rules_array');
        
        $this->options = get_option('xlanguage_options');
        if (is_admin())
        {
            include_once(dirname(__FILE__).'/admin.php');
            $this->admin = new xLanguagePluginAdmin($this->options);
            $this->register_activation(__FILE__);
        }
    }

    /**
     * Performs first-time activation
     *
     * @return void
     **/
    function activate()
    {
        if (!empty($this->admin))
        {
            $this->admin->activate();
        }
    }
    

    /**
     * Constructor instantiates the plugin and registers all the filters and actions
     *
     * @return void
     **/
    function plugins_loaded()
    {
        // Note: This will be involved twice under K2 Theme, so removing myself
        remove_action('plugins_loaded', array(&$this, 'plugins_loaded'));

        if (!empty($this->options) && 
            (!isset($this->options['structversion']) || (int) $this->options['structversion'] < xLanguageOptionsStructVersion)) {
            $this->options_upgrade();
        }
        
        $this->add_filter('mce_plugins');
        $this->add_filter('mce_external_plugins');
        $this->add_filter('mce_buttons');
        $this->add_filter('mce_css');
        
        if (is_admin())
        {
            $this->admin->plugins_loaded();
        } else
        {
            $this->add_action('parse_request');
            $this->add_filter('query_vars');
        
            $this->add_action('get_pages', 'get_pages_in_current_lang', 10, 2);
            $this->add_filter('posts_join', 'filter_posts_join');
            $this->add_filter('posts_where', 'filter_posts_where');
        
            if (is_array($this->options['language'])) {
                if (is_array($this->options['hook'])) {
                    foreach ($this->options['hook'] as $hook => $filters) {
                        if (is_array($filters)) {
                            foreach ($filters as $filter) {
                                $this->add_filter($filter, "filter_$hook", $this->options['hookpriority'][$hook]);
                            }
                        }
                    }
                }
            }
        }
        
        // Determine the language in this session
        $this->useragent = $this->determine_agent();
        $this->language = $this->find_preferred_language();
        if ($this->language !== FALSE)
        {
            setcookie( xLanguageTagCookie, $this->language, time() + 30000000, COOKIEPATH );
            $this->add_filter('template');
            $this->add_filter('stylesheet','template');
        }
    }


    /**
     * Determine the current agent (user or bot).  More biased to user mode.
     * The value is saved to the usermode property.
     *
     * @return void
     */
    function determine_agent() {
        if ($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
            return xLanguageLangAvailabilityUser;
        }

        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        if (!empty($useragent)) {
            /* If this WP has WassUp installed */
            if (function_exists('wGetSpider')) {
                return wGetSpider($useragent) != null ? xLanguageLangAvailabilityBot : xLanguageLangAvailabilityUser;
            }
            
            /**
                Data/Code Borrowed from

                Plugin Name: WassUp
                Plugin URI: http://www.wpwp.org
                Version: 1.4.4
                Author: Michele Marcucci
            */        
    $lines = array("Wordpress Pingback/Trackback|Wordpress|", "Alexa|ia_archiver|","Ask.com/Teoma|AskJeeves/Teoma)|","Biz360|Biz360|","Naver|NaverBot|","Naver|Cowbot|","cURL|curl/|","Google|Googlebot/|",
            "Google|googlebot/|","Google Images|Googlebot-Image|","LookSmart|grub-client|","Yahoo!|slurp@inktomi|","Yahoo!|Yahoo!Slurp|",
            "Yahoo!|MMCrawler|","MSN|msnbot|","AltaVista|Scooter-|","Wget|Wget/1.|","Technorati|Technoratibot|","Moreover|Moreoverbot|",
            "Yahoo FeedSeeker|YahooFeedSeeker|","Java|Java/|","relevantNOISE|www.relevantnoise.com|","Voila|VoilaBot|","IRLIRLbot/|","Snapbot|Snap|",
            "ichiro|ichiro|", "shelob|shelob|", "Google AdSense|Mediapartners-Google|", "Seekbot|HTTPFetcher|", "CommentTracker|CommentTracker|", 
            "Bloglines|bloglines|",	"Italian Blog Rankings|blogbabel", "Ask Jeeves/Teoma|ask|", "Akregator|Akregator|", "NewsGatorOnline|NewsGatorOnline|", "Rojo|rojo|", "FeedTools|feedtools|",
            "Feedfetcher-Google|Feedfetcher-google|", "AideRSS|AideRSS|", "psbot|psbot|", "Ruby|Rfeedfinder|", "everyfeed-spider|everyfeed-spider", "Attentio/Nutch|Attentio", 
            "Megite2.0|Megite.com|", "radianrss|RadianRSS|", "Sitemap Generator|Sitemap Generator|", "Feedshow|Feedshow|", "ABCdatos|ABCdatos|", "Acme.Spider|Due|", "Ahoy!|Ahoy!|", 
            "Alkaline|AlkalineBOT|", "appie|Appie|", "Arachnophilia|Arachnophilia|", "Araneo|Araneo/0.7|", "AraybOt|AraybOt/1.0|", "ArchitextSpider|ArchitextSpider|", 
            "ARIADNE|Due|", "ASpider|ASpider/0.09|", "Atomz.com|Atomz/1.0|", "AURESYS|AURESYS/1.0|", "BackRub|BackRub/*.*|", "BBot|bbot/0.100|", "Big|Big|", "Bjaaland|Bjaaland/0.5|", 
            "BlackWidow|BlackWidow|", "Die|Die|", "Borg-Bot|borg-bot/0.9|", "BoxSeaBot|BoxSeaBot/0.5|", "BSpider|BSpider/1.0|", "CACTVS|CACTVS|", "Calif|Calif/0.6|", 
            "Digimarc|Digimarc|", "Checkbot|Checkbot/x.xx|", "cIeNcIaFiCcIoN.nEt|cIeNcIaFiCcIoN.nEt|", "CMC/0.01|CMC/0.01|", "Combine|combine/0.0|", "ConfuzzledBot|Confuzzledbot/X.X|", 
            "CoolBot|CoolBot|", "Web|root/0.1|", "XYLEME|cosmos/0.3|", "Cusco|Cusco/3.2|", "CyberSpyder|CyberSpyder/2.1|", "CydralSpider|CydralSpider/X.X|", "Desert|DesertRealm.com;|", 
            "DeWeb(c)|Deweb/1.01|", "DienstSpider|dienstspider/1.0|", "Digger|Digger/1.0|", "Digital|DIIbot|", "Direct|grabber|", "DNAbot|DNAbot/1.0|", "DragonBot|DragonBot/1.0|", 
            "DWCP|DWCP/2.0|", "e-collector|LWP::|", "EIT|EIT-Link-Verifier-Robot/0.2|", "Emacs-w3|Emacs-w3/v[0-9\.]+|", "ananzi|EMC|", "esculapio|esculapio/1.1|", "Esther|esther|", 
            "Evliya|Evliya|", "nzexplorer|explorersearch|", "FastCrawler|FastCrawler|", "Dynamics|(compatible:|", "Felix|FelixIDE/1.0|", "Wild|Hazel's|", "FetchRover|ESIRover|", 
            "fido|fido/0.9|", "KIT-Fireball|KIT-Fireball/2.0|", "Fish|Fish-Search-Robot|", "Fouineur|Fouineur|", "Robot|Robot|", "Freecrawl|Freecrawl|", "FunnelWeb|FunnelWeb-1.0|", 
            "gammaSpider|gammaSpider|", "gazz|gazz/1.0|", "GCreep|gcreep/1.0|", "GetURL|GetURL.rexx|", "Golem|Golem/1.1|", "Googlebot|Googlebot/2.X|", "Gromit|Gromit/1.0|", 
            "Northern|Gulliver/1.1|", "Gulper|Gulper|", "Harvest|yes|", "havIndex|havIndex/X.xx[bxx]|", "HI|AITCSRobot/1.1|", "Hometown|Hometown|", "Wired|wired-digital-newsbot/1.5|", 
            "ht://Dig|htdig/3.1.0b2|", "HTMLgobble|HTMLgobble|", "Hyper-Decontextualizer|Hyper|", "iajaBot|iajaBot/0.1|", "IBM_Planetwide|IBM_Planetwide,|", 
            "Popular|gestaltIconoclast/1.0|", "Ingrid|INGRID/0.1|", "Imagelock|Imagelock|", "IncyWincy|IncyWincy/1.0b1|", "Informant|Informant|", "InfoSeek|InfoSeek|", 
            "Infoseek|Infoseek|", "InfoSpiders|InfoSpiders/0.1|", "Inspector|inspectorwww/1.0|", "IntelliAgent|'IAGENT/1.0'|", "Israeli-search|IsraeliSearch/1.0|", "JBot|JBot|", 
            "JCrawler|JCrawler/0.2|", "AskJeeves|Askjeeves|", "JoBo|JoBo|", "Jobot|Jobot/0.1alpha|", "JoeBot|JoeBot/x.x,|", "The|JubiiRobot/version#|", "JumpStation|jumpstation|", 
            "image.kapsi.net|image.kapsi.net/1.0|", "Katipo|Katipo/1.0|", "KDD-Explorer|KDD-Explorer/0.1|", "KO_Yappo_Robot|KO_Yappo_Robot/1.0.4(http://yappo.com/info/robot.html)|", 
            "LabelGrabber|LabelGrab/1.1|", "larbin|larbin|", "legs|legs|", "Link|Linkidator/0.93|", "LinkWalker|LinkWalker|", "logo.gif|logo.gif|", "Lycos|Lycos/x.x|", 
            "Magpie|Magpie/1.0|", "marvin/infoseek|marvin/infoseek|", "Mattie|M/3.8|", "MediaFox|MediaFox/x.y|", "NEC-MeshExplorer|NEC-MeshExplorer|", "MindCrawler|MindCrawler|", 
            "mnoGoSearch|UdmSearch|", "MOMspider|MOMspider/1.00|", "Monster|Monster/vX.X.X|", "Motor|Motor/0.2|", "MSNBot|MSNBOT/0.1|", "Muninn|Muninn/0.1|", 
            "Muscat|MuscatFerret/<version>|", "Mwd.Search|MwdSearch/0.1|", "Internet|User-Agent:|", "NDSpider|NDSpider/1.5|", "NetCarta|NetCarta|", "NetMechanic|NetMechanic|", 
            "NetScoop|NetScoop/1.0|", "newscan-online|newscan-online/1.1|", "NHSE|NHSEWalker/3.0|", "Nomad|Nomad-V2.x|", "ObjectsSearch|ObjectsSearch/0.01|", "Occam|Occam/1.0|", 
            "HKU|HKU|", "Openfind|Openfind|", "Orb|Orbsearch/1.0|", "Pack|PackRat/1.0|", "ParaSite|ParaSite/0.21|", "Patric|Patric/0.01a|", "PerlCrawler|PerlCrawler/1.0|", 
            "Phantom|Duppies|", "PhpDig|phpdig/x.x.x|", "PiltdownMan|PiltdownMan/1.0|", "Pimptrain.com's|Pimptrain|", "Pioneer|Pioneer|", "Portal|PortalJuice.com/4.0|", 
            "PGP|PGP-KA/1.2|", "PlumtreeWebAccessor|PlumtreeWebAccessor/0.9|", "Poppi|Poppi/1.0|", "PortalB|PortalBSpider/1.0|", "psbot|psbot/0.X|", "GetterroboPlus|straight|", 
            "Raven|Raven-v2|", "Resume|Resume|", "RoadHouse|RHCS/1.0a|", "RixBot|RixBot|", "Road|Road|", "Robbie|Robbie/0.1|", "ComputingSite|ComputingSite|", "RoboCrawl|RoboCrawl|", 
            "RoboFox|Robofox|", "Robozilla|Robozilla/1.0|", "Roverbot|Roverbot|", "RuLeS|RuLeS/1.0|", "SafetyNet|SafetyNet|", "Scooter|Scooter/2.0|", "Search.Aus-AU.COM|not|", 
            "Sleek|Sleek|", "SearchProcess|searchprocess/0.9|", "Senrigan|Senrigan/xxxxxx|", "SG-Scout|SG-Scout|", "Shai'Hulud|Shai'Hulud|", "Simmany|SimBot/1.0|", "SBIder|Site|", 
            "SiteTech-Rover|SiteTech-Rover|", "Skymob.com|aWapClient|", "Inktomi|Slurp/2.0|", "Smart|ESISmartSpider/2.0|", "Snooper|Snooper/b97_01|", "Solbot|Solbot/1.0|", 
            "Spider|Spider|", "spider_monkey|mouse.house/7.1|", "SpiderBot|SpiderBot/1.0|", "Spiderline|spiderline/3.1.3|", "SpiderView(tm)|SpiderView|", "Site|ssearcher100|", 
            "Suke|suke/*.*|", "suntek|suntek/1.0|", "Sygol|http://www.sygol.com|", "TACH|TACH|", "Tarantula|Tarantula/1.0|", "tarspider|tarspider|", "Tcl|dlw3robot/x.y|", 
            "TechBOT|TechBOT|", "Templeton|Templeton/{version}|", "TitIn|TitIn/0.2|", "TITAN|TITAN/0.1|", "UCSD|UCSD-Crawler|", "UdmSearch|UdmSearch/2.1.1|", "UptimeBot|uptimebot|", 
            "URL|urlck/1.2.3|", "URL|URL|", "Valkyrie|Valkyrie/1.0|", "Verticrawl|Verticrawlbot|", "Victoria|Victoria/1.0|", "vision-search|vision-search/3.0'|", 
            "void-bot|void-bot/0.1|", "Voyager|Voyager/0.0|", "VWbot|VWbot_K/4.2|", "The|w3index|", "W3M2|W3M2/x.xxx|", "WallPaper|CrawlPaper/n.n.n|", "the|WWWWanderer|", 
            "w@pSpider|w@pSpider/xxx|", "WebCatcher|WebCatcher/1.0|", "WebCopy|WebCopy/(version)|", "webfetcher|WebFetcher/0.8,|", "Webinator|weblayers|", "weblayers/0.0|WebLinker|", 
            "WebLinker/0.0|WebMirror|", "Digimarc|WebReaper|", "WebReaper|webs|", "webs@recruit.co.jp|Websnarf|", "WebVac|webvac/1.0|", "webwalk|webwalk|", 
            "WebWalker|WebWalker/1.10|", "WebWatch|WebWatch|", "Wget|Wget/1.4.0|", "whatUseek|whatUseek_winona/3.0|", "Weblog|wlm-1.1|", "w3mir|w3mir|", "WebStolperer|WOLP/1.0|", 
            "WWWC|WWWC/0.25|", "WebZinger|none|", "XGET|XGET/0.7|", "Nederland.zoek|Nederland.zoek|", "boitho.com|boitho.com|", "GreatNews|GreatNews|", "AlestiFeedBot|AlestiFeedBot|", 
            "SummizeFeedReader|SummizeFeedReader|", "FeedBurner|FeedBurner|", "lilina|Lilina|", "BuzzTracker|BuzzTracker|", "kinjabot|kinjabot|", "Krugle|Krugle|", 
            "libwww-perl|libwww-perl|", "Jakarta|Jakarta|", "VadixBot|VadixBot|", "Google Desktop|Google Desktop|", "BlogsNowBot|BlogsNowBot|", "Feedster|Feedster|",
            "WebCopier|WebCopier|", "MSRBOT|MSRBOT|", "Charlotte|Charlotte|", "wp-autoblogSimplePie|SimplePie|", "wikioblogs|wikioblogs|", "Wells Search II|Wells Search II|", 
            "BlogsNowBot|BlogsNowBot|", "NG/2.0|NG/2.0|", "BlogBridge|BlogBridge|", "Drupal|Drupal|", "WebGenBot|WebGenBot|", "CazoodleBot|CazoodleBot|", "MJ12bot|MJ12bot|", 
            "Feedreader|Feedreader|", "Strategic Board Bot|Strategic Board Bot|", "LiteFinder|LiteFinder|", "SurveyBot|SurveyBot|", "Teemer|Teemer|", "NetNewsWire|NetNewsWire|", 
            "disco/Nutch|disco/Nutch", "StackRambler|StackRambler|", "MagpieRSS|MagpieRSS|", "WebAlta|WebAlta|", "NewsAlloy|NewsAlloy|", 
            "WikioFeedBot|WikioFeedBot|", "Strategic Board Bot |Strategic Board Bot |", "Mail.ru|Mail.ru|", "OpiDig|OpiDig|", "webcollage|webcollage|", 
            "R6_FeedFetcher|R6_FeedFetcher|", "UniversalFeedParser|UniversalFeedParser|", "Syndic8|Syndic8|", "BlogBot|BlogBot|", "W3C_Validator|W3C_Validator|",
            "Runnk RSS aggregator|Runnk|", "RssBandit|RssBandit|");

            foreach ($lines as $spider) {
                list($nome,$key) = explode("|",$spider);
                if (@strpos(strtolower($useragent),strtolower($key)) !== FALSE) {
                    return xLanguageLangAvailabilityBot;
                }                                
            }
        }

        return xLanguageLangAvailabilityUser;
    }

    /**
     * The function that find out the current preferred language
     * This is to be called by the plugins_loaded so the language can be known by other plugins quickly.
     *
     * @return string The language code of the preferred language, or FALSE if none available.
     */
    function find_preferred_language() {
        global $super_cache_enabled, $wp;
        if (
            !is_array($this->options['language'])
        ) return FALSE;

        $mode = $this->useragent;
        $pref = (empty($super_cache_enabled)) ? $this->options['pref_detection'] : xLanguagePrefDetectionLink;

        $this->no_redirect = 1;

        if ($pref & xLanguagePrefDetectionLink) {
            // This is to be called by the plugins_loaded event, well before Wordpress URL parsing, so $wp->query_vars cannot be used
        
            // override with GET or permalink URL suffix
            if ($_GET[ xLanguageTagQuery ]) {
                $tmp = $_GET[ xLanguageTagQuery ];
                if (array_key_exists($tmp, $this->options['language']) && ($this->options['language'][$tmp]['availability'] & $mode)) {
                    return $tmp;
                }
            }

            $req_url = $_SERVER['REQUEST_URI'];
            $pos = strpos($req_url, '/' . xLanguageTagPermalink . '/');
            if ($pos === 0 || $pos > 0) {
                $pos_start = $pos + strlen( xLanguageTagPermalink ) + 2;
                $pos_end = strpos($req_url, '/', $pos_start); 
                if ($pos_end === FALSE) $pos_end = strlen($req_url);
                if ($pos_end > $pos_start + 1) {
                    $tmp = substr( $req_url, $pos_start, $pos_end - $pos_start);
                    if (array_key_exists($tmp, $this->options['language']) && ($this->options['language'][$tmp]['availability'] & $mode)) {
                        return $tmp;
                    }
                }
            }
        }
  
        if ($pref & xLanguagePrefDetectionCookie) {
            // Try to get setting from cookie 
            if ( isset($_COOKIE[xLanguageTagCookie]) ) {
                $tmp = trim($_COOKIE[xLanguageTagCookie]);
                if (array_key_exists($tmp, $this->options['language']) && ($this->options['language'][$tmp]['availability'] & $mode)) {
                    $this->no_redirect = 0;
                    return $tmp;
                }
            }    
        }

        
        if ($pref & xLanguagePrefDetectionBrowser) {
            // try browser setting
            if ( isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) {
                $langs = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
                
                // Sort by preferences
                $langp = array();
                foreach ($langs as $lang) {
                    $lang = preg_replace('[^a-zA-Z0-9;=]', '', $lang);
                    if (strpos($lang, ';q=') > 0) {
                        $p = explode(';q=', $lang, 2);
                        $langp['/^' . $p[0] . '(?:-[a-z0-9]+)*$/i'] = (float) $p[1];
                    } else if (strlen($lang) > 0) {
                        $langp['/^' . $lang . '(?:-[a-z0-9]+)*$/i'] = 1.0;
                    }
                }
                asort($langp, SORT_NUMERIC);
                $langp = array_reverse($langp);
                $ourlangs = array();
                foreach ($this->options['language'] as $k => $v) {
                    if ($v['availability'] & $mode) {
                        $ourlangs[] = $k;
                    }
                }
                
                // Test each preference
                foreach ($langp as $lang => $q) {
                    $result = preg_grep($lang, $ourlangs);
                    if (count($result) > 0) {
                        $this->no_redirect = 0;
                        return array_shift($result);
                    }
                }
            }
        }
        
        if (array_key_exists($mode, $this->options['default2']) &&
            array_key_exists($this->options['default2'][$mode], $this->options['language']))
        {
            $this->no_redirect = 0;
            return $this->options['default2'][$mode];
        }
  
        return FALSE;
    }
    

    /*
     * Change the template if we have specified one for this language
     */
    function template($t) {
        if (empty($this->language)) return $t;
        if (empty($this->options['language'][$this->language]['theme'])) return $t;
        return $this->options['language'][$this->language]['theme'];                
    }

    /*
     * Register the variables that we used in permalinks/url for parsing
     */
    function query_vars($query_vars)
    {
        $query_vars[] = xLanguageTagQuery;
        $query_vars[] = xLanguageTagPermalinkMode;
        return $query_vars;
    }
    
    /*
     * Determine if we need to redirect, and redirect if needed.
     *
     * @return void
     */
    function parse_request($wp) {
        global $cache_enabled;
        if (($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'HEAD') && empty($this->no_redirect)) {
            if ($this->options['redirect'] == xLanguageRedirectAlways || $this->options['redirect'] == xLanguageRedirectAuto && !empty($cache_enabled)) {
                $url = $this->filter_link_in_lang(
                    (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                    '', '', 0);
                header("Location: $url", true, 303);
                exit;
            }
        }
        if (!empty($wp->query_vars[xLanguageTagPermalinkMode]) && !empty($wp->query_vars[xLanguageTagQuery])
            && !empty($this->options['permalink_redirect']) && ($wp->query_vars[xLanguageTagPermalinkMode] & $this->options['permalink_mode']) == 0)
        {
            $url = $this->filter_link_in_lang(
                (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                $wp->query_vars[xLanguageTagQuery], '', 0);
            header("Location: $url", true, 301);
            exit;
        }
    }
    
    /**
     * Return the current preferred language code if defined
     *
     * @param string $language The default one determined by system
     * @return string Language code determined by us, fallback to the provided one if needed.
     */
    function filter_language($language = false) {
        return (empty($this->language)) ? $language : $this->options['language'][$this->language]['shownas'];
    }

    /**
     * Extracts the link content of the current language
     *
     * @param string $content The multi-lango content with special tag inside <a ..>..</a>
     * @return string Content in one language only
     */
    function filter_textlink($content) {
        if (empty($this->language)) return $content;
        if (!preg_match('/(<a[^>]*>)(.*?)(<\/a>)/i', $content)) {
            return $this->filter_text($content);
        }
        return preg_replace_callback('/(<a[^>]*>)(.*?)(<\/a>)/i', array(&$this, 'filter_textlink_callback'), $content);     
    }

    function filter_textlink_callback($matches) {
        return $matches[1] . $this->filter_text($matches[2]) . $matches[3];
    }

    /**
     * Filter away the tagged content so that it contains only the current language, 
     *   if not available, a language missing string will be produced.
     *
     * @param string $content The multi-lango content with special tag
     * @param string $lang Specified language, if this value set to empty, then current language will be used
     * @param boolean $test If true, the this function will only test the availability of given language in the multi-lango content
     * @return string If $test is false, content in the designated language only; otherwise, true or false will be returned
     */
    function filter_text($content, $forced_single = 0, $lang = '', $test = false) {
        if (empty($lang)) $lang  = $this->language;
        if (empty($lang) || $this->empty_html($content))
            return $test ? true: $content;
        
        $langtouse = $lang;
        $prepend = '';

        $aftercontent = $this->filter_text_real($content, $forced_single, $langtouse);
        reset($this->options['language'][$lang]['fallback']);
        $count = 0;
        while (
            $this->empty_html($aftercontent)
        ) {
            // Fallback Mode
            if ( list($langtouse, $prepend) = each(array_slice($this->options['language'][$lang]['fallback'], $count, 1)) ) {
                if ($langtouse == '_missing') {
                    break;
                }
                $aftercontent = $this->filter_text_real($content, $forced_single, $langtouse);
                $count++;
            }
        }
        if ($test)
            return $langtouse != '_missing' && !$this->empty_html($aftercontent);
        if (!empty($prepend)) {
            return $prepend . $aftercontent;
        }
        return $aftercontent; 
    }
    
    /**
     * Test the availability of given language in the multi-lango content 
     * Wrapper for filter_text test-contains mode.
     *
     * @param string $content The multi-lango content with special tag
     * @param string $lang Specified language, if this value set to empty, then current language will be used
     * @return boolean True is the multi-lango content contains given language, otherwise false
     */
    function contains_lang($content, $lang = '') {
        return $this->filter_text($content, false, $lang, true);
    }


    /**
     * Similar to filter_text, but Single Line Mode must apply even if the present of newline character
     *
     * @param string $content The multi-lango content with special tag
     * @return string Content in the designated language only
     */
    function filter_textsingle($content) {
        return $this->filter_text($content, 1);
    }


    /**
     * Filter away the tagged content so that it contains only the current language, 
     *   if not available, an empty string is returned
     *
     * @param string $content The multi-lango content with special tag
     * @return string Content in the designated language only or empty string
     */
    function filter_text_real($content, $forced_single, $lang = '') {
        if (empty($lang)) {
            if (empty($this->language)) return $content;
            $lang = $this->language;
        }
        if (empty($lang)) return $content;

        if (strpos($content, $this->options['split']) !== false && ($forced_single || strpos($content, "\n") === false)) {
            $index = $this->options['language'][$lang]['pos'];
            $parts = explode($this->options['split'], $content);
            return isset($parts[$index]) ? $parts[$index] : $content;
        } else {
            $new_content = false;
            foreach ($this->options['parser']['mode'] as $mode) {
                if (!isset($this->parser[$mode])) {
                    $this->parser[$mode] = ($mode == xLanguageParserXHtml ? new xLanguageXHtmlParser() : new xLanguageSBParser());
                }
                $new_content = $this->parser[$mode]->filter($content, $this->options['language'][$lang]['show'], $this->options);
                if ($new_content !== false) { break; }
            }
            if ($new_content !== false) { $content = $new_content; }
            
            return $content;
        }        
    }

    /**
     * See if given html content is empty
     *
     * @param string $content The single-lango content after filtered
     * @return boolean Is the content empty
     */
    function empty_html($content) {
        $useless_tag_names = "(?:h[1-7]|p|br|div|span|table|tr|td|th|thead|tbody|tfoot)";
        $useless_tags = "<\/?{$useless_tag_names}(?:\s[^>]*)?>";
        $empty_regex = "/^(?:\s*{$useless_tags}\s*){0,4}(?:<a[^>]*class=\"more-link\"[^>]*>[^<]*<\/a>)?(?:<!--more.*?-->)?(?:\s*{$useless_tags}\s*){0,4}$/s";
        return strlen(trim($content)) == 0 ||
            // Detect the <more> link, as well as empty HTML container tags (such as p,div) and treat them as nothing.
            preg_match($empty_regex, $content);
    }

    /**
     * Modify a link with preferred language specified
     *
     * @param string $url The original url, url & html encoded
     * @return string The url with preferred language specified, url & html encoded
     */
    function filter_link($url) {        
        return $this->filter_link_in_lang($url);
    }

    /**
     * Return the time format of the current preferred language
     *
     * @return string preferred time format
     */
    function filter_time_format($format) {
        return (empty($this->language)) ? $format : 
            (empty($this->options['language'][$this->language]['timef']) ? $format : $this->options['language'][$this->language]['timef']);
    }
    
    /**
     * Return the date format of the current preferred language
     *
     * @return string preferred date format
     */
    function filter_date_format($format) {
        return (empty($this->language)) ? $format : 
            (empty($this->options['language'][$this->language]['datef']) ? $format : $this->options['language'][$this->language]['datef']);
    }

    /**
     * Modify a url with preferred or specified language specified
     *
     * @param string $url The original url, url & html encoded
     * @return string The url with the preferred or specified language specified, url & html encoded
     */
    function filter_link_in_lang($url, $lang = '', $force_permalink = '', $html_encode = 1) {
        $url = $this->cleanup_url($url);
        
        if (empty($lang)) {
            if (empty($this->language)) return $url;
            $lang = $this->language;
        }
        if (empty($lang)) return $url;
        
        if (strpos($url, '?') > 0) {
            return $url . ($html_encode ? '&amp;' : '&') . xLanguageTagQuery . '=' . $lang;
        }
        if ($force_permalink || ($force_permalink === '' && get_option('permalink_structure'))) {
            return $this->permalink_url(trailingslashit($url), $lang);
        }
        return $url . '?' . xLanguageTagQuery . '=' . $lang;
    }
    
    /**
     * Clean up a url to remove language specifier
     *
     * @param string $url The original url potentially with the language specifier, url & html encoded
     * @return string The url without any language specifier, url & html encoded
     */
    function cleanup_url( $url )
    {
        $url = trim( $url );

        $url = preg_replace('/(&|&amp;|\?)' . xLanguageTagQuery . '=[a-z]{2,4}(?:-[a-z]{2,4}){0,2}(&)?/e', '("$1" && "$2") ? "$1" : ""', $url);
        $url = preg_replace('|/' . xLanguageTagPermalink . '/[a-z]{2,4}(?:-[a-z]{2,4}){0,2}(/?)|', '$1', $url);
        
        // clean empty searches from URL, if there are no other parameters,
        // as they can screw things up for static home pages
        $url = preg_replace( "/\?s=$/", '', $url );
  
        return $url;
    }
    
    /**
     * Add a language tag to a cleaned (no lang tag) URL.
     *
     * Note:
     * /page/N is always at the end, even in Postfix mode
     * In Prefix mode, this will only do something if it's an absolute link.
     *
     * @param string $url Any link url
     * @param string $lang The language tag
     * @return string The fixed url
     */
    function permalink_url($url, $lang) {
        if ($this->options['permalink_mode'] == xLanguagePermalinkPrefix) {
            $pre = get_option('home');
            if (strpos($url, $pre) === 0) {
                return untrailingslashit(substr($url, 0, strlen($pre)) . '/' . xLanguageTagPermalink . '/' . $lang . substr($url, strlen($pre)));
            } else return $url;
        } else if ($this->options['permalink_mode'] == xLanguagePermalinkPostfix) {
            $url .= xLanguageTagPermalink . '/' . $lang;
            $pos = strpos($url, '/page/');
            if (!$pos) { return $url; }

            $pos2 = strpos( $url, '/', $pos + 6 );
            if (!$pos2) { return $url; }

            return trailingslashit(substr( $url, 0, $pos ) .  substr( $url, $pos2 )) .
            "page/" . substr($url, $pos + 6, $pos2 - $pos - 6 );
        }
    }

    
    /**
     * Performs rewrite rules modification to support permalinks with lang arguement
     *
     * Code is adopted from Language Switcher (http://www.poplarware.com/languageplugin.html) which might 
     * also be based on Polyglot (http://fredfred.net/skriker/index.php/polyglot)
     *
     * @param array $rules The array of rules
     * @return array New set of rules
     */
    function rewrite_rules_array($rules)
    {
        global $wp_rewrite;
        $langmatch = xLanguageTagPermalink . '/([a-z]{2,4}(?:-[a-z]{2,4}){0,2})/';
        $langmatch2 = '/' . xLanguageTagPermalink . '/([a-z]{2,4}(?:-[a-z]{2,4}){0,2})';

        // rule for home page with language switch suffix
        // We don't specify any param because otherwise WP will think this is 
        // not the is_home() because of the presence of the query variables
        $new_rules = array( $langmatch . '?$' => 'index.php?' );

        // Add rules for feeds and pages
        foreach ($rules as $rule => $def ) {
            // Sometimes a URL is considered as Prefix and Postfix mode.
            // rewrite_def can detect those satisfy Prefix convention to also be marked as Prefix,
            //   but it can detect Postfix convention in the same way.
            // So it's important to process the Prefix first, before Postfix.
            // Otherwise, the Prefix processing could overwrite the Postfix, without the rule marked as Postfix
            //   and causing dead-loop in Permalink Redirection
            if ($this->options['permalink_support'] & xLanguagePermalinkPrefix) {
                // Prefix Style
                $new_rules[ $langmatch . $rule ] = 
                    $this->rewrite_def( $rule, $def, 0, xLanguagePermalinkPrefix );
            }

            if ($this->options['permalink_support'] & xLanguagePermalinkPostfix) {
                // Postfix Style

                // Basically, any time there is a feed rule (2 forms possible) 
                // or a page rule, we'll add language tags before and after
                // the feed/page stuff, 
                // because the language portion could be in either position.
                // And for other rules, we'll add langswitch as an endpoint
                // Don't want to use the generic endpoint rules, because they are
                // too permissive about matching in some cases
                
                // see if this is a feed or page, and add rules
                if( ($pos = strpos( $rule, 'feed/(feed|rdf' )) !== false ) {
                    // feeds, version 1
                    $new_rules[ substr( $rule, 0, $pos ) . $langmatch .  substr( $rule, $pos ) ] = 
                        $this->rewrite_def( $rule, $def, $pos, xLanguagePermalinkPostfix );

                } else if( ($pos = strpos( $rule, '(feed|rdf' )) !== false ) {
                    // feeds, version 2
                    $new_rules[ substr( $rule, 0, $pos ) . $langmatch .  substr( $rule, $pos ) ] = 
                        $this->rewrite_def( $rule, $def, $pos, xLanguagePermalinkPostfix );

                } else if( ($pos = strpos( $rule, 'page/?([0-9]{1,})/' )) !== false ) {
                    // subsequent pages in general
                    $new_rules[ substr( $rule, 0, $pos ) . $langmatch . substr( $rule, $pos ) ] = 
                        $this->rewrite_def( $rule, $def, $pos, xLanguagePermalinkPostfix );

                } else if( ($pos = strpos( $rule, '(/[0-9]+)' )) !== false ) {
                    // special rewrite rules for post permalink subsequent pages
                    $new_rules[ substr( $rule, 0, $pos ) . $langmatch2 .  substr( $rule, $pos ) ] = 
                        $this->rewrite_def( $rule, $def, $pos, xLanguagePermalinkPostfix );

                } else if( ($pos = strpos( $rule, '/[0-9]+/([^/]+)' )) !== false ) {
                    // special rewrite rules for post attachment (http://.../post/lang/ll/attachment_name/...)
                    $new_rules[ substr( $rule, 0, $pos + 7 ) . $langmatch2 . substr( $rule, $pos + 7 ) ] = 
                        $this->rewrite_def( $rule, $def, $pos + 7, xLanguagePermalinkPostfix );
                }
                
                if( substr( $rule, -3 ) == "/?$" ) {
                    // generic rule ending in /?$, just add at end
                    $new_rules[ substr( $rule, 0, strlen( $rule ) - 2 ) . $langmatch . substr( $rule, -2 ) ] = 
                        $this->rewrite_def( $rule, $def, strlen( $rule ), xLanguagePermalinkPostfix );
                }
            }

            // add the old rule now, to preserve matching order
            $new_rules[ $rule ] = $def;
        }

        if (0) { // debug printout of rules 
            echo "Old:\n";
            foreach( $rules as $rule => $def ) {
                echo $rule . " : " . $def . "\n";
            }

            echo "New:\n";
            foreach( $new_rules as $rule => $def ) {
                echo $rule . " : " . $def . "\n";
            }
        }
        return $new_rules;
    }

    /**
     * Utility function to rewrite a rewrite result, with a language variable inserted.
     *
     * Code is adopted from Language Switcher (http://www.poplarware.com/languageplugin.html) which might 
     * also be based on Polyglot (http://fredfred.net/skriker/index.php/polyglot)
     *
     * @param string $rule The original permalinks rule
     * @param string $def The original rewrite result
     * @param string $pos The location that the language variable expression will be inserted into the $rule
     * @param string $permalink_mode The permalink mode of the rule. Internal to xLanguage.
     * @return array New rewrite result that correctly capture the language variable
     */
    function rewrite_def($rule, $def, $pos, $permalink_mode)
    {
        global $wp_rewrite;

        // In some sistuation
        if ($pos == 0) { $permalink_mode |= xLanguagePermalinkPrefix; }

        // How many match tokens are before our insertion?
        $dum = array();
        $num_toks_before = preg_match_all( '|\(|', substr( $rule, 0, $pos), $dum );

        // Build up the new def string -- fixing all $matches[##] entries after where
        // we are inserting

        $newdef = '';
        $remain = $def;
        $tok = 0;

        while( $pos = strpos( $remain, '$matches[' )) {
            preg_match( '|\d+|', substr( $remain, $pos ), $dum );
            $num = (int) $dum[0];
            if( $num > $num_toks_before ) {
                $num++;
            }
            $newdef .= substr( $remain, 0, $pos ) . $wp_rewrite->preg_index( $num );
            $remain = substr( $remain, $pos + 10 + strlen( $dum[0] ));
        }

        // add remains and the language info
        $newdef .= 
            $remain . '&' . xLanguageTagQuery .  '=' . $wp_rewrite->preg_index( $num_toks_before + 1 ) .
            '&' . xLanguageTagPermalinkMode . '=' . $permalink_mode;

        return $newdef;
    }
    
    
    /**
     * Upgrade the options
     *
     * @return void
     **/
    function options_upgrade()
    {
        global $wp_rewrite;
        foreach ($this->options['language'] as $key => $lang) {
            if (!array_key_exists('shownas', $lang)) {
                $this->options['language'][$key]['shownas'] = $lang['code'];
            }
            if (!array_key_exists('availas', $lang)) {
                $this->options['language'][$key]['availas'] = $lang['code'];
            }
            if (!array_key_exists('availability', $lang)) {
                $this->options['language'][$key]['availability'] = 
                    count($lang['show']) ? xLanguageLangAvailabilityUser + xLanguageLangAvailabilityBot : 0;
            }
            if (!array_key_exists('fallback', $lang)) {
                $this->options['language'][$key]['fallback'] = array('_missing' => '');
            }
            if (array_key_exists('missing', $lang) && array_key_exists('_missing', $this->options['language'][$key]['fallback'])) {
                $this->options['language'][$key]['fallback']['_missing'] = $lang['missing'];
                unset($this->options['language'][$key]['missing']);
            }
        }
        if (isset($this->options['wplang_uselocale'])) {
            unset($this->options['wplang_uselocale']);
        }
        
        foreach (array(
                'redirect' => xLanguageRedirectAuto,
                'pref_detection' => xLanguagePrefDetectionDefault,
                'feedback' => array(
                    'enable' => false, 'expose' => false,
                    'last' => 0, 'last_status' => '', 'next' => 0
                ),
                'parser' => array(
                    'mode' => array(xLanguageParserXHtml),
                    'default' => xLanguageParserXHtml,
                    'option_sb_prefix' => 'lang_',
                ),
                'theme' => ''
            ) as $key => $value) {
            if (!isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }

        if (isset($this->options['default']) && !isset($this->options['default2'])) {
            $this->options['default2'] = array( 1 => $this->options['default'], 2 => $this->options['default'] );
        }

        if (is_array($this->options['hook']['text']) && !in_array('localization', $this->options['hook']['text'])) {
            array_push($this->options['hook']['text'], 'localization');
        }
        
        if (!isset($this->options['hook']['textsingle'])) {
            $this->options['hook']['textsingle'] = array(
                'term_description','term_description_rss','link_description','category_description','widget_title'
                );
            if (isset($this->options['hook']['text'])) {
                $this->options['hook']['text'] = array_diff($this->options['hook']['text'], $this->options['hook']['textsingle']);
            }
        }
        if (is_array($this->options['hook']['text']) && !in_array('widget_title', $this->options['hook']['text'])) {
            array_push($this->options['hook']['text'], 'widget_title');
        }
        
        if (!isset($this->options['hookpriority'])) {
            $this->options['hookpriority'] = array();
        }

        foreach ($this->options['hook'] as $hook => $filters) {
            if (!isset($this->options['hookpriority'][$hook])) {
                $this->options['hookpriority'][$hook] = 18;
            }
        }

        if (isset($this->options['structversion']) && (int) $this->options['structversion'] <= 10) {
            $this->options['hook']['text'] = array_merge($this->options['hook']['text'], array('link_category'));
        }

        // Support for xLanguagePermalinkPostfix is broken
        // We recommend user to change to prefix mode, and will have a one shot upgrade to help them to do so.
        if (!isset($this->options['permalink_mode'])) {
            $this->options['permalink_mode'] = xLanguagePermalinkPrefix;
            $this->options['permalink_support'] = xLanguagePermalinkPrefix + xLanguagePermalinkPostfix;
            $this->options['permalink_redirect'] = true;
        }
        if ($this->options['structversion'] < 20000 && $this->options['permalink_mode'] == xLanguagePermalinkPostfix)
        {
            $this->options['permalink_mode'] = xLanguagePermalinkPrefix;
            $this->options['permalink_support'] = xLanguagePermalinkPrefix + xLanguagePermalinkPostfix;
        }

        if (!isset($this->options['parser']['log2'])) {
            if (isset($this->options['parser']['log'])) {
                // v1.3.3 'log' option case is not handled as it's most likely corrupted.
                $this->options['parser']['log2'] = ABSPATH . $this->options['parser']['log'];
            }
            
            if (!isset($this->options['parser']['log']) || !is_dir(dirname($this->options['parser']['log2']))) {
                $ud = wp_upload_dir();
                if (!empty($ud['error'])) die($ud['error']);
                $this->options['parser']['log2'] = $ud['path'] . '/xlanguage-parser-' . rand(10000, getrandmax()) . rand(10000, getrandmax()) . '.log';
            }
            unset($this->options['parser']['log']);
        }

        // Query option, whether and how xLanguage effect the post query

        if(!isset($this->options['query'])) {
            $this->options['query'] = array(
                'enable' => false,
                'enable_for' => array(
                    'feed' => true,
                    'search' => true,
                    'post' => true,
                    'page' => true,
                )
            );
        }
        
        $this->options['structversion'] = xLanguageOptionsStructVersion;

        if (!defined('xLanguageTest')) update_option('xlanguage_options', $this->options);

        $wp_rewrite->flush_rules();
    }

    
    /**
     * TinyMCE (The rich edit box) Filter to add plugins
     * This is for WordPress < 2.5 only. (Applies to TinyMCE v2)
     *
     * @return array The plugins
     */
    function mce_plugins($plugins)
    {
        $plugins[] = 'xlanguage';
        return $plugins;
    }
    
    /**
     * TinyMCE (The rich edit box) Filter to add plugins
     * This is for WordPress >= 2.5 only. (Applies to TinyMCE v3)
     *
     * @return array The plugins
     */
    function mce_external_plugins($plugins)
    {
        $plugins['xlanguage'] = $this->url() . '/js/tinymce-plugin.js?ver=' . xLanguageJavascriptVersion;
        return $plugins;
    }
    
    /**
     * TinyMCE (The rich edit box) Filter to add buttons
     *
     * @return array The buttons
     */
    function mce_buttons($buttons)
    {
        $buttons[] = 'separator';
        if ($this->options['parser']['default'] == xLanguageParserXHtml) $buttons[] = 'xlanguage_highlight';
        $buttons[] = 'xlanguage_clear';
        
        $count = 1;
        foreach ($this->options['language'] as $lang) {
            $buttons[] = "xlanguage__${lang['code']}__${count}";
            $count++;
        }
        return $buttons;
    }

    /**
     * MCE (The rich edit box) Filter to add style sheet
     *
     * @return string The stylesheet
     */
    function mce_css($css)
    {
        return "$css," . get_bloginfo('wpurl') . "/wp-admin/?xlanguage-tinymce-css&ver=" . xLanguageJavascriptVersion;
    }
    
    /**
     * Is "query" option enabled
     *
     * @param boolean $for_page If current operation is for page
     * @return boolean the result
     */
    function query_enabled($for_page = false) {
        return
            $this->options['query']['enable'] && !is_admin() && (!empty($this->language)) &&
            ( $for_page ? 
                ($this->options['query']['enable_for']['page']) :
                !is_single() && !is_page() && (
                    $this->options['query']['enable_for']['post'] && !is_feed() && !is_search() ||
                    $this->options['query']['enable_for']['feed'] && is_feed() ||
                    $this->options['query']['enable_for']['search'] && is_search()
                    )
            );        
    }
    
    /**
     * post_join filter hook for the "query" option
     *
     * @param string $join the old join string
     * @return string the new join string
     */
    function filter_posts_join($join) {
        global $wpdb;
        if (!$this->query_enabled())
            return $join;
        return "
            $join
            LEFT JOIN {$wpdb->postmeta} AS xLanguage_Available ON (
                xLanguage_Available.post_id = {$wpdb->posts}.ID AND
                xLanguage_Available.meta_key = '" . xLanguageQueryMetadataKey . "'
            )
        ";
    }
    
    /**
     * posts_where filter hook for the "query" option
     *
     * @param string $join the old where string
     * @return string the new where string
     */
    function filter_posts_where($where) {
        global $wpdb;
        if (!$this->query_enabled())
            return $where;
        $spliter = xLanguagePostMetaAvailableLanguageListSpliter;
        return "
            $where
            AND (
                xLanguage_Available.meta_value IS NULL OR
                xLanguage_Available.meta_value LIKE '%" . addslashes("$spliter{$this->language}$spliter") . "%'
            )
        ";
    }

    /**
     * get_pages filter for filtering pages in page widgets/theme page list/...
     * please reference the get_pages function's implementation in post.php
     *
     * @return void
     */
    function get_pages_in_current_lang($pages, $r) {
        global $wpdb;
        if (!$this->query_enabled(true))
            return $pages;

        // See if cache is available
        //$r = "$r{$this->language}";
        //$key = md5( serialize( $r ) );
        //if ( $cache = wp_cache_get( 'get_pages_in_current_lang', 'posts' ) )
            //if ( isset( $cache[ $key ] ) )
              //;//return $cache[ $key ];
        
        $spliter = xLanguagePostMetaAvailableLanguageListSpliter;
        $pageids = array();
        $where = "
        (
            meta_value IS NULL OR
            meta_value LIKE '%$spliter{$this->language}$spliter%'
        ) AND (
            post_id IN (-1
        ";
        foreach ($pages as $page)
            $where .= ",{$page->ID}";
        $where .= "))";
        $query = "SELECT post_id from {$wpdb->postmeta} WHERE $where";

        foreach ($wpdb->get_results($query) as $meta)
            $pageids[$meta->post_id] = true;

        $result = array();
        foreach ($pages as $page)
            if($pageids[$page->ID])
                $result[] = $page;

        //$cache[$key] = $result;
        //wp_cache_set('get_pages_in_current_lang', $cache, 'posts');

        return $result;
    }   
}

// Replace the functionality of 'langswitch_filter_langs_with_message'
if (!function_exists('langswitch_filter_langs_with_message')) {
    function langswitch_filter_langs_with_message($content) {
        return apply_filters('language', $content);
    }
}

/**
 * Our one and only instance of the plugin
 *
 * @global xLanguage The plugin
 **/
$xlanguage =& new xLanguagePlugin();

// Test Code
if (defined('xLanguageTest')) {
    add_action('plugins_loaded', 'xLanguage_unload_init', 1);
    function xLanguage_unload_init() {
        global $wp_filter, $merged_filters, $xlanguage;
        unset($GLOBALS['wp_filter']);
        unset($GLOBALS['merged_filters']);
        $xlanguage->plugins_loaded();
    }
}

?>
