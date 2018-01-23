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
namespace System
{
    using System;
    using System.Collections.Generic;
    using System.Text;
    using System.Net;

    public static class ApiHelper
    {
        static string[] auths = { "test", "test" }; // for support multiple auth tokens, you must change it to your own auth IDs when it online.
        static int pos = 0;
        static object sync = new object();
        static string auth { get { lock (sync) { pos = pos % auths.Length; return auths[pos++]; } } }
        static int maxperpage = 100;
        static string api_pagerank { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=000&query="; } }
        static string api_metainfo { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=100&query="; } }
        static string api_shortmeta { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=101&query="; } }
        static string api_backlinks { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=011&maxperpage=" + maxperpage + "&query="; } }
        static string api_justdiscovered { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=444&maxperpage=" + maxperpage + "&query="; } }
        static string api_toprankedpages { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=222&maxperpage=" + maxperpage + "&query="; } }
        static string api_toprankedbacklinks { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=666&maxperpage=" + maxperpage + "&query="; } }
        static string api_topreferringdomains { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=333&maxperpage=" + maxperpage + "&query="; } }
        static string api_backlinksfrom { get { return "http://api.siteexplorer.info/?auth=" + auth + "&type=json&mask=334&maxperpage=" + maxperpage + "&query="; } }
        static Json CallAPI(string u)
        {
            Json json = null;
            using (WebClient wc = new WebClient())
            {
                wc.Encoding = Encoding.UTF8;
                json = Json.Parse(wc.DownloadString(u));
            }
            if (json["status"].ToString() == "1") return json;
            throw new Exception("failed." + json["message"]);
        }
        static string Normalize(string url)
        {
            if (!url.Contains("://"))
            {
                if (url.Contains("/"))
                {
                    url = "http://" + url;

                }
                else if (!url.StartsWith("domain:"))
                {
                    url = "domain:" + url;
                }
            }
            return url;
        }
        public static string GetPageRank(string url)
        {
            Json json = CallAPI(api_pagerank + Uri.EscapeDataString(Normalize(url)));
            return json["rank"].ToString();
        }
        public static List<string> GetPageRanks(List<string> urls)
        {
            if (urls.Count == 0) return null;
            StringBuilder sb = new StringBuilder();
            foreach (string url in urls)
            {
                sb.Append(Normalize(url));
                sb.Append("#");
                if (sb.Length > 2000) break; // most browser or server can't handle the url length more than 2048 chars.
            }
            sb.Length -= 1;
            Json json = CallAPI(api_pagerank + Uri.EscapeDataString(sb.ToString()));
            List<string> pageranks = new List<string>();
            foreach (string item in json["rank"].ToString().Split('#'))
            {
                pageranks.Add(item);
            }
            return pageranks;
        }
        public static Json GetMetaInfo(string url)
        {
            return CallAPI(api_metainfo + Uri.EscapeDataString(Normalize(url)));
        }
        public static Json GetShortMeta(string url)
        {
            return CallAPI(api_shortmeta + Uri.EscapeDataString(Normalize(url)));
        }
        public static Json SearchBacklinks(string url, string next)
        {
            return CallAPI(api_backlinks + Uri.EscapeDataString(Normalize(url)) + "&next=" + next);
        }
        public static Json SearchBacklinksFrom(string url, string next, string fromdomain)
        {
            return CallAPI(api_backlinksfrom + Uri.EscapeDataString(Normalize(url)) + "&domain=" + fromdomain + "&next=" + next);
        }
        public static Json SearchTopRankedPages(string url, string next)
        {
            return CallAPI(api_toprankedpages + Uri.EscapeDataString(Normalize(url)) + "&next=" + next);
        }
        public static Json SearchTopRankedBacklinks(string url, string next)
        {
            return CallAPI(api_toprankedbacklinks + Uri.EscapeDataString(Normalize(url)) + "&next=" + next);
        }
        public static Json SearchTopReferringDomains(string url, string next)
        {
            return CallAPI(api_topreferringdomains + Uri.EscapeDataString(Normalize(url)) + "&next=" + next);
        }
        public static Json SearchJustDiscovered(string url, string next, int range)
        {
            return CallAPI(api_justdiscovered + Uri.EscapeDataString(Normalize(url)) + "&range=" + range + "&next=" + next);
        }
    }
}

