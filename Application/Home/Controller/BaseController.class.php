<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize(){
    	$allow_ip=['127.0.01','10.0.2.2'];
    	$ip=$this->getIp();
    	// echo $ip;die;
    	if( !in_array($ip, $allow_ip) ){
    		exit('您没有访问权限');
    	}
    }
    /**
     * 获取用户ip
     */
    public function getIp(){
    	if (getenv('HTTP_CLIENT_IP')) { 
		$ip = getenv('HTTP_CLIENT_IP'); 
		} 
		elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_X_FORWARDED_FOR'); 
		} 
		elseif (getenv('HTTP_X_FORWARDED')) { 
		$ip = getenv('HTTP_X_FORWARDED'); 
		} 
		elseif (getenv('HTTP_FORWARDED_FOR')) { 
		$ip = getenv('HTTP_FORWARDED_FOR'); 

		} 
		elseif (getenv('HTTP_FORWARDED')) { 
		$ip = getenv('HTTP_FORWARDED'); 
		} 
		else { 
		$ip = $_SERVER['REMOTE_ADDR']; 
		} 
		return $ip; 
    }
}