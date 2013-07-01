<?php
abstract class DatabaseResult implements Iterator
{
    private $current_pos;
    private $row_count;
    private $result;
    abstract public function num_rows();
}
?>
