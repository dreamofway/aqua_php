<?php

class productionModel extends baseModel {

    private $table;
    private $table_product;
    private $table_product_unit;
    private $table_mixing_ratio;
    private $table_company_members;
    private $table_product_stock;
    private $table_doc_approval;
    private $table_doc_usage;

    function __construct() {

        $this->table = ' t_production_order ';               
        $this->table_order_materials = ' t_production_order_materials ';
        $this->table_order_products = ' t_production_order_products ';

        $this->table_done = ' t_production_done ';               
        $this->table_done_materials = ' t_production_done_materials ';
        $this->table_done_products = ' t_production_done_products ';


        $this->table_product = ' t_products_info ';    
        $this->table_product_unit = ' t_product_unit_info ';        
        $this->table_mixing_ratio = ' t_mixing_ratio ';        
        $this->table_company_members = ' t_company_members ';        
        $this->table_product_stock = ' t_product_stock ';        
        $this->table_doc_approval = ' t_document_approval ';        
        $this->table_doc_usage = ' t_document_usage ';        

        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 제품 생산 지시를 insert 한다.
     */
    public function insertProduction( $arg_data ){
        return $this->db->insert( $this->table, $arg_data );
    }

    /**
     * 제품 생산 완료정보를 insert 한다.
     */
    public function insertProductionDone( $arg_data ){
        return $this->db->insert( $this->table_done, $arg_data );
    }

    /**
     * 제품 생산 > 원부자재 사용정보를 insert 한다.
     */
    public function insertProductionOrderMaterials( $arg_data ){
        return $this->db->insert( $this->table_order_materials, $arg_data );
    }

    /**
     * 제품 생산 완료 > 원부자재 사용정보를 insert 한다.
     */
    public function insertProductionDoneMaterials( $arg_data ){
        return $this->db->insert( $this->table_done_materials, $arg_data );
    }

    /**
     * 제품 생산 > 제품 생산 정보를 insert 한다.
     */
    public function insertProductionOrderProducts( $arg_data ){
        return $this->db->insert( $this->table_order_products, $arg_data );
    }

    /**
     * 제품 생산 완료 > 제품 생산 정보를 insert 한다.
     */
    public function insertProductionDoneProducts( $arg_data ){
        return $this->db->insert( $this->table_done_products, $arg_data );
    }

    /**
     * 제품 생산 지시를 update 한다.
     */
    public function updateProduction( $arg_data, $arg_where ){
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }

    /**
     * 제품 생산 > 원부자재 정보를 update 한다.
     */
    public function updateProductionOrderMaterials( $arg_data, $arg_where ){
        return $this->db->update( $this->table_order_materials, $arg_data, $arg_where );
    }

    /**
     * 제품 생산 > 제품 정보를 update 한다.
     */
    public function updateProductionOrderProducts( $arg_data, $arg_where ){
        return $this->db->update( $this->table_order_products, $arg_data, $arg_where );
    }


    /**
     * 제품 생산 완료 정보를 update 한다.
     */
    public function updateProductionDone( $arg_data, $arg_where ){
        return $this->db->update( $this->table_done, $arg_data, $arg_where );
    }

    /**
     * 제품 생산 완료 정보 > 원부자재 정보를 update 한다.
     */
    public function updateProductionDoneMaterials( $arg_data, $arg_where ){
        return $this->db->update( $this->table_done_materials, $arg_data, $arg_where );
    }

    /**
     * 제품 생산 완료 정보 > 제품 정보를 update 한다.
     */
    public function updateProductionDoneProducts( $arg_data, $arg_where ){
        return $this->db->update( $this->table_done_products, $arg_data, $arg_where );
    }



    /**
     * 제품 생산 지시 목록을 반환한다.
     */
    public function getProductionOrders( $arg_data ){

        $result = [];
        
        $join_table = "
            (
                SELECT  
                    as_production.*
                    , as_product.product_name
                    , as_member.member_name
                    , ( SELECT GROUP_CONCAT( product_name SEPARATOR ',' ) FROM ". $this->table_product ." WHERE product_idx IN ( SELECT product_idx FROM ". $this->table_order_products ." WHERE production_idx=as_production.production_idx ) ) AS product_names
                    , ( SELECT approval_state FROM ". $this->table_done ." WHERE production_idx = as_production.production_idx ) AS approval_state
                    , ( SELECT approval_final_date FROM ". $this->table_done ." WHERE production_idx = as_production.production_idx ) AS approval_final_date
                    , ( SELECT doc_approval_idx FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_production.production_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_done ) ."' ) ) AS doc_exist
                    , ( SELECT approval_state FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_production.production_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_done ) ."' ) ) AS doc_approval_state                        
                FROM
                        ". $this->table ." AS as_production LEFT OUTER JOIN ". $this->table_company_members ." AS as_member
                        ON as_production.reg_idx = as_member.company_member_idx
                        LEFT OUTER JOIN ". $this->table_product ." AS as_product
                        ON as_production.product_idx = as_product.product_idx
                       
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
     * 신규 제조 번호를 반환 한다.
     */
    public function getNewProduceNo( $arg_no ){

        $query = " SELECT IFNULL( MAX( produce_no ), '') AS max_code FROM ". $this->table ." WHERE produce_no LIKE '". $arg_no ."%' ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_code'];

    }

    /**
     * 제품 생산 지시 상세 정보를 반환
     */
    public function getProductionOrder( $arg_where ){

        $join_table = "
            (
                SELECT  
                    as_production.*
                    , as_product.product_name
                    , as_member.member_name
                    , ( SELECT GROUP_CONCAT( product_name SEPARATOR ',' ) FROM ". $this->table_product ." WHERE product_idx IN ( SELECT product_idx FROM ". $this->table_order_products ." WHERE production_idx=as_production.production_idx ) ) AS product_names
                    , ( SELECT approval_state FROM ". $this->table_done ." WHERE production_idx = as_production.production_idx ) AS approval_state
                    , ( SELECT approval_final_date FROM ". $this->table_done ." WHERE production_idx = as_production.production_idx ) AS approval_final_date
                    , ( SELECT doc_approval_idx FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_production.production_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_done ) ."' ) ) AS doc_exist
                    , ( SELECT approval_state FROM ". $this->table_doc_approval ." WHERE ( task_table_idx = as_production.production_idx ) AND (del_flag = 'N') AND ( task_type = '". trim( $this->table_done ) ."' ) ) AS doc_approval_state                        
                FROM
                        ". $this->table ." AS as_production LEFT OUTER JOIN ". $this->table_company_members ." AS as_member
                        ON as_production.reg_idx = as_member.company_member_idx
                        LEFT OUTER JOIN ". $this->table_product ." AS as_product
                        ON as_production.product_idx = as_product.product_idx
                       
            ) AS t_new
            
        ";
     
        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 제품 생산 지시 > 원자재 사용정보
     */
    public function getProductionOrderMaterials( $arg_where ){

        $query = " SELECT * FROM ". $this->table_order_materials ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 제품 생산 지시 > 제품 생산 예정 정보
     */
    public function getProductionOrderProducts( $arg_where ){

        $query = " SELECT * FROM ". $this->table_order_products ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }


    /**
     * 제품 생산 지시 상세 정보를 반환
     */
    public function getProductionDone( $arg_where ){

        $join_table = "
            (
                SELECT  
                    as_production.*
                    , as_product.product_name
                    , as_member.member_name
                FROM
                        ". $this->table_done ." AS as_production LEFT OUTER JOIN ". $this->table_company_members ." AS as_member
                        ON as_production.reg_idx = as_member.company_member_idx
                        LEFT OUTER JOIN ". $this->table_product ." AS as_product
                        ON as_production.product_idx = as_product.product_idx
                       
            ) AS t_new
            
        ";
     
        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 제품 생산 지시 > 원자재 사용정보
     */
    public function getProductionDoneMaterials( $arg_where ){

        $query = " SELECT * FROM ". $this->table_done_materials ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 제품 생산 지시 > 제품 생산 예정 정보
     */
    public function getProductionDoneProducts( $arg_where ){

        $query = " 
            SELECT *
                    , ( SELECT product_name FROM ". $this->table_product ." WHERE product_idx = ". trim($this->table_done_products).".product_idx ) AS product_name 
            FROM ". $this->table_done_products ." WHERE 1=1 " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 생산 제품 재고를 insert 한다.
     */
    public function insertProductStock( $arg_data ){
        return $this->db->insert( $this->table_product_stock, $arg_data );
    }

    /**
     * 생산 제품 재고를 수정한다.
     */
    public function updateProductStock( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_product_stock, $arg_data, $arg_where );
    }



}

?>