<?php

class client extends baseController {

    private $model;    
    private $model_doc;    
    private $paging;
    private $page_name;    

    function __construct() {
        
        #로그인 확인
        loginState();
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # model instance
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $this->paging = $this->new('pageHelper');
        $this->model = $this->new('clientModel');         
        $this->model_product = $this->new('productModel');         
        $this->model_doc = $this->new('docModel');         
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # GET parameters
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $this->page_data = $this->paging->getParameters();
       
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET params
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $this->page_data['params'] = $this->paging->setParams([
            'top_code'
            , 'left_code'
            , 'list_rows'
            , 'sch_type'            
            , 'sch_keyword'
            , 'sch_s_date'
            , 'sch_e_date'
            , 'sch_schedule_s_date'
            , 'sch_schedule_e_date'
            , 'sch_food_code'
            , 'sch_order_field'
            , 'sch_order_status'
        ]);

    }

    /**
     * 협력업체 목록을 생성한다.
     */
    public function client_list(){        

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'client';
        

        $query_where = " AND ( del_flag = 'N' ) AND ( company_idx = '". COMPANY_CODE ."' ) ";
        
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_order_field'] ){
            $query_sort = ' ORDER BY '. $this->page_data['sch_order_field'] .' '.$this->page_data['sch_order_status'].' ';
        } else {
            $query_sort = ' ORDER BY client_idx DESC ';
        }


        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( company_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( registration_no LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( ceo_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( manager_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( manager_phone_no LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( manager_email LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                            ) ";
        }

        if($this->page_data['sch_s_date']) {
            $query_where .= " AND ( reg_date >= '".$this->page_data['sch_s_date']." 00:00:00' ) ";
        }

		if($this->page_data['sch_e_date']) {
            $query_where .= " AND ( reg_date <= '".$this->page_data['sch_e_date']." 23:59:59' ) ";
        }

        # 리스트 정보요청
        $list_result = $this->model->getClients([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging; 
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_list.php';
        $this->page_data['list'] = $list_result['rows'];        
        $this->view( $this->page_data );

    }


     
    /**
     * 고객사 작성 페이지를 구성한다.
     */
    public function client_write(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'client';
        
        $this->page_data['company_addrs'] = json_encode( [] );

        if( $this->page_data['mode'] == 'edit') {

            #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
            # 필수값 체크
            #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
            $this->issetParams( $this->page_data, ['client_idx']);
            
            $this->page_data['page_work'] = '수정';

            # 기업정보를 요청한다.
            $query_result = $this->model->getClient( " client_idx = '". $this->page_data['client_idx'] ."' " );

            if( $query_result['num_rows'] == 0 ){
                
                errorBack('해당 게시물이 삭제되었거나 정상적인 접근 방법이 아닙니다.');
                
            }

            $this->page_data = array_merge( $this->page_data, $query_result['row'] );

            if( $query_result['num_rows'] > 0 ){
                
                $this->page_data = array_merge( $this->page_data, $query_result['row'] );
                
            }

            # 업체의 배송지 주소를 요청한다.
            $query_result = $this->model->getClientComapnyAddr(" client_idx = '". $this->page_data['client_idx'] ."' AND del_flag='N' " );    
            
            if( $query_result['num_rows'] > 0 ){
                $this->page_data['company_addrs'] = json_encode( $query_result['rows'] );
            } 

        } else {

            $this->page_data['mode'] = 'ins';
            $this->page_data['page_work'] = '등록';

        }

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_write.php';
        $this->page_data['list'] = $list_result['rows'];
        
        $this->view( $this->page_data );

    }

    /**
     * 고객기업 정보 데이터를 처리한다.
     */
    public function client_proc(){

        # post 접근 체크
        postCheck();

        // echoBr( $this->page_data );

        switch( $this->page_data['mode'] ) {

            case 'ins' : {
                
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'company_name'                                        
                ]);
                
                if( empty( $this->page_data['registration_no'] ) == false ) {
                    
                    # 기업 사업자등록 번호와 일치한 정보 확인
                    $query_result = $this->model->getClient( " registration_no = '". $this->page_data['registration_no'] ."' AND company_idx = '". COMPANY_CODE ."'" );

                    if( $query_result['num_rows'] > 0 ){
                        
                        errorBack('이미 등록된 업체 입니다.');
                        
                    }    

                }
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 기업 정보 삽입
                $query_result = $this->model->insertClient([
                    'company_idx' => COMPANY_CODE
                    ,'company_name' => $this->page_data['company_name']
                    ,'registration_no' => $this->page_data['registration_no']
                    ,'ceo_name' => $this->page_data['ceo_name']
                    ,'company_tel' => $this->page_data['company_tel']
                    ,'company_fax' => $this->page_data['company_fax']
                    ,'company_homepage' => $this->page_data['company_homepage']
                    ,'client_zip_code' => $this->page_data['client_zip_code']
                    ,'client_addr' => $this->page_data['client_addr']
                    ,'client_addr_detail' => $this->page_data['client_addr_detail']
                    ,'manager_name' => $this->page_data['manager_name']
                    ,'manager_phone_no' => $this->page_data['manager_phone_no']
                    ,'manager_email' => $this->page_data['manager_email']
                    ,'use_flag' => $this->page_data['use_flag']
                    ,'reg_idx' => getAccountInfo()['idx']
                    ,'reg_date' => 'NOW()'
                    ,'reg_ip' => $this->getIP()
                ]);
                
                # 기업정보 삽입 완료된 기본키를 가져온다.
                $new_company_idx = $query_result['return_data']['insert_id'];
                
                # 납품 자재 정보 처리
                $this->companyAddrProc( 
                    $new_company_idx
                    , $this->page_data['addr_name'] 
                    , $this->page_data['zipcode'] 
                    , $this->page_data['addr'] 
                    , $this->page_data['addr_detail']                   
                );

                # 트랜잭션 종료
                $this->model->stopTransaction();

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'edit' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'client_idx'                    
                    ,'company_name'                    
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 기업 정보 삽입
                $query_result = $this->model->updateClient([
                    'company_name' => $this->page_data['company_name']
                    ,'registration_no' => $this->page_data['registration_no']
                    ,'ceo_name' => $this->page_data['ceo_name']
                    ,'company_tel' => $this->page_data['company_tel']
                    ,'company_fax' => $this->page_data['company_fax']
                    ,'company_homepage' => $this->page_data['company_homepage']
                    ,'client_zip_code' => $this->page_data['client_zip_code']
                    ,'client_addr' => $this->page_data['client_addr']
                    ,'client_addr_detail' => $this->page_data['client_addr_detail']
                    ,'manager_name' => $this->page_data['manager_name']
                    ,'manager_phone_no' => $this->page_data['manager_phone_no']
                    ,'manager_email' => $this->page_data['manager_email']
                    ,'use_flag' => $this->page_data['use_flag']
                    ,'edit_idx' => getAccountInfo()['idx']
                    ,'edit_date' => 'NOW()'
                    ,'edit_ip' => $this->getIP()
                ] ," client_idx = '" . $this->page_data['client_idx']. "'" );


                # 납품 자재 정보 처리
                $this->companyAddrProc( 
                    $this->page_data['client_idx']
                    , $this->page_data['addr_name'] 
                    , $this->page_data['zipcode'] 
                    , $this->page_data['addr'] 
                    , $this->page_data['addr_detail']  
                );

                # 트랜잭션 종료
               $this->model->stopTransaction();

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_write?client_idx='. $this->page_data['client_idx']. '&mode=edit' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'del' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'client_idx'                    
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 기업 정보 수정
                $query_result = $this->model->updateClient([
                    'del_flag' => 'Y'
                ] ," client_idx = '" . $this->page_data['client_idx']. "'" );

                # 트랜잭션 종료
                $this->model->stopTransaction();

                movePage('replace', '삭제되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            default : {
                errorBack('잘못된 접근입니다.');
            }
        }
    }

    /**
     * 납품 자재 정보 데이터 처리
     */
    private function companyAddrProc(  
        $arg_client_idx
        , $arg_addr_name
        , $zipcode
        , $addr
        , $addr_detail        
    ){


        # arg_client_idx 에 해당하는 기존 데이터 삭제처리
        $this->model->updateClientCompanyAddr([
            'del_flag' => 'Y'
        ], " client_idx  = '" . $arg_client_idx. "'"  );

        # 신규 insert 처리
        return $this->model->insertcompanyAddrs(  
            $arg_client_idx
            , $arg_addr_name
            , $zipcode
            , $addr
            , $addr_detail   
            , COMPANY_CODE 
        );

    }

    /**
     * 수주관리 목록 
     */
    public function receive_an_order_list(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'receive_an_order';
        $this->page_data['process_state_arr'] = [
            'O' => '수주'
            ,'D' => '출하'
            ,'C' => '취소'
        ];

        $query_where = " AND ( del_flag='N' ) AND ( company_idx = '". COMPANY_CODE ."' ) ";
        
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_order_field'] ){
            $query_sort = ' ORDER BY '. $this->page_data['sch_order_field'] .' '.$this->page_data['sch_order_status'].' ';
        } else {            
            $query_sort = ' ORDER BY order_idx DESC, order_date DESC, delivery_date DESC ';
        }


        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( company_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( order_idx LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( product_name LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( manager_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                            ) ";
        }
        
        if($this->page_data['sch_process_state']) {
            $query_where .= " AND ( process_state = '".$this->page_data['sch_process_state']."' ) ";
        }

        if($this->page_data['sch_s_date']) {
            $query_where .= " AND ( order_date >= '".$this->page_data['sch_s_date']."' ) ";
        }

		if($this->page_data['sch_e_date']) {
            $query_where .= " AND ( order_date <= '".$this->page_data['sch_e_date']."' ) ";
        }

        if($this->page_data['sch_schedule_s_date']) {
            $query_where .= " AND ( delivery_date >= '".$this->page_data['sch_schedule_s_date']."' ) ";
        }

		if($this->page_data['sch_schedule_e_date']) {
            $query_where .= " AND ( delivery_date <= '".$this->page_data['sch_schedule_e_date']."' ) ";
        }



        # 리스트 정보요청
        $list_result = $this->model->getReceiveOrders([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging; 
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_list.php';
        $this->page_data['list'] = $list_result['rows'];        
        $this->view( $this->page_data );


    }
    
    /**
     * 수주 등록화면 구성
     */
    public function receive_an_order_write(){
    

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'receive_an_order';
        $this->page_data['products'] = [];

        # 생산 제품별 재고 수량을 가져온다.
        $query_result = $this->model_product->getProdcuctStockState();    
        
        // echobr( $query_result ); exit;
        
        if( $query_result['num_rows'] > 0 ){
            $this->page_data['products'] = json_encode( $query_result['rows'] );
        }

        # 고객사 정보를 요청한다.
        $this->page_data['clients'] = $this->model->getClient( " ( company_idx = '". COMPANY_CODE ."' ) AND ( use_flag='Y' ) AND ( del_flag='N' ) " )['rows'];    
         
        # 고객사 별 배송지를 요청한다.
        $query_result = $this->model->getClientComapnyAddr( " ( company_idx = '". COMPANY_CODE ."' ) AND ( del_flag='N' ) " );    
        

        if( $query_result['num_rows'] > 0 ) {

            $client_addrs = [];
            
            foreach( $query_result['rows'] AS $idx=>$item ) {
            
            
                if( gettype($client_addrs[ $item['client_idx'] ]) == 'NULL' ) {
                    $client_addrs[ $item['client_idx'] ] = [];
                    $client_addrs[ $item['client_idx'] ][0]['addr_idx'] = 0;
                    $client_addrs[ $item['client_idx'] ][0]['addr_name'] = '본점';
                }
                
                array_push( $client_addrs[ $item['client_idx'] ], $item);
                
            }
            

        } else {
            $client_addrs['addr_idx'] = 0;
            $client_addrs['addr_name'] = '본점';
        }

        $this->page_data['client_addrs'] = json_encode( $client_addrs );

        $this->page_data['mode'] = 'ins';
        $this->page_data['page_work'] = '등록';


        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_write.php';
        $this->page_data['list'] = $list_result['rows'];
        
        $this->view( $this->page_data );

        
    }

    public function receive_an_order_edit(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'receive_an_order';            
        $this->issetParams( $this->page_data, ['order_idx']);
        
        $this->page_data['page_work'] = '수정';

        # 기업정보를 요청한다.
        $query_result = $this->model->getReceiveOrder( " order_idx = '". $this->page_data['order_idx'] ."' " );

        if( $query_result['num_rows'] == 0 ){
            
            errorBack('해당 게시물이 삭제되었거나 정상적인 접근 방법이 아닙니다.');
            
        } else {
            $this->page_data = array_merge( $this->page_data, $query_result['row'] );
        }
        
        # 업체의 배송지 주소를 요청한다.
        $query_result = $this->model->getClientComapnyAddr(" client_idx = '". $this->page_data['client_idx'] ."' AND del_flag='N' " );    
        
        if( $query_result['num_rows'] > 0 ){
            $this->page_data['company_addrs'] = $query_result['rows'];
        } else {
            $this->page_data['company_addrs'] = [];
        }

        // echoBr( $this->page_data['company_addrs'] ); exit;
        $this->page_data['mode'] = 'edit';
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_edit.php';
        $this->page_data['list'] = $list_result['rows'];
        
        $this->view( $this->page_data );

    }

    /**
     * 수주 데이터 처리
     */
    public function receive_an_order_proc(){
        # post 접근 체크
        postCheck();

        // echoBr( $this->page_data ); exit;

        switch( $this->page_data['mode'] ) {

            case 'ins' : {
                
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'client_idx'                                        
                    ,'order_date'                                        
                    ,'product_idx'                                        
                    ,'product_name'                                        
                    ,'food_code'                                        
                    ,'product_unit_idx'                                        
                    ,'product_unit'                                        
                    ,'product_unit_type'                                        
                    ,'packaging_unit_quantity'                                        
                    ,'addr_idx'                                        
                    ,'delivery_date'                                        
                    ,'quantity'                                        
                ]);
                
                
                # 트랜잭션 시작
                $this->model->runTransaction();
                
                foreach( $this->page_data['quantity'] AS $idx=>$val ){
                    
                    if( $val == '' ) {
                        errorBack('수량이 입력되지 않은 주문 정보가 있습니다.');
                    }

                    if( $idx == 0 ) {

                        $order_no = '';

                    } else {

                        $order_no = $get_insert_id;

                    }

                    # 수주 정보 삽입
                    $query_result = $this->model->insertClientReceiveOrder([
                        'company_idx' => COMPANY_CODE
                        ,'client_idx' => $this->page_data['client_idx']                        
                        ,'order_group' => $order_no
                        ,'order_date' => $this->page_data['order_date']
                        ,'addr_idx' => $this->page_data['addr_idx'][$idx]
                        ,'product_idx' => $this->page_data['product_idx'][$idx]
                        ,'product_name' => $this->page_data['product_name'][$idx]
                        ,'food_code' => $this->page_data['food_code'][$idx]
                        ,'product_unit_idx' => $this->page_data['product_unit_idx'][$idx]
                        ,'product_unit' => $this->page_data['product_unit'][$idx]
                        ,'product_unit_type' => $this->page_data['product_unit_type'][$idx]
                        ,'packaging_unit_quantity' => $this->page_data['packaging_unit_quantity'][$idx]
                        ,'quantity' => $this->page_data['quantity'][$idx]
                        ,'delivery_date' => $this->page_data['delivery_date'][$idx]                        
                        ,'process_state' => 'O'
                        ,'sale_unit' => 'P'
                        ,'reg_idx' => getAccountInfo()['idx']
                        ,'reg_date' => 'NOW()'
                        ,'reg_ip' => $this->getIP()                        
                    ]);

                    if( $idx == 0 ) {                        
                        $get_insert_id =  $query_result['return_data']['insert_id'];
                    }
                    
                }

                # 첫번째 주문 그룹 값 업데이트
                $this->model->updateClientReceiveOrder([ 'order_group' => $get_insert_id ], " order_idx = '" . $get_insert_id. "'" );

                # 트랜잭션 종료
                $this->model->stopTransaction();

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'edit' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'order_idx'                    
                    ,'addr_idx'                    
                    ,'order_date'                    
                    ,'delivery_date'                    
                    ,'quantity'                    
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 수주정보 수정
                $query_result = $this->model->updateClientReceiveOrder([
                    'quantity' => $this->page_data['quantity']
                    ,'addr_idx' => $this->page_data['addr_idx']
                    ,'order_date' => $this->page_data['order_date']
                    ,'delivery_date' => $this->page_data['delivery_date']
                    ,'process_state' => $this->page_data['process_state']
                    ,'edit_idx' => getAccountInfo()['idx']
                    ,'edit_date' => 'NOW()'
                    ,'edit_ip' => $this->getIP()
                ] ," order_idx = '" . $this->page_data['order_idx']. "'" );

                # 트랜잭션 종료
               $this->model->stopTransaction();

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_edit?order_idx='. $this->page_data['order_idx']. '&mode=edit' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'order_cancel' : {
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'order_idx'                                     
                ]);
                
                $query_result = $this->model->updateClientReceiveOrder( ['process_state' => 'C'] ," order_idx = '" . $this->page_data['order_idx']. "'" );
                
                movePage('replace', '취소처리되었습니다.', './'. $this->page_data['page_name'] .'_list?page='. $this->page_data['page'] . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'del' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'order_idx'                    
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 기업 정보 수정
                $query_result = $this->model->updateClientReceiveOrder([
                    'del_flag' => 'Y'
                    ,'del_idx' => getAccountInfo()['idx']
                    ,'del_date' => 'NOW()'
                    ,'del_ip' => $this->getIP()
                ] ," order_idx = '" . $this->page_data['order_idx']. "'" );

                # 트랜잭션 종료
                $this->model->stopTransaction();

                movePage('replace', '삭제되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            default : {
                errorBack('잘못된 접근입니다.');
            }
        }
    }

    /**
     * 출하관리 목록을 구성한다.
    */
    public function shipment_list(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'shipment';
        $this->page_data['process_state_arr'] = [
            'O' => '수주'
            ,'D' => '출하'
            ,'C' => '취소'
        ];

        $query_where = " AND ( del_flag='N' ) AND ( company_idx = '". COMPANY_CODE ."' ) ";
        
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_order_field'] ){
            $query_sort = ' ORDER BY '. $this->page_data['sch_order_field'] .' '.$this->page_data['sch_order_status'].' ';
        } else {            
            $query_sort = ' ORDER BY order_idx DESC, order_date DESC, delivery_date DESC ';
        }


        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( company_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( order_idx LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( product_name LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( manager_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( product_stock_used_expiration_dates LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                            ) ";
        }
        
        if($this->page_data['sch_process_state']) {
            $query_where .= " AND ( process_state = '".$this->page_data['sch_process_state']."' ) ";
        }

        if($this->page_data['sch_s_date']) {
            $query_where .= " AND ( order_date >= '".$this->page_data['sch_s_date']."' ) ";
        }

		if($this->page_data['sch_e_date']) {
            $query_where .= " AND ( order_date <= '".$this->page_data['sch_e_date']."' ) ";
        }

        if($this->page_data['sch_schedule_s_date']) {
            $query_where .= " AND ( delivery_date >= '".$this->page_data['sch_schedule_s_date']."' ) ";
        }

		if($this->page_data['sch_schedule_e_date']) {
            $query_where .= " AND ( delivery_date <= '".$this->page_data['sch_schedule_e_date']."' ) ";
        }



        # 리스트 정보요청
        $list_result = $this->model->getReceiveOrders([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging; 
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_list.php';
        $this->page_data['list'] = $list_result['rows'];        
        $this->view( $this->page_data );
    }

    /**
     * 출하관리 출하처리 화면을 구성한다.
    */
    public function shipment_write(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'shipment';            
        $this->issetParams( $this->page_data, ['order_idx']);
        
        $this->page_data['page_work'] = '수정';

        # 수주정보를 요청한다.
        $query_result = $this->model->getReceiveOrder( " order_idx = '". $this->page_data['order_idx'] ."' " );

        if( $query_result['num_rows'] == 0 ){
            
            errorBack('해당 게시물이 삭제되었거나 정상적인 접근 방법이 아닙니다.');
            
        } else {

            $this->page_data = array_merge( $this->page_data, $query_result['row'] );

            $quantity = (int)$query_result['row']['quantity'];

            // $quantity = '161';
            # 주문건에 해당하는 제품의 유통기한 별 재고 요청
            $result_expiration_date = $this->model_product->getAvailableStockByExpirationDate( $query_result['row']['product_unit_idx'] );     
            // echoBr( $result_expiration_date['rows'] ); exit;
            if( $result_expiration_date['num_rows'] > 0 ){

                $this->page_data['quantity_expiration_date_arr'] = $result_expiration_date['rows'];

              
            } else {
                $this->page_data['quantity_expiration_date_arr'] = [];
            }

        }
        
        # 업체의 배송지 주소를 요청한다.
        $query_result = $this->model->getClientComapnyAddr(" client_idx = '". $this->page_data['client_idx'] ."' AND del_flag='N' " );    
        
        if( $query_result['num_rows'] > 0 ){
            $this->page_data['company_addrs'] = $query_result['rows'];
        } else {
            $this->page_data['company_addrs'] = [];
        }

        // echoBr( $this->page_data['company_addrs'] ); exit;
        $this->page_data['mode'] = 'edit';
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_write.php';
        $this->page_data['list'] = $list_result['rows'];
        
        $this->view( $this->page_data );

    }

    /**
     * 유통기한별 재고수 배열과 이용 수량을 입력받아 수량에 해당하는 유통기한 정보를 반환
     */
    public function predictionUseStock( $arg_data ){

        $quantity = $arg_data['quantity'];
        $day_quantity = 0;
        $prediction_day = [];
        $prediction_day_stock_idx = [];
        $total_sum = 0;
        foreach( $arg_data['expiration_date_arr'] AS $idx=>$item ) {

            $day_quantity += (int)$item['stock_quantity'];
        
            if( $quantity <= (int)$item['stock_quantity'] ) {

                $day_info = [
                    'stock_idx'=>$item['stock_idx']
                    ,'expiration_date'=>$item['expiration_date']
                    ,'stock_quantity'=>$item['stock_quantity']
                ];

                $prediction_day[] = $day_info;
                $prediction_day_stock_idx[] = $item['stock_idx'];
                $total_sum += $item['stock_quantity'];

                break;
            } else {

                $day_info = [
                    'stock_idx'=>$item['stock_idx']
                    ,'expiration_date'=>$item['expiration_date']
                    ,'stock_quantity'=>$item['stock_quantity']
                ];

                $prediction_day[] = $day_info;
                $prediction_day_stock_idx[] = $item['stock_idx'];
                $total_sum += $item['stock_quantity'];
            }
            
        }

        return [
            'prediction_day_arr'=>$prediction_day
            ,'prediction_stock_idxs'=>join(',', $prediction_day_stock_idx)
            ,'total_stock_sum'=>$total_sum
        ];
    }

    /**
     * 수주 데이터 처리 
     */
    public function shipment_proc(){
        # post 접근 체크
        postCheck();

        // echoBr( $this->page_data ); exit;

        switch( $this->page_data['mode'] ) {

            case 'ins' : {
                

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'edit' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'order_idx'                    
                    ,'addr_idx'                    
                    ,'order_date'                    
                    ,'delivery_date'                    
                    ,'quantity'
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 수주정보 수정
                $query_result = $this->model->updateClientReceiveOrder([
                    'quantity' => $this->page_data['quantity']
                    ,'addr_idx' => $this->page_data['addr_idx']
                    ,'order_date' => $this->page_data['order_date']
                    ,'delivery_date' => $this->page_data['delivery_date']                    
                    ,'edit_idx' => getAccountInfo()['idx']
                    ,'edit_date' => 'NOW()'
                    ,'edit_ip' => $this->getIP()
                ] ," order_idx = '" . $this->page_data['order_idx']. "'" );

                # 트랜잭션 종료
               $this->model->stopTransaction();

                movePage('replace', '저장되었습니다.', './'. $this->page_data['page_name'] .'_write?order_idx='. $this->page_data['order_idx']. '&mode=edit' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            case 'approval_request' : {
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'order_idx'                    
                    ,'addr_idx'                    
                    ,'order_date'                    
                    ,'delivery_date'                    
                    ,'quantity'
                ]);

                $product_stock_used_info = $this->productUseProc( 
                    $this->page_data['order_idx']
                    , $this->page_data['stock_quantity']
                    , $this->page_data['product_idx']
                    , $this->page_data['product_name']
                    , $this->page_data['product_unit_idx']
                    , $this->page_data['product_unit']
                    , $this->page_data['product_unit_type']
                    , $this->page_data['packaging_unit_quantity']
                    , $this->page_data['expiration_date']
                    , $this->page_data['expiration_days']
                    , $this->page_data['quantity']
                );
                // echoBr( $product_stock_used_info );

                # 트랜잭션 시작
                $this->model->runTransaction();
                
                # 수주정보 수정
                $query_result = $this->model->updateClientReceiveOrder([
                    'quantity' => $this->page_data['order_quantity']
                    ,'addr_idx' => $this->page_data['addr_idx']
                    ,'order_date' => $this->page_data['order_date']
                    ,'delivery_date' => $this->page_data['delivery_date']                    
                    ,'product_stock_used_info' => $product_stock_used_info['stock_idxs']
                    ,'product_stock_used_expiration_dates' => $product_stock_used_info['expiration_dates']
                    ,'approval_state' => 'R'
                    ,'process_state' => 'D'
                    ,'edit_idx' => getAccountInfo()['idx']
                    ,'edit_date' => 'NOW()'
                    ,'edit_ip' => $this->getIP()
                ] ," order_idx = '" . $this->page_data['order_idx']. "'" );

                # 트랜잭션 종료
                $this->model->stopTransaction();
                
                movePage('replace', '출하처리되었습니다.', './'. $this->page_data['page_name'] .'_list?page='. $this->page_data['page'] . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            
            default : {
                errorBack('잘못된 접근입니다.');
            }
        }
    }

    /**
     * 재고 사용등록
     */
    public function productUseProc( 
        $arg_order_idx
        , $arg_stock_quantity
        , $arg_product_idx 
        , $arg_product_name 
        , $arg_product_unit_idx
        , $arg_product_unit
        , $arg_product_unit_type
        , $arg_packaging_unit_quantity
        , $arg_expiration_date
        , $arg_expiration_days
        , $arg_quantity
    ){

        $stock_idxs = [];
        $expiration_dates = [];
        $return_arr = [];
        
        $this->model_product->runTransaction();

        foreach( $arg_quantity AS $idx=>$val ){

            # 해당 재고 정보에 사용 수량을 확인한다.
            // echoBr( $val );
            

            if( $arg_quantity[ $idx ] > 0 ) {
                if( is_numeric( $arg_stock_quantity[ $idx ] ) == true ) {                    
                    
					if( empty( apache_request_headers()['access_id'] ) == false ) {
                        if( $arg_quantity[ $idx ] > $arg_stock_quantity[ $idx ] ){
                            $result['status'] = 'fail';
                            $result['msg'] = '유통기한 "'. $arg_expiration_date[ $idx ] .'" 날짜의 총 재고수량보다 큰 값을 입력하셨습니다.';
                            jsonExit( $result );
                        }
					} else {
						if( $arg_quantity[ $idx ] > $arg_stock_quantity[ $idx ] ){
							errorBack('유통기한 \"'. $arg_expiration_date[ $idx ] .'\" 날짜의 총 재고수량보다 큰 값을 입력하셨습니다.');
						}
					}

                    # 제품 사용등록
                    $query_result = $this->model_product->insertStock([
                        'company_idx' => COMPANY_CODE                        
                        ,'product_idx' => $arg_product_idx[ $idx ]
                        ,'product_unit_idx' => $arg_product_unit_idx[ $idx ]
                        ,'product_name' => $arg_product_name[ $idx ]
                        ,'product_unit' => $arg_product_unit[ $idx ]
                        ,'product_unit_type' => $arg_product_unit_type[ $idx ]
                        ,'packaging_unit_quantity' => $arg_packaging_unit_quantity[ $idx ]
                        ,'expiration_date' => $arg_expiration_date[ $idx ]
                        ,'expiration_days' => $arg_expiration_days[ $idx ]
                        ,'box_quantity' => $arg_quantity[ $idx ]
                        ,'memo' => '수주번호 [ ' . $arg_order_idx . ' ] 에 출하처리됨 '
                        ,'task_type' => 'U'
                        ,'reg_idx' => getAccountInfo()['idx']
                        ,'reg_date' => 'NOW()'
                        ,'reg_ip' => $this->getIP()
                    ]);

                    $stock_idxs[] = $query_result['return_data']['insert_id'];
                    $expiration_dates[] = $arg_expiration_date[ $idx ];
                }
            }

            
         
        }

        $this->model_product->stopTransaction();

        $return_arr['stock_idxs'] = join(',', $stock_idxs );
        $return_arr['expiration_dates'] = join(',', $expiration_dates );

        return $return_arr;
    }

    /**
     * 제품 재고 목록을 구성한다.
     */
    public function stock_list(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'stock';
        $this->page_data['products'] = [];
        
        $query_where = " AND ( del_flag='N' ) AND ( company_idx = '". COMPANY_CODE ."' )  ";
        
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_order_field'] ){
            $query_sort = ' ORDER BY '. $this->page_data['sch_order_field'] .' '.$this->page_data['sch_order_status'].' ';
        } else {
            $query_sort = ' ORDER BY stock_idx DESC, expiration_date DESC  ';
        }

        if($this->page_data['sch_s_date']) {
            $query_where .= " AND ( expiration_date >= '".$this->page_data['sch_s_date']."' ) ";
        }

		if($this->page_data['sch_e_date']) {
            $query_where .= " AND ( expiration_date <= '".$this->page_data['sch_e_date']."' ) ";
        }

    
        if($this->page_data['sch_task_type']) {
            $query_where .= " AND ( task_type = '".$this->page_data['sch_task_type']."' ) ";
        }

        if($this->page_data['sch_food_code']) {
            $query_where .= " AND ( food_code = '".$this->page_data['sch_food_code']."' ) ";
        }

        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( product_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( production_idx LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( memo LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                   
                            ) ";
        }

        $this->page_data['task_type_arr'] = [
            'I' => '입고'            
            ,'U' => '사용'
            ,'D' => '폐기'            
        ];
        
        $this->page_data['food_types'] = $this->getConfig()['food_types'];

        

        # 생산 제품별 재고 수량을 가져온다.
        $query_result = $this->model_product->getProdcuctStockState();    
        
        if( $query_result['num_rows'] > 0 ){
            $this->page_data['products'] = $query_result['rows'];
        }
        
        # 리스트 정보요청
        $list_result = $this->model_product->getProductStocks([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging;         
        $this->page_data['total_quantity'] = $list_result['total_quantity'];             
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_list.php';
        $this->page_data['list'] = $list_result['rows'];        
        $this->view( $this->page_data );
        
    }


    /**
     * 고객기업 정보 데이터를 처리한다.
     */
    public function stock_proc(){

        # post 접근 체크
        postCheck();

        // echoBr( $this->page_data );

        switch( $this->page_data['mode'] ) {

            case 'ins' : {
              

                break;
            }
            case 'edit' : {

                break;
            }
            case 'del' : {

                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
                # 필수값 체크
                #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=                
                $this->issetParams( $this->page_data, [
                    'stock_idx'                    
                ]);
                
                # 트랜잭션 시작
                $this->model->runTransaction();

                # 기업 정보 수정
                $query_result = $this->model_product->updateStock([
                    'del_flag' => 'Y'
                    ,'del_idx' => getAccountInfo()['idx']
                    ,'del_date' => 'NOW()'
                    ,'del_ip' => $this->getIP()
                ] ," stock_idx = '" . $this->page_data['stock_idx']. "'" );

                # 트랜잭션 종료
                $this->model->stopTransaction();

                movePage('replace', '삭제되었습니다.', './'. $this->page_data['page_name'] .'_list?page=1' . htmlspecialchars_decode( $this->page_data['ref_params'] ) );

                break;
            }
            default : {
                errorBack('잘못된 접근입니다.');
            }
        }
    }

    

    /**
     * 출하관리 목록을 구성한다.
    */
    public function release_list(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'release';
        $this->page_data['process_state_arr'] = [
            'O' => '수주'
            ,'D' => '출하'
            ,'C' => '취소'
        ];

        $query_where = " AND ( del_flag='N' ) AND ( process_state = 'D' ) AND ( company_idx = '". COMPANY_CODE ."' ) ";
        
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_order_field'] ){
            $query_sort = ' ORDER BY '. $this->page_data['sch_order_field'] .' '.$this->page_data['sch_order_status'].' ';
        } else {            
            $query_sort = ' ORDER BY order_idx DESC, order_date DESC, delivery_date DESC ';
        }


        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( company_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( order_idx LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( product_name LIKE '%". $this->page_data['sch_keyword'] ."%' )                                     
                                    OR ( manager_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( product_stock_used_expiration_dates LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                            ) ";
        }
        
        if($this->page_data['sch_process_state']) {
            $query_where .= " AND ( process_state = '".$this->page_data['sch_process_state']."' ) ";
        }

        if($this->page_data['sch_s_date']) {
            $query_where .= " AND ( order_date >= '".$this->page_data['sch_s_date']."' ) ";
        }

		if($this->page_data['sch_e_date']) {
            $query_where .= " AND ( order_date <= '".$this->page_data['sch_e_date']."' ) ";
        }

        if($this->page_data['sch_schedule_s_date']) {
            $query_where .= " AND ( delivery_date >= '".$this->page_data['sch_schedule_s_date']."' ) ";
        }

		if($this->page_data['sch_schedule_e_date']) {
            $query_where .= " AND ( delivery_date <= '".$this->page_data['sch_schedule_e_date']."' ) ";
        }



        # 리스트 정보요청
        $list_result = $this->model->getReceiveOrders([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging; 
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_list.php';
        $this->page_data['list'] = $list_result['rows'];        
        $this->view( $this->page_data );
    }

    /**
     * 출하관리 출하처리 화면을 구성한다.
    */
    public function release_view(){

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $page_name = 'release';            
        $this->issetParams( $this->page_data, ['order_idx']);
        
        $this->page_data['page_work'] = '수정';

        # 수주정보를 요청한다.
        $query_result = $this->model->getReceiveOrder( " order_idx = '". $this->page_data['order_idx'] ."' " );

        if( $query_result['num_rows'] == 0 ){
            
            errorBack('해당 게시물이 삭제되었거나 정상적인 접근 방법이 아닙니다.');
            
        } else {

            $this->page_data = array_merge( $this->page_data, $query_result['row'] );

            $quantity = (int)$query_result['row']['quantity'];

            // $quantity = '161';
            # 주문건에 해당하는 제품의 유통기한 별 재고 요청
            
            $this->page_data['get_shipment_stocks'] = $this->model_product->getProductStock(" AND stock_idx IN (". $this->page_data['product_stock_used_info'] .") ")['rows'];  
            

            $doc_result = $this->model_doc->getApprovalDocument( " 
                AND ( doc_usage_idx = '31' ) 
                AND ( task_type = 't_client_receive_order' ) 
                AND ( task_table_idx = '". $this->page_data['order_idx'] ."' ) 
            " );

            if( $doc_result['num_rows'] > 0 ) {
                $this->page_data['doc_approval_idx'] = $doc_result['row']['doc_approval_idx'];
                $this->page_data['doc_approval_state'] = $doc_result['row']['approval_state'];
            }


        }
        
        # 업체의 배송지 주소를 요청한다.
        $query_result = $this->model->getClientComapnyAddr(" client_idx = '". $this->page_data['client_idx'] ."' AND del_flag='N' " );    
        
        if( $query_result['num_rows'] > 0 ){
            $this->page_data['company_addrs'] = $query_result['rows'];
        } else {
            $this->page_data['company_addrs'] = [];
        }

        // echoBr( $this->page_data['company_addrs'] ); exit;
        $this->page_data['mode'] = 'edit';
        
        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = true;
        $this->page_data['use_footer'] = true;        
        $this->page_data['page_name'] = $page_name;
        $this->page_data['contents_path'] = '/client/'. $page_name .'_view.php';
        $this->page_data['list'] = $list_result['rows'];
        
        $this->view( $this->page_data );

    }

    public function shipment_info_json(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=     
        if( empty( $this->page_data['order_idx'] ) ) {
			$result['status'] = 'fail';
			$result['msg'] = '필수값 누락 : order_idx ';
			jsonExit( $result );				
        }

        # 재고 정보
        $query_result = $this->model->getReceiveOrder(" order_idx = '". $this->page_data['order_idx'] ."' " );

        if( $query_result['num_rows'] > 0 ) {

            $result['status'] = 'success';
            $result['msg'] = '';
            $result['order_info'] = $query_result['row'];
            jsonExit( $result );

        } else {
            $result['status'] = 'fail';
            $result['msg'] = '정보가 존재 하지 않습니다.';
            jsonExit( $result );
        }
    }

}

?>