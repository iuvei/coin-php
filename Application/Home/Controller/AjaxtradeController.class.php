<?php
namespace Home\Controller;

class AjaxtradeController extends HomeController
{
    public $base_path = 'https://api.huobi.pro/market/history/kline?period=1day&size=1&symbol=';
    
    //获取当前最新价格
    public function getcoinprice($symbol = null){
        if($symbol == '' || $symbol == null){
            $this->ajaxReturn(['code'=>0]);
        }
        if($symbol == "MBN/USDT"){
            $symbol = "mbn_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
            $num = 0.001 * rand(1,9);
                
            $open = $mlist['min_price'];//开盘价
            $close = $mlist['new_price'] + $num;//现价
            $lowhig =  $close - $open; //涨跌
            $high = 
            $low = 
            
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
            $data['high'] = $mlist['max_price'];
            $data['low'] = $mlist['min_price'];
            $data['amount'] = $mlist['volume'];//量
            $this->ajaxReturn($data);
            
        }else{
            $arr = explode('/',$symbol);
            $coinname = strtolower($arr[0]).strtolower($arr[1]);
            $url = $this->base_path.$coinname;
            $result = $this->get_maket_api($url);
            $pdata = $result['data'][0];
            
            $open = $pdata['open'];//开盘价
            $close = $pdata['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $change = '<span class="green fw" >+' .$change. '%</span>';
            }else{
                $change = '<span class="red fw">' .$change. '%</span>';
            }
            
            if($close >= $open){
                $close = '<span class="green fw" style="font-size:22px;">'.$close.'</span>';
            }else{
                $close = '<span class="red fw" style="font-size:22px;">'.$close.'</span>';
            }
            
            $data['code']=1;
            $data['price'] = $close;
            $data['change']= $change;
            $data['high'] = $pdata['high'];//最高
            $data['low'] = $pdata['low'];//最高
            $data['amount'] = $pdata['amount'];//量
    
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
            $url = $this->base_path.$coinname;
            $result = $this->get_maket_api($url);
            $pdata = $result['data'][0];
            $price = $pdata['close'];
            $data['code']=1;
            $data['price']=$price;
            $this->ajaxReturn($data);
        }
    }
    
    //获取10条卖出记录
    public function gettradbuyten($symbol = null){
        if($symbol == "MBN/USDT"){
            $market = "mbn_usdt";
            $list = M("trade")->where(array('market'=>$market))->order("id desc")->limit(40)->select();
            foreach($list as $key=>$vo){
                if($vo['type'] == 1){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['num']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['price']);
                    $ajdata[$key]['time'] =  date('H:i:s' ,round($vo['ts'] / 1000 ));
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
                if($direction == "buy"){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['data'][0]['amount']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['data'][0]['price']);
                    $ajdata[$key]['direction'] =  $direction;
                    $ajdata[$key]['time'] =  date('H:i:s' ,round($vo['ts'] / 1000 ));
                }

            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
        }
    }
    
