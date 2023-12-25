<?php
/* API平台接口数据 */
namespace Home\Controller;

class ApiController extends HomeController
{
	protected function _initialize()
	{
		parent::_initialize();
		// $allow_action = array("ticker","getcoinprice","getindexdata","getissueinfo","ticker2","test","depth","dologin","tickes","trades");
		// if (!in_array(ACTION_NAME,$allow_action)) {
		// 	$this->error("非法操作！");
		// }
	}

	
	// 
	public function getissueinfo($id=null) {
		$data= M("issue")->where(["id"=>$id])->find();
		if(empty($data)) {
			$this->ajaxReturn(['code'=>1,'data'=>[]]);
		} else {
			$this->ajaxReturn(['code'=>1,'data'=>$data]);
		}
	}
	// 获取主页的数据
	public function getindexdata() {
		$list = M("ctmarket")->where(array('status'=>1))->field("coinname,id,logo")->select();
		$info = M("content")->where(array('status'=>1))->order("id desc")->find();
        $nlist = M("content")->where(array('status'=>1))->order("id desc")->field("title,id")->select();
		$clist = M("config")->where(array('id'=>1))->field("websildea,websildeb,websildec")->find();
		$issue= M("issue")->where(["id"=>3])->find();
		$ret = [
			'market'=>$list,
			'info'=>$info,
			'nlist'=>$nlist,
			'issue'=>$issue,
			'clist'=>$clist,
		];
		$this->ajaxReturn(['code'=>1,'data'=>$ret]);
	}

	public function dologin($email=null,$lpwd=null,$vcode=null) {
		$user = M('User')->where(array('username' => $email))->find();
		if(empty($user)){
			$this->ajaxReturn(['code'=>0,'info'=> L('用户不存在')]);
		}
		if (md5($lpwd) != $user['password']){
			$this->ajaxReturn(['code'=>0,'info'=> L('登录密码错误')]);
		}
		
		if (isset($user['status']) && $user['status'] != 1) {
			$this->ajaxReturn(['code'=>0,'info'=> L('你的账号已冻结请联系管理员')]);
		}
		//增加登陆次数
		$incre = M("user")->where(array('id' => $user['id']))->setInc('logins', 1);/*->save("lang",$_COOKIE['think_language'])*/
		
		//新增登陆记录
		$data['userid'] = $user['id'];
		$data['type'] = L('登录');
		$data['remark'] = L('邮箱登录');
		$data['addtime'] = time();
		$data['addip'] = get_client_ip();
		$data['addr'] = get_city_ip();
		$data['status'] = 1;
		$dre = M("user_log")->add($data);
		
		if($incre && $dre){
		   $lgdata['lgtime'] = date("Y-m-d H:i:s");
		    $lgdata['loginip'] = get_client_ip();
		    $lgdata['loginaddr'] = get_city_ip();
		    $lgdata['logintime'] = date("Y-m-d H:i:s");
	    	M("user")->where(array('id' => $user['id']))->save($lgdata);
		    session('userId', $user['id']);
			session('userName', $user['username']);
			session('lifetime', time());
			$this->ajaxReturn(['code'=>1,'info'=>L('登录成功')]);
		}else{
		    $this->ajaxReturn(['code'=>0,'info'=>L('登录失败')]);
		}
	}
	
