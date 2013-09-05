MastercardOAuth
===============

A Mastercard OAuth configuration script based on Googles OAuth, similar methods but cleaner


How to setup
------------

Include your script: include("OAuthPush.php"); for example

Initialise your Key storage object:
$keys 		= new OAuthKeys("Customer Key", "Private Key");

Initialise your OAuth object and pass in the Key object:
$mc 		  = new OAuth($keys);

Call the API method:
$mc->api("URL", "Request method: POST|GET|PUT|DELETE", "(OPTIONAL) Request Body (used in PUT and POST Requests)")


Examples
--------

A GET Request

$privateKey = "-----BEGIN PRIVATE KEY-----
......
-----END PRIVATE KEY-----";
$consumerkey = "<YOUR CUSTOMER KEY>";

Using the sandbox example code here:
https://developer.mastercard.com/portal/display/api/OAuth+Validation

$method 	= "GET";
$url 		  = "https://sandbox.api.mastercard.com/atms/v1/atm?Format=XML&PageOffset=0&PageLength=10&AddressLine1=70%20Main%20St&PostalCode=63366&Country=USA";
$keys 		= new OAuthKeys($consumerkey, $privateKey);
$mc2 		  = new OAuth($keys);
echo '<xmp>' . $mc2->api($url, $method) . '</xmp>';



A POST/PUT Request


$privateKey = "-----BEGIN PRIVATE KEY-----
......
-----END PRIVATE KEY-----";

$consumerkey = "<YOUR CUSTOMER KEY>";


Using the sandbox example code here:
https://developer.mastercard.com/portal/display/api/OAuth+Validation

$method 	= "POST";

$url 		  = "https://sandbox.api.mastercard.com/fraud/merchant/v1/termination-inquiry?Format=XML&PageLength=10&PageOffset=0";

$body 		= '<?xml version="1.0" encoding="Windows-1252"?><ns2:TerminationInquiryRequest xmlns:ns2="http://mastercard.com/termination"><AcquirerId>1996</AcquirerId><TransactionReferenceNumber>1</TransactionReferenceNumber><Merchant><Name>TEST</Name><DoingBusinessAsName>TEST</DoingBusinessAsName><PhoneNumber>5555555555</PhoneNumber><NationalTaxId>1234567890</NationalTaxId><Address><Line1>5555 Test Lane</Line1><City>TEST</City><CountrySubdivision>XX</CountrySubdivision><PostalCode>12345</PostalCode><Country>USA</Country></Address><Principal><FirstName>John</FirstName><LastName>Smith</LastName><NationalId>1234567890</NationalId><PhoneNumber>5555555555</PhoneNumber><Address><Line1>5555 Test Lane</Line1><City>TEST</City><CountrySubdivision>XX</CountrySubdivision><PostalCode>12345</PostalCode><Country>USA</Country></Address><DriversLicense><Number>1234567890</Number><CountrySubdivision>XX</CountrySubdivision></DriversLicense></Principal></Merchant></ns2:TerminationInquiryRequest>';


$keys 		= new OAuthKeys($consumerkey, $privateKey);

$mc2 		  = new OAuth($keys);
echo '<xmp>' . $mc2->api($url, $method, $body) . '</xmp>';
