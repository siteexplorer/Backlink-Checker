<%@ Page Language="C#" CodePage="936" %> 
<script language="c#" runat="server">
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
    string BuildMeta(string url)
    {
        Json json = ApiHelper.GetMetaInfo(url);
        StringBuilder sb = new StringBuilder();
        sb.Append("<div class='meta'>");
        string s = json["query"].ToString();
        Json m = json["meta"];
        sb.Append("<table>" +
                    "<tr style='font-weight:bold'><td>Page Rank</td>" +
                    "<td>Referring domains</td>" +
                    "<td>Inbound Backlinks</td>" +
                    "<td>Network IPs</td>" +
                    "<td>Class C Network Subnets</td>" +
                    "<td>Text Links</td>" +
                    "<td>Home Pages</td>" +
                    "<td>No Follows</td>" +
                    "</tr><tr>" +
                    "<td>" + m["rank"] + "</td>" +
                    "<td>" + m["domains"] + "</td>" +
                    "<td>" + m["links"] + "</td>" +
                    "<td>" + m["ips"] + "</td>" +
                    "<td>" + m["subnets"] + "</td>" +
                    "<td>" + m["textlinks"] + "</td>" +
                    "<td>" + m["homepages"] + "</td>" +
                    "<td>" + m["nofollow"] + "</td>" +
                    "</tr></table>");
        string s1 = "<table><tr><td class='hd'><b>TLD</b></td>";
        string s2 = "<tr><td class='hd'><b>Link Count</b></td>";
        int c = 0;
        foreach (Json js in json["domains"].Jsons)
        {
            s1 += "<td>" + js["domain"] + "</td>";
            s2 += "<td>" + js["count"] + "</td>";
            if (++c == 20) break;
        }
        s1 += "</tr>";
        s2 += "</tr></table>";
        sb.Append(s1 + s2);
        s1 = "<table><tr><td class='hd'><b>Country</b></td>";
        s2 = "<tr><td class='hd'><b>Link Count</b></td>";
        c = 0;
        foreach (Json js in json["countries"].Jsons)
        {
            s1 += "<td>" + js["country"] + "</td>";
            s2 += "<td>" + js["count"] + "</td>";
            if (++c == 20) break;
        }
        s1 += "</tr>";
        s2 += "</tr></table>";
        sb.Append(s1 + s2);
        sb.Append("<ul><li><b>Keywords:</b></li>");
        foreach (Json js in json["keywords"].Jsons)
        {
            sb.Append("<li><i>" + js["keyword"] + "</i>(" + js["count"] + ")</li>");
        }
        sb.Append("</ul>");
        sb.Append("</div>");
        return sb.ToString();
    }
    string Mark(string u)
    {
        // return u; // show the full url link.
        return u.Replace("://", ":// ").Replace(".", ". "); // destory the full url link and show as text only.
    }
    string BuildTable(Json json)
    {
        StringBuilder sb = new StringBuilder();
        sb.Append("<div class='result'><table class='item'><tr class='header'><th class='rank'>Rank</td><th class='domain'>Referring Domains</th><th class='anchor'>Anchor Text</th><th class='dcount'>Backlinks</th><th class='home'>Home Page</th></tr>");
        Json[] results = json["results"].Jsons;
        if (results.Length == 0)
        {
            sb.Append("<tr><td colspan='5'>No result yet, come back later.</td></tr>");
        }
        else
        {
            foreach (Json result in results)
            {
                sb.Append("<tr onclick='go(this);return false;' style='background-color:#cccccc;font-size:12px;color:#000000'>");
                sb.Append("<td colspan='5'><i>Backlinks for page: " + result["backlinksfor"] + "</i></td>");
                sb.AppendLine("</tr>");
                int i = 0;
                foreach (Json domain in result["domains"].Jsons)
                {
                    sb.AppendFormat("<tr class='{0}' sid='xkey-{1}' onclick='zk(this)' title='click to expand backlink details for this domain' state='0'>", i % 2 == 0 ? "" : " tr-jsh", i);
                    sb.AppendFormat("<td class='rank'>{0}</td>" +
                                    "<td class='domain'><a href='?f=1&url=domain:{1}'>{1}</a></td>" +
                                    "<td class='anchor'>{2}</td>" +
                                    "<td class='dcount'>{3}+</td>" +
                                    "<td class='home'>{4}</td>",
                                    domain["rank"],
                                    domain["domain"],
                                    domain["anchor"],
                                    domain["count"],
                                    domain["homepage"]);

                    sb.AppendLine("</tr>");
                    sb.AppendFormat("<tr class='details {1}' id='xkey-{0}'>", i, i % 2 == 0 ? "tr-esh" : "tr-jsh");
                    sb.AppendLine("<td colspan='5'>");
                    sb.AppendLine("<table>");
                    sb.AppendLine("<tr class='header'><th class='tab'></th><th class='rank'>Rank</th><th class='url'>Inbound Links</th><th class='anchor'>Anchor Text</th><th class='follow'>Follow</th><th class='date'>First Found</th><th class='date'>Last Found</th></tr>");
                    int j = 0;
                    foreach (Json page in domain["pages"].Jsons)
                    {
                        string[] parts = page["date"].ToString().Split('-');
                        sb.AppendFormat("<tr{0}><td class='tab'></td><td class='rank'>{6}</td><td class='url' onclick='go(this)'>{1}</td><td>{2}</td><td>{3}</td><td>{4}</td><td>{5}</td></tr>\n",
                                       j % 2 == 1 ? "" : " class='tr-osh'",
                                       Mark(page["url"].ToString()),
                                       page["anchor"],
                                       page["follow"],
                                       parts[0],
                                       parts[1],
                                       page["rank"]);
                        j++;
                    }
                    sb.AppendLine("</table>");
                    sb.AppendLine("</td>");
                    sb.AppendLine("</tr>");
                    i++;
                }
            }
        }
        sb.Append("</table></div>");
        return sb.ToString();
    }

    string BuildTable2(Json json)
    {
        StringBuilder sb = new StringBuilder();
        sb.Append("<div class='result'><table class='item'><tr class='header'><th class='rank'>Rank</td><th class='domain'>Page URL</th><th class='dcount'>Referrig Domains</th><th class='dcount'>Backlinks</th></tr>");
        Json[] results = json["pages"].Jsons;
        if (results.Length == 0)
        {
            sb.Append("<tr><td colspan='4'>No result yet, come back later.</td></tr>");
        }
        else
        {
            int i = 0;
            foreach (Json page in results)
            {
                sb.AppendFormat("<tr class='{0}'>", i % 2 == 0 ? "" : " tr-jsh", i);
                sb.AppendFormat("<td class='rank'>{0}</td>" +
                                "<td class='url2'><a href='?f=1&url={1}'>{2}</a></td>" +
                                "<td class='dcount'>{3}</td>" +
                                "<td class='dcount'>{4}+</td>",
                                page["rank"],
                                Uri.EscapeDataString(page["url"].ToString()),
                                page["url"],
                                page["domains"],
                                page["backlinks"]);
                sb.AppendLine("</tr>");
                i++;
            }
        }
        sb.Append("</table></div>");
        return sb.ToString();
    }
    string BuildTable3(Json json)
    {
        StringBuilder sb = new StringBuilder();
        sb.Append("<div class='result'><table class='item'><tr class='header'><th class='rank'>Rank</td><th class='domain'>Backlink URL</th><th class='anchor'>Linked Anchor</th><th class='date2'>Date</th></tr>");
        Json[] results = json["backlinks"].Jsons;
        if (results.Length == 0)
        {
            sb.Append("<tr><td colspan='4'>No result yet, come back later.</td></tr>");
        }
        else
        {
            int i = 0;
            foreach (Json page in results)
            {
                sb.AppendFormat("<tr class='{0}'>", i % 2 == 0 ? "" : " tr-jsh", i);
                sb.AppendFormat("<td class='rank'>{0}</td>" +
                                "<td class='url2'><a href='?f=1&url={1}'>{2}</a></td>" +
                                "<td class='dcount'>{3}</td>" +
                                "<td class='date2'>{4}</td>",
                                page["rank"],
                                Uri.EscapeDataString(page["url"].ToString()),
                                page["url"],
                                page["anchor"],
                                page["date"]);
                sb.AppendLine("</tr>");
                i++;
            }
        }
        sb.Append("</table></div>");
        return sb.ToString();
    }
    string searchvalue = "";
    string result = "";
    protected void Page_Load(object sender, EventArgs e)
    {
        searchvalue = Request.QueryString["url"];
        if (String.IsNullOrEmpty(searchvalue)) return;
        StringBuilder sb = new StringBuilder();
        sb.Append(BuildMeta(searchvalue));
        Json json;
        switch (Request.QueryString["f"])
        {
            case "2":
                json = ApiHelper.SearchBacklinks(searchvalue, Request.QueryString["next"]);
                searchvalue = json["query"].ToString();
                sb.Append(BuildTable(json));
                break;
            case "3":
                json = ApiHelper.SearchJustDiscovered(searchvalue, Request.QueryString["next"], 0);
                searchvalue = json["query"].ToString();
                sb.Append(BuildTable(json));
                break;
            case "4":
                json = ApiHelper.SearchTopRankedPages(searchvalue, Request.QueryString["next"]);
                searchvalue = json["query"].ToString();
                sb.Append(BuildTable2(json));
                break;
            case "5":
                json = ApiHelper.SearchTopRankedBacklinks(searchvalue, Request.QueryString["next"]);
                searchvalue = json["query"].ToString();
                sb.Append(BuildTable3(json));
                break;
            default:
                json = ApiHelper.SearchTopReferringDomains(searchvalue, Request.QueryString["next"]);
                searchvalue = json["query"].ToString();
                sb.Append(BuildTable(json));
                break;
        }
        sb.Append("<div class='nextup'>");
        if (!String.IsNullOrEmpty(Request.QueryString["next"]))
        {
            sb.Append("<a href='javascript:prev();'>Previous Page</a>");
        }
        if (!String.IsNullOrEmpty(Request.QueryString["next"]) && json["next"] != null && !String.IsNullOrEmpty(json["next"].ToString()))
        {
            sb.Append(" &nbsp; - &nbsp; ");
        }
        if (json["next"] != null && !String.IsNullOrEmpty(json["next"].ToString()))
        {
            sb.Append("<a href='?f=" + Request.QueryString["f"] + "&url=" + Uri.EscapeDataString(searchvalue) + "&next=" + json["next"] + "'>Next Page</a>");
        }
        sb.Append("</div>");
         result = sb.ToString();
    }
