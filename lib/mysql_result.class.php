<?php
namespace BarebonesPHP;
class MysqlResult extends DatabaseResult
{
    private $current_pos;
    private $row_count = -1;
    private $result;
    public function __construct($mysql_result)
    {
        $this->result = $mysql_result;
        $this->current_pos = 0;
    }
    public function num_rows()
    {
    	if($this->row_count < 0){
    		$this->row_count = mysqli_num_rows($this->result);
    	}
        return $this->row_count;
    }
    public function current()
    {
        return $this->current_pos;
    }
    public function next()
    {
        $this->current_pos++;
        return mysqli_fetch_assoc($this->result);
    }
    public function key()
    {
        return $this->current_pos;
    }
    public function valid()
    {
        return ($this->current_pos < $this->num_rows())?true:false;
    }
    public function rewind()
    {

    }
    public function hasNext()
    {
        return $this->valid();
    }
    public function fetchAll(){
		$data = array();
		while($this->hasNext()){
			$data[] = $this->next();
		}
		return $data;
	}
}
?>
