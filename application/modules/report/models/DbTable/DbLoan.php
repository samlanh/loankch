<?php
class Report_Model_DbTable_DbLoan extends Zend_Db_Table_Abstract
{
      public function getAllLoan($search = null){//rpt-loan-released/
      	 $db = $this->getAdapter();
      	 $sql = "SELECT * FROM v_loanreleased WHERE 1 ";
      	 $where ='';
      
	    $from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
	    $where.= " AND ".$from_date." AND ".$to_date;

        if($search['branch_id']>0){
    		$where.=" AND branch_id = ".$search['branch_id'];
    	}
    	if($search['client_name']>0){
    		$where.=" AND client_id = ".$search['client_name'];
    	}
    	if($search['co_id']>0){
    		$where.=" AND co_id = ".$search['co_id'];
    	}
    	if($search['pay_every']>0){
    		$where.=" AND pay_term_id = ".$search['pay_every'];
    	}
    	
      	 if(!empty($search['adv_search'])){
      	 	$s_where = array();
      	 	$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " branch_name LIKE '%{$s_search}%'";
      	 	$s_where[] = " loan_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " client_name LIKE '%{$s_search}%'";
      	 	$s_where[] = " co_name LIKE '%{$s_search}%'";
      	 	$s_where[] = " total_capital LIKE '%{$s_search}%'";
      	 	$s_where[] = " other_fee LIKE '%{$s_search}%'";
      	 	$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      	 	$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      	 	$s_where[] = " loan_type LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
      	 }
      	 $order = " ORDER BY member_id DESC ";
      	 return $db->fetchAll($sql.$where.$order);
      }
      public function getAllDailyLoan($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$end_date = $search['end_date'];
      	$sql = "SELECT v.*,
      		(SELECT COUNT(f.member_id) FROM `ln_loanmember_funddetail` as f WHERE f.member_id=v.member_id AND f.status=1 AND f.is_completed=1 limit 1) AS installment_paid,
      		(SELECT count(f.id) FROM ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' GROUP BY v.member_id ORDER BY f.`date_payment` ASC LIMIT 1) AS amount_late,
      		(SELECT SUM(f.total_principal) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."'  LIMIT 1) AS total_principal,
      		(SELECT SUM(f.principle_after) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' LIMIT 1) AS principle_after,
      		(SELECT SUM(f.total_interest_after) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' LIMIT 1) AS total_interest_after,
      		(SELECT SUM(f.total_payment_after) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' LIMIT 1) AS total_payment_after,
			(SELECT SUM(f.penelize) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' LIMIT 1) AS penelize,
      		(SELECT SUM(f.service_charge) FROM  ln_loanmember_funddetail as f WHERE f.is_completed=0 AND f.member_id=v.member_id AND f.date_payment <'".$end_date."' LIMIT 1) AS service_charge
      	  FROM v_dailyloan AS v WHERE 1 ";
      	$where ='';
      
      	$from_date =(empty($search['start_date']))? '1': " v.date_release >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " v.date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      
      	if($search['branch_id']>0){
      		$where.=" AND v.branch_id = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND v.client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND v.co_id = ".$search['co_id'];
      	}
      	if($search['pay_every']>0){
      		$where.=" AND v.pay_term_id = ".$search['pay_every'];
      	}
      	 
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " v.branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " v.client_number LIKE '%{$s_search}%'";
      		$s_where[] = " v.client_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.co_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " v.interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " v.loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ( '.implode(' OR ',$s_where).')';
      	}
      	$order = " ORDER BY (CASE DAYOFWEEK(v.first_payment) WHEN 1 THEN 8 ELSE DAYOFWEEK(v.first_payment) END),v.first_payment DESC ";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllLoanCo($search = null){//rpt-loan-released
      	$db = $this->getAdapter();

      	$sql = "SELECT * FROM v_released_co WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['member']>0){
      		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if($search['pay_every']>0){
      		$where.=" AND pay_term_id = ".$search['pay_every'];
      	}
      	if($search['status']>-1){
      		$where.=" AND is_completed = ".$search['status'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " other_fee LIKE '%{$s_search}%'";
      		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      		$s_where[] = " name_en LIKE '%{$s_search}%'";
      		$s_where[] = " total_duration LIKE '%{$s_search}%'";
      		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order = " ORDER BY co_id DESC";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllOutstadingLoan($search=null){
      	$db = $this->getAdapter();
      	$where="";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= "  AND ".$to_date;

      	$sql="SELECT * FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
      	if($search['branch_id']>0){
      		$where.=" AND br_id = ".$search['branch_id'];
      	}
      	if($search['member']>0){
           		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if($search['status_use']>-1){
      		$where.=" AND is_badloan = ".$search['status_use'];
      	}
      	
		if(@$search['pay_every']>0){
      		if($search['pay_every']==1){
      			$pay='Day';
      		}elseif($search['pay_every']==2){
      			$pay='Week';
      		}else{
      			$pay='Month';
      		}
      		$where.= " AND pay_term  LIKE '%{$pay}%'";
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_kh LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " total_duration LIKE '%{$s_search}%'";
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$s_where[] = " total_payment LIKE '%{$s_search}%'";
      	   $where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	
//       	echo $sql.$where;
      	return $db->fetchAll($sql.$where);
      }
      
      
      public function getALLLoancollect($search = null){
      	$db = $this->getAdapter();
//       	$sql="SELECT id,
//       	(SELECT loan_number FROM ln_loan_member WHERE loan_number=(SELECT lm.loan_number FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 ) AS loan_number,
//       	(SELECT name_kh FROM ln_client WHERE client_id = (SELECT lm.client_id FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 ) AS client_name
//       	,(SELECT branch_namekh FROM ln_branch WHERE br_id= branch_id LIMIT 1) AS branch_id,
//       	(SELECT co_khname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id=(SELECT lm.client_id FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 )LIMIT 1 ) AS co,
//       	total_principal,total_interest,STATUS
//       	,total_payment,date_payment FROM ln_loanmember_funddetail WHERE 1 ";
      	
      	$from_date =(empty($search['start_date']))? '1': "f.date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': "f.date_payment <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	$Other =" ORDER BY co_name ,id DESC ";
      	$sql = " SELECT 
				  f.id ,
				  f.total_principal ,
				  f.total_interest ,
				  f.status ,
				  f.total_payment ,
				  f.date_payment ,
				  m.loan_number ,  
				  (SELECT name_kh FROM ln_client WHERE client_id=m.client_id) AS client_name , 
				  (SELECT branch_namekh FROM ln_branch WHERE br_id= m.branch_id LIMIT 1) AS branch_id ,
				  (SELECT co_khname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co,
				  (SELECT co_firstname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co_name
				  FROM `ln_loanmember_funddetail` AS f ,`ln_loan_member` AS m WHERE m.member_id = f.member_id 
				  AND f.is_completed=0 AND f.status=1 AND m.is_completed=0 ";
      	if(!empty($search['txtsearch'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['txtsearch']));
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[]=" client_name LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	
      	}
      	//echo $sql.$where.$Other;
      	return $db->fetchAll($sql.$where.$Other);
      }
     
      public function getALLGroupDisburse($id=null){
      	$db = $this->getAdapter();
      	$sql="SELECT *  
				FROM
				`v_loangroupmember` WHERE `group_id`= $id";
      	
      	//$Other =" ORDER BY member_id ASC";
//       	$where = '';
//       	if(!empty($search['txtsearch'])){
//       		$s_where = array();
//       		$s_search = $search['txtsearch'];
//       		$s_where[] = " chart_id LIKE '%{$s_search}%'";
//       		$s_where[]=" group_id LIKE '%{$s_search}%'";
//       		$where .=' AND '.implode(' OR ',$s_where).'';      		 
      //	}
      	return $db->fetchAll($sql);
      }
      public function getALLPayment(){
      	$db = $this->getAdapter();
      	$sql="select * from ln_client_receipt_money";
      	return $db->fetchAll($sql);
      }
      public function getALLLoanlate($search = null){
   		$end_date = $search['end_date'];
      	$db = $this->getAdapter();
		$sql=" SELECT 
				  CONCAT(`co_firstname`,' ',co.`co_lastname`) AS co_name ,
				  b.branch_namekh,
				  co.`co_id`,
				  c.`client_number`,
				  c.`name_kh`,
				(SELECT `ln_village`.`village_name` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `c`.`village_id`) limit 1) AS `village_name`,
				(SELECT ln_commune.`commune_name` FROM `ln_commune` WHERE (ln_commune.`com_id` = `c`.`com_id`) LIMIT 1) AS `commune_name`,
				(SELECT `d`.`district_name` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `district_name`,
				(SELECT province_en_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name,
				  c.`phone`,
				  lm.`total_capital`,
				  lm.`loan_number`,
				  lm.`interest_rate`,
				  lg.`date_release`,
				  lg.`date_line`,
				  lg.`total_duration`,
				lg.`time_collect`,
				  lm.`currency_type` AS curr_type,
				  lm.`collect_typeterm`,
				  lm.`pay_after`,
				  (SELECT `ln_currency`.`symbol` FROM `ln_currency` WHERE (`ln_currency`.`id` = lm.`currency_type` ) limit 1) AS `currency_type`,
				  (SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = `lg`.`pay_term`)) limit 1) AS `Term Borrow`,
				  (SELECT `crm`.`date_input` FROM (`ln_client_receipt_money` `crm` JOIN `ln_client_receipt_money_detail` `crmd`) WHERE ((`crm`.`loan_number` = lm.`loan_number`)
								          AND (`crm`.`id` = `crmd`.`crm_id`) AND (`crmd`.`lfd_id` = f.`id`)) ORDER BY `crm`.`date_input` DESC LIMIT 1) AS `last_pay_date`,
				  SUM(f.`total_principal`) AS total_principal,
				  SUM(f.`principle_after`) AS principle_after,
				  SUM(f.`total_interest_after`) AS total_interest_after,
				  SUM(f.`total_payment_after`) AS total_payment_after,
				  SUM(f.`penelize`) AS penelize,
				  SUM(f.`service_charge`) AS service_charge,
				  f.`date_payment` ,
				  COUNT(lm.`group_id`) AS amount_late,
				 f.`branch_id`
				FROM
				  `ln_loanmember_funddetail` AS f,
				  `ln_loan_group` AS lg,
				  `ln_loan_member` AS lm,
				  `ln_co` AS co,
				  `ln_client` AS c ,
      			  `ln_branch` AS b 
				WHERE f.`is_completed` = 0 
				  AND `lg`.`is_badloan`=0
				  AND lg.`g_id` = lm.`group_id` 
				  AND lm.`member_id` = f.`member_id` 
				  AND lg.`status` = 1 
				  AND lm.`status` = 1 
				  AND f.`status`=1
				  AND co.`co_id` = lg.`co_id` 
				  AND c.`client_id` = lm.`client_id` 
				  AND b.`br_id`=f.`branch_id`
				  AND lm.is_reschedule!=1 ";
      	$where='';
      	if(!empty($search['adv_search'])){
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " b.branch_namekh LIKE '%{$s_search}%'";
      		$s_where[] = "  lm.`currency_type` LIKE '%{$s_search}%'";
      		$s_where[] = " lm.loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      		$s_where[] = " f.principle_after LIKE '%{$s_search}%'";
      		$s_where[] = " f.principal_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " f.total_interest_after LIKE '%{$s_search}%'";
      		$s_where[] = " f.total_payment_after LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if(($search['co_id']>0)){
    		//$where.=" AND f.collect_by =".$search['co_id'];
    		$where.=" AND co.`co_id` =".$search['co_id'];//before use collect by
    	}  
    	 
    	
      	if(!empty($search['end_date'])){
			$where.=" AND f.date_payment < '$end_date'";
		}
      	if($search['branch_id']>0){
      		$where.=" AND f.`branch_id` = ".$search['branch_id'];
      	}
      
      	//$order = " ORDER BY currency_type ,date_payment ASC ";
        $group_by = " GROUP BY lm.`group_id` ORDER BY f.`date_payment` ASC";
        return $db->fetchAll($sql.$where.$group_by);
      }
      public function getALLLoandateline(){
      	//$to_date = (empty($search['to_date']))? '1': "date_payment <= '".$search['to_date']." 23:59:59'";
      	$db = $this->getAdapter();
//       	$sql="select g.level,(select first_name from rms_users where id=g.group_id) as first_name,(select last_name from rms_users where id=g.co_id)as last_name
// 		,g.zone_id,g.date_release,g.date_line,g.create_date,g.total_duration,g.first_payment,g.time_collect
// 		,g.collect_typeterm,g.pay_term,g.payment_method,g.holiday,g.is_renew,g.branch_id,g.loan_type,g.status,g.is_verify,g.is_badloan,g.teller_id
// 		,m.chart_id,m.member_id,m.loan_number,m.currency_type,m.total_capital,m.admin_fee,m.interest_rate,m.loan_cycle,m.loan_purpose,m.pay_before
// 		,m.pay_after,m.graice_period,m.amount_collect_principal,m.show_barcode,m.is_completed,m.semi from ln_loan_group as g,ln_loan_member as m where m.group_id = g.g_id";
      	$sql="SELECT g.level,(SELECT first_name FROM rms_users WHERE id=g.group_id) AS first_name,(SELECT last_name FROM rms_users WHERE id=g.co_id)AS last_name
		,g.zone_id,g.date_release,g.`date_release` AS date_line,g.create_date,g.total_duration,g.first_payment,g.time_collect
		,g.collect_typeterm,g.pay_term,g.payment_method,g.holiday,g.is_renew,g.branch_id,g.loan_type,g.status,g.is_verify,g.is_badloan,g.teller_id
		,m.chart_id,m.member_id,m.loan_number,m.currency_type,m.total_capital,m.admin_fee,m.interest_rate,m.loan_cycle,m.loan_purpose,m.pay_before
		,m.pay_after,m.graice_period,m.amount_collect_principal,m.show_barcode,m.is_completed FROM ln_loan_group AS g,ln_loan_member AS m WHERE m.group_id = g.g_id";
      	return $db->fetchAll($sql);
      }
      public function getALLLoanTotalcollect($search=null){
//       	$to_date = (empty($search['to_date']))? '1': "date_payment <= '".$search['to_date']." 23:59:59'";
       	$db = $this->getAdapter();
        $start_date = $search['start_date'];
   		$end_date = $search['end_date'];
		$sql="SELECT * FROM v_getcollect WHERE is_completed = 0 ";
		$where ='';		
		if(!empty($search['start_date']) or !empty($search['end_date'])){
			$where.=" AND date_payment BETWEEN '$start_date' AND '$end_date'";
		}
		if($search['branch_id']>0){
			$where.=" AND branch_id= ".$search['branch_id'];
		}
		if($search['client_name']>0){
			$where.=" AND client_id = ".$search['client_name'];
		}
        if($search['co_id']>0){
			$where.=" AND collect_by = ".$search['co_id'];
		}
		if(!empty($search['adv_search'])){
			//print_r($search);
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " client_name LIKE '%{$s_search}%'";
			$s_where[] = " co_name LIKE '%{$s_search}%'";
			$s_where[] = " total_principal LIKE '%{$s_search}%'";
			$s_where[] = " principal_permonth LIKE '%{$s_search}%'";
			$s_where[] = " total_interest LIKE '%{$s_search}%'";
			$s_where[] = " total_payment LIKE '%{$s_search}%'";
			$s_where[] = " amount_day LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order=" ORDER BY currency_type DESC ";
		//echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanPayment($search=null){
      	$db = $this->getAdapter();
      	$sql="SELECT * FROM v_getcollectmoney WHERE status=1 ";
      	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	if($search['branch_id']>0){
      		$where.=" AND branch_id= ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	} 
        	if($search['co_id']>0){
        		$where.=" AND co_id = ".$search['co_id'];
        	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_principal_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest LIKE '%{$s_search}%'";
      		//$s_where[] = " amount_payment LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amount LIKE '%{$s_search}%'";
      		$s_where[] = " service_charge LIKE '%{$s_search}%'";      		
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC ";
      	return $db->fetchAll($sql.$where.$order);
      	 
      }
      public function getALLCommission($search=null){
      	$db = $this->getAdapter();
      	$sql="SELECT * FROM v_comissionco WHERE status=1 ";
      	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	 
      	if($search['branch_id']>0){
      		$where.=" AND branch_id= ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_principal_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest LIKE '%{$s_search}%'";
      		//$s_where[] = " amount_payment LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amount LIKE '%{$s_search}%'";
      		$s_where[] = " service_charge LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC ";
      		return $db->fetchAll($sql.$where.$order);
      
      }
      
      
      
      
      
      
      
      public function getALLLoanIcome($search=null){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM v_getcollectmoney where status=1 ";
//     	$sql = "SELECT lcrm.`id`,
// 					lcrm.`receipt_no`,
// 					lcrm.`loan_number`,lcrm.service_charge,
// 					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`group_id`) AS team_group ,
// 					lcrm.`total_principal_permonth`,
// 					lcrm.`total_payment`,
//     			  (SELECT symbol FROM `ln_currency` WHERE id =lcrm.currency_type) AS currency_typeshow ,lcrm.currency_type,
// 					lcrm.`recieve_amount`,
// 					lcrm.`total_interest`,lcrm.amount_payment,
// 					lcrm.`penalize_amount`,
// 					lcrm.`date_pay`,
// 					lcrm.`date_input`,
// 				    (SELECT co.`co_khname` FROM `ln_co` AS co WHERE co.`co_id`=lcrm.`co_id`) AS co_name,
//     				(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lcrm.`branch_id`) AS branch
// 				FROM `ln_client_receipt_money` AS lcrm WHERE lcrm.is_group=0 AND lcrm.`status`=1";
    	$where ='';
    	if(!empty($search['advance_search'])){
    		//print_r($search);
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = "client_name LIKE '%{$s_search}%'";
    		$s_where[] = " client_number LIKE '%{$s_search}%'";
    		$s_where[] = " co_name LIKE '%{$s_search}%'";
    		$s_where[] = " loan_number LIKE '%{$s_search}%'";
    		
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']!=""){
    		$where.= " AND status = ".$search['status'];
    	}
    	
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND date_input BETWEEN '$start_date' AND '$end_date'";
    	}
    	if($search['client_name']>0){
    		$where.=" AND client_id = ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND branch_id= ".$search['branch_id'];
    	}
    	if($search['co_id']>0){
    		$where.=" AND `co_id`= ".$search['co_id'];
    	}    	
    	//$where='';
    	$order="";
    	$order = " ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
      }
      
      public function getALLLoanCollectionco($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	 
      	$db = $this->getAdapter();
		$sql =" SELECT 
				  crm.`receipt_no`,
				  crm.`date_input`,
				  crm.`co_id`,
				  crm.`payment_option`,
				  crm.`recieve_amount`,
				  crmd.`loan_number`,
				  (SELECT c.`phone` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS phone,
				  (SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=crm.`branch_id`) AS branch,
				  (SELECT CONCAT(c.`co_code`,'-',c.`co_khname`,'-',c.`co_firstname`,' ',c.`co_lastname`) FROM ln_co AS c WHERE c.`co_id`=crm.`co_id`) AS co_name,
				  (SELECT c.`client_number` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS client_code,
				  (SELECT c.`name_kh` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS client_name,
				  lg.`loan_type`,
				  lg.`total_duration`,
				  lg.`time_collect`,
				  lg.`collect_typeterm`,
				  lg.`date_release`,
				  lg.`date_line`,
				  lm.`interest_rate`,
				  lm.`total_capital` as capital,
				 `crm`.`total_principal_permonth` AS `principle_amount`,
				 
				 (crm.`total_interest`) AS interest,
				 (crm.`penalize_amount`) AS penelize,
				 (crm.`service_charge`) AS service,
				 
				 crm.`currency_type` AS curr_type,
				 crmd.`date_payment`,
				 
				SUM(crm.`return_amount`) AS return_amount,
				SUM(crm.`recieve_amount`) AS amount_recieve,
				SUM(`crm`.`total_payment`) AS `payment`,
				
				SUM(crmd.`capital`) AS total_printciple,
				SUM(crmd.`principal_permonth`) AS total_principal_permonth,
				SUM(crmd.`total_payment`) AS total_payment,
				SUM(crmd.`total_interest`) AS total_interest,
				SUM(crmd.`total_recieve`) AS recieve_amount,
				SUM(crmd.`penelize_amount`) AS penelize_amount,
				SUM(crmd.`service_charge`) AS service_charge,
				
				(SELECT `ln_currency`.`symbol` FROM `ln_currency` WHERE (`ln_currency`.`id` = crm.`currency_type`)) AS `currency_type`,
      			(SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = (SELECT lg.`pay_term` FROM `ln_loan_group` AS lg WHERE lg.`g_id`=(SELECT `group_id` FROM `ln_loan_member` AS lm WHERE lm.`member_id`=(SELECT f.`member_id` FROM `ln_loanmember_funddetail` AS f WHERE f.`id`=crmd.`lfd_id`)))))) AS name_en
				FROM
				  `ln_client_receipt_money` AS crm,
				  `ln_client_receipt_money_detail` AS crmd,
				  `ln_loan_member` AS lm,
				  `ln_loan_group` AS lg,
				  `ln_loanmember_funddetail` AS lf 
				WHERE crmd.`lfd_id` = lf.`id` 
				AND crmd.`crm_id`=crm.`id`
				  AND lf.`member_id`=lm.`member_id`
				  AND lm.`group_id`=lg.`g_id` ";
      	$where ='';
      	if(!empty($search['advance_search'])){
      		//print_r($search);
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " crmd.`loan_number` LIKE '%{$s_search}%'";
      		$s_where[] = " crm.`receipt_no` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`total_payment` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`total_interest` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`penelize_amount` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`service_charge` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']!=""){
      		$where.= " AND crm.status = ".$search['status'];
      	}
      	 
      	if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND crm.`date_input` BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['client_name']>0){
      		$where.=" AND crmd.`client_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND crm.`branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND crm.`co_id`= ".$search['co_id'];
      	}
      	if($search['paymnet_type']>0){
      		$where.=" AND crm.`payment_option`= ".$search['paymnet_type'];
      	}
      	 
      	
      	$groupby=" GROUP BY lm.`group_id`,crm.`date_input` ORDER BY crm.`co_id` , crm.`date_input` DESC ";
      	return $db->fetchAll($sql.$where.$groupby);
      }
      public function getALLLFee($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	 
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM 
      				v_loanreleased WHERE 1 ";
//       	$sql = "SELECT m.member_id,(SELECT branch_namekh FROM ln_branch WHERE br_id = m.branch_id LIMIT 1) AS branch,
// 				m.loan_number,m.admin_fee,m.other_fee,m.currency_type,(SELECT symbol FROM `ln_currency` WHERE id =m.currency_type) AS currency_typeshow,
// 				g.date_release
// 				FROM ln_loan_member AS m, `ln_loan_group` AS g WHERE m.group_id = g.g_id AND m.`status`=1";
		$where ='';
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      		$s_where[] = " other_fee LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['branch_id']>0){
      		$where.=" AND `branch_id`= ".$search['branch_id'];
      	}
//       	if($search['status']!=""){
//       		$where.= " AND status = ".$search['status'];
//       	}
      	if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND date_release BETWEEN '$start_date' AND '$end_date'";
      	}
      	//$where='';
      	$order = " ORDER BY currency_type";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanPayoff($search=null){
     
      
      	$db = $this->getAdapter();
      	$sql = " SELECT * FROM v_getloanpayoff WHERE 1 ";

      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
      	$where= " AND ".$from_date." AND ".$to_date;
      	 
      	if(!empty($search['advance_search'])){

      		$s_where = array();
      		$s_search = trim(addslashes($search['advance_search']));
//       		echo print_r($search['advance_search']);
      		$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
//       		$s_where[] = " `receipt_no` LIKE '%{$s_search}%'";
      		$s_where[] = " `total_payment` LIKE '%{$s_search}%'";
      		$s_where[] = " `total_interest` LIKE '%{$s_search}%'";
//       		$s_where[] = " `penalize_amount` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']!=""){
      		$where.= " AND status = ".$search['status'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND `group_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND `branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND `co_id`= ".$search['co_id'];
      	}
//       	if($search['paymnet_type']>0){
//       		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
//       	}
      
      	//$where='';
      	$order = " ORDER BY id DESC ";
//      echo $sql.$where.$order;
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanExpectIncome($search=null){
      	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	$where= " AND ".$from_date." AND ".$to_date;
      	
      	$db = $this->getAdapter();
      	$sql = "SELECT *,SUM(total_capital) AS total_capital ,
      					SUM(principle_permonth) AS principle_permonth
      	 			   ,SUM(total_interest) AS total_interest  
      	                FROM `v_getexpectincome` WHERE 1 ";
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " total_duration LIKE '%{$s_search}%'";
      		$s_where[] = " principle_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest LIKE '%{$s_search}%'";
      		
      		$where .=' AND ( '.implode(' OR ',$s_where).')';
      	}
      	if($search['status']!=""){
      		$where.= " AND status = ".$search['status'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	$group_by = " GROUP BY group_id ,date_payment ORDER BY currency_type ASC, date_payment DESC ";
      	return $db->fetchAll($sql.$where.$group_by);
      }
      public function getALLBadloan($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      
      	$db = $this->getAdapter();
    	
    	$sql = "SELECT l.id,loan_number,b.branch_namekh,
    	CONCAT((SELECT client_number FROM `ln_client` WHERE client_id = l.client_code LIMIT 1),' - ',		
    	(SELECT name_en FROM `ln_client` WHERE client_id = l.client_code LIMIT 1)) AS client_name_en,
  		l.loss_date, l.`cash_type`,(SELECT c.symbol FROM `ln_currency` AS c WHERE c.status = 1 AND c.id = l.`cash_type`) AS currency_typeshow,
		l.total_amount ,l.intrest_amount ,CONCAT (l.tem,' Days')as tem,l.note,l.date,l.status FROM `ln_badloan` AS l,ln_branch AS b 
		WHERE b.br_id = l.branch AND l.is_writoff= 0";    	
    	$where='';
    	if(($search['status']>0)){
    		$where.=" AND l.status =".$search['status'];
    	}
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND l.date BETWEEN '$start_date' AND '$end_date'";
    	}
    	if(!empty($search['branch'])){
    		$where.=" AND b.br_id = ".$search['branch'];
    	}
    	if(!empty($search['client_name'])){
    		$where.=" AND l.client_code = ".$search['client_name'];
    	}
    	if(!empty($search['client_code'])){
    		$where.=" AND l.client_code = ".$search['client_code'];
    	}
    	if(!empty($search['Term'])){
    		$where.=" AND l.tem = ".$search['Term'];
    	}
    	if(!empty($search['cash_type'])){
    		$where.=" AND l.`cash_type` = ".$search['cash_type'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]=" l.note LIKE '%{$s_search}%'";
    		$s_where[]=" total_amount LIKE '%{$s_search}%'";
    		$s_where[]=" intrest_amount LIKE '%{$s_search}%'";
    		$s_where[]=" l.tem = '{$s_search}' ";
    		$where .=' AND ('.implode(' OR ',$s_where).' )';
    	}
    	$order = ' ORDER BY l.`cash_type` ';
//     	echo $sql.$where;exit();
    	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLWritoff($search=null){
      	$db = $this->getAdapter();
      	 $sql = " SELECT  
		 v.*,ci.phone,
            (SELECT `ln_village`.`village_name` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `ci`.`village_id`) limit 1) AS `village_name`,
			(SELECT `c`.`commune_name` FROM `ln_commune` `c` WHERE (`c`.`com_id` = `ci`.`com_id`) LIMIT 1) AS `commune_name`,
			(SELECT `d`.`district_name` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `ci`.`dis_id`) LIMIT 1) AS `district_name`,
			(SELECT province_en_name FROM `ln_province` WHERE province_id= ci.pro_id  LIMIT 1) AS province_en_name	
		 FROM  v_badloan AS v,ln_client AS ci WHERE v.client_id =ci.client_id ";
      	$where='';
      	$from_date =(empty($search['start_date']))? '1': " v.payof_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " v.payof_date <= '".$search['end_date']." 23:59:59'";
      	
      	$where.= " AND ".$from_date." AND ".$to_date;

      	if(!empty($search['branch'])){
      		$where.=" AND v.branch_id = ".$search['branch'];
      	}
      	if(!empty($search['client_name'])){
      		$where.=" AND v.client_id = ".$search['client_name'];
      	}
      	if(($search['co_id'])>0){
      		$where.=" AND v.co_id = ".$search['co_id'];
      	}
      	if(!empty($search['Term'])){
      		$where.=" AND v.tem = ".$search['Term'];
      	}
      	if(!empty($search['cash_type'])){
      		$where.=" AND v.`curr_type` = ".$search['cash_type'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where=array();
      		$s_search=addslashes(trim($search['adv_search']));
      		$s_where[] = " v.branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " v.client_number LIKE '%{$s_search}%'";
      		$s_where[] = " v.client_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.co_name LIKE '%{$s_search}%'";
      		$s_where[] = " v.total_capital LIKE '%{$s_search}%'";
      		
      		$s_where[] = " v.interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " v.loan_type LIKE '%{$s_search}%'";
      		
      		$where .=' AND ('.implode(' OR ',$s_where).' )';
      	}
       	$order = ' ORDER BY v.co_name ';
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLNPLLoan($search=null){
      	    $db = $this->getAdapter();
      		$end_date =(empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	    $db = $this->getAdapter();

      		$sql="SELECT * FROM v_getnplloan ";
      		$where=" WHERE ".$end_date;
      		if(!empty($search['adv_search'])){
      			
      			$s_where = array();
      			$s_search = addslashes(trim($search['adv_search']));
      			$s_where[] = " branch_name LIKE '%{$s_search}%'";
      			$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
      			$s_where[] = " `client_number` LIKE '%{$s_search}%'";
      			$s_where[] = " `name_kh` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_capital` LIKE '%{$s_search}%'";
      			$s_where[] = " `interest_rate` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_duration` LIKE '%{$s_search}%'";
      			$s_where[] = " `term_borrow` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_principal` LIKE '%{$s_search}%'";
      			
      			$where .=' AND ('.implode(' OR ',$s_where).')';
      		}
      		if($search['branch_id']>0){
      			$where.=" AND `branch_id` = ".$search['branch_id'];
      		}
      		if(!empty($search['cash_type'])){
      			$where.=" AND `curr_type` = ".$search['cash_type'];
      		}
      		return $db->fetchAll($sql.$where);
      }
      public function getAllxchange($search = null){
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM `v_xchange` WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': "statusDate >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': "statusDate <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	
//       	if($search['branch_id']>0){
//       		$where.=" AND branch_id = ".$search['branch_id'];
//       	}
//       	if($search['client_name']>0){
//       		$where.=" AND client_id = ".$search['client_name'];
//       	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addcslashes($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " changedAmount LIKE '%{$s_search}%'";
      		$s_where[]=" fromAmount LIKE '%{$s_search}%'";
      		$s_where[] = " rate LIKE '%{$s_search}%'";
      		$s_where[]=" recieptNo LIKE '%{$s_search}%'";
      		$s_where[] = " recievedAmount LIKE '%{$s_search}%'";
      		$s_where[]=" status_in LIKE '%{$s_search}%'";
      		$s_where[] = " statusDate LIKE '%{$s_search}%'";
      		$s_where[]=" toAmount LIKE '%{$s_search}%'";
      		$s_where[]=" toAmountType LIKE '%{$s_search}%'";
      		$s_where[]=" fromAmountType LIKE '%{$s_search}%'";
      		$s_where[]=" from_to LIKE '%{$s_search}%'";
      		$s_where[]=" recievedType LIKE '%{$s_search}%'";
      		$s_where[]=" specail_customer LIKE '%{$s_search}%'";
      		
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	
      	}
      	$order=" ORDER BY id DESC";
//       	echo $sql.$where;
      	return $db->fetchAll($sql.$where.$order);
      	
      } 
      public function getRescheduleLoan($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM v_rescheduleloan WHERE 1";
      	$where ='';
      
      	$from_date =(empty($search['start_date']))? '1': " reschedule_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " reschedule_date <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	
      	if($search['pay_every']>0){
      		$where.=" AND pay_term_id = ".$search['pay_every'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " re_loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " re_amount LIKE '%{$s_search}%'";
      		$s_where[] = " re_interest_rate LIKE '%{$s_search}%'";
      		
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC";
      	//echo $sql.$where;
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllLoanByCo($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	$db = $this->getAdapter();
      	$sql="SELECT 
				  CONCAT(co.`co_code`,',',co.`co_khname`,'-',co.`co_firstname`,' ',co.`co_lastname`) AS co_name ,
				  b.branch_namekh,
				  co.`co_id`,
				  lm.`loan_number`,
				  c.`client_number`,
				  c.`name_kh`,
				  c.`phone`,
				  lm.`total_capital`,
				  lm.`interest_rate`,
				  lg.`date_release`,
				  lg.`date_line`,
				  lg.`total_duration`,
				  lm.`currency_type` AS curr_type,
				  lm.`collect_typeterm`,
				  lm.`pay_after`,
				  (SELECT `ln_currency`.`symbol` FROM `ln_currency` WHERE (`ln_currency`.`id` = lm.`currency_type`)) AS `currency_type`,
				  (SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = `lg`.`pay_term`))) AS `Term Borrow`,
				  (SELECT `crm`.`date_input` FROM (`ln_client_receipt_money` `crm` JOIN `ln_client_receipt_money_detail` `crmd`) WHERE ((`crm`.`loan_number` = lm.`loan_number`)
								          AND (`crm`.`id` = `crmd`.`crm_id`) AND (`crmd`.`lfd_id` = f.`id`)) ORDER BY `crm`.`date_input` DESC LIMIT 1) AS `last_pay_date`,
				  SUM(f.`total_principal`) AS total_principal,
				  SUM(f.`principle_after`) AS principle_after,
				  SUM(f.`total_interest_after`) AS total_interest_after,
				  SUM(f.`total_payment_after`) AS total_payment_after,
				  SUM(f.`penelize`) AS penelize,
				  SUM(f.`service_charge`) AS service_charge,
				  f.`date_payment` 
				FROM
				  `ln_loanmember_funddetail` AS f,
				  `ln_loan_group` AS lg,
				  `ln_loan_member` AS lm,
				  `ln_co` AS co,
				  `ln_client` AS c ,
      			  `ln_branch` AS b 
				WHERE f.`is_completed` = 0 
				  AND lg.`g_id` = lm.`group_id` 
				  AND lm.`member_id` = f.`member_id` 
				  AND lg.`status` = 1 
				  AND co.`co_id` = lg.`co_id` 
				  AND c.`client_id` = lm.`client_id` 
				  AND b.`br_id`=f.`branch_id`
				";
      	$where ='';
      	$group_by=" GROUP BY lm.`group_id`,f.`date_payment` ";
      	$order = " ORDER BY lg.`group_id`";
      if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND f.`date_payment` BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['client_name']!=""){
      		$where.=" AND lg.`group_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>-1){
      		$where.=" AND f.`branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']!=""){
      		$where.=" AND co.`co_id` = ".$search['co_id'];
      	}
      	if($search['status']!=""){
      		$where.=" AND lm.`status`=".$search['status'];
      	}
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " b.branch_namekh LIKE '%{$s_search}%'";
      		$s_where[] = " lm.`loan_number` LIKE '%{$s_search}%'";
      		$s_where[] = " name_kh LIKE '%{$s_search}%'";
      		$s_where[] = " lm.total_capital LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
       	//echo $sql.$where.$group_by.$order;
      	return $db->fetchAll($sql.$where.$group_by.$order);
      }
      public function getAllTransferoan($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM v_gettransferloan WHERE 1";
      	$where ='';
      
      	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND ( `from` = ".$search['co_id']." OR `to` = ".$search['co_id'].") ";
      	}
//       	if($search['pay_every']>0){
//       		$where.=" AND pay_term_id = ".$search['pay_every'];
//       	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " from_coname LIKE '%{$s_search}%'";
      		$s_where[] = " to_coname LIKE '%{$s_search}%'";
//       		$s_where[] = " other_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
//       		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	return $db->fetchAll($sql.$where);
      }
      
public function getAllinfoZone($search = null){
    	$db = $this->getAdapter();
    	$sql = " SELECT id, 
	(SELECT branch_namekh FROM `ln_branch` 
	WHERE `ln_branch`.`br_id`=`ln_tranfser_zone`.`branch_id`)
	AS branch_id,

	(SELECT zone_name FROM `ln_zone`
	WHERE`ln_zone`.`zone_id`=`ln_tranfser_zone`.`from_zone`) 
	AS `from_zone`,
	
	
	(SELECT co_khname FROM `ln_co`
	WHERE `ln_co`.`co_id`=`ln_tranfser_zone`.`to_co`)
	AS to_co ,date, note, 
	
	(SELECT name_en FROM `ln_view` 
	WHERE TYPE=3 AND key_code =status)
	AS status 
	
 	FROM `ln_tranfser_zone` WHERE 1 ";
    	
    	$where='';
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	//$order=" ORDER BY  DESC ";
		if($search["branch_name"]>-1){
		  	$where.=' AND branch_id='.$search["branch_name"];
		  }
	if(!empty($search['co_code'])){
    		$where.=" AND to_co =".$search['co_code'];
    	}
  
       if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]=" note LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where).' ';
    	}
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where);
    }
      
      public function getClientLoanCo($search = null){//rpt-loan-released
      	$db = $this->getAdapter();
      
      	$sql = "SELECT *,sum(total_capital) as alltotal_principle,count(level) as totallevel FROM v_released_co WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	 
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['member']>0){
      		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if($search['pay_every']>0){
      		$where.=" AND pay_term_id = ".$search['pay_every'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
//       		$s_where[] = " total_capital LIKE '%{$s_search}%'";
//       		$s_where[] = " other_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      		$s_where[] = " name_en LIKE '%{$s_search}%'";
      		$s_where[] = " client_khname LIKE '%{$s_search}%'";
      		
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order = " GROUP BY client_id ORDER BY co_id DESC,totallevel DESC ";
      	return $db->fetchAll($sql.$where.$order);
      }
      function roundhundred($n,$cu_type){
      	if($cu_type==1){
      		$y = round($n);
      		$a = $y%100 > 0 ? ($y-($y%100)+100) : $y;
      		$x= $a;
      	}else{
      		$total = $n;
      		$x = number_format($total,2);
      	}
      	return $x;
      }
	  public function getLoanCollectIcome($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	 
      	$db = $this->getAdapter();
      	$sql = " SELECT * FROM v_getcollectmoney where status=1 ";
      	$where ='';
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      	}
      	 
      	if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND date_input BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['branch_id']>0){
      		$where.=" AND branch_id= ".$search['branch_id'];
      	}
      	if($search['currency_type']>0){
      		$where.=" AND currency_type= ".$search['currency_type'];
      	}
      	$order="";
      	$order = " ORDER BY id DESC";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getLoanadminFeeIcome($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM
      	v_loanreleased WHERE 1 ";
      	$where ='';
      	if($search['branch_id']>0){
      		$where.=" AND `branch_id`= ".$search['branch_id'];
      	}
      	if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND date_release BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['currency_type']>0){
      		$where.=" AND curr_type= ".$search['currency_type'];
      	}
      	$order = " ORDER BY currency_type ";
      	return $db->fetchAll($sql.$where.$order);
      }
      function getExpenseincomereport($search=null){
      	$this->_name='ln_income_expense';
      	$db = $this->getAdapter();
      	$session_user=new Zend_Session_Namespace('auth');
      	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
      	$where = " WHERE ".$from_date." AND ".$to_date;
      
      	$sql=" SELECT id,
      	(SELECT branch_namekh FROM `ln_branch` WHERE ln_branch.br_id = branch_id LIMIT 1) AS branch_name,
      	account_id,
      	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id = curr_type) AS currency_type,invoice,
      	curr_type,
      	total_amount,disc,date,status FROM $this->_name ";
      	if($search['currency_type']>-1){
      		$where.= " AND curr_type = ".$search['currency_type'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND `branch_id`= ".$search['branch_id'];
      	}
      	$order=" order by id desc ";
//       	echo $sql.$where.$order;exit();
      	return $db->fetchAll($sql.$where.$order);
      }
	  function getLoanByVillage(){
      	$db = $this->getAdapter();
      	$sql=" SELECT
				 `g`.`loan_number`     AS `loan_number`,
				  SUM(`g`.`total_capital`) AS `total_capital`,
				  `g`.`interest_rate`   AS `interest_rate`,
				  `g`.`currency_type`   AS `curr_type`,
				  `g`.`client_id`       AS `client_id`,
				  `g`.`branch_id` AS `br_id`,
				  `lg`.`date_release`   AS `date_release`,
				  `lg`.`date_line`      AS `date_line`,
				  `lg`.`co_id`          AS `co_id`,
				  `lg`.`total_duration` AS `total_duration`,
				  `lg`.`loan_type`      AS `loantype`,
				  (SELECT
				     `ln_branch`.`branch_namekh`
				   FROM `ln_branch`
				   WHERE (`ln_branch`.`br_id` = `g`.`branch_id`)
				   LIMIT 1) AS `branch_name`,
				   `c`.`client_number`,
				   `c`.`name_kh` AS `client_kh`,
				   `c`.`name_en` AS `client_en`,
   				(SELECT `ln_village`.`village_name` FROM `ln_village` WHERE (`c`.`village_id`) LIMIT 1 ) AS `village_name`,
				(SELECT `cm`.`commune_name` FROM `ln_commune` `cm` WHERE (`cm`.`com_id` = `c`.`com_id`) LIMIT 1 ) AS `commune_name`,
				(SELECT `d`.`district_name` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `c`.`dis_id`) LIMIT 1 ) AS `district_name`,
				(SELECT province_en_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1 ) AS province_en_name,
				(SELECT
				     `ln_currency`.`symbol`
				   FROM `ln_currency`
				   WHERE (`ln_currency`.`id` = `g`.`currency_type`)) AS `currency_type`,
				  (SELECT
				     SUM(`cd`.`principal_permonth`)
				   FROM `ln_client_receipt_money_detail` `cd`
				   WHERE (`cd`.`loan_number` = `g`.`loan_number`)) AS `total_payment`,
				  
				  (SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 24) AND (`ln_view`.`key_code` = `lg`.`for_loantype`)) LIMIT 1) AS `loan_type`,
				  (SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = `lg`.`pay_term`))) AS `pay_term`,
				  (SELECT `ln_co`.`co_khname` FROM `ln_co` WHERE (`ln_co`.`co_id` = `lg`.`co_id`)) AS `co_name`,
				  (SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = `lg`.`pay_term`))) AS `name_en`
				
				FROM (`ln_loan_group` `lg`
				   , `ln_loan_member` `g`,
				   ln_client AS c )
				   
				WHERE ((`lg`.`g_id` = `g`.`group_id`)
				       AND (`g`.`status` = 1)
				       AND `g`.`client_id`= c.client_id
				       AND (`g`.`is_completed` = 0)
				       AND (`g`.`is_reschedule` <> 1))
				GROUP BY `g`.`group_id` ORDER BY 
               c.pro_id,c.dis_id,c.com_id,c.village_id ";
      	return $db->fetchAll($sql);
      }
      function getLoandisburseByMonth(){
      	$sql="SELECT
			   SUM(`m`.`total_capital`) AS `total_capital`,
			   `m`.`currency_type`   AS `curr_type`,
			  `lg`.`date_release`   AS `date_release`,
			  (SELECT
			     `ln_currency`.`symbol`
			   FROM `ln_currency`
			   WHERE (`ln_currency`.`id` = `m`.`currency_type`)) AS `currency_type`,
			  (SELECT
			     `ln_view`.`name_en`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`type` = 24)
			          AND (`ln_view`.`key_code` = `lg`.`for_loantype`))
			   LIMIT 1) AS `loan_type`
			FROM (`ln_loan_group` `lg`
			   JOIN `ln_loan_member` `m`)
			WHERE ((`lg`.`g_id` = `m`.`group_id`)
			       AND (`m`.`status` = 1)
			       AND (`lg`.`is_reschedule` <> 1)
			       AND (`lg`.`g_id` = `m`.`group_id`))
			GROUP BY YEAR(date_release),MONTH(date_release),`m`.`currency_type` ";
      	$db = $this->getAdapter();
      	return $db->fetchAll($sql);
      }
      public function getALLParLoan($search=null){
//       	$end_date =(empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	$db = $this->getAdapter();
      	$end_date = $search['end_date'];
      	$sql="SELECT * FROM v_getloanpar ";
//       	$where=" WHERE ".$end_date;
      	$where=" WHERE 1 ";
      	if(!empty($search['adv_search'])){
      		 
//       		$s_where = array();
//       		$s_search = addslashes(trim($search['adv_search']));
//       		$s_where[] = " branch_name LIKE '%{$s_search}%'";
//       		$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
//       		$s_where[] = " `client_number` LIKE '%{$s_search}%'";
//       		$s_where[] = " `name_kh` LIKE '%{$s_search}%'";
//       		$s_where[] = " `total_capital` LIKE '%{$s_search}%'";
//       		$s_where[] = " `interest_rate` LIKE '%{$s_search}%'";
//       		$s_where[] = " `total_duration` LIKE '%{$s_search}%'";
//       		$s_where[] = " `term_borrow` LIKE '%{$s_search}%'";
//       		$s_where[] = " `total_principal` LIKE '%{$s_search}%'";
      		 
//       		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
//       	if($search['branch_id']>0){
//       		$where.=" AND `branch_id` = ".$search['branch_id'];
//       	}
      	if(!empty($search['end_date'])){
      		$where.=" AND date_payment < '$end_date'";
      	}
//       	if(!empty($search['cash_type'])){
//       		$where.=" AND `curr_type` = ".$search['cash_type'];
//       	}

      	return $db->fetchAll($sql.$where);
      }
      public function getLoanlateById($search = null){
      	$end_date = $search['end_date'];
      	$db = $this->getAdapter();
      	$sql="
      		SELECT
      	(SELECT `crm`.`date_input` FROM (`ln_client_receipt_money` `crm` JOIN `ln_client_receipt_money_detail` `crmd`) 
      	WHERE `crm`.`loan_number` = lm.`loan_number`) AS date_input,
      	SUM(f.`total_principal`) AS total_principal,
      	SUM(f.`principle_after`) AS principle_after,
      	SUM(f.`total_interest_after`) AS total_interest_after,
      	SUM(f.`total_payment_after`) AS total_payment_after,
      	SUM(f.`penelize`) AS penelize,
      	SUM(f.`service_charge`) AS service_charge,
      	f.`date_payment` 
      	FROM
      	`ln_loanmember_funddetail` AS f,
      	`ln_loan_group` AS lg,
      	`ln_loan_member` AS lm,
      	`ln_branch` AS b
      	WHERE 
      	f.`is_completed` = 0
      	AND lg.`g_id` = lm.`group_id`
      	AND lm.`member_id` = f.`member_id`
      	AND lg.`status` = 1
      	AND lm.`status` = 1
      	AND f.`status`=1
      	AND b.`br_id`=f.`branch_id`
      	AND lm.is_reschedule!=1 ";
      	$where=" AND lm.loan_number= '".$search['loan_number']."'";
//       	if(!empty($search['adv_search'])){
//       		$s_search = addslashes(trim($search['adv_search']));
//       		$s_where[] = " b.branch_namekh LIKE '%{$s_search}%'";
//       		$s_where[] = "  lm.`currency_type` LIKE '%{$s_search}%'";
 //     		$s_where[] = " lm.loan_number LIKE '%{$s_search}%'";
//       		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
//       		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
//       		$s_where[] = " f.principle_after LIKE '%{$s_search}%'";
//       		$s_where[] = " f.principal_permonth LIKE '%{$s_search}%'";
//       		$s_where[] = " f.total_interest_after LIKE '%{$s_search}%'";
//       		$s_where[] = " f.total_payment_after LIKE '%{$s_search}%'";
//       		$where .=' AND ('.implode(' OR ',$s_where).')';
//       	}
      	if(!empty($search['end_date'])){
      		$where.=" AND f.date_payment < '$end_date'";
      	}
      	if(!empty($search['co_id'])){
      		$where.=" AND `co_id` = ".$search['co_id'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND f.`branch_id` = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND lm.client_id = ".$search['client_name'];
      	}
        if($search['currency_type']>0){
      		$where.=" AND lm.currency_type = ".$search['currency_type'];
      	}
      	$group_by = " GROUP BY lm.`group_id`,f.`date_payment` ORDER BY f.`date_payment` ASC";
      	$row =  $db->fetchAll($sql.$where.$group_by);
      	$result =array();
      	$total_principal=0;$total_interest=0;
      	foreach ($row as $rs){
      		$total_principal = $total_principal+$rs['principle_after'];
      		$total_interest = $total_interest+$rs['total_interest_after'];
      	}
      	$result["principle_after"]=$total_principal;
      	$result["total_interest_after"]=$total_interest;
      	return $result;
      }
      function getContractinfo($id){
      	$sql = "SELECT lm.*,g.total_duration,g.pay_term,
      	(SELECT curr_namekh FROM `ln_currency` WHERE id=lm.currency_type LIMIT 1) as curr_type
      	FROM ln_loan_member AS lm ,ln_loan_group AS g WHERE lm.member_id = g.g_id
      	AND lm.member_id = $id LIMIT 1";
      	$db = $this->getAdapter();
      	return $db->fetchRow($sql);
      }
      function getCalleteralByClient($client_id){
      	$db = $this->getAdapter();
   $sql =" SELECT
`d`.`number_collecteral` AS `number_collecteral`,
`d`.`is_return`          AS `is_return`,
`d`.`issue_date`         AS `issue_date`,
`d`.`note`               AS `note`,
d.collecteral_code, 
d.owner_name AS c_ownername,
relative ,   
(SELECT
`ct`.`title_en`
FROM `ln_callecteral_type` `ct`
WHERE (`ct`.`id` = `d`.`collecteral_type`)) AS `collecteral_title_en`,
(SELECT
`ct`.`title_kh`
FROM `ln_callecteral_type` `ct`
WHERE (`ct`.`id` = `d`.`collecteral_type`)) AS `collecteral_title_kh`,
(SELECT
`ln_view`.`name_kh`
FROM `ln_view`
WHERE ((`ln_view`.`type` = 21)
     AND (`ln_view`.`key_code` = `d`.`owner_type`))) AS `collecteral_owner`,
 d.number_collecteral,
  d.issue_date,
  d.note as coll_note,
  (SELECT
     `ln_branch`.`branch_namekh`
   FROM `ln_branch`
   WHERE (`ln_branch`.`br_id` = `c`.`branch_id`)
   LIMIT 1) AS `branch_name`,
  (SELECT
     `ln_co`.`co_khname`
   FROM `ln_co`
   WHERE (`ln_co`.`co_id` = `c`.`co_id`)
   LIMIT 1) AS `co_id`,
  `d`.`collecteral_code`   AS `collecteral_code`,
  `d`.`owner_name` AS `owner_name`,
  `cl`.`client_number` AS `client_code`,
  `cl`.`name_kh` AS `name_kh`,
  `cl`.`name_en` AS `client_name`,
  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=cl.sex) AS gender,
  `cl`.`client_id`          AS `client_id`,
  `cl`.`branch_id`          AS `branch_id`,
   cl.`join_with`,
   cl.`relate_with`,
   cl.`spouse_name`,
   cl.dob_guarantor,
   cl.guarantor_with,
   cl.dob,
   cl.house,cl.street,
(SELECT `ln_village`.`village_name` FROM `ln_village` WHERE (`ln_village`.`vill_id` = `cl`.`village_id`)) AS `village_name`,
(SELECT `c`.`commune_name` FROM `ln_commune` `c` WHERE (`c`.`com_id` = `cl`.`com_id`) LIMIT 1) AS `commune_name`,
(SELECT `d`.`district_namekh` FROM `ln_district` `d` WHERE (`d`.`dis_id` = `cl`.`dis_id`) LIMIT 1) AS `district_name`,
(SELECT province_kh_name FROM `ln_province` WHERE province_id= cl.pro_id  LIMIT 1) AS province_en_name,
(SELECT name_kh FROM `ln_view` WHERE id = cl.client_d_type LIMIT 1) AS client_doctype,
cl.nation_id,
dob_join_acc ,
`c`.`date`               AS `date`,
`c`.`status`             AS `status`
FROM (
ln_client AS cl ,
`ln_client_callecteral` `c`
JOIN `ln_client_callecteral_detail` `d`)
WHERE ((
cl.client_id = c.client_id AND
`c`.`id` = `d`.`client_coll_id`)
AND (`c`.`status` = 1)
AND (`d`.`status` = 1)
AND cl.client_id = $client_id )";
      	$order = " ORDER BY client_id ";
      	return $db->fetchAll($sql.$order);
      }
      function getAllExpenseReport($search=null){
      	$db = $this->getAdapter();
      	$session_user=new Zend_Session_Namespace('auth');
      	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
      	$where = " WHERE ".$from_date." AND ".$to_date;
      
      	$sql=" SELECT id,
      	(SELECT branch_namekh FROM `ln_branch` WHERE ln_branch.br_id =branch_id LIMIT 1) AS branch_name,
      	account_id,
      	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
      	curr_type,
      	total_amount,disc,date,status FROM ln_income_expense ";
      
      	if (!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addslashes($search['adv_search']));
      		$s_where[] = " account_id LIKE '%{$s_search}%'";
      		$s_where[] = " title LIKE '%{$s_search}%'";
      		$s_where[] = " total_amount LIKE '%{$s_search}%'";
      		$s_where[] = " invoice LIKE '%{$s_search}%'";
      
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']>-1){
      		$where.= " AND status = ".$search['status'];
      	}
//       	if($search['currency_type']>-1){
//       		$where.= " AND curr_type = ".$search['currency_type'];
//       	}
      	$order=" order by id desc ";
      	return $db->fetchAll($sql.$where.$order);
      }
      function getAllOtherIncomeReport($search=null){
      	$db = $this->getAdapter();
      	$session_user=new Zend_Session_Namespace('auth');
      	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
      	$where = " WHERE ".$from_date." AND ".$to_date;
      
      	$sql=" SELECT id,
      	(SELECT branch_namekh FROM `ln_branch` WHERE ln_branch.br_id =branch_id LIMIT 1) AS branch_name,
      	account_id,
      	(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
      	curr_type,
      	total_amount,disc,date,status FROM ln_income ";
      
      	if (!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addslashes($search['adv_search']));
      		$s_where[] = " account_id LIKE '%{$s_search}%'";
      		$s_where[] = " title LIKE '%{$s_search}%'";
      		$s_where[] = " total_amount LIKE '%{$s_search}%'";
      		$s_where[] = " invoice LIKE '%{$s_search}%'";
      
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']>-1 ){
      		$where.= " AND status = ".$search['status'];
      	}
//       	if($search['currency_type']>-1){
//       		$where.= " AND curr_type = ".$search['currency_type'];
//       	}
      	$order=" order by id desc ";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getCollectDailyPayment($search=null){
      	$db = $this->getAdapter();
      	$sql="SELECT * FROM v_getcollectmoney WHERE status=1 ";
      	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	 
      	if($search['branch_id']>0){
      		$where.=" AND branch_id= ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
//       	if($search['currency_type']>-1){
//       		$where.= " AND currency_type = ".$search['currency_type'];
//         }
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_principal_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amount LIKE '%{$s_search}%'";
      		$s_where[] = " service_charge LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC ";
      		return $db->fetchAll($sql.$where.$order);
      
      }
      function getAmountReceiveByLoanNumber($loan_number,$from_date=null){
      	$db = $this->getAdapter();
      	$sql="
		      	SELECT
		      	crm.`payment_option`,
		      	`crm`.`total_principal_permonth` AS `principle_amount`,
		      	(crm.`total_interest`) AS interest,
		      	(crm.`penalize_amount`) AS penelize,
		      	(crm.`service_charge`) AS service,
		      	crm.`return_amount` AS return_amount,
		      	crm.`recieve_amount` AS amount_recieve,
		      	(`crm`.`total_payment`) AS `payment`,
		      	SUM(crmd.`principal_permonth`) AS total_principal_permonth,
		      	SUM(crmd.`total_payment`) AS total_payment,
		      	SUM(crmd.`total_interest`) AS total_interest,
		      	SUM(crmd.`total_recieve`) AS recieve_amount,
		      	SUM(crmd.`penelize_amount`) AS penelize_amount,
		      	SUM(crmd.`service_charge`) AS service_charge
		      	FROM
		      	`ln_client_receipt_money` AS crm,
		      	`ln_client_receipt_money_detail` AS crmd
		      	WHERE crmd.`crm_id`=crm.`id` AND  crmd.`loan_number`='".$loan_number."'  ";
      	if($from_date!=null){
      		$to_date = (empty($from_date))? '1': " date_input <= '".$from_date." 23:59:59'";
      		$sql.=" AND $to_date ";
      	}
      	$sql.=" GROUP BY crmd.`crm_id` ";
      	$principle_amount=0;
      	$row =  $db->fetchAll($sql);
      	if(!empty($row)){
      		 
      		$interest=0;
      		$alltotal=0;
      		foreach ($row as $rs){
      			if($rs["payment_option"]==4){
      				$principle= $rs["principle_amount"];
      				$total_pay = $rs["payment"];
      				$interest= $rs["interest"];
      				$recieve = $rs["amount_recieve"]-$rs["return_amount"];
      				$penelize = $rs["penelize"];
      				$service_charge =$rs["service"];
      				$is_set=1;
      				// 					}
      			}else{
      				$is_set=0;
      				$service_charge = $rs["service_charge"];
      				$interest = $rs["total_interest"];
      				$principle = $rs["total_principal_permonth"];
      				$penelize = $rs["penelize"];
      				$recieve = $rs["recieve_amount"]-$rs["return_amount"];
      				$total_pay = $rs["total_payment"];
      			}
      			$new_service = $recieve-$service_charge;
      			if($new_service>=0){
      				$service = $service_charge;
      				$new_penelize = $new_service - $penelize;
      				if($new_penelize>=0){
      					$penelize_amount =  $penelize;
      					$new_interest = $new_penelize - $interest;
      					if($new_interest>=0){
      						$interest_amount = $interest;
      						//echo $interest_amount;exit();
      						$new_printciple = $new_interest - $principle;
      						if($new_printciple>=0){
      							$principle_amount = $principle;
      							// 									exit();
      						}else{
      							$principle_amount = abs($new_interest);
      							// 									echo  $principle_amount;
      						}
      					}else{
      						$interest_amount = abs($new_penelize);
      						$principle_amount=0;
      
      					}
      				}else{
      					$penelize_amount = abs($new_service);
      					$interest =0;
      					$principle_amount=0;
      						
      				}
      			}else{
      				$service = abs($recieve);
      				$penelize_amount = 0;
      				$interest =0;
      				$principle_amount=0;
      			}
      			$alltotal = $alltotal+$principle_amount;
      		}
      	}else{
      		$alltotal=0;
      	}
      	return $alltotal;
      }
      function getReceiveByLoanNumber($loan_number,$search){
      	$db = $this->getAdapter();
      	$to_date = (empty($search['end_date']))? '1': " date_input < '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$to_date;
		$sql=" SELECT
				crm.`payment_option`,
				`crm`.`total_principal_permonth` AS `principle_amount`,
				(crm.`total_interest`) AS interest,
				(crm.`penalize_amount`) AS penelize,
				(crm.`service_charge`) AS service,
				crm.`return_amount` AS return_amount,
				crm.`recieve_amount` AS amount_recieve,
				(`crm`.`total_payment`) AS `payment`,
				SUM(crmd.`principal_permonth`) AS total_principal_permonth,
				SUM(crmd.`total_payment`) AS total_payment,
				SUM(crmd.`total_interest`) AS total_interest,
				SUM(crmd.`total_recieve`) AS recieve_amount,
				SUM(crmd.`penelize_amount`) AS penelize_amount,
				SUM(crmd.`service_charge`) AS service_charge
				FROM
				`ln_client_receipt_money` AS crm,
				`ln_client_receipt_money_detail` AS crmd
				WHERE 
				crmd.`crm_id`=crm.`id` 
				AND crmd.`loan_number`='".$loan_number."' ";
		$group="  GROUP BY crmd.`crm_id` ";
		$principle_amount=0;
		$row =  $db->fetchAll($sql.$where.$group);
		if(!empty($row)){
			$interest=0;
			$alltotal=0;
			$alltotal_interest = 0;
			$total_penelize=0;
			foreach ($row as $rs){
				if($rs["payment_option"]==4){
					$principle= $rs["principle_amount"];
					$total_pay = $rs["payment"];
					$interest= $rs["interest"];
					$recieve = $rs["amount_recieve"]-$rs["return_amount"];
					$penelize = $rs["penelize"];
					$service_charge =$rs["service"];
					$is_set=1;
					// 					}
				}else{
					$is_set=0;
					$service_charge = $rs["service_charge"];
					$interest = $rs["total_interest"];
					$principle = $rs["total_principal_permonth"];
					$penelize = $rs["penelize"];
					$recieve = $rs["recieve_amount"]-$rs["return_amount"];
					$total_pay = $rs["total_payment"];
				}
				$new_service = $recieve-$service_charge;
				if($new_service>=0){
					$service = $service_charge;
					$new_penelize = $new_service - $penelize;
					if($new_penelize>=0){
						$penelize_amount =  $penelize;
						$new_interest = $new_penelize - $interest;
						if($new_interest>=0){
							$interest_amount = $interest;
							//echo $interest_amount;exit();
							$new_printciple = $new_interest - $principle;
							if($new_printciple>=0){
								$principle_amount = $principle;
								// 									exit();
							}else{
								$principle_amount = abs($new_interest);
								// 									echo  $principle_amount;
							}
						}else{
							$interest_amount = abs($new_penelize);
							$principle_amount=0;
	
						}
					}else{
						$penelize_amount = abs($new_service);
						$interest_amount =0;
						$principle_amount=0;
	
					}
				}else{
					$service = abs($recieve);
					$penelize_amount = 0;
					$interest_amount =0;
					$principle_amount=0;
				}
				$alltotal = $alltotal+$principle_amount;
				$alltotal_interest = $alltotal_interest+$interest_amount;
				$total_penelize = $total_penelize+$penelize_amount;
			}
		}else{
			$alltotal=0;
			$alltotal_interest=0;
			$total_penelize=0;
		}
		 $data['total_collectprinciple']=$alltotal;
		 $data['total_interest']=$alltotal_interest;
		 $data['total_penelize']=$total_penelize;
		 return $data;
      }
      function getReceiveByLoanNumberPeriod($loan_number,$search){
      	$db = $this->getAdapter();
      	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_input < '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	$sql=" SELECT
      	crm.`payment_option`,
      	`crm`.`total_principal_permonth` AS `principle_amount`,
      	(crm.`total_interest`) AS interest,
      	(crm.`penalize_amount`) AS penelize,
      	(crm.`service_charge`) AS service,
      	crm.`return_amount` AS return_amount,
      	crm.`recieve_amount` AS amount_recieve,
      	(`crm`.`total_payment`) AS `payment`,
      	SUM(crmd.`principal_permonth`) AS total_principal_permonth,
      	SUM(crmd.`total_payment`) AS total_payment,
      	SUM(crmd.`total_interest`) AS total_interest,
      	SUM(crmd.`total_recieve`) AS recieve_amount,
      	SUM(crmd.`penelize_amount`) AS penelize_amount,
      	SUM(crmd.`service_charge`) AS service_charge
      	FROM
      	`ln_client_receipt_money` AS crm,
      	`ln_client_receipt_money_detail` AS crmd
      	WHERE
      	crmd.`crm_id`=crm.`id`
      	AND crmd.`loan_number`='".$loan_number."' ";
      	$group="  GROUP BY crmd.`crm_id`";
      	$principle_amount=0;
      	$row =  $db->fetchAll($sql.$where.$group);
      	if(!empty($row)){
      		$interest=0;
      		$alltotal=0;
      		$alltotal_interest = 0;
      		$total_penelize=0;
      		foreach ($row as $rs){
      			if($rs["payment_option"]==4){
      				$principle= $rs["principle_amount"];
      				$total_pay = $rs["payment"];
      				$interest= $rs["interest"];
      				$recieve = $rs["amount_recieve"]-$rs["return_amount"];
      				$penelize = $rs["penelize"];
      				$service_charge =$rs["service"];
      				$is_set=1;
      				// 					}
      			}else{
      				$is_set=0;
      				$service_charge = $rs["service_charge"];
      				$interest = $rs["total_interest"];
      				$principle = $rs["total_principal_permonth"];
      				$penelize = $rs["penelize"];
      				$recieve = $rs["recieve_amount"]-$rs["return_amount"];
      				$total_pay = $rs["total_payment"];
      			}
      			$new_service = $recieve-$service_charge;
      			if($new_service>=0){
      				$service = $service_charge;
      				$new_penelize = $new_service - $penelize;
      				if($new_penelize>=0){
      					$penelize_amount =  $penelize;
      					$new_interest = $new_penelize - $interest;
      					if($new_interest>=0){
      						$interest_amount = $interest;
      						$new_printciple = $new_interest - $principle;
      						if($new_printciple>=0){
      							$principle_amount = $principle;
      						}else{
      							$principle_amount = abs($new_interest);
      						}
      					}else{
      						$interest_amount = abs($new_penelize);
      						$principle_amount=0;
      					}
      				}else{
      					$penelize_amount = abs($new_service);
      					$interest_amount =0;
      					$principle_amount=0;
      
      				}
      			}else{
      				$service = abs($recieve);
      				$penelize_amount = 0;
      				$interest_amount =0;
      				$principle_amount=0;
      			}
      			$alltotal = $alltotal+$principle_amount;
      			$alltotal_interest = $alltotal_interest+$interest_amount;
      			$total_penelize = $total_penelize+$penelize_amount;
      		}
      	}else{
      		$alltotal=0;
      		$alltotal_interest=0;
      		$total_penelize=0;
      	}
      	$data['total_collectprinciple']=$alltotal;
      	$data['total_interest']=$alltotal_interest;
      	$data['total_penelize']=$total_penelize;
      	return $data;
      }
      function getAllSavingRelease($search){
//       	$from_date =(empty($search['start_date']))? '1': " saving_date >= '".$search['start_date']." 00:00:00'";
//       	$to_date = (empty($search['end_date']))? '1': " saving_date <= '".$search['end_date']." 23:59:59'";
//       	$where = " AND ".$from_date." AND ".$to_date;
      	$where="";
      	 
      	$db = $this->getAdapter();
      	$sql="
      	SELECT id,saving_number,
      	(SELECT branch_namekh FROM `ln_branch` WHERE br_id =ln_savingaccount.branch_id LIMIT 1) AS branch,
      	saving_number,
      	name_kh AS client_name_kh,
      	name_en AS client_name_en,
      	reciept_no,withdrawing,currency_type AS curr_type,(SELECT symbol FROM `ln_currency` WHERE id = currency_type LIMIT 1) currency_type,
      	deposit_amount,interest_rate,
      	(SELECT name_en FROM `ln_view` WHERE type=28 AND key_code=saving_method LIMIT 1) saving_method  ,
      	(SELECT name_en FROM `ln_view` WHERE type=1 AND key_code=term_type LIMIT 1) term_type, withdrawing,level,
      	saving_date,saving_close FROM
        `ln_savingaccount`,ln_clientsaving where ln_clientsaving.client_id = ln_savingaccount.client_id ";
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addslashes($search['adv_search']));
      		$s_where[] = " saving_number LIKE '%{$s_search}%'";
      		$s_where[] = " deposit_amount LIKE '%{$s_search}%'";
      		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " reciept_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	
      	if(($search['account_type'])>0){
      		$where.= " AND saving_method = ".$search['account_type'];
      	}
      	if(($search['branch_id'])>0){
      		$where.= " AND ln_savingaccount.branch_id = ".$search['branch_id'];
      	}
      	if(($search['currency_type'])>0){
      		$where.= " AND ln_savingaccount.currency_type=".$search['currency_type'];
      	}
      	
      	
      	$order = " ORDER BY id DESC";
      	$db = $this->getAdapter();
      	//      	echo $sql.$where.$order;
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllProfitco($search=null){
      	$db = $this->getAdapter();
      	$sql="SELECT * FROM v_profitcocollect WHERE status=1 ";
      	$from_date =(empty($search['start_date']))? '1': " date_input >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_input <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	 
      	if($search['branch_id']>0){
      		$where.=" AND branch_id= ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_principal_permonth LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest LIKE '%{$s_search}%'";
      		//$s_where[] = " amount_payment LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amount LIKE '%{$s_search}%'";
      		$s_where[] = " service_charge LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC ";
      		return $db->fetchAll($sql.$where.$order);
      
      }
      
      
 }

