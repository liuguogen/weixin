<?php
class ImgReply
{
	public $item;
	public $wechat_id;
	public $siteUrl;
	public $token;
	public $action;

	public function __construct($token, $wechat_id, $data, $siteUrl, $key)
	{
		$this->wechat_id = $wechat_id;
		$this->siteUrl = $siteUrl;
		$this->token = $token;
		$keyword = $key;
		$this->db = M($data['module']);
		$like['keyword'] = $keyword;
		$like['precisions'] = 1;
		$like['token'] = $this->token;
		$data2 = M('keyword')->where($like)->order('id desc')->find();

		if (!$data2) {
			$like['keyword'] = array('like', '%' . $keyword . '%');
			$like['precisions'] = 0;
			$data2 = M('keyword')->where($like)->order('id desc')->find();
		}

		$this->item = M($data2['module'])->field('id,text,pic,url,title')->limit(9)->order('usort desc')->where($like)->select();
		$this->action = A('Home/Weixin');
	}

	public function index()
	{
		$this->action->api('requestdata', 'imgnum');
		$idsWhere = 'id in (';
		$comma = '';

		foreach ($this->item as $keya => $infot) {
			$idsWhere .= $comma . $infot['id'];
			$comma = ',';

			if ($infot['url'] != false) {
				if (!(strpos($infot['url'], 'http') === false)) {
					$url = $this->action->api('getFuncLink', html_entity_decode($infot['url']));
				}
				else {
					$url = $this->action->api('getFuncLink', $infot['url']);
				}
			}
			else {
				$url = rtrim($this->siteUrl, '/') . U('Wap/Index/content', array('token' => $this->token, 'id' => $infot['id'], 'wecha_id' => $this->data['FromUserName']));
			}

			$url = str_replace(array('{changjingUrl}'), array('http://www.meihua.com'), $url);
			$return[] = array($infot['title'], $this->action->api('handleIntro', $infot['text']), $infot['pic'], $url);
		}

		$idsWhere .= ')';

		if ($this->item) {
			$this->db->where($idsWhere)->setInc('click');
		}

		return array($return, 'news');
	}
}

echo "\r\n";

?>
