<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	$source=896985930;
    	$this->assign('source',$source);
      	$this->display();
    }
}