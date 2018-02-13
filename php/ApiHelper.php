<?php
/**
 * Backlink Checker is based on api interface of https://siteexplorer.info.
 *
 * @author siteexplorer.info <https://siteexplorer.info/>
 * @version 0.1
 * @link https://siteexplorer.info/
 *
 * LICENSE:
 * --------
 * This program is free software; you can redistribute it and/or modify it
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ------------------------------------------------------------------------
 */
class ApiHelper {
    static $apiurl = "http://api.siteexplorer.info/?auth=";
    static $auths = array("test", "test"); // for support multiple auth tokens, you must change it to your own auth IDs when it online.
    static $pos = -1;
    static function auth() {
	if(self::$pos == -1){ self::$pos = rand(0, count(self::$auths)-1);} // as PHP not support a real static variable, it's not like the C#, JAVA
        $i = self::$pos = self::$pos % count(self::$auths);
        self::$pos++;
        return self::$auths[$i];
    }
    static $maxperpage = 100;
    static function api_pagerank() {
        return self::$apiurl . self::auth() . "&type=json&mask=000&query=";
    }
    static function api_metainfo() {
        return self::$apiurl . self::auth() . "&type=json&mask=100&query=";
    }
    static function api_shortmeta() {
        return self::$apiurl . self::auth() . "&type=json&mask=101&query=";
    }
    static function api_backlinks() {
        return self::$apiurl . self::auth() . "&type=json&mask=011&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function api_justdiscovered() {
        return self::$apiurl . self::auth() . "&type=json&mask=444&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function api_toprankedpages() {
        return self::$apiurl . self::auth() . "&type=json&mask=222&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function api_toprankedbacklinks() {
        return self::$apiurl . self::auth() . "&type=json&mask=666&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function api_topreferringdomains() {
        return self::$apiurl . self::auth() . "&type=json&mask=333&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function api_backlinksfrom() {
        return self::$apiurl . self::auth() . "&type=json&mask=334&maxperpage=" . self::$maxperpage . "&query=";
    }
    static function CallAPI($u) {
        $json = json_decode(file_get_contents($u));
        if ($json->status == "1") return $json;
        throw new Exception("failed." . $json->message);
    }
    static function Normalize($url) {
        if (strpos($url, "://")===false) {
            if (!(strpos($url, "/")===false)) {
                $url = "http://" . $url;
            } else if (strpos($url, "domain:") != 0) {
                $url = "domain:" . $url;
            }
        }
        return $url;
    }
    public static function GetPageRank($url) {
        return self::CallAPI(self::api_pagerank() . urlencode(self::Normalize($url)))->rank;
    }
    public static function GetPageRanks($urls) {
        if (count($urls) == 0) return null;
		if(count($urls) > 50)$url=range($url, 0, 50); // max 50 urls per request
        $sb = "";
        foreach ($urls as $url) {
            $sb = $sb . self::Normalize($url) . '#';
            if (strlen($sb) > 2000) break; // most browser or server can't handle the url length more than 2048 chars.
            
        }
        $sb = rtrim($sb, '#');
        $json = self::CallAPI(self::api_pagerank() . urlencode($sb));
        return explode('#', $json->rank);
    }
    public static function GetMetaInfo($url) {
        return self::CallAPI(self::api_metainfo() . urlencode(self::Normalize($url)));
    }
    public static function GetShortMeta($url) {
        return self::CallAPI(self::api_shortmeta() . urlencode(self::Normalize($url)));
    }
    public static function GetShortMetas($urls) {
        if (count($urls) == 0) return null;
		if(count($urls) >20)$url=range($url, 0, 20); // max 20 urls per request
        $sb = "";
        foreach ($urls as $url) {
            $sb = $sb . self::Normalize($url) . '#';
            if (strlen($sb) > 2000) break; // most browser or server can't handle the url length more than 2048 chars.
            
        }
        $sb = rtrim($sb, '#');
        return self::CallAPI(self::api_shortmeta() . urlencode($sb));
    }
    public static function SearchBacklinks($url, $next) {
        return self::CallAPI(self::api_backlinks() . urlencode(self::Normalize($url)) . "&next=" . $next);
    }
    public static function SearchBacklinksFrom($url, $next, $fromdomain) {
        return self::CallAPI(self::api_backlinksfrom() . urlencode(self::Normalize($url)) . "&domain=" . $fromdomain . "&next=" . $next);
    }
    public static function SearchTopRankedPages($url, $next) {
        return self::CallAPI(self::api_toprankedpages() . urlencode(self::Normalize($url)) . "&next=" . $next);
    }
    public static function SearchTopRankedBacklinks($url, $next) {
        return self::CallAPI(self::api_toprankedbacklinks() . urlencode(self::Normalize($url)) . "&next=" . $next);
    }
    public static function SearchTopReferringDomains($url, $next) {
        return self::CallAPI(self::api_topreferringdomains() . urlencode(self::Normalize($url)) . "&next=" . $next);
    }
    public static function SearchJustDiscovered($url, $next, $range) {
        return self::CallAPI(self::api_justdiscovered() . urlencode(self::Normalize($url)) . "&range=" . $range . "&next=" . $next);
    }
}
?>