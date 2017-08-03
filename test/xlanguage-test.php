<?php
/*
xLanguage-Test - The unit test script for xLanguage

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

// Allows CLI access only for security reason
if (php_sapi_name() != 'cli') return;

define(xLanguageTest, 1);

chdir(dirname(__FILE__) . '/../../../../');
include('wp-blog-header.php');
include_once(dirname(__FILE__) . '/../xlanguage.php');

//TestFindPreferredLanguage(); //TODO make this test pass
//TestxLanguageParser(); //TODO make this test pass
TestxLanguageFilter();
TestxLanguageLink();
TestxLanguageTemplate();

function TestFindPreferredLanguage() {
    global $xlanguage;
    $xlanguage->options = array(
        'default' => 'ps', 
        'language' => array( 
            'ps' => array('code'=>'ps','show'=>array('ps')), 
            'ps-one' => array('code'=>'ps-one','show'=>array('ps-one')), 
            'ps-two' => array('code'=>'ps-two','show'=>array('ps-two')), 
            'en' => array('code'=>'en','show'=>array('en')),
            'xx' => array('code'=>'xx','show'=>array())
        ), 
    );
    $xlanguage->options_upgrade();
    TestReport('Upgrade Default', $xlanguage->options['default'], 'ps');
    TestReport('Upgrade Default2-1', $xlanguage->options['default2'][1], 'ps');
    TestReport('Upgrade Default2-2', $xlanguage->options['default2'][2], 'ps');
    
    $xlanguage->options['pref_detection'] = xLanguagePrefDetectionDefault;
    TestReport('One', $xlanguage->find_preferred_language(), 'ps');

    $_GET[ xLanguageTagQuery ] = 'ps-one';
    TestReport('Get 1', $xlanguage->find_preferred_language(), 'ps-one');
    $_GET[ xLanguageTagQuery ] = 'xx';
    TestReport('Get 2', $xlanguage->find_preferred_language(), 'ps');
    unset($_GET[ xLanguageTagQuery ]);
    
    $xlanguage->options['pref_detection'] = xLanguagePrefDetectionDefault;
    $_SERVER['REQUEST_URI'] = '/somewhere/lang/ps-one/however';
    TestReport('Permalink 1', $xlanguage->find_preferred_language(), 'ps-one');
    $_SERVER['REQUEST_URI'] = '/somewhere/lang/ps-two';
    TestReport('Permalink 2', $xlanguage->find_preferred_language(), 'ps-two');
    $_SERVER['REQUEST_URI'] = '/lang/ps-two';
    TestReport('Permalink 3', $xlanguage->find_preferred_language(), 'ps-two');
    $_SERVER['REQUEST_URI'] = '/somewhere/lang/no-no';
    TestReport('Permalink Negative', $xlanguage->find_preferred_language(), 'ps');
    $xlanguage->options['pref_detection'] = 0;
    $_SERVER['REQUEST_URI'] = '/somewhere/lang/ps-one/however';
    TestReport('Permalink No Pref', $xlanguage->find_preferred_language(), 'ps');
    unset($_SERVER['REQUEST_URI']);

    $xlanguage->options['pref_detection'] = xLanguagePrefDetectionDefault;
    $_COOKIE[xLanguageTagCookie] = 'ps-one';
    TestReport('Cookie Positive', $xlanguage->find_preferred_language(), 'ps-one');
    $_COOKIE[xLanguageTagCookie] = 'no-no';
    TestReport('Cookie Negative', $xlanguage->find_preferred_language(), 'ps');
    $xlanguage->options['pref_detection'] = 0;
    $_COOKIE[xLanguageTagCookie] = 'ps-one';
    TestReport('Cookie No Pref', $xlanguage->find_preferred_language(), 'ps');
    unset($_COOKIE[xLanguageTagCookie]);

    $xlanguage->options['pref_detection'] = xLanguagePrefDetectionDefault;
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='en,ps;q=0.2';
    TestReport('Language 1', $xlanguage->find_preferred_language(), 'en');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='ps,en;q=0.2';
    TestReport('Language 2', $xlanguage->find_preferred_language(), 'ps');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='ps-one,ps;q=0.2';
    TestReport('Language 3', $xlanguage->find_preferred_language(), 'ps-one');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='ps-tw,ps;q=0.2';
    TestReport('Language 4', $xlanguage->find_preferred_language(), 'ps');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='ps-tw,ps;q=0.2,en;q=0.5';
    TestReport('Language 5', $xlanguage->find_preferred_language(), 'en');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='ps,en;q=2';
    TestReport('Language 6', $xlanguage->find_preferred_language(), 'en');
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='no,thi,ng';
    TestReport('Language Negative', $xlanguage->find_preferred_language(), 'ps');
    $xlanguage->options['pref_detection'] = 0;
    $_SERVER['HTTP_ACCEPT_LANGUAGE']='en';
    TestReport('Language No Pref', $xlanguage->find_preferred_language(), 'ps');
    unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    

    $xlanguage->options['pref_detection'] = 0;
    $xlanguage->options['default2'][1] = 'ps-one';
    $xlanguage->options['default2'][2] = 'ps-two';
    TestReport('Default User', $xlanguage->find_preferred_language(), 'ps-one');
    $_SERVER['HTTP_USER_AGENT'] = 'googlebot/1.0';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $xlanguage->useragent = $xlanguage->determine_agent();
    TestReport('Default Bot', $xlanguage->find_preferred_language(), 'ps-two');
    unset($_SERVER['HTTP_USER_AGENT']);
    $xlanguage->useragent = $xlanguage->determine_agent();
    
    return true;
}

function TestxLanguageFilter() {
    global $xlanguage;

    $xlanguage->options = array(
        'split' => '|',
        'default' => 'zh', 
        'language' => array( 
            'zh' => array('code'=>'zh', 'show'=> array('zh'), 'pos' => 0, 'shownas' => 'zh'), 
            'en' => array('code'=>'en', 'show'=> array('en'), 'pos' => 1, 'shownas' => 'en'),
            'all-en' => array('code'=>'en', 'show' => array('zh','en','all-en'), 'pos' => 1, 'shownas' => 'en-us')
        ),
        'rlookup' => array( 'zh' => 0, 'en' => 1 )
    );
    $xlanguage->options_upgrade();
    $xlanguage->language = 'en';
    TestReport('Filter Language', $xlanguage->filter_language('xx'), 'en');
    
    $xlanguage->language = 'en';
    TestReport('One Line A', $xlanguage->filter_text('Whole Line'), 'Whole Line');
    $xlanguage->language = 'en';
    TestReport('One Line 1', $xlanguage->filter_text('First zh|Second en'), 'Second en');
    $xlanguage->language = 'zh';
    TestReport('One Line 2', $xlanguage->filter_text('First zh|Second en'), 'First zh');
    TestReport('One Line 3', $xlanguage->filter_text('中哂文啦|Second en'), '中哂文啦');
    $xlanguage->language = 'all-en';
    TestReport('One Line 4', $xlanguage->filter_text('First zh|Second en'), 'Second en');

    $xlanguage->options['split'] = '~~';
    $xlanguage->language = 'zh';
    TestReport('One Line 5 Split', $xlanguage->filter_text('First zh~~Second en'), 'First zh');
    $xlanguage->language = 'all-en';
    TestReport('One Line 6', $xlanguage->filter_text('First zh~~Second en'), 'Second en');
    $xlanguage->options['split'] = '|';

    $xlanguage->language = 'en';
    TestReport('Paragraph A', $xlanguage->filter_text("More Line\nWhole Life"), "More Line\nWhole Life");
    TestReport("Paragraph 1", $xlanguage->filter_text('<span lang="en">Whole Line</span><span lang="zh">Whole Life</span>'), '<span lang="en">Whole Line</span>');
    TestReport("Paragraph 2", $xlanguage->filter_text('<p><span lang="en">Whole Line</span></p><p><span lang="zh">Whole Life</span></p>'), '<p><span lang="en">Whole Line</span></p>');
    TestReport("Paragraph 3", $xlanguage->filter_text('<div><p><span lang="en">Whole Line</span></p><p><span lang="zh">Whole Life</span></p></div><div><span lang="rub"></span></div>'), '<div><p><span lang="en">Whole Line</span></p></div>');
    TestReport("Paragraph 4", $xlanguage->filter_text('<div><span lang="no">Inside <span lang="en">English</span> Job</span></div>'), '<div><span lang="en">English</span></div>');
    TestReport("Paragraph 5", $xlanguage->filter_text('<div><p><span lang="no">Inside</span></p></div>Outside'), 'Outside');
    TestReport("Paragraph 6", $xlanguage->filter_text('<span lang="en" more="&amp;&gt;Like this">hey? World //</span>'), '<span lang="en" more="&amp;&gt;Like this">hey? World //</span>');
    $xlanguage->language = "all-en";
    TestReport("Paragraph 7", $xlanguage->filter_text('<span lang="en">Whole Line</span><span lang="zh">Whole Life</span>'), '<span lang="en">Whole Line</span><span lang="zh">Whole Life</span>');
    TestReport("Paragraph 8", $xlanguage->filter_text('<span lang="en">Whole Line</span><span lang="all-en">Whole Life</span>'), '<span lang="en">Whole Line</span><span lang="en-us">Whole Life</span>');

    $xlanguage->language = 'en';
    TestReport('Empty 1', $xlanguage->empty_html('<div> <br /> <p> <img src="aaa" alt="I am IMG" /> <a class="more-link">I am English but I am nothing</a></p></div>'), false);
    TestReport('Empty 2', $xlanguage->empty_html('<div><p> <br /> <a class="more-link">I am English but I am nothing</a></p></div>'), true);
    
    $xlanguage->language = 'zh';
    TestReport('Empry & More 1', $xlanguage->contains_lang('<div><p><span lang="zh">我是中文</span> <a class="more-link">I am English but I am nothing</a><span lang="zh">我是中文</span></p></div>', 'en'), false);
    TestReport('Empry & More 2', $xlanguage->contains_lang('<div><p><span lang="en">I \' am Mr. English</span> <img src="aaa" alt="I am IMG" /> <a class="more-link">I am English but I am nothing</a></p></div>'), true);
    TestReport('Empry & More 3', $xlanguage->contains_lang('<span lang="en">English only</span>', 'zh'), false);

    $xlanguage->language = "en";
    TestReport("Line A", $xlanguage->filter_textlink('More Line\nWhole Life'), 'More Line\nWhole Life');
    TestReport("Line 1", $xlanguage->filter_textlink('<a href="something">More Line|Whole Life</a>'), '<a href="something">Whole Life</a>');
    TestReport("Line 2", $xlanguage->filter_textlink('Pre<a href="something">More Line|Whole Life</a>Mid<a>zh|en</a>Post'), 'Pre<a href="something">Whole Life</a>Mid<a>en</a>Post');

    $xlanguage->language = "en";
    $xlanguage->options['parser']['mode'] = array(2);
    $xlanguage->options['parser']['option_sb_prefix'] = 'langsome';
    TestReport("SB Prefix 1", $xlanguage->filter_text('[langsomeen]Now this[/langsomeen][langsomezh]and that too[/langsomezh]'), 'Now this');
    $xlanguage->options['parser']['option_sb_prefix'] = 'lang_';
    TestReport("SB Prefix 2", $xlanguage->filter_text('[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 'Now this');

    $xlanguage->options['parser']['mode'] = array(1);
    TestReport("Mode 1", $xlanguage->filter_text('<span lang="en">Here is This</span><span lang="zh">And That</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 
        '<span lang="en">Here is This</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]');
    $xlanguage->options['parser']['mode'] = array(2);
    TestReport("Mode 2", $xlanguage->filter_text('<span lang="en">Here is This</span><span lang="zh">And That</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 
        '<span lang="en">Here is This</span><span lang="zh">And That</span>Now this');
    $xlanguage->options['parser']['mode'] = array(2,1);
    TestReport("Mode 3 SB", $xlanguage->filter_text('<span lang="en">Here is This</span><span lang="zh">And That</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 
        '<span lang="en">Here is This</span><span lang="zh">And That</span>Now this');
    TestReport("Mode 3 XHTML", $xlanguage->filter_text('<span lang="en">Here is This</span><span lang="zh">And That</span>'), 
        '<span lang="en">Here is This</span>');
    $xlanguage->options['parser']['mode'] = array(1,2);
    TestReport("Mode 4 XHTML", $xlanguage->filter_text('<span lang="en">Here is This</span><span lang="zh">And That</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 
        '<span lang="en">Here is This</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]');
    TestReport("Mode 4 SB", $xlanguage->filter_text('<span lang="en"></error>Here is This</span><span lang="zh">And That</span>[lang_en]Now this[/lang_en][lang_zh]and that too[/lang_zh]'), 
        '<span lang="en"></error>Here is This</span><span lang="zh">And That</span>Now this');
    
    return true;
}

function TestxLanguageLink() {
    global $xlanguage;
    TestReport('Cleanup URL 1', $xlanguage->cleanup_url('http://example.com'), 'http://example.com');
    TestReport('Cleanup URL 2', $xlanguage->cleanup_url('http://example.com/'), 'http://example.com/');
    TestReport('Cleanup URL 3', $xlanguage->cleanup_url('http://example.com/lang/zh'), 'http://example.com');
    TestReport('Cleanup URL 4', $xlanguage->cleanup_url('http://example.com/test/lang/ps-ps'), 'http://example.com/test');
    TestReport('Cleanup URL 5', $xlanguage->cleanup_url('http://example.com/lang/ps-ps/test'), 'http://example.com/test');
    TestReport('Cleanup URL 6', $xlanguage->cleanup_url('http://example.com/test?lang=ps-ps'), 'http://example.com/test');
    TestReport('Cleanup URL 7', $xlanguage->cleanup_url('http://example.com/test?something=notimportant&lang=ps-ps'), 'http://example.com/test?something=notimportant');
    TestReport('Cleanup URL 8', $xlanguage->cleanup_url('http://example.com/test?lang=ps-ps&something=notimportant'), 'http://example.com/test?something=notimportant');
    TestReport('Cleanup URL 9', $xlanguage->cleanup_url('http://example.com/test?here=true&lang=en&there=false'), 'http://example.com/test?here=true&there=false');

    $siteurl = get_option("siteurl");
    $xlanguage->options = array( "permalink_mode" => xLanguagePermalinkPrefix );
    $xlanguage->language = "en";
    TestReport("Link Pre In Lang 1", $xlanguage->filter_link_in_lang("$siteurl", "en", true), "$siteurl/lang/en");
    TestReport("Link Pre In Lang 2", $xlanguage->filter_link_in_lang("$siteurl", "en", false), "$siteurl?lang=en");
    TestReport("Link Pre In Lang 3", $xlanguage->filter_link_in_lang("$siteurl/test", "en", true), "$siteurl/lang/en/test");
    TestReport("Link Pre In Lang 4", $xlanguage->filter_link_in_lang("$siteurl/test", "en", false), "$siteurl/test?lang=en");
    TestReport("Link Pre In Lang 5", $xlanguage->filter_link_in_lang("$siteurl/lang/zh", "en", true), "$siteurl/lang/en");
    TestReport("Link Pre In Lang 6", $xlanguage->filter_link_in_lang("$siteurl/lang/zh", "en", false), "$siteurl?lang=en");
    TestReport("Link Pre In Lang 7", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "en", true), "$siteurl/test?something=notimportant&amp;lang=en");
    TestReport("Link Pre In Lang 8", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "en", false), "$siteurl/test?something=notimportant&amp;lang=en");
    $xlanguage->language = "zh";
    TestReport("Link Pre In Lang 9", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "", true), "$siteurl/test?something=notimportant&amp;lang=zh");
    TestReport("Link Pre In Lang 10", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "",false), "$siteurl/test?something=notimportant&amp;lang=zh");
    TestReport("Link Pre In Lang 11", $xlanguage->filter_link_in_lang("$siteurl/lang/ps-ps", "", true), "$siteurl/lang/zh");

    $xlanguage->options = array( "permalink_mode" => xLanguagePermalinkPostfix );
    $xlanguage->language = "en";
    TestReport("Link Post In Lang 1", $xlanguage->filter_link_in_lang("$siteurl", "en", true), "$siteurl/lang/en");
    TestReport("Link Post In Lang 2", $xlanguage->filter_link_in_lang("$siteurl", "en", false), "$siteurl?lang=en");
    TestReport("Link Post In Lang 3", $xlanguage->filter_link_in_lang("$siteurl/test", "en", true), "$siteurl/test/lang/en");
    TestReport("Link Post In Lang 4", $xlanguage->filter_link_in_lang("$siteurl/test", "en", false), "$siteurl/test?lang=en");
    TestReport("Link Post In Lang 5", $xlanguage->filter_link_in_lang("$siteurl/lang/zh", "en", true), "$siteurl/lang/en");
    TestReport("Link Post In Lang 6", $xlanguage->filter_link_in_lang("$siteurl/lang/zh", "en", false), "$siteurl?lang=en");
    TestReport("Link Post In Lang 7", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "en", true), "$siteurl/test?something=notimportant&amp;lang=en");
    TestReport("Link Post In Lang 8", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "en", false), "$siteurl/test?something=notimportant&amp;lang=en");
    $xlanguage->language = "zh";
    TestReport("Link Post In Lang 9", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "", true), "$siteurl/test?something=notimportant&amp;lang=zh");
    TestReport("Link Post In Lang 10", $xlanguage->filter_link_in_lang("$siteurl/test?something=notimportant&amp;lang=ps-ps", "",false), "$siteurl/test?something=notimportant&amp;lang=zh");
    TestReport("Link Post In Lang 11", $xlanguage->filter_link_in_lang("$siteurl/lang/ps-ps", "", true), "$siteurl/lang/zh");
}

function TestxLanguageTemplate() {
    global $xlanguage;
    $xlanguage->options = array(
        'split' => '|',
        'default' => 'en', 
        'language' => array( 
            'zh' => array('code'=>'zh', 'show'=> array('zh'), 'pos' => 0, 'shownas' => 'zh', 'name' => 'zh in zh|zh in en', 'availas' => array('zh') ), 
            'en' => array('code'=>'en', 'show'=> array('en'), 'pos' => 1, 'shownas' => 'en', 'name' => 'en in zh|en in en', 'availas' => array('en') ),
        ), 
        'parser' => array(
            'mode' => array(1, 2),
            'option_sb_prefix' => 'lang_'
        )
    );
    $xlanguage->options_upgrade();
    
    $xlanguage->language = 'zh';
    TestReport("wp_localization", wp_localization('String 1|String 2'), 'String 1');

    $xlanguage->language = 'en';
    TestReport("xlanguage_current_language 1", xlanguage_current_language(), 'en in en');
    $xlanguage->language = 'zh';
    TestReport("xlanguage_current_language 2", xlanguage_current_language(), 'zh in zh');

    TestReport("xlanguage_current_language_code", xlanguage_current_language_code(), 'zh');
    TestReport("wp_localization", wp_localization('String 1|String 2'), 'String 1');

    global $post;

    $post->post_content = '<span lang="en">English</span><p lang="zh">Chinese</span>';
    ob_start(); xlanguage_post_other_langs(); $output = ob_get_contents(); ob_end_clean();
    TestReportRegex("xlanguage_post_other_langs_1", $output, '/title="en in zh"/');
    
    $post->post_content = '[lang_en]English[/lang_en][lang_zh]Chinese[/lang_zh]';
    ob_start(); xlanguage_post_other_langs(); $output = ob_get_contents(); ob_end_clean();
    TestReportRegex("xlanguage_post_other_langs_2", $output, '/title="en in zh"/');
}

function TestxLanguageParser() {
$parser = new xLanguageXhtmlParser();

// 0
$test[] = <<<TEST
TEST;
$answer[] = <<<TEST
TEST;

// 1
$test[] = <<<TEST
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
TEST;
$answer[] = <<<TEST
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
TEST;

// 2
$test[] = <<<TEST
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</span>
<span lang="zh">我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。</span>
TEST;
$answer[] = <<<TEST
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</span>

TEST;

// 3
$test[] = <<<TEST
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</span>
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
TEST;
$answer[] = <<<TEST
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</span>
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
TEST;

// 4
$test[] = <<<TEST
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
<span lang="other">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</span>
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
TEST;
$answer[] = <<<TEST
我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。

我是一個大蘋果，又香又甜又好吃，個個孩子都愛我。
TEST;

// 5
$test[] = <<<TEST
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidi</span>

<span lang="zh-hk">dunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitatio</span>

<span lang="en">n ullamco laboris nisi ut aliquip </span><span lang="en">ex ea commodo con</span><span lang="en">sequat. Duis aute irure dolor in </span><span lang="zh-hk">reprehenderit in voluptate velit </span>

<span lang="en">esse cill</span>

<span lang="en">um dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</span>
TEST;
$answer[] = <<<TEST
<span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidi</span>



<span lang="en">n ullamco laboris nisi ut aliquip </span><span lang="en">ex ea commodo con</span><span lang="en">sequat. Duis aute irure dolor in </span>

<span lang="en">esse cill</span>

<span lang="en">um dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</span>
TEST;

// 6
$test[] = <<<TEST
<ul>
    <li><span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidi</span></li>
</ul>
<span lang="zh-hk">dunt ut labore et dolor<a href="http://example.com">e magna aliqua. Ut enim ad </a>minim veniam, quis nostrud exercitatio</span>

<span lang="en">n ullamco laboris nisi ut aliquip </span><span lang="en">ex ea commodo <strong>con</strong></span><strong><span lang="en">sequat. Duis aute irure <u>dolor in </u></span></strong><span lang="zh-hk"><strong><u>reprehenderit</u> in voluptate</strong> velit </span>

<span lang="en">um dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</span>
TEST;
$answer[] = <<<TEST
<ul>
    <li><span lang="en">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidi</span></li>
</ul>


<span lang="en">n ullamco laboris nisi ut aliquip </span><span lang="en">ex ea commodo <strong>con</strong></span><strong><span lang="en">sequat. Duis aute irure <u>dolor in </u></span></strong>

<span lang="en">um dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</span>
TEST;

// 7
$test[] = <<<TEST
<p><span lang="en">At Some Time<br /><strong what="?"> At Somewhere</strong><br /> For Something</span><br /><strong></strong></p>
TEST;
$answer[] = <<<TEST
<p><span lang="en">At Some Time<br /><strong what="?"> At Somewhere</strong><br /> For Something</span><br /><strong></strong></p>
TEST;

for ($i = 0; $i < count($test); $i++) {
    $res = $parser->filter($test[$i], array('en'), $dummy_options = array('language' => array ('en' => array('shownas' => 'en'))) );
    TestReport("Case $i", $res, $answer[$i]);
}
}

function TestReport($description, $result, $answer) {
    $trace = debug_backtrace();
    $msg = "{$trace[1]['function']}, line {$trace[0]['line']}: $description";
    if ($result !== $answer) {
        echo "$msg Failed:\n";
        echo "Result:\n**{$result}**\nAnswer:\n**{$answer}**\n";
        exit;
    } else {
        echo "$msg Passed\n";
    }
}

function TestReportRegex($description, $result, $answer) {
    $trace = debug_backtrace();
    $msg = "{$trace[1]['function']}, line {$trace[0]['line']}: $description";
    if (!preg_match($answer, $result)) {
        echo "$msg Failed:\n";
        echo "Result:\n**{$result}**\nAnswer:\n**{$answer}**\n";
        exit;
    } else {
        echo "$msg Passed\n";
    }
}
?>
