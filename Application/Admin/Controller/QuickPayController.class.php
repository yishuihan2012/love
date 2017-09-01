<?php

namespace Mrpiadmin\Controller;

use Think\Controller;

class QuickPayController extends ConController {

    /**
     * 银行卡列表
     * @return [type] [description]
     */
    public function cardList(){
        $where='';
        $post=I('post.');
        if($card_number=$post['card_number']){
            $where['card_number']=array("like","%".$card_number."%");          
            $this->assign('card_number',$card_number);
        }
        if($card_name=$post['card_name']){
            $where['card_name']=array("like","%".$card_name."%"); 
            $this->assign('card_name',$card_name);
        }
        if($card_tel=$post['card_tel']){
            $where['card_tel']=array("like","%".$card_tel."%");
            $this->assign('card_tel',$card_tel);
        }
        if($card_bank=$post['card_bank']){
            $where['card_bank']=array("like","%".$card_bank."%");
            $this->assign('card_bank',$card_bank);
        }
        $table = M('quickpaycard');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $data = $table ->join('usermanage u on u.id=quickpaycard.user_id','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('card_id desc') 
                       -> select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 删除卡号
     */
    public function deleteCard(){
        $id=I('post.id');
        if(!$id){
            echo 0;die;
         }
        $re = M('quickpaycard') -> where(array('card_id' => $id)) -> delete();
        if($re){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 修改卡号
     * @return [type] [description]
     */
    public function updateCard(){
        if($_POST){
            $post = $_POST; 
            $id = $post['id'];
            unset($post['id']);
            $re = M('quickpaycard')->save($post);
            if($re){
                $this->success('修改成功',U('QuickPay/cardList'));
            }else{
                $this->error('您未做修改');
            }
        }else{
            $id = $_GET['id'];
            $data = M('quickpaycard') -> where(array('card_id' => $id)) -> find();
            $card_list=M('bankmanager')->field('id,bank_name,bank_logo')->select();
            $this->assign('card_list',$card_list);
            $this->assign('data',$data);
            $this->display();
        }
    }
    /**
     * 支付订单列表
     * @return [type] [description]
     */
    public function orderList(){
        // 搜索
        $where='';
        $post=I('post.');
        if($qp_id=$post['qp_id']){
            $where['qp_id']=array("like","%".$qp_id."%");          
            $this->assign('qp_id',$qp_id);
        }
        if($u_nick=$post['u_nick']){
            $where['u_nick']=array("like","%".$u_nick."%"); 
            $this->assign('u_nick',$u_nick);
        }
        if($u_starts=$post['u_start']){
            $u_start=strtotime($u_starts);
            $where['qp_create_time']=array('GT',$u_start);
            $this->assign('u_start',$u_starts);
        }

        if($u_ends=$post['u_end']){
            $u_end=strtotime($u_ends);
            $where['qp_create_time']=array('LT',$u_end);
            $this->assign('u_end',$u_ends);
        }
        $table = M('quickpayorder');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $data = $table ->join('usermanage u on u.id=quickpayorder.qp_uid','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('qp_create_time desc') 
                       -> select();
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
    /**
     * 订单详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function showOrder($id){
        $data= M('quickpayorder')->where(array('qp_id'=>$id))->find();
        $this->assign('data',$data);
        $this->display();
    }
    /**
     * 删除订单
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteOrder($id){
         if(!$id){
            echo 0;die;
         }
         $re= M('quickpayorder')->where(array('qp_id'=>$id))->delete();
         if($re){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 分润列表
     * @return [type] [description]
     */
    public function subProfit(){
        $where['log_option']=3;
        $table = M('userwalletlog');
        $count = $table -> count();
        $page = new \Think\Page($count,10);
        $show = $page->show();
        $data = $table ->join('userwallet w on w.user_wallet_id=userwalletlog.log_wallet_id','left')
                        ->join('quickpayorder o on o.qp_id=userwalletlog.other_info','left')
                       -> limit($page->firstRow.','.$page->listRows)  
                       ->where($where)
                       -> order('qp_create_time desc') 
                       -> select();
        foreach ($data as $k => $v) {
            $data[$k]['user']=M('usermanage')->where(array('id'=>$v['wallet_user_id']))->find();
            $data[$k]['feeuser']=M('usermanage')->where(array('id'=>$v['qp_uid']))->find();
        }
        $this->assign('data',$data);
        $this->assign('page',$show);
        $this->display();
    }
}