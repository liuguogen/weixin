<?php
class CustomAction extends WapAction{
	public $token;
	public $wecha_id;
	public $thisForm;
	private $field_db;
	private $info_db;
	private $limit_db;
	public  $isamap;
	public  $amap;

	public function __construct(){
		parent::_initialize();
        session('wapupload',1);
		$this->field_db		= M('custom_field');
		$this->info_db		= M('custom_info');
		$this->limit_db		= M('custom_limit');
		$this->token		= $this->_get('token');
		if (!defined('RES')){
			define('RES',THEME_PATH.'common');
		}
		//$this->wecha_id		= $this->wecha_id;
		if (!$this->wecha_id){
			$this->wecha_id	= 'null';
		}

		$this->thisForm 	= M('custom_set')->where(array('token'=>$this->token,'set_id'=>$this->_get('id','intval')))->find();

		$this->assign('token',$this->token);
		$this->assign('thisForm',$this->thisForm );
		$this->assign('wecha_id',$this->wecha_id);

		if (C('baidu_map')){
			$this->isamap=0;
		}else {
			$this->isamap=1;
			$this->amap=new amap();
		}
	}

	public function index(){
		$set_id 	= $this->_get('id','intval');
		$formData 	= $this->_createForms($this->token,$set_id);

		if(IS_POST){
			$limit_info = $this->limit_db->where(array('limit_id'=>$this->thisForm['limit_id']))->find();

			if($limit_info['enddate']){
				if($limit_info['enddate']<time()){
					$this->error('抱歉，时间已过期，无法提交');
				}
			}

			if($limit_info['today_total'] >0){
				$time 		= strtotime(date('Y-m-d')); //凌晨时间
				$total 		= $this->info_db->where(array('token'=>$this->token,'wecha_id'=>$this->wecha_id,'set_id'=>$this->thisForm['set_id'],'add_time'=>array('gt',$time)))->count();
				if($total >= $limit_info['today_total']){
					$this->error('抱歉，今日只能提交'.$limit_info['today_total'].'次');
				}
			}		

			if($limit_info['sub_total'] >0){
				$total 		= $this->info_db->where(array('token'=>$this->token,'set_id'=>$this->thisForm['set_id'],'wecha_id'=>$this->wecha_id))->count();
				if($total >= $limit_info['sub_total']){
					$this->error('抱歉，提交总数已经超过'.$limit_info['sub_total'].'次');
				}
			}
	
			$data['token']		= $this->token;
			$data['wecha_id']	= $this->wecha_id;
			$data['set_id']		= $set_id;
			$data['add_time']	= time();		
			$data['user_name']	= empty($this->fans['wechaname'])?'匿名':$this->fans['wechaname'];
			$data['phone']		= empty($this->fans['tel'])?'匿名':$this->fans['tel'];
			$data['sub_info']	= $this->_serializeSubInfo($this->_request(),$set_id);
			if($this->info_db->add($data)){
			Sms::sendSms($this->token, '你的表单“'.$this->thisForm['title'].'”中有新的信息'); //发送商家短信
			$info=M('deliemail')->where(array('token'=>$_POST['token']))->find();
			$mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
			$emailstatus=$info['wn'];
			$emailreceive=$info['receive'];
			$content = $this->sms();
			if($info['type'] == 1){
			$emailsmtpserver=$info['smtpserver'];
			$emailport=$info['port'];
			$emailsend=$info['name'];
			$emailpassword=$info['password'];
			}else{
			$emailsmtpserver=C('email_server');
			$emailport=C('email_port');
			$emailsend=C('email_user');
			$emailpassword=C('email_pwd');
			}
			$emailuser=explode('@', $emailsend);
			$emailuser=$emailuser[0];
			//if ($emailstatus == 1) {
				/*if ($content) {
					date_default_timezone_set('PRC');
					require("class.phpmailer.php");
					$mail = new PHPMailer();
					$mail->IsSMTP();                                      // set mailer to use SMTP
					$mail->Host = "$emailsmtpserver";  // specify main and backup server
					$mail->SMTPAuth = true;     // turn on SMTP authentication
					$mail->Username = "$emailuser"; // SMTP username
					$mail->Password = "$emailpassword"; // SMTP password
					$mail->From = $emailsend;
					$mail->FromName = C('site_name');
					$mail->AddAddress("314262515@qq.com", "商户");
					//$mail->AddAddress("ellen@example.com");                  // name is optional
					$mail->AddReplyTo($emailsend, "Information");

					$mail->WordWrap = 50;                                 // set word wrap to 50 characters
					//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
					//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
					$mail->IsHTML(false);                                  // set email format to HTML

					$mail->Subject = '您有新的预约产生';
					$mail->Body    = $content;
					$mail->AltBody = "";

					if(!$mail->Send())
					{
					   echo "没有设置发邮件邮箱. <p>";
					   echo "基本设置-邮箱配置-自定义邮箱设置: " . $mail->ErrorInfo;
					   exit;
					}
					//echo "Message has been sent";    
				}*/
			//}
				$this->success($this->thisForm['succ_info']);
			}else{
				$this->error($this->thisForm['err_info']);
			}
		}else{
			//提交记录
			$spoor = $this->info_db->where(array('token'=>$this->token,'wecha_id'=>$this->wecha_id,'set_id'=>$set_id))->count();
			$this->assign('spoor',$spoor);

			$this->assign('verify',$formData['verify']);
			$this->assign('formData',$formData['string']);
		  	$this->display();
		}
	}
	public function sms(){
		$this->info_db=M('custom_info');
		$data=$this->info_db->where(array('token'=>$_POST['token']))->order('date desc')->limit(0,1)->find();
			$str="\r\n订单编号：".$data['info_id']."\r\n姓名：".$data['user_name']."\r\n电话：".$data['phone']."\r\n预约时间：".$data['add_time']."\r\n备注信息：".$data['sub_info']."\r\n";
			return $str;
	}

