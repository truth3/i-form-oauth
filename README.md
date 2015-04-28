# iFormOAuth

<p>Package is designed to help jumpstart development with iFormBulder API by providing an easy interface for generating access tokens.</p> 

<h2>Example Use:</h2>


<pre>

use iForm\Auth\iFormTokenResolver;

//instantiate iFormTokenResolver with required parameters and call getToken() method

$url = "https://SERVER_NAME.iformbuilder.com/exzact/api/oauth/token";
$client = "XXXXXX";
$secret = "XXXXXX";

$token = (new iFormTokenResolver($url, $client, $secret))->getToken(); 


</pre>

<h2>API Access Requirement</h2>

<p>See how to find your credentials: <a href="https://iformbuilder.zendesk.com/hc/en-us/articles/201702900-What-are-the-API-Apps-Start-Here-">here</a></p>

<h2>Licenses</h2>
<a href="http://opensource.org/licenses/MIT">MIT (open source)</a>
