<?php

class clientModel extends baseModel {

    private $table_client;
    private $table_client_company_addr;    
    private $table_client_receive_order;    
    private $table_doc_approval;
    private $table_members;
    private $table_product_stock;

    function __construct() {

        $this->table_client = ' t_client_company ';        
        $this->table_client_company_addr = ' t_client_company_addr ';        
        $this->table_client_receive_order = ' t_client_receive_order ';  
        $this->table_doc_approval = ' t_document_approval ';
        $this->table_members = ' t_company_members ';
        $this->table_product_stock = ' t_product_stock ';
        
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 수주 업체 목록을 반환한다.
     */
    public function getClients( $arg_data ){

        $result = [];

        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_client ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_client ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }
    
    /**
     * 수주업체 정보를 가져온다.
     */
    public function getClient( $arg_where ){

        $query = " SELECT * FROM ". $this->table_client ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 수주업체 정보를 insert 한다.
     */
    public function insertClient( $arg_data ){
        return $this->db->insert( $this->table_client, $arg_data );
    }

    /**
     * 수주업체 정보를 수정한다.
     */
    public function updateClient( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_client, $arg_data, $arg_where );
    }


     /**
     * 수주업체 정보를 가져온다.
     */
    public function getClientComapnyAddr( $arg_where ){

        $query = " SELECT * FROM ". $this->table_client_company_addr ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 수주업체 정보를 insert 한다.
     */
    public function insertClientCompanyAddr( $arg_data ){
        return $this->db->insert( $this->table_client_company_addr, $arg_data );
    }

    /**
     * 수주업체 정보를 수정한다.
     */
    public function updateClientCompanyAddr( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_client_company_addr, $arg_data, $arg_where );
    }

    /**
     * 수주정보를 insert 한다.
     */
    public function insertClientReceiveOrder( $arg_data ){
        return $this->db->insert( $this->table_client_receive_order, $arg_data );
    }

    /**
     * 수주정보를 수정한다.
     */
    public function updateClientReceiveOrder( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_client_receive_order, $arg_data, $arg_where );
    }

    
     /**
     * 고객사 배송지 정보를 insert 한다.
     */ 
    public function insertcompanyAddrs( 
        $arg_client_idx
        , $arg_addr_name
        , $zipcode
        , $addr
        , $addr_detail  
        , $arg_company_idx
     ){

        $insert_query = ' INSERT INTO '. $this->table_client_company_addr . ' 
                        (   
                            client_idx
                            , addr_name
                            , zipcode
                            , addr
                            , addr_detail
                            , company_idx                          
                            , reg_idx
                            , reg_ip
                            , reg_date 
                        ) VALUES ';

        $insert_add_query = [];

        foreach( $arg_addr_name AS $idx=>$val ){            

            if( ( empty( $val ) == false )  ) {
                $insert_add_query[] = " ( 
                    '". $arg_client_idx ."'
                    ,'". $arg_addr_name[$idx] ."'
                    ,'". $zipcode[$idx] ."'
                    ,'". $addr[$idx] ."'
                    , '". $addr_detail[ $idx ] ."'
                    ,'". $arg_company_idx ."'                   
                    , '". getAccountInfo()['idx'] ."'
                    , '". $this->getIP() ."'
                    , NOW() 
                ) ";
            }

        }
        
        if( count($insert_add_query) > 0 ) {

            $insert_query .= join( ', ', $insert_add_query );
            $return_data = $this->db->execute( $insert_query );

        } else {
            $return_data['state'] = true;
        }
        

        return $return_data;
    }

    /**
     * 수주정보 목록을 반환한다.
     */
    public function getReceiveOrders( $arg_data ){
        $result = [];

        $join_table = "
            (
                SELECT  
                        as_order.*                                                  
                        , as_client.company_name
                        , as_client.manager_name
                        , as_client.manager_phone_no                
                        , IFNULL( as_branch.addr_name, '본점' )  AS branch_name      
                        , ( SELECT doc_approval_idx FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_order.order_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_client_receive_order ) ."' ) ) AS doc_exist
                        , ( SELECT approval_state FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_order.order_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_client_receive_order ) ."' ) ) AS doc_approval_state                                
                        , ( SELECT GROUP_CONCAT( expiration_date SEPARATOR ',' ) FROM ". $this->table_product_stock ." WHERE ( stock_idx IN ( product_stock_used_info ) ) ) AS expiration_dates                                
                FROM
                        ". $this->table_client_receive_order ." AS as_order LEFT OUTER JOIN ". $this->table_client ." AS as_client
                        ON as_order.client_idx = as_client.client_idx
                        LEFT OUTER JOIN ". $this->table_client_company_addr ." AS as_branch
                        ON as_order.addr_idx = as_branch.addr_idx
            ) AS t_new
            
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
     * 수주 정보를 반환한다.
     */
    public function getReceiveOrder( $arg_where ){

        $join_table = "
            (
                SELECT  
                        as_order.*                                                  
                        , as_client.company_name
                        , as_client.manager_name
                        , as_client.manager_phone_no                
                        , as_client.client_zip_code                
                        , as_client.client_addr                
                        , as_client.client_addr_detail                
                        , IFNULL( as_branch.addr_name, '본점' )  AS branch_name          
                        , ( SELECT doc_approval_idx FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_order.order_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_client_receive_order ) ."' ) ) AS doc_exist
                        , ( SELECT approval_state FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_order.order_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_client_receive_order ) ."' ) ) AS doc_approval_state                                    
                        , ( SELECT member_name FROM ". $this->table_members ." WHERE company_member_idx = as_order.reg_idx ) AS member_name
                FROM
                        ". $this->table_client_receive_order ." AS as_order LEFT OUTER JOIN ". $this->table_client ." AS as_client
                        ON as_order.client_idx = as_client.client_idx
                        LEFT OUTER JOIN ". $this->table_client_company_addr ." AS as_branch
                        ON as_order.addr_idx = as_branch.addr_idx
            ) AS t_new
            
        ";

        $query = " SELECT * FROM ". $join_table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 날짜 기준 지점별 수주 정보 확인
     */
    public function getReceveOrderbyBranch( $arg_company_idx, $arg_search_date ){

        $join_table = "
            (
                SELECT  
                        as_order.*                                                  
                        , as_client.company_name
                        , as_client.manager_name
                        , as_client.manager_phone_no                
                        , as_client.client_zip_code                
                        , as_client.client_addr                
                        , as_client.client_addr_detail                
                        , IFNULL( as_branch.addr_name, '본점' )  AS branch_name              
                FROM
                        ". $this->table_client_receive_order ." AS as_order LEFT OUTER JOIN ". $this->table_client ." AS as_client
                        ON as_order.client_idx = as_client.client_idx
                        LEFT OUTER JOIN ". $this->table_client_company_addr ." AS as_branch
                        ON as_order.addr_idx = as_branch.addr_idx
                WHERE  ( as_order.company_idx = '". $arg_company_idx ."' )  AND ( process_state <> 'C')
                GROUP BY as_order.client_idx, as_order.addr_idx  
            ) AS t_new
            
        ";

        $query = " SELECT * FROM ". $join_table;
        $query_result = $this->db->execute( $query );
//echoBr( $query );
        return $query_result['return_data'];

    }

    


}



?>