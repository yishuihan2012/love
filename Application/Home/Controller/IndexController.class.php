<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	$data['source']=896985930;
    	$data['man']='jack';
    	$data['woman']='rose';
    	$data['tell_to_her']="Love u forever and ever.";
    	$data['title']="因为爱所以爱";
    	$meet_time='2017-06-05 12:12:12';
    	$time=strtotime($meet_time);
    	$time=date('Y:m:d:H:i:s',$time-30*24*60*60);
    	$data['meet_time']=explode(':', $time);
    	$this->assign('data',$data);
      	$this->display();
    }
}