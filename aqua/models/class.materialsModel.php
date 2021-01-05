<?php

class materialsModel extends baseModel {

    private $table_company;
    private $table_materials_usage;    
    private $table_materials_order;    
    private $table_materials_stock;    
    private $table_materials;    
    private $table_doc_approval;    
    private $table_members;    

    function __construct() {

        $this->table_company = ' t_material_company ';        
        $this->table_materials_usage = ' t_materials_usage ';
        $this->table_materials_order = ' t_materials_order ';
        $this->table_materials_stock = ' t_materials_stock ';
        $this->table_materials = ' t_materials ';
        $this->table_doc_approval = ' t_document_approval ';
        $this->table_members = ' t_company_members ';
        
        $this->db = $this->connDB('masic');

    }

    function __destruct() {

        # db close
        $this->db->dbClose();

    }

    /**
     * 납품 업체 목록을 반환한다.
     */
    public function getCompanys( $arg_data ){

        $result = [];

        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_company ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_company ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;

    }
    
    /**
     * 납품업체 정보를 가져온다.
     */
    public function getCompany( $arg_where ){

        $query = " SELECT * FROM ". $this->table_company ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 납품업체 정보를 insert 한다.
     */
    public function insertCompany( $arg_data ){
        return $this->db->insert( $this->table_company, $arg_data );
    }

    /**
     * 납품업체 정보를 수정한다.
     */
    public function updateCompany( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_company, $arg_data, $arg_where );
    }

    /**
     * 주문 정보를 insert 한다.
     */
    public function insetOrder( $arg_data ){
        return $this->db->insert( $this->table_materials_order, $arg_data );
    }

