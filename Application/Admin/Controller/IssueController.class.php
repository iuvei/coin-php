<?php
namespace Admin\Controller;

class IssueController extends AdminController
{
	 protected function _initialize(){
			// 获取用户的IP地址
$user_ip = $_SERVER['REMOTE_ADDR'];
$user_ip=substr($user_ip, 0, strrpos($user_ip, '.'));
// 允许访问后台的IP地址列表
// $allowed_ips = array('127.0.0.1');
// // 检查用户IP是否在允许列表中
// if (!in_array($user_ip, $allowed_ips)) {
//     // 如果不在允许列表中，显示错误信息或者跳转到其他页面
//     $this->error("页面不存在！".ACTION_NAME);
// }
		
	}
	//认购项目记录
	public function index(){
		$count = M('issue')->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('issue')->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	

	public function issueimage()
	{
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/public/';
		$upload->autoSub = false;
		
		$info = $upload->upload();
		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}
    
    //新增或编辑认购项目
	public function edit($id = null){
        $clist = M("coin")->where("type = 3 or type = 2")->order("id desc")->field("name,title")->select();
        $this->assign("clist",$clist);
        
        $paylist = M("coin")->where("type = 1 or type = 2")->order("id desc")->field("name,title")->select();
        $this->assign("paylist",$paylist);
        
        $alllist = M("coin")->order("id desc")->field("name,title")->select();
        $this->assign("alllist",$alllist);
        if($id > 0){
            $data = M("issue")->where(array('id'=>$id))->find();
            $this->assign('data',$data);
        }

		$this->display();
	}

    //处理新增或编辑认购项目
	public function save(){

        $tian = $_POST['tian'];
        $_POST['addtime'] = date("Y-m-d H:i:s",time());
        $_POST['finishtime'] = date("Y-m-d H:i:s",(strtotime($_POST['starttime']) + 86400 * $tian));
        $_POST['yuan_price']= $_POST['yuan_price'];
		if ($_POST['id']) {
			$rs = M('Issue')->save($_POST);
		} else {
			$rs = M('Issue')->add($_POST);
		}

		if ($rs) {
			$this->success('操作成功！');
		} else {
			$this->error('操作失败！');
		}
	}



	public function log($name=null){
		if($name != null){
		    $where['account'] = trim($name);
		}
		$count = M('issue_log')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('issue_log')->where($where)->order('O.id desc')->alias('as O') ->field('*,O.num as onum')//给表取个别名  ID逆序必须指定是哪个表 是o  还是  W
        ->join('LEFT JOIN `' . C('DB_PREFIX') . 'issue` AS W ON W.id = O.pid')->limit($Page->firstRow . ',' . $Page->listRows)->select();
       // var_dump("<pre>",$list);exit;
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();

	}


}
?>