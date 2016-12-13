 <?php

class Capital_Model_DbTable_DbInterestResource extends Zend_Db_Table_Abstract
{
    protected $_name = 'in_ln_interest';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='in_ln_interest';
	  $_arr = array(
	    	'value'=>$_data['inter_one'],
	    	'label'=>$_data['inter_two'],
	    );
	   
	  $this->insert($_arr); 
 } 
 
function getInterest(){
		$db=$this->getAdapter();
		$sql="SELECT id , value,label FROM `in_ln_interest`";
		return $db->fetchAll($sql);
	}
	
	public function updateinterestId($_data){
		print_r($_data); exit();
		$_arr = array(
					
				'value'=>$_data['inter_one'],
	    		'label'=>$_data['inter_two'],
				 
		);
		$where = "id =".$_data['id'];
		$this->update($_arr, $where);
	
	}
	
	
	public function getinterestId($id)
	{
		$db = $this->getAdapter();
		$sql="SELECT id , value,label FROM `in_ln_interest` WHERE id = $id";
		return $db->fetchRow($sql);
	}
     
     
}
