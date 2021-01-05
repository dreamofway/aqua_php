<?php

class haccpModel extends baseModel {

    private $table_storage_status;    
    private $table_storage_temp_log;    
    private $table_storage_warning_log;  
    private $table_kpi;  
    
    function __construct() {

        $this->table_storage_status = ' t_storage_status ';               
        $this->table_storage_temp_log = ' t_storage_temp_log ';               
        $this->table_storage_warning_log = ' t_storage_warning_log ';               
        $this->table_kpi = ' t_kpi_data ';               
        
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 저장고 별 최신 현황 반환
     */
    public function getStoragesRecencyLog( $arg_company_idx ){

        $query = ' SELECT * FROM (';

        $storage_result = $this->getStorage( " AND company_idx ='" . $arg_company_idx . "'" )['rows'];

        foreach( $storage_result AS $key=>$item ){
            
            if( $key == 0 ) {
                $query .= "( SELECT * FROM ". $this->table_storage_temp_log ." where storage_code='".$item['storage_code']."' ORDER BY temp_log_idx DESC LIMIT 1 )";
            } else {
                $query .= " UNION ALL ( SELECT * FROM ". $this->table_storage_temp_log ." where storage_code='".$item['storage_code']."' ORDER BY temp_log_idx DESC LIMIT 1 )";
            }
            
        }

        $query .= ") AS t_new ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['rows'];

    }

    /**
     * 저장고 현황 반환
     */
    public function getStorages( $arg_data ){

        $result = [];
        
        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_storage_status ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = "  SELECT  *
                            ,( SELECT temp_state FROM t_storage_temp_log WHERE storage_idx = t_storage.storage_idx ORDER BY temp_log_idx DESC limit 1 ) AS temp_state
                            ,( SELECT temperature FROM t_storage_temp_log WHERE storage_idx = t_storage.storage_idx ORDER BY temp_log_idx DESC limit 1 ) AS temperature
                    FROM ". $this->table_storage_status ." AS t_storage WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    public function getStorage( $arg_where ){

        $query = "  SELECT   * 
                            ,( SELECT temp_state FROM t_storage_temp_log WHERE storage_idx = t_storage.storage_idx ORDER BY temp_log_idx DESC limit 1 ) AS temp_state
                            ,( SELECT temperature FROM t_storage_temp_log WHERE storage_idx = t_storage.storage_idx ORDER BY temp_log_idx DESC limit 1 ) AS temperature
                    FROM ". $this->table_storage_status ." AS t_storage WHERE 1=1 " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 저장고 정보수정
     */
    public function updateStorage( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_storage_status, $arg_data, $arg_where );
    }

    /**
     * 이탈 로그 목록 반환
     */
    public function getStorageWarningLogs( $arg_data ){
        $result = [];
        $join_table = "
            (
                SELECT  
                        as_log.*                        
                        ,as_storage.storage_name

                FROM
                        ". $this->table_storage_warning_log ." AS as_log LEFT OUTER JOIN ". $this->table_storage_status ." AS as_storage
                        ON as_log.storage_idx = as_storage.storage_idx
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
     * 이탈 로그 목록 반환
     */
    public function getStorageWarningLog( $arg_where ){
        $result = [];
        $join_table = "
            (
                SELECT  
                        as_log.*                        
                        ,as_storage.storage_name

                FROM
                        ". $this->table_storage_warning_log ." AS as_log LEFT OUTER JOIN ". $this->table_storage_status ." AS as_storage
                        ON as_log.storage_idx = as_storage.storage_idx
            ) AS t_new
            
        ";
        
        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_where;
        
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }


    /**
     * 이탈정보 삽입
     */
    public function insertStorageWarningLog( $arg_data ){
        return $this->db->insert( $this->table_storage_warning_log, $arg_data );
    }

    /**
     * 이탈정보 수정
     */
    public function updateStorageWarningLog( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_storage_warning_log, $arg_data, $arg_where );
    }

    /**
     * 모니터링 로그 목록 반환
     */
    public function getStorageLogs( $arg_data ){

        $result = [];
       
        
        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_storage_temp_log ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_storage_temp_log ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    /**
     * kpi 목록 반환
     */
    public function getKpis( $arg_data ){

        $result = [];
       
        
        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_kpi ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_kpi ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }

    /**
     * kpi 목록 반환
     */
    public function getKpi( $arg_where ){

        $query = " SELECT * FROM ". $this->table_kpi ." WHERE 1=1 " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * kpi 삽입
     */
    public function insertKpi( $arg_data ){
        return $this->db->insert( $this->table_kpi, $arg_data );
    }

    /**
     * kpi 수정
     */
    public function updateKpi( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_kpi, $arg_data, $arg_where );
    }
	

	public function getStorageLogCount( $arg_where ){

        $query = " SELECT COUNT(temp_log_idx) AS cnt FROM ". $this->table_storage_temp_log ." WHERE 1=1 " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['cnt'];

    }

	/**
     * 온도 모니터링 데이터 삽입
     */
    public function insertStorageLog( $arg_data ){
        return $this->db->insert( $this->table_storage_temp_log, $arg_data );
    }
    
}

?>