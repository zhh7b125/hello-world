<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "weixin");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();

//$wechatObj->valid();
//exit;

class wechatCallbackapiTest
{
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }

	//处理业务逻辑
    public function responseMsg()
    {
		//get post data, May be due to the different environments
		//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		//$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : "" ;
		
		$postStr = file_get_contents("php://input");
		
      	//extract post data
		if (!empty($postStr))
		{
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
				$event = $postObj->Event;
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";           

			    $newsTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[news]]></MsgType>
							<ArticleCount>1</ArticleCount>
							<Articles>
							<item>
							<Title><![CDATA[%s]]></Title> 
							<Description><![CDATA[%s]]></Description>
							<PicUrl><![CDATA[%s]]></PicUrl>
							<Url><![CDATA[%s]]></Url>
							</item>
							</Articles>
							</xml>";
							
				$imageTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[image]]></MsgType>
							<Image>
							<MediaId><![CDATA[%s]]></MediaId>
							</Image>
							</xml>";
				switch($postObj->MsgType)
				{
					case "event":
					//关注事件
					if($event == "subscribe")
					{
						$contentStr = "welcome to 微之恋！";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
						echo $resultStr;
					}
					break;
					
					case "text":
					//文本消息
					if(!empty( $keyword ))
					{
						$msgType = "text";
						
						if($keyword == 'OK')
						{
							$contentStr = "OK"; 
						}
						else
						{
							preg_match('/(\d+)([+-])(\d+)/i',$keyword,$res);
							if($res[2]=='+')
							{
								$result = $res[1] + $res[3];
							}
							else if($res[2]=='-')
							{
								$result = $res[1] - $res[3];
							}
							$contentStr = "Welcome to wechat world! 运算结果是".$result;
						}
						
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					}
					else
					{
						echo "Input something...";
					}
					
					/*
					$sqlTool = new SqlTool();
					
					if($keyword == "帮助")
						{
							$contentStr = "指令指南：\r\n1：查询ID号\r\n2：查看自己数据\r";
							$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
							echo $resultStr;
						}*/
					break;
					
					default:
						$msgType = "text";
						$contentStr = "something error!";
						$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
						echo $resultStr;
					break;
					
				}
        }
		else
		{
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) 
		{
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>