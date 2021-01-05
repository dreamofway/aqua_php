<?php

class authModel extends baseModel {

    function __construct() {

        $this->db = $this->connDB('masic');

    }

    public function loginProc( $arg_data ) {

        $query = '  SELECT * 
                    FROM t_company_members 
                    WHERE   ( 
                                (  phone_no = "'. $arg_data['id'] .'" ) AND ( password = "'. $arg_data['pw'] .'" ) 
                            ) AND ( use_flag="Y" ) AND ( del_flag="N" ) AND company_idx = "'. COMPANY_CODE .'"
                            
                            
        ';
        
        
        $query_result = $this->db->execute( $query );

        return $query_result;

    }

    public function loginApp( $arg_data ) {

        $query = "  SELECT * 
                    FROM t_company_members 
                    WHERE   company_member_idx = '". $arg_data ."'
        ";
        
        
        $query_result = $this->db->execute( $query );

        return $query_result;

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

}

?>