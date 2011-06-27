<?xml version="1.0" encoding="{L_ENCODING}"?>
<!-- BEGIN switch_enable_xslt -->
<?xml-stylesheet type="text/xsl" href="templates/rss.xsl"?> 
<!-- END switch_enable_xslt -->
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:annotate="http://purl.org/rss/1.0/modules/annotate/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
<channel>
<title>{BOARD_TITLE}</title>
<link>{BOARD_URL}</link>
<description>{BOARD_DESCRIPTION}</description>
<managingEditor>{BOARD_MANAGING_EDITOR}</managingEditor>
<docs>http://blogs.law.harvard.edu/tech/rss</docs>
<generator>{PROGRAM}</generator>{LANGUAGE}
<lastBuildDate>{BUILD_DATE}</lastBuildDate>
<image>
	<url>{BOARD_URL}images/logo/logo.gif</url>
	<title>{BOARD_TITLE}</title>
	<link>{BOARD_URL}</link>
	<width>122</width>
	<height>56</height>
</image>
<!-- BEGIN post_item -->
<item>
<title>{post_item.FORUM_NAME} :: {post_item.TOPIC_TITLE}</title>
<link>{post_item.POST_URL}</link>
<pubDate>{post_item.UTF_TIME}</pubDate>
<guid isPermaLink="true">{post_item.POST_URL}</guid>
<description>{L_AUTHOR}: {post_item.AUTHOR}&lt;br /&gt;
{post_item.POST_SUBJECT}
{L_POSTED}: {post_item.POST_TIME}&lt;br /&gt;
&lt;br /&gt;&lt;span class="postbody"&gt;
{post_item.POST_TEXT}{post_item.USER_SIG}&lt;/span&gt;&lt;br /&gt;
</description>
<dc:creator>{post_item.AUTHOR0}</dc:creator>
<dc:subject>{post_item.FORUM_NAME}</dc:subject>
<annotate:reference rdf:resource="{post_item.FIRST_POST_URL}" />
<comments>{post_item.REPLY_URL}</comments>
</item>
<!-- END post_item -->
</channel>
</rss>