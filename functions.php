<?php


	function insertMagicBricksInfoIntoDb($arr)
	{

		global $db;
		
		$table="society_info";
		
		$data=array(
			'keyword'=>$arr['keyword'],
			'used_keyword'=>$arr['used_keyword'],
			'input_city'=>$arr['input_city'],
			'google_search_result_link'=>$arr['google_search_result_link'],
			'project_name'=>$arr['project_name'],
			'project_data'=>$arr['project_data'],
			'project_city'=>$arr['project_city'],
			'project_locality'=>$arr['project_locality'],
			'project_unit'=>$arr['project_unit'],
			'project_tower'=>$arr['project_tower'],
			'project_currency'=>$arr['project_currency'],
			'project_min_price'=>$arr['project_min_price'],
			'project_max_price'=>$arr['project_max_price'],
			'project_min_price_display'=>$arr['project_min_price_display'],
			'project_max_price_display'=>$arr['project_max_price_display'],
			'project_min_price_per_sqft'=>$arr['project_min_price_per_sqft'],
			'project_max_price_per_sqft'=>$arr['project_max_price_per_sqft'],
			'created_at'=>$arr['created_at']
			
		);

		$db->insert($table,$data );

	}


	function getGoogleSearchResult($in)
	{
		global $current_time,$site_config;
		$in = str_replace(' ','+',$in); // space is a +

		$url="https://www.google.com/search?q=".$in."&oq=".$in;

		//$url="sample-html-files/google-search-result-html.html";

		$html = voku\helper\HtmlDomParser::file_get_html($url);

		$i=0;
		$linkObjs = $html->find('h3.r a');

		$arr=array();
		foreach ($linkObjs as $linkObj) {
			$arr_loc=array();
			$title = trim($linkObj->plaintext);
			$link  = trim($linkObj->href);

			$arr_loc['title']=$title;
			$arr_loc['link_ori']=$link;

			// if it is not a direct link but url reference found inside it, then extract
			if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
				$link = $matches[1];
			} else if (!preg_match('/^https?/', $link)) { // skip if it is not a valid link
				continue;
			}

			$arr_loc['link']=$link;

			$descr = $html->find('span.st',$i); // description is not a child element of H3 thereforce we use a counter and recheck.

			$arr_loc['desc']=(string)$descr;

			$arr[]=$arr_loc;
			$i++;

		}

		return $arr;

	}


	function getMagicBricks($url,$i)
	{
		global $current_time,$site_config;
		$arr=array();

		//$url="sample-html-files/magicbricks-html.html";

		$html = voku\helper\HtmlDomParser::file_get_html($url);

		$project_name_h1_objs=$html->find('.projectName .h1Important .h1Block h1');
		$arr['project_name']="";
		foreach ($project_name_h1_objs as $project_name_h1_obj)
		{
			$arr['project_name']=$project_name_h1_obj->children(0)->outerhtml;
		}
		$project_data_objs=$html->find('#projectData');
		$arr['project_data']="";
		foreach ($project_data_objs as $project_data_obj)
		{
			$arr['project_data']=$project_data_obj->attr['value'];
		}

		$project_city_data_objs=$html->find('#cityIdData');
		$arr['project_city']="";
		foreach ($project_city_data_objs as $project_city_data_obj)
		{
			$arr['project_city']=$project_city_data_obj->attr['value'];
		}

		$project_locality_data_objs=$html->find('#localityIdData');
		$arr['project_locality']="";
		foreach ($project_locality_data_objs as $project_locality_data_obj)
		{
			$arr['project_locality']=$project_locality_data_obj->attr['value'];
		}

		$arr['price_info']=$price_detail_arr=getPriceDetailFromMagicBricks($html);

		$arr['project_info']=$project_info_arr=getProjectDetailFromMagicBricks($html);

		return $arr;


	}


	function getProjectDetailFromMagicBricks($html)
	{
		$project_info_cont_heading_objs = $html->find('.newPiceBlock th.newPiceBlockSec');

		$arr=array(
			'project_unit'=>"",
			'project_tower'=>""
		);
		$i=0;
		foreach ($project_info_cont_heading_objs as $project_info_cont_heading_obj) {

			if(strtolower($project_info_cont_heading_obj->plaintext)=='price')
			{

			}

			if(strtolower($project_info_cont_heading_obj->plaintext)=='project info')
			{
				$project_info_cont_value_objs = $html->find('.newPiceBlock td.newPiceBlockSec',$i);

				$project_info_value_str= $project_info_cont_value_objs->plaintext;

				$project_info_value_str=preg_replace('/\s+/', ' ',$project_info_value_str);

				$project_info_value_str_exp=explode(' ',$project_info_value_str);

				if(strtolower($project_info_value_str_exp[1])=='units')
				{
					$arr['project_unit']=strtolower($project_info_value_str_exp[0]);
				}
				if(strtolower($project_info_value_str_exp[3])=='towers')
				{
					$arr['project_tower']=strtolower($project_info_value_str_exp[2]);
				}

				if(strtolower($project_info_value_str_exp[3])=='units')
				{
					$arr['project_unit']=strtolower($project_info_value_str_exp[2]);
				}
				if(strtolower($project_info_value_str_exp[1])=='towers')
				{
					$arr['project_tower']=strtolower($project_info_value_str_exp[0]);
				}

			}

			$i++;

		}

		return $arr;

	}


	function getPriceDetailFromMagicBricks($html)
	{

		$arr=array(
			'currency'=>"",
			'min_price'=>"0",
			'max_price'=>"0",
			'min_price_display'=>"",
			'max_price_display'=>"",
			'min_price_per_sqft'=>"0",
			'max_price_per_sqft'=>"0",
		);

		$project_meta_cont_objs = $html->find('.projectName div meta');

		foreach ($project_meta_cont_objs as $project_meta_cont_obj) {

			//echo $project_meta_cont_obj->getAttribute('itemprop');

			if ($project_meta_cont_obj->attr['itemprop']  && trim($project_meta_cont_obj->attr['itemprop'])=='priceCurrency') {
				$arr['currency']=$project_meta_cont_obj->attr['content'];
			}

			if ($project_meta_cont_obj->attr['itemprop']  && trim($project_meta_cont_obj->attr['itemprop'])=='minPrice') {
				$arr['min_price']=$project_meta_cont_obj->attr['content'];
			}

			if ($project_meta_cont_obj->attr['itemprop']  && trim($project_meta_cont_obj->attr['itemprop'])=='maxPrice') {
				$arr['max_price']=$project_meta_cont_obj->attr['content'];
			}

		}


		$project_price_objs=$html->find('.projectPriceCont .projectPrice');

		foreach ($project_price_objs as $project_price_obj)
		{

			$project_price_cont=$project_price_obj->plaintext;

			$project_price_cont = preg_replace('/[[:^print:]]/', "", $project_price_cont);

			$project_price_cont = preg_replace('/&[\s\S]+?;/', '', $project_price_cont);

			$project_price_cont_exp=explode('-',$project_price_cont);

			if (strpos(strtolower(trim($project_price_cont_exp[0])), 'call') === false)
			{
				if (trim($project_price_cont_exp[0]))
					$arr['min_price_display'] = trim($project_price_cont_exp[0]);
			}

			if (strpos(strtolower(trim($project_price_cont_exp[1])), 'call') === false)
			{

				if (trim($project_price_cont_exp[1]))
					$arr['max_price_display'] = trim($project_price_cont_exp[1]);

			}
		}

		$project_unit_price_objs=$html->find('.projectPriceCont .projectUPrice');

		foreach ($project_unit_price_objs as $project_unit_price_obj)
		{
			$project_unit_price_cont=$project_unit_price_obj->plaintext;

			$project_unit_price_cont = preg_replace('/[[:^print:]]/', "", $project_unit_price_cont);

			$project_unit_price_cont = preg_replace('/&[\s\S]+?;/', '', $project_unit_price_cont);

			if (strpos($project_unit_price_cont, 'sqft') !== false)
			{
				$project_unit_price_cont=str_replace('(','',$project_unit_price_cont);
				$project_unit_price_cont=str_replace(')','',$project_unit_price_cont);
				$project_unit_price_cont=str_replace('per','',$project_unit_price_cont);
				$project_unit_price_cont=str_replace('sqft','',$project_unit_price_cont);
				$project_unit_price_cont=str_replace(',','',$project_unit_price_cont);

				$project_unit_price_cont_exp=explode('-',$project_unit_price_cont);

				if(trim($project_unit_price_cont_exp[0]))
					$arr['min_price_per_sqft']=trim($project_unit_price_cont_exp[0]);

				if(trim($project_unit_price_cont_exp[1]))
					$arr['max_price_per_sqft']=trim($project_unit_price_cont_exp[1]);

			}


		}

		return $arr;

	}


