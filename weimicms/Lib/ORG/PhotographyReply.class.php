<?php
class PhotographyReply
{	
	public $item;
	public $wechat_id;
	public $siteUrl;
	public $token;
	public function __construct($token,$wechat_id,$data,$siteUrl)
	{
		$this->item=M('Photography')->where(array(
			'id' => $data['pid']
		))->find();
		$this->wechat_id=$wechat_id;
		$this->siteUrl=$siteUrl;
		$this->token=$token;
	}
	public function index(){
		return array(
			array(
				array(
					$this->item['title'],
					strip_tags(htmlspecialchars_decode($this->item['word'])),
					$this->item['picurl'],
					$this->siteUrl . '/index.php?g=Wap&m=Photography&a=index&token=' . $this->token . '&wecha_id=' . $this->wechat_id . '&id=' . $this->item['id'] . ''
				)
			),
			'news'
		);
	}
}
?>

