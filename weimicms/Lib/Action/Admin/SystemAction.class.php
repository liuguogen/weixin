<?php
/**
 *网站后台
 *@package 
 *@author 
 **/
class SystemAction extends BackAction{
	public $server_url;
	public $key;
	public $topdomain;
	public $dirtype;
	public function _initialize() {
		parent::_initialize();
		$this->server_url=trim(C('server_url'))；
		$this->key=trim(C('server_key'));
		$this->topdomain=trim(C('server_topdomain'));
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/Lib')&&is_dir($_SERVER['DOCUMENT_ROOT'].'/Lib')){
			$this->dirtype=2;
		}else {
			$this->dirtype=1;
		}
	}
	public function index(){
		$where['display']=1;
		$where['status']=1;
		$order['sort']='asc';
		$nav=M('node')->where($where)->order($order)->select();
		$this->assign('nav',$nav);
		$this->display();
	}

	public function menu(){
		if(empty($_GET['pid'])){
			$where['display']=2;
			$where['status']=1;
			$where['pid']=2;
			$where['level']=2;
			$order['sort']='asc';
			$nav=M('node')->where($where)->order($order)->select();
			$this->assign('nav',$nav);
		}
		$this->display();
	}

	public function main(){
		/*
		require_once('test.php');
		if (!class_exists('test')){
		$canEnUpdate=0;
		}else {
		$canEnUpdate=1;
		}
		*/
		$canEnUpdate=1;
		$this->assign('canEnUpdate',$canEnUpdate);
		$updateRecord=M('System_info')->order('lastsqlupdate DESC')->find();
		if ($updateRecord['lastsqlupdate']>$updateRecord['version']){
			$updateRecord['version']=$updateRecord['lastsqlupdate'];
		}
		$this->assign('updateRecord',$updateRecord);
		$this->display();
	}
function deletedir($dirname){
	$result = false;
	if(! is_dir($dirname)){
		echo " $dirname is not a dir!";
		exit(0);
	}
	$handle = opendir($dirname); //打开目录
	while(($file = readdir($handle)) !== false) {
		if($file != '.' && $file != '..'){ //排除"."和"."
			$dir = $dirname.DIRECTORY_SEPARATOR.$file;
			//$dir是目录时递归调用deletedir,是文件则直接删除
			is_dir($dir) ? deletedir($dir) : unlink($dir);
		}
	}
	closedir($handle);
	$result = rmdir($dirname) ? true : false;
	return $result;
}
function weimicms_getcontents($url){
	if (function_exists('curl_init')){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}else {
		$str=file_get_contents($url);
		return $str;
	}
}