</script>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Free Backlink Checker (Examples from https://siteexplorer.info/)</title>
    <meta name="description" content="Free Backlink Checker (Examples from https://siteexplorer.info/)" />
    <meta name="keywords" content="SiteExplorer, Backlink Checker" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
* {
	margin: 0;
	padding: 0;
	border: 0;
	font-family: Arial;
}
body {
	margin: 0;
	padding: 0;
	background: #336699;
	font-family: Verdana,Arial,sans-serif;
	font-size: 13px;
	color: #fff;
}
.main {
	width: 1000px;
	margin: 0 auto;
}
.content {
	width: 100%;
	padding: 0;
	margin: 0;
	background: #336699;
}
#footer {
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	margin: 0 auto;
	width: 600px;
	padding: 5px 0 5px 0;
	background: #003366;
	text-align: center;
	color: #ffffff;
}
.result {
	border-left: 1px solid gray;
	border-right: 1px solid gray;
	width: 1000px;
	margin: 0 auto;
	text-overflow: ellipsis;
	background: #fff;
	color: #333;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
}
.result table {
	width: 100%;
	border-collapse: collapse;
	table-layout: fixed;
	background-color: White;
	line-height: 30px;
}
.result tr {
	cursor: pointer;
}
.details tr {
	cursor: default;
}
.result tr.header {
	cursor: default;
}
.result tr.tr-jsh {
	background: #efefef;
}
.result tr td {
	font-size: 14px;
	text-align: center;
	height: 25px;
	overflow: hidden;
	font-size: 14px;
	white-space: nowrap;
	border-right: 1px dotted #ccc;
}
.details table {
	width: 100%;
	border-collapse: collapse;
	table-layout: fixed;
	background-color: White;
	border: 2px solid #cccccc;
}
.result tr.tr-osh {
	background: #f2fdfd;
}
.details tr td {
	text-indent: 5px;
	height: 20px;
	font-size: 12px;
	text-overflow: ellipsis;
	-o-text-overflow: ellipsis;
	white-space: nowrap;
}
.details tr th {
	border-right: 1px solid #fff;
}
.result tr .tab {
	width: 39px;
}
.result tr .rank {
	width: 80px;
	text-align: center;
	font-size: 18px;
	color: green;
	border-bottom: 0;
	border-right: 1px dotted #cccccc;
}
.details tr .rank {
	font-size: 12px;
	width: 40px;
}
.details tr .url {
	width: 450px;
	line-height: 20px;
	padding-top: 5px;
	text-align: left;
	text-overflow: ellipsis;
	-o-text-overflow: ellipsis;
	white-space: pre-line;
	text-decoration:underline;
	color:blue;
	cursor:pointer;	
}
.result tr .anchor {
	width: 210px;
	text-indent: 10px;
	text-align: left;
	text-overflow: ellipsis;
	-o-text-overflow: ellipsis;
	white-space: nowrap;
}
.details tr .anchor {
	width: 258px;
	text-overflow: ellipsis;
	-o-text-overflow: ellipsis;
	white-space: nowrap;
}
.details tr .follow {
	width: 40px;
	padding: 0;
	margin: 0;
}
.details tr .date {
	width: 75px;
	padding: 0;
	margin: 0;
}
.result tr .domain {
	width: 430px;
	padding-left: 10px;
	text-align: left;
}
.result tr .dcount {
	width: 120px;
	color: green;
}
.result tr .home {
	width: 120px;
	border-right: 0;
}
.result tr.header th {
	height: 40px;
	color: #4e7fa4;
	font-size: 14px;
	height: 25px;
	border-bottom: 1px solid #cccccc;
	border-top: 1px solid #cccccc;
	text-align: center;
	padding: 0px;
	border-right: 1px solid #fff;
}
.details tr.header th {
	height: 14px;
	font-size: 12px;
	line-height: 14px;
	color: #fcfcfc;
	background-color: #dddddd;
}
.details tr.header .rank {
	width: 40px;
	border-bottom: 0;
	border-right: 1px solid #fff;
}
.result tr .url2 {
	width: 410px;
	padding-left: 10px;
	text-align: left;
}
.result tr .date2 {
	width: 140px;
	text-align: left;
}
a {
	color: #498ac1;
}
a {
	color: rgb(73, 138, 193);
}
#logo {
	margin-top: 20px;
	width: 350px;
	margin: 0 auto;
}
#sbox .fbox {
	margin: 0 auto;
	height: 70px;
	width: 760px;
	margin: 0 auto;
	margin-top: 10px;
	background-color: #003366;
	padding: 15px 0 15px 15px;
	padding-bottom: 0px;
	position: relative;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
	margin-bottom: 15px;
}
#sbox .fbox input {
	line-height: 30px;
	height: 25px;
	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
}
#sbox .fbox input[type=text] {
	padding-left: 5px;
	border: 1px solid #1962A5;
	margin: 0px;
	width: 610px;
	font-size: 14px;
	color: blue;
}
#sbox .fbox input[type=submit] {
	margin-left: 5px;
	background-color: #EE6911;
	bottom: 1px;
	right: 5px;
	width: 105px;
	height: 30px;
	text-transform: uppercase;
	font-size: 18px;
	cursor: pointer;
}
.meta
{
	border-left: 1px solid gray;
	border-right: 1px solid gray;
	width: 1000px;
	margin: 0 auto;
	text-overflow: ellipsis;
	background: #fff;
	color: #333;
}
.meta h1
{
    padding:5px;
    font-size:18px;
}
.meta table
{
    width:100%;
	border-collapse: collapse;
	margin-top:5px;
	padding:5px;
}
.meta table .hd
{
    padding-left:5px;
    text-align:left;
}
.meta table td 
{
    border:1px solid gray;
	vertical-align:top;
}
.meta table td  
{
    border:1px solid gray;
	vertical-align:middle;
}
.meta tr td {
	font-size: 14px;
	text-align: center;
	height: 25px;
	overflow: hidden;
	white-space: nowrap;
	border-right: 1px dotted #ccc;
}
.meta ul
{
    list-style:none;
    padding:0;
}
.meta ul li
{
	font-size: 14px;
    display:inline-block;
    min-width:20px;
    padding:5px;
}
.meta ul li i
{
    font-style:normal;
    text-decoration:underline;
    padding-right:5px;
}
.link, .link a
{
    color:Gray;
    padding: 10px;
}
.nextup
{
    display:block;
    background-color:#dddddd;
	margin: 0 auto;
	width:300px;
	height:30px;
	text-align:center;
}
#menu
{
    margin-top:5px;
}
#menu a
{
    margin-right:10px;
}
</style>
<script>

    function trim(val) {
        return val.replace(/^(\s+)|(\s+)$/g, '');
    }
    function zk(o) {
        var sid = o.getAttribute("sid");
        var s = document.getElementById(sid);
        if (s.style.display != "none") {
            s.style.display = "none";
        } else {
            s.style.display = "block";
        }
    }
    function search(f) {
        var o = document.getElementById('url');
        var u = trim(o.value);
        if (u == '' || u.indexOf('.') == -1 || o.value == o.title) {
            o.style.borderColor = 'red';
            return;
        }
        var p = u.indexOf('//');
        if (p != -1) {
            u = u.substr(p + 2);
            if(u.indexOf('/')==-1) u = u + '/';
        }
        p = u.indexOf('/');
        if (p != -1) {
            u = 'http://' + u.substr(0, p).toLowerCase() + u.substr(p);
        }
        else {
            u = u.toLowerCase();
        }
        location.href = f.action + '&url=' + escape(u);
    }
    function ov(o) {
        var t = document.getElementById('tip');
        t.style.display = 'block';
        if (o.value == o.title) {
            o.value = '';
        }
    }
    function ou(o) {
        var t = document.getElementById('tip');
        t.style.display = 'none';
        if (o.value == '') {
            o.value = o.title;
        }
    }
    function init() {
        var t = document.getElementById('tip');
        var o = document.getElementById('url');
        o.title = t.children[0].innerText; 
        if (o.value == '') {
            o.value = o.title;
        }
        o.addEventListener("mouseover", function (event) {
            ov(this);
        });
        o.addEventListener("mouseout", function (event) {
            ou(this);
        });
    }
    function prev() {
        if (document.referrer && document.referrer.indexOf('//'+location.host) > 0) {
            history.back(-1);
        }
    }
    function go(o) {
        location.href = o.innerText.replace(':\/\/ ', ':\/\/').replace(/\. /g, '.');
    }
