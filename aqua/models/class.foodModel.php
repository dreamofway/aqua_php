<?php

class foodModel extends baseModel {

    private $table;

    function __construct() {

        $this->table = ' t_food_types ';
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 식품유형 정보를 가져온다.
     */
    public function getFoodTypes( $arg_data ){
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
     * 유형 코드를 생성해 반환한다.
     */
    public function createFoodTypeCode( $arg_type ){

        $query = " SELECT IFNULL( MAX( food_code ), '') AS max_code FROM ". $this->table ." WHERE food_code LIKE '". $arg_type ."%' ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_code'];

    }

    /**
     * 코드에 해당하는 유형의 정보를 반환한다.
     */
    public function getFoodTypeInfo( $arg_where  ){

        $query = " SELECT * FROM ". $this->table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row'];

    }

    /**
     * 식품유형 정보를 저장한다.
     */
    public function insertFoodType( $arg_data ) {
        return $this->db->insert( $this->table, $arg_data );
    }

    /**
     * 식품유형 정보를 수정한다.
     */
    public function updateFoodType( $arg_data, $arg_where ) {
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }

    /**
     * 식품유형 정보를 삭제한다.
     */
    public function deleteFoodType( $arg_where ) {
        return $this->db->delete( $this->table, $arg_where );
    }

}

?>