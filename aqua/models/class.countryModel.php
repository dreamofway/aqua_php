<?php

class countryModel extends baseModel {

    private $table;

    function __construct() {

        $this->table = ' t_country_code ';
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    
    /**
     * 국가 코드 목록을 호출한다.
     */
    public function getCountryCodes( $arg_data ){

        $result = [];

        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    /**
     * 국가 코드 등록 처리
     */
    public function insertCountryCode( $arg_data ){

        return $this->db->insert( $this->table, $arg_data );

    }

    /**
     * 국가코드 수정 처리
     */
    public function updateCountryCode( $arg_data, $arg_where ) {
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }

    /**
     * 국가코드 삭제 처리
     */
    public function deleteCountryCode( $arg_where ) {
        return $this->db->delete( $this->table, $arg_where );
    }

}

?>