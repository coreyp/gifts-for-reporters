<?PHP
/*
Quick PHP Gift Registry 
AKA DirtyGiftReg
https://github.com/this-is-ari/php-wedding-gift-registry

A very simple "mock" gift registry, which enables the collection of paypal payments intead of actual gifts.

@TODO:
- Process paypal IPN payments
- Clean up the Amazon data extraction
- Create proper thumbnails 

Copyright (c) 2013 https://github.com/this-is-ari

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

//////////////////////////////////////////
// Begin User configuration:
/////////////////////////////////////////
$paypal = array("email" => "user@paypal.com",
				"currency" => "USD",
				"country" => "CA",
				);



//////////////////////////////////////////
// End - no need to edit anything else...
/////////////////////////////////////////

$giftFilename = "gifts.txt";
$flatFilename = "flatfile.txt";
$templateFilename = "template.html";
$imageDirectory = "./images";
$flatFileCols = array (
					"{id}",
					"{title}",
					"{price}",
					"{description}",
					"{image}",
					"{paypal}",
				);

$template = file_get_contents($templateFilename);
$headerEndCharacter = strpos($template, "{gifts}");
$FooterStartCharacter = strpos($template, "{/gifts}");
$header = substr($template, 0, $headerEndCharacter);
$giftTemplate = substr($template, $headerEndCharacter + 7, $FooterStartCharacter-($headerEndCharacter + 7));
$footer = substr($template, $FooterStartCharacter + 8);

$giftsUrls = file($giftFilename);


// If the gift file has been updated
if ($giftsUrls[0][0] !== '#') {
	$row = 1;
	$productData = NULL;

	foreach ($giftsUrls as $giftUrl) {
		//@TODO - if we have retrieved information about this item before, skip it
		$giftData = file_get_contents($giftUrl);

		$dom = new DOMDocument;
		@$dom->loadHTMLFile($giftUrl);
		$xpath = new DOMXPath($dom);

		//PRICES
		$prices = $xpath->query( "//b[@class='priceLarge']" );
		foreach ($prices as $price) {
			$productPrice = $price->firstChild->nodeValue;
		}

		//DESCRIPTION
		$descriptions = $xpath->query( "//div[@class='productDescriptionWrapper']" );
		foreach ($descriptions as $description) {
			$productDescription = $description->firstChild->nodeValue;
		}

		//TITLE
		$titles = $xpath->query( "//span[@id='btAsinTitle']" );
		foreach ($titles as $title) {
			$productTitle = $title->firstChild->nodeValue;
		}

		//IMAGES
		$images = $xpath->query( "//img[@id='main-image']/@src" );
		foreach ($images as $image) {
    		$imageUrl = $image->firstChild->nodeValue;
    		if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    			$pathParts = pathinfo($imageUrl);
    			// If we don't have the image, download it
    			if (!file_exists($imageDirectory."/".$pathParts['basename'])) {
    				file_put_contents($imageDirectory."/".$pathParts['basename'], file_get_contents($imageUrl));
    				//Create thumbnail

    				}
    			}
    		else {
    			// If no image is founder
    		}
    		// add information to our flatfile
    		$giftsData .= $row.",\"".addslashes($productTitle)."\",".$productPrice.",\"".trim(addslashes(strip_tags($productDescription)))."\",\"".$pathParts['basename']."\"\n";
    		$row++;
		}
	}

	  array_unshift($giftsUrls, "#DELETE THIS LINE AND RE-SAVE TO UPDATE GIFT LIST"."\r\n");
	  file_put_contents($giftFilename, $giftsUrls);
	  file_put_contents($flatFilename, $giftsData);
}

echo $header;

// Output the body
switch (addslashes($_GET['q'])) {
		case "thank-you":
			echo "<h1>Thank you for your purchase!</h1>";
			break;

		case "PayPal_IPN":
			break;

		default:
			foreach ( str_getcsv ( file_get_contents( $flatFilename ), "\n" ) as $row ) {
			 			$gift = str_getcsv( $row, ',', '"'); 

			 			$paypalButton = '<form action="https://www.paypal.com/cgi-bin/webscr" class="pull-right" method="post" target="_top">
			 							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			 			 				<a href="mailto:admin@newoldmedia.net?subject='.$gift[1].'" class="btn btn-info">Give Time</a>
			 			 				<button type="submit" class="btn btn-primary" name="submit" >Give Money</button>
										<input type="hidden" name="cmd" value="_xclick">
										<input type="hidden" name="business" value="'.$paypal["email"].'">
										<input type="hidden" name="lc" value="'.$paypal["country"].'">
										<input type="hidden" name="item_number" value="'.$gift[0].'">
										<input type="hidden" name="item_name" value="'.$gift[1].'">
										<input type="hidden" name="amount" value="'.$gift[2].'">
										<input type="hidden" name="currency_code" value="'.$paypal["currency"].'">
										<input type="hidden" name="button_subtype" value="services">
										<input type="hidden" name="no_note" value="0">
										<input type="hidden" name="cn" value="Add special message:">
										<input type="hidden" name="no_shipping" value="1">
										<input type="hidden" name="rm" value="1">
										<input type="hidden" name="return" value="'.$_SERVER['REQUEST_URI'].'?q=thank-you">
										<input type="hidden" name="tax_rate" value="0.000">
										<input type="hidden" name="shipping" value="0.00">
										<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_SM.gif:NonHosted">
										<input type="hidden" name="notify_url" value='.$_SERVER['REQUEST_URI'].'?q=PayPal_IPN">
										<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
										</form>';
						array_push($gift,$paypalButton);
			 			$giftHtml = $giftTemplate;
			 			$giftHtml = str_replace($flatFileCols, $gift, $giftHtml);
			 			echo $giftHtml;
			 		}
 			break;
		
	}

 	echo $footer;
?>