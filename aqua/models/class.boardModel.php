<?php

class boardModel extends baseModel {

    function __construct() {

        $this->db = $this->connDB('gnchurch');

    }

    public function getBoards( $arg_data ) {
        
        $result = [];
        
        $join_table = "
            T_board
        ";
        
        $query = " SELECT COUNT(*) AS cnt FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where'];
        
        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    public function getBoard( $arg_data ) {
        
        $query = " SELECT * FROM T_board WHERE " . $arg_data;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }


    function __destruct() {

        # db close
        $this->db->dbClose();

    }

}

?>