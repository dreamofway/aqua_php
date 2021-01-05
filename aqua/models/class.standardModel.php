<?php

class standardModel extends baseModel {

    private $table;    
    private $table_file;    
    private $table_mes;    
    private $table_doc_usage;
    private $table_company;
    private $table_items;

    function __construct() {

        $this->table = ' t_document_files ';               
        $this->table_file = ' t_files ';               
        $this->table_mes = ' t_mes_work_checklist ';
        $this->table_doc_usage = ' t_document_usage ';        
        $this->table_company = ' t_company_info ';
        $this->table_items = ' t_service_items ';
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 제품 생산 지시 목록을 반환한다.
     */
    public function getDocumentFiles( $arg_data ){

        $result = [];
        $join_table = "
            (
                SELECT  
                        as_std.*                                                    
                        , as_files.idx AS file_idx
                        , as_files.path
                        , as_files.server_name
                        , as_files.origin_name                
                        , as_company_doc.doc_title AS df_work_checklist_doc_title               
                        , ( SELECT member_name FROM t_company_members WHERE company_member_idx = as_std.approve_idx ) AS approve_name
                        , ( SELECT member_name FROM t_company_members WHERE company_member_idx = as_std.reg_idx ) AS writer_name
                        , as_mes.checklist_title AS df_work_checklist_title
                FROM
                        ". $this->table ." AS as_std LEFT OUTER JOIN ". $this->table_file ." AS as_files
                        ON as_std.df_idx = as_files.tb_key
                        AND as_files.tb_name = 't_document_files'
                        AND as_files.del_flag = 'N'
                        LEFT OUTER JOIN ". $this->table_doc_usage ." AS as_company_doc
                        ON as_std.df_work_checklist_doc = as_company_doc.doc_usage_idx
                        LEFT OUTER JOIN ". $this->table_mes ." AS as_mes
                        ON as_std.df_work_checklist = as_mes.checklist_code
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
     * 게시판 유형에 따른 sort 반환
     */
    public function getMaxSort( $arg_type ){

        $query = " SELECT IFNULL( MAX(df_sort), 0 ) AS max_sort FROM ". $this->table ." WHERE df_type='". $arg_type ."' AND del_flag='N'  ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_sort'];

    }

    public function getMesWorkChecklist(){

        $query = " SELECT * FROM ". $this->table_mes ." WHERE del_flag = 'N' AND use_flag='Y' ";
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['rows'];

    }

    /**
     * 문서 정보를 반환한다.
     */
    public function getDoc( $arg_where ) {

        $join_table = "
            (
                SELECT  
                        as_company_doc.*
                        , as_servic_item.title AS item_title
                        ,as_company.company_name

                FROM
                        ". $this->table_doc_usage ." AS as_company_doc LEFT OUTER JOIN ". $this->table_items ." AS as_servic_item
                        ON as_company_doc.item_code = as_servic_item.item_code
                        LEFT OUTER JOIN ". $this->table_company ." AS as_company
                        ON as_company_doc.company_idx = as_company.company_idx


            ) AS t_new
            
        ";

        $query = " SELECT * FROM ". $join_table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }


    /**
     * 문서 정보 insert
     */
    public function insertDocumentFile( $arg_data ){
        return $this->db->insert( $this->table, $arg_data );
    }

    /**
     * 문서 정보 update
     */
    public function updateDocumentFile( $arg_data, $arg_where ) {
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }


}

?>