    //获取10条购买记录
    public function gettradsellten($symbol = null){
        if($symbol == "MBN/USDT"){
            $market = "mbn_usdt";
            $list = M("trade")->where(array('market'=>$market))->order("id desc")->limit(40)->select();
            foreach($list as $key=>$vo){
                if($vo['type'] == 2){
                    $ajdata[$key]['amount'] = sprintf("%.4f",$vo['num']);
                    $ajdata[$key]['price'] =  sprintf("%.4f",$vo['price']);
                    $ajdata[$key]['time'] =  date('H:i:s' ,round($vo['ts'] / 1000 ));
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
                    $ajdata[$key]['direction'] =  $direction;
                    $ajdata[$key]['time'] =  date('H:i:s' ,round($vo['ts'] / 1000 ));
                }
            }
            $this->ajaxReturn(['code'=>1,'data'=>$ajdata]);
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
                if($vo['type'] == 1){
                    $str = L('买入');
                    $ajdata[$key]['strtype'] = '<span class="fzmm green">'. $str  .'</span>';
                    $ajdata[$key]['amount'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['num']) .'</span>';
                    $ajdata[$key]['price'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['price']) .'</span>';
                }elseif($vo['type'] == 2){
                    $str = L('卖出');
                    $ajdata[$key]['strtype'] = '<span class="fzmm red">'. $str.'</span>';
                    $ajdata[$key]['amount'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['num']) .'</span>';
                    $ajdata[$key]['price'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['price']) .'</span>';
                }
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
                if($direction == "sell"){
                    $str = L('卖出');
                    $ajdata[$key]['strtype'] = '<span class="fzmm red">'. $str.'</span>';
                    $ajdata[$key]['amount'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
                    $ajdata[$key]['price'] = '<span class="fzmm red">'. sprintf("%.4f",$vo['data'][0]['price']) .'</span>';
                }elseif($direction == "buy"){
                    $str = L('买入');
                    $ajdata[$key]['strtype'] = '<span class="fzmm green">'. $str  .'</span>';
                    $ajdata[$key]['amount'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['data'][0]['amount']) .'</span>';
                    $ajdata[$key]['price'] = '<span class="fzmm green">'. sprintf("%.4f",$vo['data'][0]['price']) .'</span>';
                }
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
        if($change >= 0){
            $change = '<span class="green" style="font-size:14px;font-weight: 500;">+' .$change. '%</span>';
        }else{
            $change = '<span class="red" style="font-size:14px;font-weight: 500;">' .$change. '%</span>';
        }
        
        if($close >= $open){
            $close = '<span class="green" style="font-size:16px;font-weight: 500;">'.$close.'</span>';
        }else{
            $close = '<span class="red" style="font-size:16px;font-weight: 500;">'.$close.'</span>';
        }
        
        $pdata['open'] = $close; 
        $pdata['change'] = $change;
        return $pdata;
    }
    
    
    //获取行情单个行情数据
    public function obtain_btc($coin){
        
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        
        $api = $this->base_path.$symbol;
        
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//量
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_eth($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_eos($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_doge($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_bch($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }


    //获取行情单个行情数据
    public function obtain_ltc($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_iota($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_fil($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_flow($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_jst($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_itc($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
            $this->ajaxReturn($alldata);
        }else{
            $this->ajaxReturn(['code'=>0,'info'=>"error"]);
        }

    }

    //获取行情单个行情数据
    public function obtain_ht($coin){
        $symbol = $coin."usdt";
        $cname = strtoupper($coin)."/USDT";
        $api = $this->base_path.$symbol;
        $data = $this->get_maket_api($api);
        if($data['status'] == 'ok'){
            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $high = $price_arr['high'];//最高
            $low = $price_arr['low'];//最高
            $amount = $price_arr['amount'];//最高
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
            }

            if($close >= $open){
                $close = "<span  class='f14 fgreen fw'>".$close."</span>";
            }else{
                $close = "<span  class='f14 fred fw'>".$close."</span>";
            }
            $alldata['code'] = 1;
            $alldata['cname'] = $cname;
            $alldata['open'] = $close;
            $alldata['highlow'] = $high." / ".$low;
            $alldata['amount'] = $amount;
            $alldata['change'] = $changestr;
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
        $close = $mlist['new_price'];// + $num;//现价
        $high = $mlist['max_price'];//最高
        $low = $mlist['min_price'];//最高
        $amount = $mlist['volume'];//最高
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        if($change >= 0){
                $changestr = "<span  class='f14 fgreen profit_loss_g fw'>+". $change ."%</span>";
        }else{
            $changestr = "<span  class='f14 fred profit_loss_r fw'>". $change ."%</span>";
        }

        if($close >= $open){
            $close = "<span  class='f14 fgreen fw'>".$close."</span>";
        }else{
            $close = "<span  class='f14 fred fw'>".$close."</span>";
        }
        
        $alldata['code'] = 1;
        $alldata['cname'] = "MBN/USDT";
        $alldata['open'] = $close;
        $alldata['highlow'] = $high." / ".$low;
        $alldata['amount'] = $amount;
        $alldata['change'] = $changestr;
        $this->ajaxReturn($alldata);

        
    }
    
    
    
    
    //获取行情数据
    public function get_maket_api($api){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt ($ch, CURLOPT_URL, $api );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        $result = json_decode(curl_exec($ch),true);
        curl_close($ch);

        return $result;
    }
    
    //处理行情数理
    public function processing_data($array,$cname){
        $price_arr = $array['data'][0];
        $open = $price_arr['open'];//开盘价
        $close = $price_arr['close'];//现价
        $lowhig =  $close - $open; //涨跌
        $change = round(($lowhig / $open * 100),2); //涨跌幅
        if($change >= 0){
            $changestr = "<span  class='fzmm bgreen' style='color:#fff;border-radius: 2px;width:70px;height:35px;line-height:35px;text-align:center;display:inline-block;'>+". $change ."%</span>";
        }else{
            $changestr = "<span  class='fzmm bred' style='color:#fff;border-radius: 2px;width:70px;height:35px;line-height:35px;text-align:center;display:inline-block;'>". $change ."%</span>";
        }
        
        if($close >= $open){
            $close = "<span  class='fzmmm green'>".$close."</span>";
        }else{
            $close = "<span  class='fzmmm red'>".$close."</span>";
        }
        
        
        $pdata['open'] = $close; 
        $pdata['cname'] = $cname; 
        $pdata['change'] = $changestr;
        
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
                $api = $this->base_path.$symbol;
                $data = $this->get_maket_api($api);
               // print_r($data);die;
                $price_arr = $data['data'][0];
                $open = $price_arr['open'];//开盘价
                $close = $price_arr['close'];//现价
                $change =  $close - $open; //涨跌
//dump($change);
                if($change >= 0){
                    $change = round(($change / $open * 100),2); //涨跌幅
                    $changestr = "<span  class='fzmm bgreen' style='color:#fff;border-radius: 2px;width:70px;height:35px;line-height:35px;text-align:center;display:inline-block;'>+". $change ."%</span>";
                }else{
                    $change = round(($change / $open * 100),2); //涨跌幅
                    $changestr = "<span  class='fzmm bred' style='color:#fff;border-radius: 2px;width:70px;height:35px;line-height:35px;text-align:center;display:inline-block;'>". $change ."%</span>";
                }
        
                if($close >= $open){
                    $close = "<span  class='fzmmm green'>".$close."</span>";
                }else{
                    $close = "<span  class='fzmmm red'>".$close."</span>";
                }
                $alldata[$k]['sid'] = $sid;
                $alldata[$k]['cname'] = $cname;
                $alldata[$k]['open'] = $close;
                $alldata[$k]['change'] = $changestr;
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
        $change = $lowhig / $open * 100;
//        $change = round(($lowhig / $open * 100),2); //涨跌幅
//        $change = abs($change);
//        $change = number_format($change, 2);


        if($change >= 0){
            $change = abs($change);
            $change = number_format($change, 2);
            $changestr = "<span class='fzmm green'><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 320 512\" fill=\"#0ecb81\"><path d=\"M9.39 265.4l127.1-128C143.6 131.1 151.8 128 160 128s16.38 3.125 22.63 9.375l127.1 128c9.156 9.156 11.9 22.91 6.943 34.88S300.9 320 287.1 320H32.01c-12.94 0-24.62-7.781-29.58-19.75S.2333 274.5 9.39 265.4z\"/></svg>".$change."%</span>";
            
        }else{
            $change = abs($change);
            $change = number_format($change, 2);
            $changestr = "<span class='fzmm red'><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"12\" height=\"12\" viewBox=\"0 0 320 512\" fill=\"#ff0000\"><path d=\"M310.6 246.6l-127.1 128C176.4 380.9 168.2 384 160 384s-16.38-3.125-22.63-9.375l-127.1-128C.2244 237.5-2.516 223.7 2.438 211.8S19.07 192 32 192h255.1c12.94 0 24.62 7.781 29.58 19.75S319.8 237.5 310.6 246.6z\"/></svg>".$change."%</span>";
        }
        
        if($close >= $open){
            $close = "<span class='fzmm green'>".$close."</span>";
        }else{
            $close = "<span class='fzmm red'>".$close."</span>";
        }
        
        
        $pdata['open'] = $close; 
        $pdata['cname'] = $cname; 
        $pdata['change'] = $changestr;
        
        return $pdata;
    }
    
    //合约页面获取所有交易对
    public function getallcoin(){
        $where['status'] = 1;
       // $where['coinname'] = array('neq','mbn');
        $list = M("ctmarket")->where($where)->field("coinname,id")->select();
        
        if(!empty($list)){
            foreach($list as $k=>$v){
                if($v['coinname'] == "mbn"){
                    $market = "mbn_usdt";
                    $data = M("trade_json")->where(array('market'=>$market,'type'=>1))->field("data")->find();
                    //var_dump($data)exit;
                   // var_dump($data);exit;
                    $symbol = "mbnusdt";
                    $cname = "MBN/USDT";
                    $sid = $v['id'];
                    $api = $this->base_path.$symbol;
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
                    $api = $this->base_path.$symbol;
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
        $coinname = trim($_POST['coinname']);
        if($coinname == "MBN"){
            $symbol = "mbn_usdt";
            $mlist = M("market")->where(array('name'=>$symbol))->field("new_price,min_price,max_price,faxingjia,volume")->find();
            
            $num = 0.001 * rand(1,9);
            
            $open = $mlist['min_price'];//开盘价
            $close = $mlist['new_price'] + $num;//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='green' style='font-size:16px;'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='red' style='font-size:16px;'>". $change ."%</span>";
            }
            
            if($close >= $open){
                $close = "<span  class='green' style='font-size:22px;'>".$close."</span>";
            }else{
                $close = "<span  class='red' style='font-size:22px;'>".$close."</span>";
            }
            $high = $mlist['max_price'];
            $low = $mlist['min_price'];
            $amount = $mlist['volume'];
            
            $result['close'] = $close;
            $result['change'] = $changestr;
            $result['high'] = $high;
            $result['low'] = $low;
            $result['amount'] = sprintf("%.6f",$amount);
            $result['code'] = 1;
            $this->ajaxReturn($result);
            
            
        }else{
            $symbol = strtolower($coinname)."usdt";
            
            $cname = strtoupper($coinname)."/USDT";
            $api = $this->base_path.$symbol;
            $data = $this->get_maket_api($api);

            $price_arr = $data['data'][0];
            $open = $price_arr['open'];//开盘价
            $close = $price_arr['close'];//现价
            $lowhig =  $close - $open; //涨跌
            $change = round(($lowhig / $open * 100),2); //涨跌幅
            if($change >= 0){
                $changestr = "<span  class='green' style='font-size:16px;'>+". $change ."%</span>";
            }else{
                $changestr = "<span  class='red' style='font-size:16px;'>". $change ."%</span>";
            }
            
            if($close >= $open){
                $close = "<span  class='green' style='font-size:22px;'>".$close."</span>";
            }else{
                $close = "<span  class='red' style='font-size:22px;'>".$close."</span>";
            }
            $high = $price_arr['high'];
            $low = $price_arr['low'];
            $amount = $price_arr['amount'];
            
            $result['close'] = $close;
            $result['change'] = $changestr;
            $result['high'] = $high;
            $result['low'] = $low;
            $result['amount'] = sprintf("%.6f",$amount);
            $result['code'] = 1;
            $this->ajaxReturn($result);
        }
    }

}
?>