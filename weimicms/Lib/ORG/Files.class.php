<?php
class Files
{
	public function index($url, $size, $type, $users_id=0, $token='', $wecha_id='')
	{
		$url_array = explode(".", $url);
		$add["type"] = ($type ? $type : $url_array[count($url_array) - 1]);
		$add["size"] = $size;
		$add["url"] = $url;
		$add["users_id"] = ($users_id ? $users_id : 0);
		$add["token"] = ($token ? $token : 0);
		$add["wecha_id"] = ($wecha_id ? $wecha_id : 0);
		$add["upload_ip"] = get_client_ip();
		$add["time"] = time();
		$Files_id = M("Files")->add($add);
	}
}


?>
