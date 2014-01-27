<?php
namespace Barebones\Lib;
interface DatabaseInterface
{
    public function connect();
    public function query($query);
    public function get_error();
    public function close();
    public function escape($query);
}
?>
