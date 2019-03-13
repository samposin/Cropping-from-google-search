<?php

	use voku\db\DB;
	include('config.php');
	require_once 'vendor/autoload.php';
	include('functions.php');

	ini_set("user_agent",'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

	//increase max execution time of this script to 150 min:
	ini_set('max_execution_time', 9000);

	//increase Allowed Memory Size of this script:
	ini_set('memory_limit','1024M');


	//$url='https://www.indiacom.com/yellow-pages/co-operative-housing-societies/pune/';


	$url="sample-html-files/indiacom.html";

	$html = voku\helper\HtmlDomParser::file_get_html($url);




	/*
	$file = fopen($site_config['abs_path']."/sample-html-files/indiacom.html","w");
	fwrite($file,$html->outerhtml);
	fclose($file);
	*/
	//echo  $html->outerHtml;

	$b_listing_objs=$html->find('.b_listing');

	foreach ($b_listing_objs as $b_listing_obj)
	{
		$society_info=array(
			'society_name'=>"",
			'society_address'=>"",
		);


		echo $b_listing_obj->outerhtml;

		$b_names=$b_listing_obj->find('.b_name a');

		foreach ($b_names as $b_name) {
			echo $b_name->outerhtml;
			//$b_name->attr['href'];
			echo $b_name->innerhtml;
			$society_info['society_name']=$b_name->innerhtml;

		}

		$b_listingdetail_addresses=$b_listing_obj->find('.b_listingdetail .b_address');

		foreach ($b_listingdetail_addresses as $b_listingdetail_address) {
			$society_info['society_address']=$b_listingdetail_address->plaintext;
		}

		$society_arr[]=$society_info;

	}

	echo '<pre>';
	print_r($society_arr);