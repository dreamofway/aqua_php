<?php

class baseModel extends aqua {
    
    protected $db;

    function __construct() {
        
    }

    /**
     * 트랜잭션을 시작한다.
     */
    public function runTransaction(){
        $this->db->runTransaction();
    }
     /**
     * 트랜잭션을 종료한다.
     */
    public function stopTransaction(){
        $this->db->stopTransaction();
    }
    

}

?>