</script>
</head>
<body onload='init()'>
    <div class="main">
        <div id="logo">
        <a href="https://siteexplorer.info/"><img src="https://siteexplorer.info/static/logo.gif" alt="SiteExplorer" /></a>
        </div>
        <div id="sbox">
            <form id="fbox" class="fbox" action="?f=<%=Request.QueryString["f"] %>" method="get" onsubmit="search(this);return false;">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <div id="tip" style="position: absolute; margin-top: -30px; display: none">
                                <div style="background: rgb(237, 23, 240); padding: 2px 5px; border-radius: 2px;
                                    border: 1px solid rgb(153, 153, 153); border-image: none; line-height: 18px;
                                    font-size: 12px; box-shadow: 1px 2px #999999;">
                                    Enter a full URL (e.g. http://www.yourwebsite.com/) or a domain(e.g. domain:yourdomain.com)</div>
                                <img alt="" style="margin: 2px 0 0 20px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAGAQMAAAAmF1bBAAAAA3NCSVQICAjb4U/gAAAABlBMVEWZmZn///+D7jMZAAAAAnRSTlP/AOW3MEoAAAAJcEhZcwAALiMAAC4jAXilP3YAAAAfdEVYdFNvZnR3YXJlAE1hY3JvbWVkaWEgRmlyZXdvcmtzIDi1aNJ4AAAAFnRFWHRDcmVhdGlvbiBUaW1lADA0LzIwLzE3Fy008QAAABlJREFUCJljYAACNgYGOwbGfwz8/xjq/wEAEAUDzvKz7qUAAAAASUVORK5CYII=" />
                            </div>
                            <input name="url" tabindex="1" id="url" type="text" value="<%=searchvalue %>" />
                        </td>
                        <td>
                            <input type="submit" value="Search" />
                        </td>
                    </tr>
                    <tr>
                        <td><div id="menu">
                            <a href="?f=1&url=<%=searchvalue %>">Referring Domains</a> <a href="?f=2&url=<%=searchvalue %>">
                                All Backlinks</a> <a href="?f=3&url=<%=searchvalue %>">New Backlinks</a> <a href="?f=4&url=<%=searchvalue %>">
                                    Top Ranked Pages</a> <a href="?f=5&url=<%=searchvalue %>">Top Ranked Backlinked</a></div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
        </div>
        <div style="clear: both;">
        </div>
    </div>
    <div class="content">
        <%= result %>
    </div>
    <br />
    <div id="footer">
        <div class='link'><a href="https://siteexplorer.info/">SiteExplorer.Info</a> - <a href="#">Terms Condition & Privacy Policy</a> - <a href="#">Contact Us</a></div>
        &copy; 2013 - 2018 siteexplorer.info, All Rights Reserved.
    </div>
</body>
</html>