	/*表单详情简介*/
	public function detail(){
		$this->display();
	}
	/*表单提交记录*/
	public function spoor(){
		$set_id = $this->_get('id','intval');
		$where 	= array('token'=>$this->token,'wecha_id'=>$this->wecha_id,'set_id'=>$set_id);
		$list	= $this->info_db->where($where)->order('add_time desc')->select();
	
		foreach($list as $key=>$value){
			$list[$key]['sub_info']	= unserialize($value['sub_info']);
		}
		$this->assign('set_id',$set_id);
		$this->assign('list',$list);
		$this->display();
	}
	/*地图位置*/
	public function maps(){	
		$this->apikey	= C('baidu_map_api');
		$this->assign('apikey',$this->apikey);

		if (!$this->isamap){
			$this->display();
		}else {			
			$link=$this->amap->getPointMapLink($this->thisForm['longitude'],$this->thisForm['latitude'],$this->thisForm['title']);
			header('Location:'.$link);
		}
	}
	/*创建序列化提交信息*/
	private function _serializeSubInfo($post,$set_id){
		$field_info = $this->field_db->where(array('token'=>$this->token,'set_id'=>$set_id))->field('field_name,item_name,field_type')->order('sort desc')->select();
		$info 		= array();
		foreach($field_info as $key=>$value){
			$info[$key]['name'] 	= $value['field_name'];
			if($value['field_type'] == 'checkbox'){
				$info[$key]['value']	= implode(',', $post[$value['item_name']]);
			}else{
				$info[$key]['value']	= $post[$value['item_name']];
			}
		}
		return serialize($info);
	}

