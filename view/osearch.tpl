<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
	  <ShortName>Friendica Global Directory</ShortName>
	  <Description>Search Friendica Global Directory</Description>
	  <InputEncoding>UTF-8</InputEncoding>
      <Image width="16" height="16" type="image/x-icon">http://dir.friendica.com/images/friendica-16.ico</Image>
      <Image width="64" height="64" type="image/png">http://dir.friendica.com/images/friendica-64.png</Image>
	  <Url type="text/html" method="GET" template="http://dir.friendica.com/directory">
      <Param name="search" value="{searchTerms}"/>
		</Url>

	  <moz:SearchForm>http://dir.friendica.com</moz:SearchForm>
</OpenSearchDescription>
