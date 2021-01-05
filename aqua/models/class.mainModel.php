<?php

class mainModel extends baseModel {

    function __construct() {

        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

}

?>