    /**
     * 주문 정보를 수정한다.
     */
    public function updateOrder( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_materials_order, $arg_data, $arg_where );
    }

    /**
     * 원부자재 insert
     */
    public function insetMaterialStd( $arg_data ){
        return $this->db->insert( $this->table_materials, $arg_data );
    }

    /**
     * 원부자재 update
     */
    public function updateMaterialStd( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_materials, $arg_data, $arg_where );
    }

    /**
     * 재고 정보를 insert 한다.
     */
    public function insertStock( $arg_data ){
        return $this->db->insert( $this->table_materials_stock, $arg_data );
    }

    /**
     * 재고 정보를 update 한다.
     */
    public function updateStock( $arg_data, $arg_where ){
        return $this->db->update( $this->table_materials_stock, $arg_data, $arg_where );
    }

    /**
     * 재고 정보 확인
     */
    public function doubleCheckStock( $arg_where ){

        $query = " SELECT count(*) AS check_cnt FROM ". $this->table_materials_stock ." WHERE " . $arg_where;

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['check_cnt'];

    }

    /**
     * 유형 코드를 생성해 반환한다.
     */
    public function getMaxCode( $arg_type ){

        $query = " SELECT IFNULL( MAX( product_registration_no ), '') AS max_code FROM ". $this->table ." WHERE product_registration_no LIKE '". $arg_type ."%' ";

        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['max_code'];

    }


    /**
     * 자재 정보를 가져온다.
     */
    public function getMaterials( $arg_where ){

        $query = "  SELECT as_material.*,  as_company.company_name
                    FROM ". $this->table_materials_usage ." AS as_material LEFT OUTER JOIN ". $this->table_company ." AS as_company
                    ON as_material.material_company_idx = as_company.material_company_idx
                    WHERE ( as_material.del_flag='N' ) AND ( as_company.del_flag='N' ) AND " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 원부자재 재고 정보들을 가져온다.
     */
    public function getMaterialStocks( $arg_data ){
        $result = [];

        $join_table = "
            (
                SELECT  
                        as_stock.*                                                    
                        , as_company.company_name
                        , as_company.manager_name
                        , as_company.manager_phone_no                

                FROM
                        ". $this->table_materials_stock ." AS as_stock LEFT OUTER JOIN ". $this->table_materials_usage ." AS as_materials
                        ON as_stock.materials_usage_idx = as_materials.materials_usage_idx
                        LEFT OUTER JOIN ". $this->table_company ." AS as_company
                        ON as_materials.material_company_idx = as_company.material_company_idx

            ) AS t_new
            
        ";

        $query = " SELECT COUNT(*) AS cnt FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT IFNULL( SUM( quantity ), 0 ) AS total_quantity FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_quantity'] = $query_result['return_data']['row']['total_quantity'];


        $query = " SELECT * FROM ". $join_table ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;
    }


    /**
     * 원부자재 기준 정보들을 가져온다.
     */
    public function getMaterialStds( $arg_data ){
        $result = [];

        $query = " SELECT COUNT(*) AS cnt FROM ". $this->table_materials ." WHERE 1=1 " . $arg_data['query_where'];

        $query_result = $this->db->execute( $query );

        $result['total_rs'] = $query_result['return_data']['row']['cnt'];

        $query = " SELECT * FROM ". $this->table_materials ." WHERE 1=1 " . $arg_data['query_where']. $arg_data['query_sort'] . $arg_data['limit'];
        
        $query_result = $this->db->execute( $query );

        $result['rows'] = $query_result['return_data']['rows'];

        return $result;
    }

    /**
     * 원부자재 기준 정보를 가져온다.
     */
    public function getMaterialStd( $arg_where ){

        $query = " SELECT * FROM ". $this->table_materials ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 원부자재 기준 정보와 전월 를 가져온다.
     */
    public function getMaterialStockMonthHistory( $arg_date ){

        $result = [];

        # 원부자재 정보 + 전월 재고량
        $query = "  SELECT 
                        * 
                        ,((
                            SELECT 
                                IFNULL( SUM( quantity ), 0 ) AS in_sum
                            FROM 
                                t_materials_stock
                            WHERE 
                                ( del_flag='N' ) AND ( task_type = 'I'  ) AND ( LEFT( receipt_date, 7 ) < '". $arg_date ."'  ) AND ( material_idx=t_std.material_idx ) 
                        ) 
                        - 
                        (
                            SELECT 
                                IFNULL( SUM( quantity ), 0 ) AS use_sum
                            FROM 
                                t_materials_stock
                            WHERE 
                                ( del_flag='N' ) AND ( ( task_type <> 'I'  ) AND ( task_type <> 'S'  ) ) AND ( LEFT( reg_date, 7 ) < '". $arg_date ."'  ) AND ( material_idx=t_std.material_idx ) 
                        )) AS before_month_stock
                    FROM 
                        ". $this->table_materials ." AS t_std
                    WHERE 
                        ( company_idx = '". COMPANY_CODE ."' ) AND ( del_flag='N' ) AND ( use_flag='Y' )
        ";

        $query_result = $this->db->execute( $query );

        $result['materials'] = $query_result['return_data'];

        if( $result['materials']['num_rows'] > 0 ){
            $query = "
                SELECT *
                FROM (
            ";
            foreach( $result['materials']['rows'] AS $idx=>$item ){
                if( $idx > 0 ) {
                    $query .= " UNION ALL ";
                } 

                $query .= "
                (
                    SELECT 
                        *
                    FROM (
                        
                        (
                            SELECT 
                                SUM( quantity ) AS day_quantity 
                                , receipt_date AS occur_day
                                , material_idx
                                , material_kind 
                                , task_type
                                , 1 AS sort
                            FROM 
                                t_materials_stock 
                            WHERE 
                                ( del_flag='N' ) AND ( material_idx='". $item['material_idx'] ."' ) AND ( LEFT( receipt_date, 7 ) = '". $arg_date ."'  )  AND ( task_type = 'I' )
                            GROUP BY receipt_date
            
                        ) 
                        UNION ALL
                        (
                            SELECT 
                                SUM( quantity ) AS day_quantity 
                                , LEFT( reg_date, 10 ) AS occur_day
                                , material_idx
                                , material_kind 
                                , task_type
                                , 2 AS sort
                            FROM 
                                t_materials_stock 
                            WHERE 
                                ( del_flag='N' ) AND ( material_idx='". $item['material_idx'] ."' ) AND ( LEFT( reg_date, 7 ) = '". $arg_date ."'  )  AND ( task_type = 'U' )
                            GROUP BY LEFT( reg_date, 10 )
            
                        ) 
                        UNION ALL
                        (
                            SELECT 
                                SUM( quantity ) AS day_quantity 
                                , LEFT( reg_date, 10 ) AS occur_day
                                , material_idx
                                , material_kind 
                                , task_type
                                , 3 AS sort
                            FROM 
                                t_materials_stock 
                            WHERE 
                                ( del_flag='N' ) AND ( material_idx='". $item['material_idx'] ."' ) AND ( LEFT( reg_date, 7 ) = '". $arg_date ."'  )  AND ( task_type = 'R' )
                            GROUP BY LEFT( reg_date, 10 )
            
                        ) 
                        UNION ALL
                        (
                            SELECT 
                                SUM( quantity ) AS day_quantity 
                                , LEFT( reg_date, 10 ) AS occur_day
                                , material_idx
                                , material_kind 
                                , task_type
                                , 4 AS sort
                            FROM 
                                t_materials_stock 
                            WHERE 
                                ( del_flag='N' ) AND ( material_idx='". $item['material_idx'] ."' ) AND ( LEFT( reg_date, 7 ) = '". $arg_date ."'  )  AND ( task_type = 'D' )
                            GROUP BY LEFT( reg_date, 10 )
            
                        ) 
            
                    ) AS t_by_type
                    ORDER BY occur_day ASC, sort ASC  
            
                ) 
                ";
            }

            $query .= " ) AS t_result ";

            $query_result = $this->db->execute( $query );

            $result['material_month_info'] = $query_result['return_data'];


        }

        

        return $result;

    }

    /**
     * 배합비율 정보를 insert 한다.
     */ 
    public function insertMaterial( $arg_data ){
        return $this->db->insert( $this->table_materials_usage, $arg_data );
    }

    /**
     * 배합비율 정보를 수정한다.
     */
    public function updateMaterial( $arg_data, $arg_where ) {
        return $this->db->update( $this->table_materials_usage, $arg_data, $arg_where );
    }

    

    /**
     * 주문정보 목록을 불러온다.
     */
    public function getOrders( $arg_data ){

        $result = [];

        $join_table = "
            (
                SELECT  
                        as_order.*
                        , as_materials.material_code                                                                    
                        , as_company.company_name
                        , as_company.manager_name
                        , as_company.manager_phone_no
                        , IFNULL( as_doc_appr.doc_approval_idx, '') AS doc_exist
                        , IFNULL( as_doc_appr.approval_state, 'W') AS doc_approval_state
                        , ( SELECT member_name FROM ". $this->table_members ." WHERE company_member_idx = as_order.reg_idx ) AS member_name
                FROM
                        ". $this->table_materials_order ." AS as_order LEFT OUTER JOIN ". $this->table_materials_usage ." AS as_materials
                        ON as_order.materials_usage_idx = as_materials.materials_usage_idx
                        LEFT OUTER JOIN ". $this->table_company ." AS as_company
                        ON as_materials.material_company_idx = as_company.material_company_idx
						LEFT OUTER JOIN ". $this->table_doc_approval ." AS as_doc_appr
                        ON as_doc_appr.task_table_idx = as_order.order_idx
						AND ( as_doc_appr.task_type = '". trim( $this->table_materials_order ) ."' ) 
						AND ( as_doc_appr.del_flag = 'N' )
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
     * 주문 정보를 가져온다.
     */
    public function getOrder( $arg_where ){

        $join_table = "
            (
                SELECT  
                        as_order.*
                        , as_materials.material_code                                                                    
                        , as_company.company_name
                        , as_company.manager_name
                        , as_company.manager_phone_no
                        , IFNULL( as_doc_appr.doc_approval_idx, '') AS doc_exist
                        , IFNULL( as_doc_appr.approval_state, 'W') AS doc_approval_state
                        , ( SELECT member_name FROM ". $this->table_members ." WHERE company_member_idx = as_order.reg_idx ) AS member_name
                FROM
                        ". $this->table_materials_order ." AS as_order LEFT OUTER JOIN ". $this->table_materials_usage ." AS as_materials
                        ON as_order.materials_usage_idx = as_materials.materials_usage_idx
                        LEFT OUTER JOIN ". $this->table_company ." AS as_company
                        ON as_materials.material_company_idx = as_company.material_company_idx
						LEFT OUTER JOIN ". $this->table_doc_approval ." AS as_doc_appr
                        ON as_doc_appr.task_table_idx = as_order.order_idx
						AND ( as_doc_appr.task_type = '". trim( $this->table_materials_order ) ."' ) 
						AND ( as_doc_appr.del_flag = 'N' )
            ) AS t_new
            
        ";

        $query = " SELECT * FROM ". $join_table ." WHERE " . $arg_where;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 사용가능한 재고 수를 반환 한다.
     */
    public function getAvailableStocks( $arg_order_idx ){
        
        $query = " 
            SELECT 
                ( quantity - ( SELECT IFNULL( CAST( SUM( quantity ) AS DECIMAL(10,4) ), 0 )  FROM ". $this->table_materials_stock ." WHERE order_idx = '". $arg_order_idx ."' AND task_type <> 'I' AND del_flag='N' ) ) AS stock_quantity
            FROM ". $this->table_materials_stock ." WHERE order_idx = '". $arg_order_idx ."' AND task_type='I' AND ( del_flag='N' )
        ";
        $query_result = $this->db->execute( $query );

        return $query_result['return_data']['row']['stock_quantity'];


    }

    /**
     * 원부자재 입고 날짜별 수량
     */
    public function getQuantityByReceivingDate( $arg_where ){

        $query = " 
        SELECT * 
        FROM (
                SELECT * ,  CAST( ( total_in_quantity - use_quantity ) AS DECIMAL(10,1)  ) AS stock_quantity
                FROM (
                
                    SELECT * 
                        ,(SELECT IFNULL(  CAST( SUM( quantity ) AS DECIMAL(10,1)  ) , 0 ) FROM ". $this->table_materials_stock ." WHERE ( del_flag='N' ) AND ( task_type <> 'I' ) AND (  material_idx=use_insert_quantity.material_idx ) AND (receipt_date=use_insert_quantity.receipt_date)  ) AS use_quantity                        
                    FROM 
                    (
                        SELECT  CAST( SUM( quantity ) AS DECIMAL(10,1)  ) AS total_in_quantity , receipt_date, material_idx, material_kind   		
                        FROM ". $this->table_materials_stock ." WHERE ( del_flag='N' ) AND ( task_type='I' ) ". $arg_where ."
                        GROUP BY receipt_date, material_idx
                    ) AS use_insert_quantity
                
                )  AS t_cur
        ) AS t_result
        WHERE  stock_quantity > 0
        ";


        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 부자재 재고수량 (2020-06-12 부자재 사용처리 시 입고일자를 무시하기 때문에 부자재용 새로 생성)
     */
    public function getQuantitySub( $arg_where ){

        $query = " 
        SELECT * 
        FROM (
                SELECT * , ( total_in_quantity - use_quantity ) AS stock_quantity
                FROM (
                
                    SELECT * 
                        ,(SELECT IFNULL( SUM( quantity ), 0 ) FROM ". $this->table_materials_stock ." WHERE ( del_flag='N' ) AND ( task_type <> 'I' ) AND (  material_idx=use_insert_quantity.material_idx ) ) AS use_quantity                        
                    FROM 
                    (
                        SELECT  SUM( quantity ) AS total_in_quantity ,  material_idx, material_kind   		
                        FROM ". $this->table_materials_stock ." WHERE ( del_flag='N' ) AND ( task_type='I' ) ". $arg_where ."
                        GROUP BY  material_idx
                    ) AS use_insert_quantity
                
                )  AS t_cur
        ) AS t_result
        
        ";


        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }



     /**
     * 원부자재별 입고/사용수/재고수
     */
    public function getMaterialStockState(){

        $query = " 
            SELECT 
                as_mt.*
                , IFNULL( t_result.stock_quantity , 0 ) AS stock_quantity                
                , ( SELECT IFNULL( SUM(quantity), 0 ) FROM ". $this->table_materials_stock ." WHERE ( material_idx=t_result.material_idx ) AND ( del_flag='N' )  AND ( task_type = 'I' ) ) AS total_in_quantity
                , ( SELECT IFNULL( SUM(quantity), 0 ) FROM ". $this->table_materials_stock ." WHERE ( material_idx=t_result.material_idx ) AND ( del_flag='N' )  AND ( task_type = 'R' ) ) AS total_return_quantity
                , ( SELECT IFNULL( SUM(quantity), 0 ) FROM ". $this->table_materials_stock ." WHERE ( material_idx=t_result.material_idx ) AND ( del_flag='N' )  AND ( task_type = 'U' ) ) AS total_use_quantity
                , ( SELECT IFNULL( SUM(quantity), 0 ) FROM ". $this->table_materials_stock ." WHERE ( material_idx=t_result.material_idx ) AND ( del_flag='N' )  AND ( task_type = 'D' ) ) AS total_discard_quantity
                , ( SELECT IFNULL( SUM(quantity), 0 ) FROM ". $this->table_materials_stock ." WHERE ( material_idx=t_result.material_idx ) AND ( del_flag='N' )  AND ( task_type = 'S' ) ) AS total_schedule_quantity                
            FROM ".$this->table_materials." AS as_mt LEFT OUTER JOIN (
                SELECT * 
                FROM (
                    SELECT 
                            *,
                            SUM(total_in_quantity) AS stock_quantity
                    FROM (
                            (
                                SELECT
                                    material_idx
                                    , IFNULL( SUM( quantity ), 0 ) AS total_in_quantity
                                    , receipt_date
                                    , material_kind
                                FROM ". $this->table_materials_stock ."
                                WHERE ( del_flag='N' ) AND ( task_type='I' ) AND ( company_idx = '". COMPANY_CODE ."' ) GROUP BY material_idx
                            
                            ) UNION ALL
                            (
                            
                                SELECT
                                    material_idx
                                    , ( IFNULL( SUM( quantity ), 0 ) * -1) AS use_quantity
                                    , receipt_date
                                    , material_kind
                                FROM ". $this->table_materials_stock ."
                                WHERE ( del_flag='N' ) AND ( task_type<>'I' ) AND ( company_idx = '". COMPANY_CODE ."' ) GROUP BY material_idx
                            
                            )
                    ) AS as_get_union
                    GROUP BY material_idx
                ) AS as_get_stock
            ) AS t_result
            ON as_mt.material_idx = t_result.material_idx
            WHERE as_mt.del_flag='N' AND as_mt.use_flag='Y' AND as_mt.company_idx = '". COMPANY_CODE ."'

                
        ";

// echoBr( $query ); exit;
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];

    }

    /**
     * 입고 및 사용 발생월을 가져온다.
     */
    public function getReceiptOccurMonth(){
        $query = " 
            SELECT LEFT( receipt_date, 7 ) AS stock_receipt_month 
            FROM ". $this->table_materials_stock ."
            GROUP BY LEFT( receipt_date, 7 )  
            ORDER BY stock_receipt_month DESC
        ";
        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];
    }
        


}



?>