	/*获取自定义表单字段信息*/
	private function _createForms($token,$set_id){

		$where	= array('token'=>$token,'set_id'=>$set_id,'is_show'=>'1');
		$forms 	= $this->field_db->where($where)->order('sort desc')->select();

		$str	= '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="kuang">';
		$arr 	= array();
		foreach($forms as $key=>$value){
			$str	.= '<tr><th>';
			$str	.= $value['field_name'];
			$str 	.= '</th><td>';
			$str	.= $this->_getInput($value);
			$str	.= '</td></tr>';

			$arr[] 	 = array('id'=>$value['item_name'],'name'=>$value['field_name'],'type'=>$value['field_type'],'match'=>$value['field_match'],'is_empty'=>$value['is_empty'],'err_info'=>$value['err_info']);  //js验证信息
		}
		$str	.= '</table>';
		return array('string'=>$str,'verify'=>$arr);
	}

	/*获取自定义表单*/
	private function _getInput($value){
		$input 	= '';
		switch($value['field_type']){
			case 'text':
				$input 	.= '<input type="text" class="px" id="'.$value['item_name'].'" name="'.$value['item_name'].'" value="">';
				break;
			case 'textarea':
				$input 	.= '<textarea name="'.$value['item_name'].'" id="'.$value['item_name'].'" rows="4" cols="25"  ></textarea>';
				break;
			case 'checkbox':
				$option = explode('|', $value['filed_option']);
				for($i=0;$i<count($option);$i++){
					$input 	.= '<input type="checkbox" name="'.$value['item_name'].'[]" id="'.$value['item_name'].'" value="'.$option[$i].'"  />'.$option[$i];
				}
				break;
			case 'radio':
				$option = explode('|', $value['filed_option']);
				for($i=0;$i<count($option);$i++){
					$checked = $i==0?'checked=true':'';
					$input 	.= '<input type="radio" name="'.$value['item_name'].'" id="'.$value['item_name'].'" value="'.$option[$i].'" '.$checked.' />'.$option[$i];
				}
				break;
			case 'select':
				$input 	.= '<select name="'.$value['item_name'].'" id="'.$value['item_name'].'"><option value="">请选择..</option>';
				$op_arr	= explode('|',$value['filed_option']);
				$num	= count($op_arr);
				if($num > 0){
					for($i=0;$i<$num;$i++){
						$input 	.= '<option value="'.$op_arr[$i].'">'.$op_arr[$i].'</option>';
					}
				}
				$input  .='</select>';
				break;
			case 'date':
				$input 	.= '<input type="text" class="px" name="'.$value['item_name'].'" id="'.$value['item_name'].'" value="'.date('Y-m-d',time()).'" onClick="WdatePicker()"/>';
            case 'file':
                $input  .= '<a href="###" onclick="upl()" class="a_upload" style="color:red"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" /></a>';
                $input  .= '<dd style="display:none" id="imgdd1" type="image"><img src="" class="portrait_src" id="portrait1_src" /><input class="portrait" type="hidden" value="" id="portrait1" name="'.$value['item_name'].'[]'.'" size="80" /></dd><dt><label> </label></dt><dd style="display:none" id="imgdd2" type="image"><img src="" class="portrait_src" id="portrait2_src" /><input class="portrait" type="hidden" value="" id="portrait2" name="'.$value['item_name'].'[]'.'" size="80" /></dd><dt><label> </label></dt><dd style="display:none" id="imgdd3" type="image"><img src="" class="portrait_src" id="portrait3_src" /><input class="portrait" type="hidden" value="" id="portrait3" name="'.$value['item_name'].'[]'.'" size="80" /></dd><dt><label> </label></dt><dd style="display:none" id="imgdd4" type="image"><img src="" class="portrait_src" id="portrait4_src" /><input class="portrait" type="hidden" value="" id="portrait4" name="'.$value['item_name'].'[]'.'" size="80" /></dd><dt><label> </label></dt>';
		}
		return $input;
	}

	function generateQRfromGoogle($chl,$widhtHeight ='150',$EC_level='L',$margin='0'){
		$chl = urlencode($chl);
    	$src='http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$chl;
    return $src;
	}

}
?>