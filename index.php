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


	$db = DB::getInstance($db_host,$db_user, $db_pass, $db_name);

	if(isset($_POST['btn_submit']))
	{
		$insert_arr=array();
		$keyword="";
		if(isset($_POST['keyword']) && trim($_POST['keyword'])!="")
			$keyword=$_POST['keyword'];

		$input_city='pune';

		if($keyword)
		{
			$insert_arr['keyword']=$keyword;

			$used_keyword=$keyword;
			if (strpos(strtolower($keyword), 'society') === false)
			{
				$used_keyword='society '.$keyword.' '.$input_city.' pdpid';
			}

			$insert_arr['used_keyword']=$used_keyword;

			$insert_arr['input_city']=$input_city;

			$google_search_arr=getGoogleSearchResult($used_keyword);

			for($i=0;$i<count($google_search_arr);$i++)
			{
				if(trim($google_search_arr[$i]['link'])!="")
				{
					if (strpos(strtolower(trim($google_search_arr[$i]['link'])), 'magicbricks') !== false)
					{
						if (strpos(strtolower(trim($google_search_arr[$i]['link'])), 'pdpid') !== false)
						{

							$google_search_result_link=trim($google_search_arr[$i]['link']);

							$insert_arr['google_search_result_link']=$google_search_result_link;

							$magic_bricks_result_arr=getMagicBricks($google_search_result_link,$i);

							$insert_arr['project_name']=$magic_bricks_result_arr['project_name'];
							$insert_arr['project_data']=$magic_bricks_result_arr['project_data'];
							$insert_arr['project_city']=$magic_bricks_result_arr['project_city'];
							$insert_arr['project_locality']=$magic_bricks_result_arr['project_locality'];

							$insert_arr['project_unit']=$magic_bricks_result_arr['project_info']['project_unit'];
							$insert_arr['project_tower']=$magic_bricks_result_arr['project_info']['project_tower'];

							$insert_arr['project_currency']=$magic_bricks_result_arr['price_info']['currency'];
							$insert_arr['project_min_price']=$magic_bricks_result_arr['price_info']['min_price'];
							$insert_arr['project_max_price']=$magic_bricks_result_arr['price_info']['max_price'];
							$insert_arr['project_min_price_display']=$magic_bricks_result_arr['price_info']['min_price_display'];
							$insert_arr['project_max_price_display']=$magic_bricks_result_arr['price_info']['max_price_display'];
							$insert_arr['project_min_price_per_sqft']=$magic_bricks_result_arr['price_info']['min_price_per_sqft'];
							$insert_arr['project_max_price_per_sqft']=$magic_bricks_result_arr['price_info']['max_price_per_sqft'];

							$insert_arr['created_at']=date('Y-m-d H:i:s');

							insertMagicBricksInfoIntoDb($insert_arr);

							//print_r($magic_bricks_result_arr);

							//ob_flush();
							//flush();

							sleep(20);

						}
					}
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
<div style="margin:0 auto;width:400px;margin-top:100px;">
	<form method="post">
		<table>
			<tr>
				<td><label for="keyword">Society Name </label><input id="keyword" name="keyword" type="text"> </td>
				<td><input type="submit" name="btn_submit" value="Submit"> </td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>

