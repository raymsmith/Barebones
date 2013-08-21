<?php
namespace BarebonesPHP;
class ApplicationDatabase
{
    private $strategy;
    public function __construct(DatabaseInterface $strategy)
    {
        $this->setStrategy($strategy);
    }
    public function setStrategy(DatabaseInterface $strategy)
    {
        $this->strategy = $strategy;
    }
    public function query($query)
    {
        return $this->strategy->query($query);
    }
    public function get_error()
    {
        return $this->strategy->get_error();
    }
    public function escape($query)
    {
        return $this->strategy->escape($query);
    }
}
?>
