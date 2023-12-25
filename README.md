接口统一地址:127.0.0.1/Api/
方法:post
1：登录
Api/dologin
参数:
email,
lpwd
返回参数:
成功:
['code'=>1,'info'=>L('登录成功')]
失败
['code'=>1,'info'=>L('登录失败')]

2：注册 (未完成)
Api/doregister






3:获取首页数据
Api/getindexdata
返回
{"code":1,"data":{"market":[{"coinname":"btc","id":"2","logo":"\/xm\/1613786496962262.png"},{"coinname":"eth","id":"3","logo":"\/xm\/1613786513998262.png"},{"coinname":"eos","id":"4","logo":"\/xm\/5f8738fd439bc57.png"},{"coinname":"doge","id":"5","logo":"\/xm\/doge.png"},{"coinname":"bch","id":"6","logo":"\/xm\/5fc.png"},{"coinname":"ltc","id":"7","logo":"\/xm\/5f87397132a8b02.png"},{"coinname":"trx","id":"8","logo":"\/xm\/trx.png"}],"info":null,"nlist":[],"issue":{"id":"3","name":"MORBION","coinname":"mbn","buycoin":"usdt","num":"999999999.000000","price":"335.442520","lixi":"29.50","sellnum":"57392152.381968","ysnum":"15000000.000000","allmax":"9999999.000000","min":"100.000000","max":"9999999999999.000000","lockday":"90","tian":"180","imgs":"635fc4ba73dcd.png","content":"<h2 style=\"color:#74777A;text-indent:0pt;background-color:#FFFFFF;font-family:;\">\r\n\tIndustry background\r\n<\/h2>\r\n<p>\r\n\t<span>MORBION coins are jointly launched by Mobie Exchange, with a limited issuance of 100 million. On October 31, 2022, the first subscription will be launched worldwide. The first subscription will have a circulation of 999999999 for 180 days. The subscription coins will be automatically released after 90 days of freezing. After the release, the currency market will open MBN\/USDT trading pairs, and the contract market will open MBN\/USDT trading pairs for trading. The price is expected to be 10 times of the subscription unit price<\/span>\r\n<\/p>","addtime":"2022-11-26 01:32:15","starttime":"2022-10-31 11:02:32","finishtime":"2023-04-29 11:02:32","status":"1","state":"1","jlcoin":"usdt","one_jl":"0.00","two_jl":"0.00","three_jl":"0.00","yuan_price":"0.140000"},"clist":{"websildea":"62ac7f6a64120.png","websildeb":"62ac7f72d5c2d.png","websildec":"62ac7f79b3703.png"}}}



4:获取货币价格
Api/getnewprice
参数:
symbol
返回值:




5:获取5条卖出记录

Api/gettradbuy
参数:
symbol
返回值:


6:获取5条购买记录

Api/gettradsell
参数:
symbol
返回值:


7:获取最新买卖记录
Api/gettradlist
参数:
coinname


8:获取发行信息
Api/getissueinfo
参数:
id




