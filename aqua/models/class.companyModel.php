<?php

class companyModel extends baseModel {

    function __construct() {

        $this->table = ' t_company_info ';
        $this->table_member = ' t_company_members ';
        $this->table_food_usage = ' t_food_type_usage ';
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 기업정보 목록을 반환한다.
     */
    public function getCompanys( $arg_data ){

        $result = [];
        $join_table = "
            (
                SELECT  
                        as_company.*
                        , as_comp_member.member_name AS partner_name
                        , as_comp_member.phone_no AS partner_phone_no
                        , as_comp_member.email AS partner_email
                FROM
                        ". $this->table ." AS as_company LEFT OUTER JOIN ". $this->table_member ." AS as_comp_member
                        ON as_company.company_idx = as_comp_member.company_idx
                        AND as_comp_member.partner= 'Y'
            ) AS t_company
            
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
     * 기업 회원 목록을 반환한다.
     */
    public function getCompanyMembers( $arg_data ){

        $result = [];
        $join_table = "
            (
                SELECT  
                        as_company.company_idx
                        ,as_company.company_name
                        , as_company.registration_no
                        , as_company.ceo_name
                        , as_company.company_tel
                        , as_comp_member.company_member_idx
                        , as_comp_member.member_name
                        , as_comp_member.phone_no 
                        , as_comp_member.email
                        , as_comp_member.del_flag
                        , as_comp_member.use_flag
                        , as_comp_member.reg_date
                FROM
                        ". $this->table_member ." AS as_comp_member LEFT OUTER JOIN ". $this->table ." AS as_company
                        ON as_company.company_idx = as_comp_member.company_idx                        
            ) AS t_member
            
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
     * 기업정보를 반환한다.
     */
    public function getCompany( $arg_where ) {

        $query = " SELECT * FROM ". $this->table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 기업회원 정보를 반환한다.
     */
    public function getCompanyMember( $arg_where ) {

        $query = " SELECT * FROM ". $this->table_member ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 기업 제조식품유형 정보를 반환한다.
     */
    public function getFoodUsage( $arg_company_idx ) {

        $query = " 
                    SELECT	as_food_usage.food_code, as_food_types.title, as_food_types.group_code, as_food_types.parent_code
                            ,( SELECT title FROM t_food_types WHERE food_code = as_food_types.group_code  ) AS large_title
                            ,( SELECT title FROM t_food_types WHERE food_code = as_food_types.parent_code  ) AS middle_title
                    FROM	t_food_type_usage AS as_food_usage LEFT OUTER JOIN t_food_types AS as_food_types
                            ON as_food_usage.food_code = as_food_types.food_code
                    WHERE	as_food_usage.company_idx='". $arg_company_idx ."'

                    ORDER BY as_food_usage.idx ASC
        ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 기업정보를 insert 한다.
     */
    public function insertCompanyInfo( $arg_data ){
        return $this->db->insert( $this->table, $arg_data );
    }

    /**
     * 기업정보를 수정한다.
     */
    public function updateCompanyInfo( $arg_data, $arg_where ) {
        return $this->db->update( $this->table, $arg_data, $arg_where );
    }
    
    /**
     * 기업회원정보를 insert 한다.
     */
    public function insertCompanyMember( $arg_data ){
        return $this->db->insert( $this->table_member, $arg_data );
    }

    /**
     * 기업회원 정보를 수정한다.
     */
    public function updateCompanyMember( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_member, $arg_data, $arg_where );
    }

    
    /**
     * 기업 제조식품유형 데이터를 update 한다.
     */
    public function updateFoodUsage( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_food_usage, $arg_data, $arg_where );
    }

    /**
     * 기업 제조식품 유형 데이터를 insert 한다.
     */
    public function insertFoodUsage( $arg_company_idx, $arg_data ){

        $insert_query = ' INSERT INTO '. $this->table_food_usage . ' ( food_code, company_idx, reg_idx, reg_ip, reg_date ) VALUES ';
        $insert_add_query = [];

        foreach( $arg_data AS $item ){            
            $insert_add_query[] = " ( '". $item ."', '". $arg_company_idx ."', '". getAccountInfo()['idx'] ."', '". $this->getIP() ."', NOW() ) ";
        }
        
        $insert_query .= join( ', ', $insert_add_query );

        return $this->db->execute( $insert_query );
    }



    
    


}

?>