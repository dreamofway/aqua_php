<?php

class contractModel extends baseModel {

    function __construct() {

        $this->table = ' t_service_client ';        
        $this->table_company = ' t_company_info ';        
        $this->table_member = ' t_company_members ';        
        $this->table_service = ' t_service ';        
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 계약정보 기준 기업정보 목록을 반환한다.
     */
    public function getContracts( $arg_data ){

        $result = [];
        $join_table = "
            (
                SELECT  
                        as_contract.idx
                        ,as_contract.service_code
                        , as_contract.start_date
                        , as_contract.end_date
                        ,as_company.*
                        , as_comp_member.member_name AS partner_name
                        , as_comp_member.phone_no AS partner_phone_no
                        , as_comp_member.email AS partner_email
                        , as_service.service_name

                FROM
                        ". $this->table ." AS as_contract LEFT OUTER JOIN ". $this->table_company ." AS as_company
                        ON as_contract.company_idx = as_company.company_idx
                        LEFT OUTER JOIN ". $this->table_member ." AS as_comp_member
                        ON as_company.company_idx = as_comp_member.company_idx
                        AND as_comp_member.partner= 'Y'
                        LEFT OUTER JOIN ". $this->table_service ." AS as_service
                        ON as_contract.service_code = as_service.service_code
                        
            ) AS t_contract
            
        ";

        $query = " SELECT COUNT(*) AS cnt FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    /**
     * 계약정보를 가져온다.
     */
    public function getContract( $arg_where ){

        $query = " SELECT * FROM ". $this->table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 계약 정보를 insert 한다.
     */
    public function insetContract( $arg_data ){
        return $this->db->insert( $this->table, $arg_data );
    }

    /**
     * 계약정보를 수정한다.
     */
    public function updateContract( $arg_data, $arg_where ) {
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }
    

}

?>