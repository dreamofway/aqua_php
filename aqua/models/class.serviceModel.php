<?php

class serviecModel extends baseModel {

    
    private $table_service;
    private $table_items;

    function __construct() {

        $this->table_service = ' t_service ';
        $this->table_items = ' t_service_items ';
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 서비스 신규 코드를 생성해 반환한다.
     */
    public function createServiceCode() {

        $query = " SELECT IFNULL( max( service_code ), '') AS max_code FROM " . $this->table_service;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_code'];
    }

    /**
     * 서비스 항목 코드를 생성해 반환한다.
     */
    public function createItemCode( $arg_type ) {

        $query = " SELECT IFNULL( MAX( item_code ), '') AS max_code FROM ". $this->table_items ." WHERE item_code LIKE '". $arg_type ."%' ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_code'];
    }

    /**
     * 서비스 목록을 반환한다.
     */
    public function getServices( $arg_data ){

        $result = [];
       
        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_service ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_service ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    /**
     * 서비스 정보를 반환 한다
     */
    public function getService( $arg_where ) {

        $query = " SELECT * FROM ". $this->table_service ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row'];

    }

    /**
     * 서비스 항목을 반환 한다.
     */
    public function getServiceItems( $arg_where ){

        // SELECT * FROM `t_service_items` WHERE 1=1 order by `item_group` ASC, `depth` ASC, `sort` ASC 

        $query = " SELECT * FROM ". $this->table_items ." WHERE " . $arg_where . " ORDER BY title ASC";
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['rows'];
    }

    /**
     * 서비스 항목 등록 처리
     */
    public function insertServiceItem( $arg_data ){
        return $this->db->insert( $this->table_items, $arg_data );
    }

    /**
     * 서비스 항목 수정 처리
     */
    public function updateServiceItem( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_items, $arg_data, $arg_where );
    }

    /**
     * 서비스 항목 삭제 처리
     */
    public function deleteServiceItem( $arg_where ) {
        return $this->db->delete( $this->table_items, $arg_where );
    }

    /**
     * 서비스 등록 처리
     */
    public function insertService( $arg_data ){
        return $this->db->insert( $this->table_service , $arg_data );
    }

    /**
     * 서비스 수정 처리
     */
    public function updateService( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_service, $arg_data, $arg_where );
    }

    /**
     * 서비스 삭제 처리
     */
    public function deleteService( $arg_where ) {
        return $this->db->delete( $this->table_service, $arg_where );
    }


}

?>