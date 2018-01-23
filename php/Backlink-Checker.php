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
include ('ApiHelper.php');
function GET($name) {
    if (!empty($_GET[$name])) return $_GET[$name];
    return null;
}
function BuildMeta($url) {
    $json = ApiHelper::GetMetaInfo($url);
    $sb = "<div class='meta'>";
    $s = $json->query;
    $m = $json->meta;
    $sb = $sb . "<table>
        " . "<tr style='font-weight:bold'>
          <td>Page Rank</td>" . "<td>Referring domains</td>" . "<td>Inbound Backlinks</td>" . "<td>Network IPs</td>" . "<td>Class C Network Subnets</td>" . "<td>Text Links</td>" . "<td>Home Pages</td>" . "<td>No Follows</td>" . "
        </tr><tr>
          " . "<td>" . $m->rank . "</td>" . "<td>" . $m->domains . "</td>" . "<td>" . $m->links . "</td>" . "<td>" . $m->ips . "</td>" . "<td>" . $m->subnets . "</td>" . "<td>" . $m->textlinks . "</td>" . "<td>" . $m->homepages . "</td>" . "<td>" . $m->nofollow . "</td>" . "
        </tr>
      </table>";
    $s1 = "<table>
        <tr>
          <td class='hd'>
            <b>TLD</b>
          </td>";
    $s2 = "<tr>
            <td class='hd'>
              <b>Link Count</b>
            </td>";
    $c = 0;
    foreach ($json->domains as $js) {
        $s1 = $s1 . "<td>" . $js->domain . "</td>";
        $s2 = $s2 . "<td>" . $js->count . "</td>";
        if (++$c == 20) break;
    }
    $s1 = $s1 . "
          </tr>";
    $s2 = $s2 . "
        </tr>
      </table>";
    $sb = $sb . $s1 . $s2;
    $s1 = "<table>
        <tr>
          <td class='hd'>
            <b>Country</b>
          </td>";
    $s2 = "<tr>
            <td class='hd'>
              <b>Link Count</b>
            </td>";
    $c = 0;
    foreach ($json->countries as $js) {
        $s1 = $s1 . "<td>" . $js->country . "</td>";
        $s2 = $s2 . "<td>" . $js->count . "</td>";
        if (++$c == 20) break;
    }
    $s1 = "
          </tr>";
    $s2 = "
        </tr>
      </table>";
    $sb = $sb . $s1 . $s2;
    $sb = $sb . "<ul>
        <li>
          <b>Keywords:</b>
        </li>";
    foreach ($json->keywords as $js) {
        $sb = $sb . "<li>
          <i>" . $js->keyword . "</i>(" . $js->count . ")
        </li>";
    }
    $sb = $sb . "
      </ul>";
    $sb = $sb . "
    </div>";
    return $sb;
}
function Mark($u) {
    // return $u; // show the full url link.
    return str_replace(".", ". ", str_replace("://", ":// ", $u)); // destory the full url link and show as text only.
    
}
function BuildTable($json) {
    $sb = "<div class='result'>
      <table class='item'>
        <tr class='header'>
          <th class='rank'>
            Rank</td><th class='domain'>Referring Domains</th><th class='anchor'>Anchor Text</th><th class='dcount'>Backlinks</th><th class='home'>Home Page</th>
        </tr>";
    if (count($json->results) == 0) {
        $sb = $sb . "<tr>
          <td colspan='5'>No result yet, come back later.</td>
        </tr>";
    } else {
        foreach ($json->results as $result) {
            $sb = $sb . "<tr onclick='go(this);return false;' style='background-color:#cccccc;font-size:12px;color:#000000'>
          ";
            $sb = $sb . "<td colspan='5'>
            <i>Backlinks for page: " . $result->backlinksfor . "</i>
          </td>";
            $sb = $sb . "
        </tr>";
            $i = 0;
            foreach ($result->domains as $domain) {
                $sb = $sb . "<tr class='" . ($i % 2 == 0 ? "" : " tr-jsh") . "' sid='xkey-" . $i . "' onclick='zk(this)' title='click to expand backlink details for this domain' state='0'>";
                $sb = $sb . "<td class='rank'>" . $domain->rank . "</td>" . "<td class='domain'>
            <a href='?f=1&url=domain:" . $domain->domain . "'>" . $domain->domain . "</a>
          </td>" . "<td class='anchor'>" . $domain->anchor . "</td>" . "<td class='dcount'>" . $domain->count . "+</td>" . "<td class='home'>" . $domain->homepage . "</td>";
                $sb = $sb . "
        </tr>";
                $sb = $sb . "<tr class='details " . ($i % 2 == 0 ? "tr-esh" : "tr-jsh") . "' id='xkey-" . $i . "'>";
                $sb = $sb . "<td colspan='5'>";
                $sb = $sb . "<table>";
                $sb = $sb . "<tr class='header'>
                <th class='tab'></th>
                <th class='rank'>Rank</th>
                <th class='url'>Inbound Links</th>
                <th class='anchor'>Anchor Text</th>
                <th class='follow'>Follow</th>
                <th class='date'>First Found</th>
                <th class='date'>Last Found</th>
              </tr>";
                $j = 0;
                foreach ($domain->pages as $page) {
                    $parts = explode('-', $page->date);
                    $sb = $sb . "<tr" . ($j % 2 == 1 ? "" : " class='tr-osh'") . ">
                <td class='tab'></td>
                <td class='rank'>" . $page->rank . "</td>
                <td class='url' onclick='go(this)'>" . Mark($page->url) . "</td>
                <td>" . $page->anchor . "</td>
                <td>" . $page->follow . "</td>
                <td>" . $parts[0] . "</td>
                <td>" . $parts[1] . "</td>
        </tr>";
                    $j++;
                }
                $sb = $sb . "
      </table>";
                $sb = $sb . "</td>";
                $sb = $sb . "</tr>";
                $i++;
            }
        }
    }
    $sb = $sb . "</table>
    </div>";
    return $sb;
}
function BuildTable2($json) {
    $sb = "<div class='result'>
      <table class='item'>
        <tr class='header'>
          <th class='rank'>
            Rank</td><th class='domain'>Page URL</th><th class='dcount'>Referrig Domains</th><th class='dcount'>Backlinks</th>
        </tr>";
    if (count($json->pages) == 0) {
        $sb = $sb . "<tr>
          <td colspan='4'>No result yet, come back later.</td>
        </tr>";
    } else {
        $i = 0;
        foreach ($json->pages as $page) {
            $sb = $sb . "<tr class='" . ($i % 2 == 0 ? "" : " tr-jsh") . "'>";
            $sb = $sb . "<td class='rank'>" . $page->rank . "</td>" . "<td class='url2'>
            <a href='?f=1&url=" . urlencode($page->url) . "'>" . $page->url . "</a>
          </td>" . "<td class='dcount'>" . $page->domains . "</td>" . "<td class='dcount'>" . $page->backlinks . "+</td>";
            $sb = $sb . "
        </tr>";
            $i++;
        }
    }
    $sb = $sb . "
      </table>
    </div>";
    return $sb;
}
function BuildTable3($json) {
    $sb = "<div class='result'>
      <table class='item'>
        <tr class='header'>
          <th class='rank'>
            Rank</td><th class='domain'>Backlink URL</th><th class='anchor'>Linked Anchor</th><th class='date2'>Date</th>
        </tr>";
    if (count($json->backlinks) == 0) {
        $sb = $sb . "<tr>
          <td colspan='4'>No result yet, come back later.</td>
        </tr>";
    } else {
        $i = 0;
        foreach ($json->backlinks as $backlink) {
            $sb = $sb . "<tr class='" . ($i % 2 == 0 ? "" : " tr-jsh") . "'>";
            $sb = $sb . "<td class='rank'>" . $backlink->rank . "</td>" . "<td class='url2'>
            <a href='?f=1&url=" . urlencode($backlink->url) . "'>" . $backlink->url . "</a>
          </td>" . "<td class='dcount'>" . $backlink->anchor . "</td>" . "<td class='date2'>" . $backlink->date . "</td>";
            $sb = $sb . "
        </tr>";
            $i++;
        }
    }
    $sb = $sb . "</table></div>";
    return $sb;
}
function init(&$searchvalue, &$result) {
    $searchvalue = GET("url");
    if ($searchvalue == "") return $searchvalue;
    $sb = BuildMeta($searchvalue);
    switch (GET("f")) {
        case "2":
            $json = ApiHelper::SearchBacklinks($searchvalue, GET("next"));
            $searchvalue = $json->query;
            $sb = $sb . BuildTable($json);
        break;
        case "3":
            $json = ApiHelper::SearchJustDiscovered($searchvalue, GET("next"), 0);
            $searchvalue = $json->query;
            $sb = $sb . BuildTable($json);
        break;
        case "4":
            $json = ApiHelper::SearchTopRankedPages($searchvalue, GET("next"));
            $searchvalue = $json->query;
            $sb = $sb . BuildTable2($json);
        break;
        case "5":
            $json = ApiHelper::SearchTopRankedBacklinks($searchvalue, GET("next"));
            $searchvalue = $json->query;
            $sb = $sb . BuildTable3($json);
        break;
        default:
            $json = ApiHelper::SearchTopReferringDomains($searchvalue, GET("next"));
            $searchvalue = $json->query;
            $sb = $sb . BuildTable($json);
        break;
    }
    $sb = $sb . "<div class='nextup'>
      ";
    if (GET("next") != "") {
        $sb = $sb . "<a href='javascript:prev();'>Previous Page</a>";
    }
    if (GET("next") && $json->next) {
        $sb = $sb . " &nbsp; - &nbsp; ";
    }
    if (!empty($json->next) && $json->next) {
        $sb = $sb . "<a href='?f=" . GET("f") . "&url=" . urlencode($searchvalue) . "&next=" . $json->next . "'>Next Page</a>";
    }
    $sb = $sb . "
    </div>";
    $result = $sb;
}
$result = "";
$searchvalue = "";
init($searchvalue, $result);
?>
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
          <a href="https://siteexplorer.info/">
            <img src="https://siteexplorer.info/static/logo.gif" alt="SiteExplorer" />
          </a>
        </div>
        <div id="sbox">
          <form id="fbox" class="fbox" action="?f="
            <?php echo GET("f") ?>" method="get" onsubmit="search(this);return false;">
            <table>
              <tbody>
                <tr>
                  <td>
                    <div id="tip" style="position: absolute; margin-top: -30px; display: none">
                      <div style="background: rgb(237, 23, 240); padding: 2px 5px; border-radius: 2px;
                                    border: 1px solid rgb(153, 153, 153); border-image: none; line-height: 18px;
                                    font-size: 12px; box-shadow: 1px 2px #999999;">
                        Enter a full URL (e.g. http://www.yourwebsite.com/) or a domain(e.g. domain:yourdomain.com)
                      </div>
                      <img alt="" style="margin: 2px 0 0 20px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAGAQMAAAAmF1bBAAAAA3NCSVQICAjb4U/gAAAABlBMVEWZmZn///+D7jMZAAAAAnRSTlP/AOW3MEoAAAAJcEhZcwAALiMAAC4jAXilP3YAAAAfdEVYdFNvZnR3YXJlAE1hY3JvbWVkaWEgRmlyZXdvcmtzIDi1aNJ4AAAAFnRFWHRDcmVhdGlvbiBUaW1lADA0LzIwLzE3Fy008QAAABlJREFUCJljYAACNgYGOwbGfwz8/xjq/wEAEAUDzvKz7qUAAAAASUVORK5CYII=" />
                    </div>
                    <input name="url" tabindex="1" id="url" type="text" value="<?php echo $searchvalue; ?>" />
                  </td>
                  <td>
                    <input type="submit" value="Search" />
                  </td>
                </tr>
                <tr>
                  <td>
                    <div id="menu">
                      <a href="?f=1&url=<?php echo $searchvalue; ?>">Referring Domains
                      </a>
                      <a href="?f=2&url=<?php echo $searchvalue; ?>">
                        All Backlinks
                      </a>
                      <a href="?f=3&url=<?php echo $searchvalue; ?>">New Backlinks
                      </a>
                      <a href="?f=4&url=<?php echo $searchvalue; ?>">
                        Top Ranked Pages
                      </a>
                      <a href="?f=5&url=<?php echo $searchvalue; ?>">Top Ranked Backlinked
                      </a>
                    </div>
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
        <?php echo $result; ?>
      </div>
      <br />
      <div id="footer">
        <div class='link'>
          <a href="https://siteexplorer.info/">SiteExplorer.Info</a> - <a href="#">Terms Condition & Privacy Policy</a> - <a href="#">Contact Us</a>
        </div>
        &copy; 2013 - 2018 siteexplorer.info, All Rights Reserved.
      </div>
    </body>
  </html>
