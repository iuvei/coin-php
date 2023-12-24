<?php
namespace Admin\Controller;

class ArticleController extends AdminController
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
		parent::_initialize();
		$allow_action=array("index","edit","wenzhangimg","ggeditup","setstatus");
		if(!in_array(ACTION_NAME,$allow_action)){
			$this->error("页面不存在！");
		}
	}
	
	//删除公告记录
	public function setstatus($id=null){
	    $where = array();
        if(empty($id)){
            $this->error("缺少重要参数");exit();
        }
        $where['id'] = array('in',$id);
        $list = M("content")->where($where)->field("id,title")->select();
        if(!empty($list)){
            foreach($list as $key=>$vo){
                $oid = $vo['id'];
                M("content")->where(array('id'=>$oid))->delete();
            }
            $this->success("删除成功");exit();
        }else{
            $this->error("没有选择数据");exit();
        }
        
	}
	

	//公告中心
	public function index($name = NULL, $field = NULL, $status = NULL){
		$where = array();
		$count = M('content')->where($where)->count();
		$Page = new \Think\Page($count, 15);
		$show = $Page->show();
		$list = M('content')->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();
	}
	
    //新增或编辑公告页面
	public function edit($id = NULL, $type = NULL){
        
        if($id > 0){
            $info = M("content")->where(array('id'=>$id))->find();
            $this->assign('data',$info);
        }
        
		$this->display();
	}
	
	//新增或编辑处理
	public function ggeditup($title=null,$img=null,$content=null,$status=null,$id=null){
	    if($id <= 0){
	        $_POST['addtime']  = date("Y-m-d H:i:s",time());
	        $re = M("content")->add($_POST);
	        if($re){
	            $this->success("公告添加成功");
	        }else{
	            $this->error("公告添加失败");
	        }
	    }else{

	        $re = M("content")->where(array('id'=>$id))->save($_POST);
	        if($re){
	            $this->success("公告修改成功");
	        }else{
	            $this->error("公告修改失败");
	        }
	    }
	   
	}
	
	//上传图片
	public function wenzhangimg(){
		$upload = new \Think\Upload();
		$upload->maxSize = 3145728;
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');
		$upload->rootPath = './Upload/article/';
		$upload->autoSub = false;
		$info = $upload->upload();
		foreach ($info as $k => $v) {
			$path = $v['savepath'] . $v['savename'];
			echo $path;
			exit();
		}
	}

}

?>