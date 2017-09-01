<?php
namespace Mrpiadmin\Controller;
use Think\Controller;
use Think\Page;

class UserManageController extends ConController {
    //登录
    public function index(){
        $this->display('login');
    }
    //用户添加
    public function UserAdd(){

        $MP_data=I("post.");

        if($MP_data){

            $MP_data["u_pass"]=md5pass($MP_data["u_pass"]);

            $MP_data["u_account"]=$MP_data["u_mobile"];

            $MP_data["u_code"]=rand_zifu(3,8);

            $MP_data["u_state"]=1;

            $MP_data["u_nick"]=$MP_data["u_mobile"];

            $MP_data["u_times"]=date('Y-m-d H:i:s',time());

            $MP_data["update_at"]=date('Y-m-d H:i:s',time());

            $MP_data["u_member_id"]=$MP_data['u_member_id']; //后台添加 为员工

            $MP_check = M("usermanage")->where(['u_mobile'=>$MP_data["u_mobile"]])->count();

            if(!$MP_check) {

            $MP_sql = M("usermanage")->data($MP_data)->add();

            $user_no=date("ymd").str_pad($MP_sql,12,0,STR_PAD_LEFT); //商户号

            M('usermanage')->where(['id'=>$MP_sql])->setField(['user_no'=>$user_no]);

            //添加level信息
            M('userlevel')->add(['user_id'=>$MP_sql,'parent_id'=>0]);

            //二维码图片生成
            vendor("phpqrcode.phpqrcode");

            $data = HOST.U("Home/Wsite/join_us",array("id"=>$MP_sql));//生成内容

            $lv = "L";//容错级别L,M,Q,H
            $size = 10;//大小1~10
            $path = "./Uploads/".date("Y-m-d",time())."/";//图片保存地址
            if(!file_exists($path)){
                mkdir($path);
            }
            $filename = "yt" . time() . $size .$MP_sql. ".png";//图片名称

            \QRcode::png($data, $path.$filename, $lv, $size);

            if(!empty($MP_data["u_image"])){

                    $logo=LOGO;

                    $QR=$path.$filename;

                    $QR = imagecreatefromstring(file_get_contents($QR));

                    $logo = imagecreatefromstring(file_get_contents($logo));

                    $QR_width = imagesx($QR);//二维码图片宽度

                    $QR_height = imagesy($QR);//二维码图片高度

                    $logo_width = imagesx($logo);//logo图片宽度

                    $logo_height = imagesy($logo);//logo图片高度

                    $logo_qr_width = $QR_width / 5;

                    $scale = $logo_width/$logo_qr_width;

                    $logo_qr_height = $logo_height/$scale;

                    $from_width = ($QR_width - $logo_qr_width) / 2;

                    //重新组合图片并调整大小
                    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

                    $QR_img=imagepng($QR,$path.$filename);

            }
                $QR_img=HOST.substr($path.$filename,1);

                $MP_set=M("usermanage")->where(['id'=>$MP_sql])->setField("u_ewm",$QR_img);

                echo 1;

            }else{

                echo 0;

            }
        }
        else{
            // 获取角色信息
            $role=M('membertype')->select();
            $this->assign('role',$role);
            $this->display();
        }

    }
    //用户列表
    public function UserList(){

        $User = M('usermanage');

        $mobile=$_REQUEST['u_mobile'];

        $parent=I('get.parent');

        $where=[];

        if(session('login_type')=='2'){
            $u_account=session('tringid');
            $where['paths']==array('like','%'.$u_account.'%');
        }

        if(!empty($parent))
           $where['parent_id']=$parent;

        if( !empty( $mobile ) ){
              $where['u_mobile']=array('like','%'.$mobile.'%');
              $this->assign('mobile',$mobile);
        }
        $u_member_id=$_REQUEST['u_member_id'];
        if(!empty($u_member_id)){
            $where['member_id']=$u_member_id;
              $this->assign('u_member_id',$u_member_id);
        }
        $count  = $User->where($where)
                           ->join('userlevel on userlevel.user_id = usermanage.id','left')
                           ->join('membertype m on m.member_id =usermanage.u_member_id','left')
                           ->order('id desc')
                           ->count();

        $Page   = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        if(isset($mobile)) //搜索条件 拼接
            $Page->parameter['u_mobile']=$mobile;

        if(isset($parent)) //上级信息拼接
            $Page->parameter['parent'] = $parent;
        if(isset($u_member_id)){
             $Page->parameter['member_id']=$u_member_id;
        }
        $show  = $Page->show();// 分页显示输出

        $list = $User->where($where)
                     ->join('userlevel on userlevel.user_id = usermanage.id','left')
                     ->join('usercertification uc on uc.usercertification_user_id=usermanage.id','left')
                     ->join('membertype m on m.member_id =usermanage.u_member_id','left')
                     ->order('id desc')
                     ->limit($Page->firstRow.','.$Page->listRows)
                     ->select();

        //处理列表显示数据
        foreach($list as $key=>&$val){

            $val["u_image"]=empty($val["u_image"]) ? DEFAULT_AVATAR:$val["u_image"];

            $val['usercertification_user_state']=$val['usercertification_state']?'已认证':'未认证'; //认证状态

            $val['true_name']=$val['usercertification_state']?$val['usercertification_name']:$val['u_nick']; //真实姓名
            $param['log_option']=1;
            $param['wallet_user_id']=$val['id'];
            // $list[$key]['log']=M('userwallet')
            //                 ->join('userwalletlog l on l.log_wallet_id=userwallet.user_wallet_id','left')
            //                 ->where($param)
            //                 ->find();
            $res=M('userorder')->where(array('order_user_id'=>$val['id']))->order('user_order_id','desc')->limit(1)->select();
            if($res && $res['order_type']=='alipay'){
                $list[$key]['log']="会员升级";
            }
            if($res && $res['order_type']=='后台'){
                $list[$key]['log']="后台升级";
            }
        }
        //显示统计信息
        $role=M('membertype')->select();
        $this->assign('role',$role);

        foreach ($role as $k => $v) {
            $role_name=$v['member_name'];
            $role_id=$v['member_id'];
            $counts[$role_name]=$User->where(array('u_member_id'=>$role_id))->count();
        }
        $this->assign('counts',$counts);

        $this->assign('count',$count);

        $this->assign('list',$list);

        $this->assign('page',$show);

        $this->display();

    }
    //用户提现列表
    public function Cashlist(){

      $get=I('get.');

      $page_size=20;

      $page_now=$get['p']?$get['p']:1;

      $where=[];

      if($get['status'])
          $where['cash_state']=$get['status'];

      $cashs_count=M('usercash')->join('usermanage um on um.id=usercash.cash_user_id','left')->where($where)->count();

      $Page=new Page($cashs_count,$page_size);

      $show= $Page->show();// 分页显示输出

      $cashs=M('usercash')->join('usermanage um on um.id=usercash.cash_user_id','left')->where($where)->page($page_now.','.$page_size)->select();

      $this->assign('show',$show);

      $this->assign('cashs',$cashs);

       $this->assign('status',$get['status']);

       $this->display('Cashlist');


    }
    public function UserList10(){
        $User = M('usermanage'); // 实例化User对象
        $MP_data=M("fordermanage")->where("f_status=1")->field("f_uid")->select();
        $uids="";
        foreach($MP_data as $value){
            $uids.=$value['f_uid'].",";
        }
        $uids=rtrim($uids,',');
        $where['id']=array('in',$uids);
        $where['u_level']=3;
        $count      = $User->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $User->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //        //处理列表显示数据
        foreach($list as $key=>$val){
            $val["u_scale"]=$val["u_scale"]*100;
            $val["u_image"]=empty($val["u_image"]) ? "/Public/web/images/logo.jpg":$val["u_image"];
            if($val['u_level']==3){
                $list[$key]["trose"]=1;
            }

        }
        $this->uids=$uids;
        $this->assign('user_list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    public function UserEdit(){
        $MP_data=I("post.");
        if($MP_data){
            $MP_data["u_scale"]=$MP_data["u_scale"]/100;
            $MP_data["u_account"]=$MP_data["u_mobile"];
            $MP_check=M("usermanage")->where("u_account=".$MP_data["u_mobile"])->count();
            if($MP_check==1) {
                if($MP_data["u_level"]==1){
                    $MP_data["u_isrose"]=1;
                }
                else{
                    $MP_data["u_isrose"]=0;
                }
                $MP_sql = M("usermanage")->where("id=" . $MP_data["id"])->data($MP_data)->save();
                //进行总代理更新
                if($MP_data["u_level"]==1){
                    //进行3级以内数据更新
                    $where["u_thea|u_theb|u_thec"]=$MP_data["id"];
                    $where["u_rose"]=M("usermanage")->where("id=".$MP_data["id"])->getField("u_rose");
                    $where["u_isrose"]=array("eq",0);
                    $data=M("usermanage")->where($where)->count();
                    if($data){
                        $info["u_rose"]=$MP_data["id"];
                        $update=M("usermanage")->where($where)->save($info);
                    }
                    //进行3级以外数据更新
                    $fordata=M("usermanage")->where("u_thea=".$MP_data["id"])->field("id")->select();
                    updaterose($fordata,$MP_data["id"],$where["u_rose"]);
                }
                echo 1;
            }
            else{
                echo 0;
            }
        }
        else{
            $id=I("get.id");
            $MP_sql=M("usermanage")->where("id=".$id)->find();
            if($MP_sql){
                $MP_sql["u_scale"]=$MP_sql["u_scale"]*100;
                $this->assign("u",$MP_sql);
            }
            $this->display();
        }
    }
    public function UserState(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $state=$MP_data["state"];
            $MP_sql=M("usermanage")->where(["id"=>$id])->setField("u_state",$state);
            if($MP_sql){
                echo 1;;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->error("非法操作");
        }
    }
    public function UserPass(){
        $MP_data=I("post.");
        if($MP_data){
            $id=$MP_data["id"];
            $pass=md5pass("888888");
            $MP_sql=M("usermanage")->where("id=".$id)->setField("u_pass",$pass);
            if($MP_sql){
                echo 1;;
            }
            else{
                echo 0;
            }
        }
        else{
            $this->error("非法操作");
        }
    }
    public function UserCash(){
        $MP_data=I("get.");
        $ListWhere['f_types']=array('neq',5);
        if(!empty($MP_data)){
            if(empty($MP_data["starttime"]) && $MP_data["endtime"]){
                $times=NOW_TIME;
                $endtime=str_format_time($MP_data["endtime"],2);
                $ListWhere["f_datetime"]=array("BETWEEN","{$times},{$endtime}");
                $map["endtime"]=$MP_data["endtime"];
            }
            else if(empty($MP_data["endtime"]) && $MP_data["starttime"]){
                $times=NOW_TIME;
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["f_datetime"]=array("BETWEEN","{$starttime},{$times}");
                $map["starttime"]=$MP_data["starttime"];
            }
            else if($MP_data["starttime"] && $MP_data["endtime"]){
                $endtime=str_format_time($MP_data["endtime"],2);
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["f_datetime"]=array("BETWEEN","{$starttime},{$endtime}");
                $map["starttime"]=$MP_data["starttime"];
                $map["endtime"]=$MP_data["endtime"];
            }
            if(!empty($MP_data['uname'])){
                $map['uname']=$MP_data['uname'];
                $map1['u_account|u_mobile']=array('like','%'.$map['uname'].'%');

               $id_arr= M('usermanage')->where($map1)->field('id')->select();
                $ids="";
                foreach ($id_arr as $v){
                    $ids.=$v['id'].',';
                }
                $ListWhere['f_uid']=array('in',rtrim($ids,','));
            }

        }else{
            $ListWhere['f_types']=array('neq',5);
        }

        $data = M('financemanage'); // 实例化User对象
        $count      = $data->where($ListWhere)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        if(!empty($MP_data)){
            $Page->parameter=$map;
        }
        $show       = $Page->show();// 分页显示输出

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $data
            ->alias("f")
            ->join("usermanage u on f.f_uid=u.id")
            ->where($ListWhere)
            ->field("f.*,u.u_account,u.u_mobile")
            ->order(array('f.f_status','f.f_datetime'=>'desc'))
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        //处理列表显示数据
        //        foreach($list as $key=>$val){
        //
        //        }
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->display(); // 输出模板
    }
    public function exportEB(){
        $MP_data=I("get.");
        $ListWhere['f_types']=array('eq',4);
        if(!empty($MP_data)){
            if(empty($MP_data["starttime"]) && $MP_data["endtime"]){
                $times=NOW_TIME;
                $endtime=str_format_time($MP_data["endtime"],2);
                $ListWhere["f_datetime"]=array("BETWEEN","{$times},{$endtime}");
                $map["endtime"]=$MP_data["endtime"];
            }
            else if(empty($MP_data["endtime"]) && $MP_data["starttime"]){
                $times=NOW_TIME;
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["f_datetime"]=array("BETWEEN","{$starttime},{$times}");
                $map["starttime"]=$MP_data["starttime"];
            }
            else if($MP_data["starttime"] && $MP_data["endtime"]){
                $endtime=str_format_time($MP_data["endtime"],2);
                $starttime=str_format_time($MP_data["starttime"],1);
                $ListWhere["f_datetime"]=array("BETWEEN","{$starttime},{$endtime}");
                $map["starttime"]=$MP_data["starttime"];
                $map["endtime"]=$MP_data["endtime"];
            }
            if(!empty($MP_data['uname'])){
                $map['uname']=$MP_data['uname'];
                $map1['u_account|u_mobile']=array('like','%'.$map['uname'].'%');

                $id_arr= M('usermanage')->where($map1)->field('id')->select();
                $ids="";
                foreach ($id_arr as $v){
                    $ids.=$v['id'].',';
                }
                $ListWhere['f_uid']=array('in',rtrim($ids,','));
            }

        }else{
            $ListWhere['f_types']=array('eq',4);
        }

        $data = M('financemanage'); // 实例化User对象
        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = $data
            ->alias("f")
            ->join("usermanage u on f.f_uid=u.id")
            ->where($ListWhere)
            ->field("f.*,u.u_account,u.u_mobile")
            ->order(array('f.f_status','f.f_datetime'=>'desc'))
            ->select();
        //处理列表显示数据
        if(count($list)==0){
            echo "无数据 <a href='".U('UserManage/UserCash',$ListWhere)."'>返回</a>";
            die;
        }
        Vendor('excel.PHPExcel');
        $phpExcel=new \PHPExcel();
        Vendor('excel.PHPExcel.IOFactor');
        Vendor('excel.PHPExcel.style');
        $phpExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $phpExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

        $phpExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $letter=array('A','B','C','D','E','F','G','H');
        $tableheader=array('信息','金额','姓名','银行卡号','开户行','开户行地址','电话','状态');
        for($i = 0;$i < count($tableheader);$i++) {
            $phpExcel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }
        $count = count($list)+1;

        for ($i = 2; $i <= $count; $i++) {
            $phpExcel->getActiveSheet()->getColumnDimension("A".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("B".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("C".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("D".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("E".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("F".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("G".$i)->setAutoSize(true);
            $phpExcel->getActiveSheet()->getColumnDimension("H".$i)->setAutoSize(true);

            $phpExcel->getActiveSheet()->setCellValue('A' . $i, $list[$i-2]['f_text']);
            $phpExcel->getActiveSheet()->setCellValue('B' . $i,"￥".$list[$i-2]['f_price']);
            $phpExcel->getActiveSheet()->setCellValue('C' . $i, $list[$i-2]['f_bname']);
            $phpExcel->getActiveSheet()->setCellValue('D' . $i, $list[$i-2]['f_bnumber']);
            $phpExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            $phpExcel->getActiveSheet()->setCellValue('E' . $i, $list[$i-2]['f_bkname']);
            $phpExcel->getActiveSheet()->setCellValue('F' . $i, $list[$i-2]['f_bkadd']);
            $phpExcel->getActiveSheet()->setCellValue('G' . $i,$list[$i-2]['u_mobile']);
            $phpExcel->getActiveSheet()->getStyle('G'.$i)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
            if($list[$i-2]['f_status']==-1){
                $mes="失败";
            }
            if($list[$i-2]['f_status']==0){
                $mes="待提现";
            }
            if($list[$i-2]['f_status']==1){
                $mes="已提现";
            }
            $phpExcel->getActiveSheet()->setCellValue('H' . $i,$mes);
        }

        header("Content-Transfer-Encoding:binary");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition: attachment; filename="银行卡提现' . time() . '.xls"');
        $phpExcel->createSheet();
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    public function CashSure(){
        $id=I("post.id");
        if($id){
            $info=M("financemanage")->where("id=".$id)->find();
            if($info['f_types']==3){
                $openid=M('usermanage')->where('id='.$info['f_uid'])->getField('u_wxopenid');
                if(empty($openid)){
                    $t['status']=1;
                    $this->ajaxReturn($t);
                }
                //                print_r($openid);
                vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
                $mchPay = new \WxMchPay();
                // 用户openid
                $mchPay->setParameter('openid', $openid);
                // 商户订单号
                $TXorder="TX".time();
                $mchPay->setParameter('partner_trade_no', $TXorder);
                // 校验用户姓名选项
                $mchPay->setParameter('check_name', 'NO_CHECK');
                // 企业付款金额  单位为分
                //                $mchPay->setParameter('amount', 500);
                $tx_money=$info['f_price'];
               $mchPay->setParameter('amount', $tx_money*100);
                //$mchPay->setParameter('amount', 100);
                // 企业付款描述信息
                $mchPay->setParameter('desc', '用户提现');
                // 调用接口的机器IP地址  自定义
                $mchPay->setParameter('spbill_create_ip', get_client_ip()); # getClientIp()
                // 收款用户姓名
                // $mchPay->setParameter('re_user_name', 'Max wen');
                // 设备信息
                // $mchPay->setParameter('device_info', 'dev_server');

                $response = $mchPay->postXmlSSL();
                if(!empty($response)) {
                  //                    $data = simplexml_load_string($response, null, LIBXML_NOCDATA);
                    $data = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
                    if($data["return_code"]=="SUCCESS" && $data["result_code"]=="SUCCESS" && $info["f_status"]==0){
                        $set_data=M("financemanage")->where("id=".$id)->setField("f_status",1);
                        $t['status']=2;
                        $t['info']=$TXorder;
                        $this->ajaxReturn($t);
                    }
                    else{
                        file_put_contents('tx.txt', $data, FILE_APPEND);

                        $t['info']=$data["return_msg"];
                        $t['status']=3;
                        $this->ajaxReturn($t);

                    }
                }else{
                    echo json_encode( array('return_code' => 'FAIL', 'return_msg' => 'transfers_接口出错', 'return_ext' => array()) );
                }
            }else{
                if(M("financemanage")->where("id=".$id)->setField("f_status",1)){

                    $t['status']=4;
                    $this->ajaxReturn($t);
                }else{
                    $t['status']=5;
                    $this->ajaxReturn($t);
                }
            }
        }
    }
    public function CashSureAll(){
        $me=I("post.me");
        if($me==1){
            $info=M("financemanage")->where("status=0 and f_types==3")->select();
            $arr="";
            foreach($info as $k=> $v){
                $openid=M('usermanage')->where('id='.$v['f_uid']." and  u_wxopenid<>''")->getField('u_wxopenid');
                if(empty($openid)){
                    $arr[$k]['oid']=$openid;
                    $arr[$k]['fp']=$v['f_price'];
                    $arr[$k]['id']=$v['id'];
                }


            }
            $status="";
            $TXorderAll="";
            foreach($arr as $key=>$val){
                vendor('Wxpay.WxPayPubHelper.WxPayPubHelper');
                $mchPay = new \WxMchPay();
                // 用户openid
                $mchPay->setParameter('openid', $val['oid']);
                // 商户订单号
                $TXorder="TX".$val['id'].time();
                $mchPay->setParameter('partner_trade_no',$TXorder);
                // 校验用户姓名选项
                $mchPay->setParameter('check_name', 'NO_CHECK');
                // 企业付款金额  单位为分
                //                $mchPay->setParameter('amount', 500);
                $mchPay->setParameter('amount', $val['f_price']*100);
                //$mchPay->setParameter('amount', 100);
                // 企业付款描述信息
                $mchPay->setParameter('desc', '用户提现');
                // 调用接口的机器IP地址  自定义
                $mchPay->setParameter('spbill_create_ip', get_client_ip()); # getClientIp()
                // 收款用户姓名
                // $mchPay->setParameter('re_user_name', 'Max wen');
                // 设备信息
                // $mchPay->setParameter('device_info', 'dev_server');

                $response = $mchPay->postXmlSSL();
                if(!empty($response)) {
                  //                    $data = simplexml_load_string($response, null, LIBXML_NOCDATA);
                    $data = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
                    if($data["return_code"]=="SUCCESS" && $data["result_code"]=="SUCCESS" && $info["f_status"]==0){
                        $set_data=M("financemanage")->where("id=".$val['id'])->setField("f_status",1);
                        $TXorderAll.=$TXorder.",";
                    }
                    else{

                        file_put_contents('tx.txt', $data, FILE_APPEND);
                        $t['info']=$data["return_msg"];
                        $set_data=M("financemanage")->where("id=".$val['id'])->setField("f_status",-1);
                    }
                    $status[]=$set_data;
                }else{
                    echo json_encode( array('return_code' => 'FAIL', 'return_msg' => 'transfers_接口出错', 'return_ext' => array()) );
                }

            }
            if(in_array(0,$status)){
                $t['status']=1;
                    $t['info']=rtrim(',',$TXorderAll);
               $this->ajaxReturn($t);
            }else{
                $t['status']=2;
                $t['info']=rtrim(',',$TXorderAll);
                $this->ajaxReturn($t);
            }

        }else{
            if(M("financemanage")->where("f_types=4")->setField("f_status",1)){
                $t['status']=3;
                $this->ajaxReturn($t);
            }else{
                $t['status']=4;
                $this->ajaxReturn($t);
            }
        }

    }
    public function CashDel(){
        $id=I("post.id");
        if($id){
            $f_info=M("financemanage")->where("id=".$id)->find();
            if($f_info['f_status']!=1){
                $re=M('usermanage')->where('id='.$f_info['f_uid'])->setInc('u_commission',$f_info['f_price']);
            }else{
                $re=true;
            }

            if($re){
                if(M("financemanage")->where("id=".$id)->delete()){
                    echo 1;
                }else{
                    echo 0;
                }
            }
            else{
                echo 0;
            }
        }
    }
    public function UserCon(){
        $uid=I("get.id");
        if($uid){
            //用户个人信息
            $users=M('usermanage')->where(['id'=>$uid])
              ->join('usercertification uc on uc.usercertification_user_id=usermanage.id','left')
              ->find();

            $user_1st=M('userlevel')->join('usermanage um on um.id=userlevel.user_id','right')->where(['parent_id'=>$uid])->select(); //直接用户

            $user_2nd=M('userlevel')->join('usermanage um on um.id=userlevel.user_id','right')->where(['path_3rd'=>['like','%,'.$uid.",%"]])->select(); //间接用户

            $user_2nd_2=M('userlevel')->join('usermanage um on um.id=userlevel.user_id','right')->where(['path_3rd'=>['like','%,'.$uid]])->select(); //间接用户(1)

            foreach($user_2nd_2 as $key=>$val){
                $count=count(explode(',',$val['path_3rd']));
                if($count!=2){
                  unset($user_3rd[$key]);
                }else{
                  $user_2nd[]=$val;
                }
            }


            $user_3rd=M('userlevel')->join('usermanage um on um.id=userlevel.user_id','right')->where(['path_3rd'=>['like','%,'.$uid]])->select(); //三级用户

            foreach($user_3rd as $key=>$val){
                $count=count(explode(',',$val['path_3rd']));
                if($count!=3){
                  unset($user_3rd[$key]);
                }
            }

            $user_parent=M('userlevel')->join('usermanage um on um.id=userlevel.parent_id','right')->where(['user_id'=>$uid])->find();//上级信息

            $wallet_amount=M('userwallet')->where(['wallet_user_id'=>$uid])->select(); //获取钱包信息

            //提现记录
            $crash_list=M('userwalletlog')->join('userwallet uw on uw.user_wallet_id=userwalletlog.log_wallet_id')->where(['wallet_user_id'=>$uid,'log_option'=>2])->select();
            //分润记录
            $profit_list=M('userwalletlog')->join('userwallet uw on uw.user_wallet_id=userwalletlog.log_wallet_id')->where(['wallet_user_id'=>$uid,'log_option'=>3])->select();
            $this->assign('profit_list',$profit_list);
            $this->assign('users',$users);
            $this->assign('user_1st',$user_1st);
            $this->assign('user_2nd',$user_2nd);
            $this->assign('user_3rd',$user_3rd);
            $this->assign('user_parent',$user_parent);
            $this->assign('wallets',$wallet_amount);
            $this->assign('crash_list',$crash_list);
            $this->display();

        }
        else{
            $this->error("非法操作");
        }
    }
    public function AuditAll(){
        $ids=I('post.ids');
        $map['id']=array('in',$ids);
        if(M('usermanage')->where($map)->setField('u_level',2)){
            echo 1;
        }else{
            echo 0;
        }
    }
    public function UserTeam(){
        $map=I('get.');
        if(!empty($map)){
           $lw['u_level']=$map['u_level'];
        }else{
            $lw['u_level']=array('in','1,2,3');
        }
        $MP_user=M("usermanage")->where($lw)->select();
        foreach($MP_user as $k=>$v){
            if($v['u_isrose']==1){
                $where["u_rose"]=$v['id'];
            }
            else{
                $where["u_thea|u_theb|u_thec"]=$v['id'];
            }
            $team=M("usermanage")->where($where)->select();
            $MP_user[$k]['tids']=$v['id'].',';
            foreach($team as $key=>$value){
                $MP_user[$k]['tids'].=$value['id'].",";
            }
            $MP_user[$k]['tids']=rtrim($MP_user[$k]['tids'],',');
            $MP_user[$k]['sale_price']+=M("ordermanage")->where("o_status>0 and o_status<>6 and o_uid in (". $MP_user[$k]['tids'].")")->sum("o_price");
        }
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => 'sale_price',       //排序字段
        );
        $arrSort = array();
        foreach($MP_user AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$sort['field']], constant($sort['direction']), $MP_user);
        $this->list=$MP_user;
        $this->display();

    }
    //用户分成列表
    public function commission(){

      $get=I('get.');

      $users=M('usermanage')->where(['id'=>$get['id']])->find();

      $commission=M('orderfy_commission oc')
            ->join('userorder oy on oy.user_order_id=oc.commission_order','left')
            ->join('usermanage u on oy.order_user_id=u.id','left')
            ->where(['commission_user_id'=>$get['id']])
            ->select();

        $users['u_image']=$users['u_image']?$users['u_image']:DEFAULT_AVATAR;

        $this->assign('commissions',$commission);

        $this->assign('user_info',$users);

        $this->display();
    }
    //会员升级
    public function upgrade(){

        $id=I('post.user_id');
        $u_member_id=I('post.u_member_id');
        $up_level=I('post.up_level');
        $user=M('usermanage')->where(['id'=>$id])->find();

        if(!$user)
          $this->error('用户信息不存在！');

        //查询分成信息
        $sys=M('sysxfmanage')->where(['proxy'=>1])->find();

        $sys_conf=unserialize($sys['info']);
        // 查询升级所需要的费用
        $role=M('membertype')->select();
        foreach ($role as $k => $v) {
            if($v['member_id']==$u_member_id){
                $now_price=$v['member_up_money'];
                $old_level=$v['member_name'];
            }
            if($v['member_id']==$up_level){
                $need_total=$v['member_up_money'];
                $new_level=$v['member_name'];
            }
        }
        $need_price=$need_total-$now_price;
        $order_id=M('userorder')->add(['order_user_id'=>$user['id'],'order_amount'=>$need_price,'order_serial'=>'后台会员升级','order_type'=>'后台','create_at'=>date("Y-m-d H:i:s",time()),'update_at'=>date("Y-m-d H:i:s",time())]);
        if(!$order_id)
            $this->error('订单信息生成失败！请重试');

        $order=new \Mrpiadmin\Controller\OrderfyController();
        $params['need_price']=$need_price;
        $params['u_member_id']=$u_member_id;
        $params['up_level']=$up_level;
        $params['up_level_name']=$new_level;
        $result=$order->sure($order_id,2,$params);

        if($result['status']==4){
             //加入用户升级等级log区分是后台升级还是前台升级的
             $arr=array(
                'lel_uid'=>$user['id'],
                'lel_old_level'=>$u_member_id,
                'lel_new_level'=>$up_level,
                'lel_old_name'=>$old_level,
                'lel_new_name'=>$new_level,
                'lel_money'=>$need_price,
                'lel_type'=>0,//0后台升级1自主升级
                'lel_time'=>time(),
            );
            M("level_up_log")->add($arr);
            // $res['info']="升级成功";
            // $this->ajaxReturn($res);
          $this->success('升级成功！');
        }else{
          $this->error("升级失败！错误信息：".$result['msg']);
           // $res['info']="升级失败！错误信息：".$result['msg'];
           // $this->ajaxReturn($res);
        }

    }
    public function reject(){ //驳回提现申请
      $get=I('get.');
      if(false===M('usercash')->where(['cash_id'=>$get['id']])->setField(['cash_state'=>3]))
          $this->error('驳回失败！请重新处理');

      $this->success('处理成功！',U('UserManage/Cashlist'));

    }
    public function refuse(){ //拒绝提现申请
        $get=I('get.');

        $usercash=M('usercash');

        $usercash->startTrans();

        $user_cash=$usercash->where(['cash_id'=>$get['id']])->find();

        $cash_state=$usercash->where(['cash_id'=>$get['id']])->setField(['cash_state'=>4]); //返回余额

        $wallet=M('userwallet')->where(['wallet_user_id'=>$user_cash['cash_user_id']])->find();

        $wallet_amount=bcadd($wallet['wallet_amount'],bcadd($user_cash['cash_amount'],$user_cash['service_charge'],4),4);

        $update_wallet=M('userwallet')
              ->where(['wallet_user_id'=>$user_cash['cash_user_id'],'wallet_amount'=>$wallet['wallet_amount']])
              ->setField(['wallet_amount'=>$wallet_amount]);

        if(false!=$update_wallet && false!=$cash_state){

              $usercash->commit();

              $this->success('申请已拒绝！',U('UserManage/Cashlist'));

        }else{

            $usercash->rollback();

            $this->error('处理失败！',U('UserManage/Cashlist'));
        }

    }
    /**
     * [账号封禁]
     */
    public function UserDel(){
        $id=I('post.id');
        $arr['status']=0;
        $res=M("usermanage")->where(['id'=>$id])->save($arr);
        echo $res;die;
    }
    /**
     * 账号解禁
     */
    public function UserOpen(){
        $id=I('post.id');
        $arr['status']=1;
        $res=M("usermanage")->where(['id'=>$id])->save($arr);
        echo $res;die;
    }
}