    //获取当前最新价格
    public function getcoinprice($symbol = null){
        if($symbol == '' || $symbol == null){
            $this->ajaxReturn(['code'=>0]);
        }
        // exit("1");
		$data = [];
		// $symbol = "BTC/USDT";
        if($symbol == "MBN/USDT"){
            $symbol = "mbn_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
            $num = 0.001 * rand(1,9);
                
            $open = $mlist['min_price'];//开盘价
            $close = $mlist['new_price'] + $num;//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            
            if($change >= 0){
                $change = '<span class="green" style="font-size:18px;font-weight: 500;">+' .$change. '%</span>';
            }else{
                $change = '<span class="red" style="font-size:18px;font-weight: 500;">' .$change. '%</span>';
            }
            
            if($close >= $open){
                $close = '<span class="green" style="font-size:18px;font-weight: 500;">'.$close.'</span>';
            }else{
                $close = '<span class="red" style="font-size:18px;font-weight: 500;">'.$close.'</span>';
            }
            
            $data['code']=1;
            $data['price'] = $close;
            $data['change']= $change;
 
            $this->ajaxReturn($data);
            
        }else{
            $arr = explode('/',$symbol);
            $coinname = strtolower($arr[0]).strtolower($arr[1]);
            $url = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$coinname;
			
            $result = $this->get_maket_api($url);
            $pdata = $result['data'][0];

			// var_dump($result, $url);
            
            
            $open = $pdata['open'];//开盘价
            $close = $pdata['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $change = '<span class="green" style="font-size:18px;font-weight: 500;">+' .$change. '%</span>';
            // }else{
            //     $change = '<span class="red" style="font-size:18px;font-weight: 500;">' .$change. '%</span>';
            // }
            
            // if($close >= $open){
            //     $close = '<span class="green" style="font-size:18px;font-weight: 500;">'.$close.'</span>';
            // }else{
            //     $close = '<span class="red" style="font-size:18px;font-weight: 500;">'.$close.'</span>';
            // }
            
            $data['code']=1;
            $data['price'] = $close;
            $data['change']= $change;
    
            $this->ajaxReturn($data);
        }
    }
    
    
    //获取当前最新价格
    public function getnewprice($symbol = null){
        if($symbol == '' || $symbol == null){
            $this->ajaxReturn(['code'=>0]);
        }

        if($symbol == "MBN/USDT"){
            $symbol = "mbn_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price")->find();

            $num = 0.001 * rand(1,9);

            $close = $mlist['new_price'] + $num;//现价

            $data['code']=1;
            $data['price']=$close;
            $this->ajaxReturn($data);
            
        }else{
        
            $arr = explode('/',$symbol);
            $coinname = strtolower($arr[0]).strtolower($arr[1]);
            $url = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$coinname;
            $result = $this->get_maket_api($url);
            $pdata = $result['data'][0];
            $price = $pdata['close'];
            $data['code']=1;
            $data['price']=$price;
            $this->ajaxReturn($data);
        }
    }
    
    //获取5条卖出记录
    public function gettradbuy($symbol = null){
        if($symbol == "MBN/USDT"){
            $market = "mbn_usdt";
            $list = M("trade")->where(array('market'=>$market))->order("id desc")->limit(20)->select();
            foreach($list as $key=>$vo){
                if($vo['type'] == 1){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['num']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['price']);
                }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }else{
            $arr = explode('/',$symbol);
            $coinname = strtolower($arr[0]).strtolower($arr[1]); 
            $url = "https://api.huobi.pro/market/history/trade?symbol=".$coinname."&size=20";
            $result = $this->get_maket_api($url);
            $data = $result['data'];
            $ajdata = array();
            foreach($data as $key=>$vo){
                $direction = $vo['data'][0]['direction'];
                if($direction == "buy"){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['data'][0]['amount']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['data'][0]['price']);
                }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }
    }
    //获取5条购买记录
    public function gettradsell($symbol = null){
        if($symbol == "MBN/USDT"){
            $market = "mbn_usdt";
            $list = M("trade")->where(array('market'=>$market))->order("id desc")->limit(20)->select();
            foreach($list as $key=>$vo){
                if($vo['type'] == 2){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['num']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['price']);
                }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }else{
            $arr = explode('/',$symbol);
            $coinname = strtolower($arr[0]).strtolower($arr[1]); 
            $url = "https://api.huobi.pro/market/history/trade?symbol=".$coinname."&size=40";
            $result = $this->get_maket_api($url);
            $data = $result['data'];
            $ajdata = array();
            foreach($data as $key=>$vo){
                $direction = $vo['data'][0]['direction'];
                if($direction == "sell"){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['data'][0]['amount']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['data'][0]['price']);
                }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }
    }

