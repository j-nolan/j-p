<?php
/* A preference is a cell of a hungarian algorithm matrix. It is an intersection
 * between a task and a worker. Its value is the cost of this worker being
 * assign to that task.
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>

<?php
require_once('Cell.class.php');
class Preference extends Cell {

    private $worker;
    private $task;

    public function __construct($cost = self::MAX_VALUE, $worker = null, $task = null) {
        parent::setValue($cost);
        self::setWorker($worker);
        self::setTask($task);
    }
    
    public function __clone() {
    
    }
    
    public function setTask($task) {
        $this->task = $task;
    }
    
    public function getTask() {
        return $this->task;
    }
    
    public function setWorker($worker) {
        $this->worker = $worker;
    }
    
    public function getWorker() {
        return $this->worker;
    }
    
    public function disable() {
        $this->setValue(self::MAX_VALUE);
    }
}
?>