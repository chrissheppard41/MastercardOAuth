<?php
include("OAuth.php");

$privateKey = "-----BEGIN PRIVATE KEY-----
<REQUIRED>
-----END PRIVATE KEY-----";

$consumerkey = "<REQUIRED>";

//GET
$method 	= "GET";
$url 		= "https://sandbox.api.mastercard.com/atms/v1/atm?Format=XML&PageOffset=0&PageLength=10&AddressLine1=70%20Main%20St&PostalCode=63366&Country=USA";
$keys 		= new OAuthKeys($consumerkey, $privateKey);
$mc2 		= new OAuth($keys);
echo '<xmp>' . $mc2->api($url, $method) . '</xmp>';


//POST
/*$method 	= "POST";
$url 		= "https://sandbox.api.mastercard.com/fraud/merchant/v1/termination-inquiry?Format=XML&PageLength=10&PageOffset=0";
$body 		= '<?xml version="1.0" encoding="Windows-1252"?><ns2:TerminationInquiryRequest xmlns:ns2="http://mastercard.com/termination"><AcquirerId>1996</AcquirerId><TransactionReferenceNumber>1</TransactionReferenceNumber><Merchant><Name>TEST</Name><DoingBusinessAsName>TEST</DoingBusinessAsName><PhoneNumber>5555555555</PhoneNumber><NationalTaxId>1234567890</NationalTaxId><Address><Line1>5555 Test Lane</Line1><City>TEST</City><CountrySubdivision>XX</CountrySubdivision><PostalCode>12345</PostalCode><Country>USA</Country></Address><Principal><FirstName>John</FirstName><LastName>Smith</LastName><NationalId>1234567890</NationalId><PhoneNumber>5555555555</PhoneNumber><Address><Line1>5555 Test Lane</Line1><City>TEST</City><CountrySubdivision>XX</CountrySubdivision><PostalCode>12345</PostalCode><Country>USA</Country></Address><DriversLicense><Number>1234567890</Number><CountrySubdivision>XX</CountrySubdivision></DriversLicense></Principal></Merchant></ns2:TerminationInquiryRequest>';

$keys 		= new OAuthKeys($consumerkey, $privateKey);
$mc2 		= new OAuth($keys);
echo '<xmp>' . $mc2->api($url, $method, $body) . '</xmp>';*/

?>