    //获取最新买卖记录
    public function gettradlist(){
        $coinname = trim(I('post.coinname'));
        if($coinname == "MBN"){
            $symbol = "mbn_usdt";
            $tlist = M("trade")->where(array("market"=>$symbol))->order("id desc")->limit(20)->select();
            $ajdata = array();
            foreach($tlist as $key=>$vo){

				$ajdata[$key]['strtype'] = $vo['type'];
				// '<span class="fzmm red">'. $str.'</span>';
				$ajdata[$key]['amount'] = sprintf("%.4f",$vo['num']);
				// '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
				$ajdata[$key]['price'] = sprintf("%.4f",$vo['price']);
                // if($vo['type'] == 1){
                //     $str = L('买入');
                //     $ajdata[$key]['strtype'] = '<span class="fzmm green">'. $str  .'</span>';
                //     $ajdata[$key]['amount'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['num']) .'</span>';
                //     $ajdata[$key]['price'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['price']) .'</span>';
                // }elseif($vo['type'] == 2){
                //     $str = L('卖出');
                //     $ajdata[$key]['strtype'] = '<span class="fzmm red">'. $str.'</span>';
                //     $ajdata[$key]['amount'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['num']) .'</span>';
                //     $ajdata[$key]['price'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['price']) .'</span>';
                // }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }else{
            $symbol = strtolower($coinname).'usdt';
            $url = "https://api.huobi.pro/market/history/trade?symbol=".$symbol."&size=20";
            $result = $this->get_maket_api($url);
            $data = $result['data'];
            $ajdata = array();
            foreach($data as $key=>$vo){
                $direction = $vo['data'][0]['direction'];
                // if($direction == "sell"){
                //     $str = L('卖出');
                //     $ajdata[$key]['strtype'] = $direction;
				// 	// '<span class="fzmm red">'. $str.'</span>';
                //     $ajdata[$key]['amount'] = sprintf("%.4f",$vo['data'][0]['amount']);
				// 	// '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
                //     $ajdata[$key]['price'] = sprintf("%.4f",$vo['data'][0]['price']);
				// 	// '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['price']) .'</span>';
                // }elseif($direction == "buy"){
                //     $str = L('买入');
                //     $ajdata[$key]['strtype'] = '<span class="fzmm green">'. $str  .'</span>';
                //     $ajdata[$key]['amount'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
                //     $ajdata[$key]['price'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['data'][0]['price']) .'</span>';
                // }

				$ajdata[$key]['strtype'] = $direction;
				// '<span class="fzmm red">'. $str.'</span>';
				$ajdata[$key]['amount'] = sprintf("%.4f",$vo['data'][0]['amount']);
				// '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
				$ajdata[$key]['price'] = sprintf("%.4f",$vo['data'][0]['price']);
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }
        
    }
    
    //获取主流货币详情
    public function get_market_one(){
        $btcapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=btcusdt";
        $ethapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=ethusdt";
        $bchapi = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=bchusdt";
        $btcresult = $this->get_maket_api($btcapi);
        $ethresult = $this->get_maket_api($ethapi);
        $bchresult = $this->get_maket_api($bchapi);
        
        $btcdata = $this->processing_onedata($btcresult);
        $ethdata = $this->processing_onedata($ethresult);
        $bchdata = $this->processing_onedata($bchresult);
        
        $market['btccoin'] = "BTC/USDT";
        $market['btcnewprice'] = $btcdata['open'];
        $market['btcchange'] = $btcdata['change'];
        
        $market['ethcoin'] = "ETH/USDT";
        $market['ethnewprice'] = $ethdata['open'];
        $market['ethchange'] = $ethdata['change'];
        
        $market['bchcoin'] = "BCH/USDT";
        $market['bchnewprice'] = $bchdata['open'];
        $market['bchchange'] = $bchdata['change'];
        $market['code'] = 1;
        $this->ajaxReturn($market);
    }
    
    //处理单个行情数理
    public function processing_onedata($array){
        $price_arr = $array['data'][0];
        $open = $price_arr['open'];//开盘价
        $close = $price_arr['close'];//现价
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        // if($change >= 0){
        //     $change = '<span class="green f16 fw">+' .$change. '%</span>';
        // }else{
        //     $change = '<span class="red f16 fw">' .$change. '%</span>';
        // }
        
        // if($close >= $open){
        //     $close = '<span class="fe6 f22 fw">'.$close.'</span>';
        // }else{
        //     $close = '<span class="fe6 f22 fw">'.$close.'</span>';
        // }
        
        $pdata['open'] = $close; 
        $pdata['change'] = $change;
        return $pdata;
    }
    
    
    //获取行情单个行情数据
    public function obtain_btc($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = ($lowhig / $open * 100); //涨跌幅
            // print_r($data);exit;
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_eth($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_eos($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_doge($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_bch($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }


    //获取行情单个行情数据
    public function obtain_ltc($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_iota($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_fil($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_flow($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_jst($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_itc($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_ht($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
            // }

            // if($close >= $open){
            //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
            // }else{
            //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
            // }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['change'] = $change;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }



    //获取行情单个行情数据
    public function obtain_usdz($coin){
        $symbol = "mbn_usdt";
        $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
        //$num = 0.001 * rand(1,9);

        $open = $mlist['min_price'];//开盘价
        $close = $mlist['new_price']; //+ $num;//现价
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        // if($change >= 0){
        //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
        // }else{
        //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
        // }

        // if($close >= $open){
        //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
        // }else{
        //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
        // }

        $alldata['code'] = 1;
        $alldata['cname'] = $cname;
        $alldata['open'] = $close;
        $alldata['change'] = $change;
        $this->ajaxReturn($alldata);


    }

// 	$ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, "http://example.com");
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// $result = curl_exec($ch);
// $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// curl_close($ch);

// switch ($httpCode) {
//     case 200:
//         // 处理请求正常返回结果
//         break;
//     case 404:
//         // 处理请求结果未找到的情况
//         break;
//     default:
//         // 处理其他情况
//         break;
// }




    //获取行情数据
    public function get_maket_api($api){

		// var_dump("????");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt ($ch, CURLOPT_URL, $api );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,1000);
		// curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:10810');
		// $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// $result = curl_exec($ch);
		// var_dump($result,$httpCode);
        $result = json_decode(curl_exec($ch),true);
        return $result;
    }

    //处理行情数理
    public function processing_data($array,$cname){
        $price_arr = $array['data'][0];
        $open = $price_arr['open'];//开盘价
        $close = $price_arr['close'];//现价
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        // if($change >= 0){
        //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
        // }else{
        //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
        // }

        // if($close >= $open){
        //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
        // }else{
        //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
        // }


        $pdata['open'] = $close;
        $pdata['cname'] = $cname;
        $pdata['change'] = $change;

        return $pdata;
    }

    //获取市场行情
    public function getallsymbol(){
        $list = M("ctmarket")->where(array('status'=>1))->field("coinname,id")->select();
        if(!empty($list)){
            foreach($list as $k=>$v){
                $symbol = $v['coinname']."usdt";
                $cname = strtoupper($v['coinname'])."/USDT";

                $sid = $v['id'];
                $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
                $data = $this->get_maket_api($api);
               // print_r($data);die;
                $price_arr = $data['data'][0];
                $open = $price_arr['open'];//开盘价
                $close = $price_arr['close'];//现价
                $lowhig =  $close - $open; //涨跌
                $change = round(($lowhig / $open * 100),2); //涨跌幅
                // if($change >= 0){
                //     $changestr = "<span  class='fzmm bgreen market-list-info'>+". $change ."%</span>";
                // }else{
                //     $changestr = "<span  class='fzmm bred market-list-info'>". $change ."%</span>";
                // }
        
                // if($close >= $open){
                //     $close = "<span class='fzmmm fw  fe6 '>".$close."</span>";
                // }else{
                //     $close = "<span  class='fzmmm fw  fe6 '>".$close."</span>";
                // }
                $alldata[$k]['sid'] = $sid;
                $alldata[$k]['cname'] = $cname;
                $alldata[$k]['open'] = $close;
                $alldata[$k]['change'] = $change;
            }

            $this->ajaxReturn(['code'=>1,'data'=>$alldata]);
        }else{
            $this->ajaxReturn(['code'=>0]);
        }
    }
    
    //处理合约页面交易对数据
    public function hydata($array,$cname){
        $price_arr = $array['data'][0];
        $open = $price_arr['open'];//开盘价
        $close = $price_arr['close'];//现价
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        // if($change >= 0){
        //     $changestr = "<span class='fzmm green'>+".$change."%</span>";
            
        // }else{
        //     $changestr = "<span class='fzmm red'>".$change."%</span>";
        // }
        
        // if($close >= $open){
        //     $close = "<span class='fzmm green'>".$close."</span>";
        // }else{
        //     $close = "<span class='fzmm red'>".$close."</span>";
        // }
        
        
        $pdata['open'] = $close; 
        $pdata['cname'] = $cname; 
        $pdata['change'] = $change;
        
        return $pdata;
    }
    
    //合约页面获取所有交易对
    public function getallcoin(){
        $where['status'] = 1;
      //  $where['coinname'] = array('neq','mbn');
        $list = M("ctmarket")->where($where)->field("coinname,id")->select();
       
        if(!empty($list)){
            foreach($list as $k=>$v){
                if($v['coinname'] == "mbn"){
                    $market = "mbn_usdt";
                    $data = M("trade_json")->where(array('market'=>$market,'type'=>1))->field("data")->find();
                    
                    $symbol = "mbnusdt";
                    $cname = "MBN/USDT";
                    $sid = $v['id'];
                    $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
                    $data = $this->get_maket_api($api);
                    $result = $this->hydata($data,$cname);
                    $alldata[$k]['sid'] = $sid;
                    $alldata[$k]['coin'] = strtoupper($v['coinname']);
                    $alldata[$k]['cname'] = $result['cname'];
                    $alldata[$k]['symbol'] = $v['coinname'];
                    $alldata[$k]['open'] = $result['open'];
                    $alldata[$k]['change'] = $result['change'];



                }else{
                    $symbol = $v['coinname']."usdt";
                    $cname = strtoupper($v['coinname'])."/USDT";
                    $sid = $v['id'];
                    $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
                    $data = $this->get_maket_api($api);
                    $result = $this->hydata($data,$cname);
                    $alldata[$k]['sid'] = $sid;
                    $alldata[$k]['coin'] = strtoupper($v['coinname']);
                    $alldata[$k]['cname'] = $result['cname'];
                    $alldata[$k]['symbol'] = $v['coinname'];
                    $alldata[$k]['open'] = $result['open'];
                    $alldata[$k]['change'] = $result['change'];
                }
            }

            $this->ajaxReturn(['code'=>1,'data'=>$alldata]);
            
            
        }else{
            $this->ajaxReturn(['code'=>0]);
        }
    }
    
    
    //获取交易对数据
    public function getcoin_data(){
        
      //  var_dump($_POST['coinname']);exit;
        $coinname = trim($_POST['coinname']);
        if($coinname == "MBN"){
            $symbol = "mbn_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
            
            $num = 0.001 * rand(1,9);
            
            $open = $mlist['min_price'];//开盘价
            $close = $mlist['new_price'] + $num;//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='green' style='font-size:16px;'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='red' style='font-size:16px;'>". $change ."%</span>";
            // }
            
            // if($close >= $open){
            //     $close = "<span  class='green' style='font-size:22px;'>".$close."</span>";
            // }else{
            //     $close = "<span  class='red' style='font-size:22px;'>".$close."</span>";
            // }
            $high = $mlist['max_price'];
            $low = $mlist['min_price'];
            $amount = $mlist['volume'];
            
            $result['close'] = $close;
            $result['change'] = $change;
            $result['high'] = $high;
            $result['low'] = $low;
            $result['amount'] = sprintf("%.6f",$amount);
            $result['code'] = 1;
            $this->ajaxReturn($result);
            
            
        }else{
            $symbol = strtolower($coinname)."usdt";
            
            $cname = strtoupper($coinname)."/USDT";
            $api = "https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=".$symbol;
            $data = $this->get_maket_api($api);
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            // if($change >= 0){
            //     $changestr = "<span  class='green' style='font-size:16px;'>+". $change ."%</span>";
            // }else{
            //     $changestr = "<span  class='red' style='font-size:16px;'>". $change ."%</span>";
            // }
            
            // if($close >= $open){
            //     $close = "<span  class='green' style='font-size:22px;'>".$close."</span>";
            // }else{
            //     $close = "<span  class='red' style='font-size:22px;'>".$close."</span>";
            // }
            $high = $price_arr['high'];
            $low = $price_arr['low'];
            $amount = $price_arr['amount'];
            
            $result['close'] = $close;
            $result['change'] = $change;
            $result['high'] = $high;
            $result['low'] = $low;
            $result['amount'] = sprintf("%.6f",$amount);
            $result['code'] = 1;
            $this->ajaxReturn($result);
        }
    }
}
?>