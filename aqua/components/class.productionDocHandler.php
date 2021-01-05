<?php
/**
 * ---------------------------------------------------
 * AQUA Framework 생산일보 문서 처리자 v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.0.0]
 * - 생산일보 생성
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.05.26 - 이정훈
 *  - createDoc() 개발
 * 
 * ---------------------------------------------------
*/

class productionDocHandler extends aqua {

    public $production_model;

    private $doc_model;    
    private $model_product;    
    private $model_material;    
    private $doc_info_arr = [];
    private $doc_title = '';
    private $production_date = '';
    private $product_name = '';
    private $member_name = '';
    private $reg_idx = '';
    private $writer_qrcode = '';
    private $get_production_data = '';
    private $get_materials_data = '';
    private $get_products_data = '';
    private $unit_info_result = '';
    private $product_unit_info = [];
    private $file_manager = '';
    private $qrcode = '';
    private $tr_count = 0;
    private $th_style = 'width:100px; height:30px; border:1px solid; font-size:11px; text-align:center; cursor:pointer;background:#ddd';
    private $td_style = 'width:100px; height:30px; border:1px solid; font-size:11px; text-align:center; cursor:pointer';
    private $td_none_style = 'width:100px; height:30px; border:1px solid; font-size:11px; text-align:center; cursor:pointer';
    
    function __construct() {
		
        $this->doc_model = $this->new('docModel');
        $this->production_model = $this->new('productionModel');         
        $this->model_product = $this->new('productModel');         
        $this->model_material = $this->new('materialsModel');
        $this->qrcode = $this->new('QRcodeHandler');        
        $this->file_manager = $this->new('fileUploadHandler');   

    }

    /**
     * 문서 생성
     */
    public function createDoc( $arg_production_idx ){
        
        // $doc_result = $this->doc_model->getDocumentForm(" AND doc_usage_idx='26' ");
        // $decode_doc_table_style_data = htmlspecialchars_decode( $doc_result['row']['doc_table_style_data']);
        // $decode_doc_data = htmlspecialchars_decode( $doc_result['row']['doc_data']);
        // $doc_frame = json_decode( htmlspecialchars_decode( $decode_doc_table_style_data ), true );
        // $doc_data = json_decode( htmlspecialchars_decode( $decode_doc_data ), true );

        # 생산 완료정보 호출
        $this->get_production_data = $this->production_model->getProductionDone( " AND production_idx = '". $arg_production_idx ."' " );
        
        if( $this->get_production_data['num_rows'] > 0 ){

            $this->production_date = $this->get_production_data['row']['production_date'];
            $this->product_name = $this->get_production_data['row']['product_name'];
            $this->member_name = $this->get_production_data['row']['member_name'];
            $this->reg_idx = $this->get_production_data['row']['reg_idx'];
            // $this->doc_title = '생산일보 (' . $this->product_name . ') ';
            $this->doc_title = '생산일보';


            $this->get_materials_data = $this->production_model->getProductionDoneMaterials( " AND material_kind='raw' AND production_idx = '". $arg_production_idx ."' AND ( del_flag='N' ) " );
            $this->get_products_data = $this->production_model->getProductionDoneProducts( " AND production_idx = '". $arg_production_idx ."' AND ( del_flag='N' ) " );
            // $this->unit_info_result = $this->model_product->getProductUnitInfo( " del_flag='N' AND product_idx = '". $this->get_production_data['row']['product_idx'] ."' " );

            $this->unit_info_result = $this->model_product->getProductUnitInfo( " del_flag='N' AND ( use_flag='Y' ) AND company_idx = '". COMPANY_CODE ."' " );


            // if( count( $this->unit_info_result['row'] ) > 0  ) {
                
            //     $this->product_unit_info = [];

            //     foreach( $this->unit_info_result['rows'] AS $key=>$item){                    
            //         $this->product_unit_info[ $item['product_idx'] ][ $item['product_unit_idx'] ]['product_unit'] = $item['product_unit'];
            //         $this->product_unit_info[ $item['product_idx'] ][ $item['product_unit_idx'] ]['packaging_unit_quantity'] = $item['packaging_unit_quantity'];
            //     }

            // } 

            foreach( $this->unit_info_result['rows'] AS $idx=>$item ) {
                $this->product_unit_info[ $item['product_unit_idx'] ]['product_unit'] = $item['product_unit'];
                $this->product_unit_info[ $item['product_unit_idx'] ]['packaging_unit_quantity'] = $item['packaging_unit_quantity'];
            }

            // echoBr( $this->product_unit_info ); exit;

            $this->makeDocHeader();
            $this->makeMaterialArea();
            $this->makeDocProductArea();
            $this->saveApprovalDoc( $arg_production_idx );
            
            $result['frame'] = json_encode( $this->makeDocFrame() ); 
            $result['data'] = json_encode( $this->doc_info_arr ); 

        } else {
            $this->errorHandler( 'productionDocHandler->createDoc()', '생산정보가 존재하지않습니다.' ); 
        }

        

        return $result;
    }
    /**
     * 
     */
    private function saveApprovalDoc( $arg_production_idx ){
        # htmlspecialchars 은 안써도 되지만 js 에서 생성되어 저장되는 데이터와 통일성을 갖기 위해 사용한다.
        // echoBr( htmlspecialchars( json_encode( $this->doc_info_arr ), ENT_QUOTES, 'ISO-8859-1', true ) );
        // echoBr( json_encode( $this->doc_info_arr ) );
        // echoBr( $this->doc_info_arr );
        // exit;
        # 트랜잭션 시작
        $this->doc_model->runTransaction();
        
        # 승인문서 등록
        $query_result = $this->doc_model->insertApprovalDoc([
            'company_idx' => COMPANY_CODE
            ,'doc_usage_idx' => '26'
            ,'task_type' => 't_production_done'
            ,'task_table_idx' => $arg_production_idx
            ,'item_code' => 'SF021'
            ,'doc_title' => $this->doc_title
            ,'doc_table_style_data' => htmlspecialchars( json_encode(  $this->makeDocFrame(),JSON_UNESCAPED_UNICODE ) )
            ,'doc_data' =>  htmlspecialchars( json_encode( $this->doc_info_arr,JSON_UNESCAPED_UNICODE ) )
            ,'writer_idx' => $this->reg_idx       
            ,'reg_date' => 'NOW()'
            ,'reg_ip' => $this->getIP()
        ]);

        
        
        $new_doc_approval_idx = $query_result['return_data']['insert_id'];

        # QRcode 데이터베이스에 적재
        $this->qrcode->createQRcode([
            'purpose' => 'reporter'
            ,'qrcode_val' => SITE_DOMAIN . '/doc/qr_result?key=' . $new_doc_approval_idx
            ,'file_name' => $new_doc_approval_idx.'_reporter_'.$this->reg_idx
            ,'tb_name' => 't_document_approval'             
            ,'tb_key' => $new_doc_approval_idx
        ]);
        
        $qrcode_result = $this->qrcode->getQRcode([
            'purpose' => 'reporter'
            ,'tb_name' => 't_document_approval'                
            ,'tb_key' => $new_doc_approval_idx
        ]);


        if( $qrcode_result['num_rows'] > 0 ) {

            $this->doc_info_arr = [];
            $this->writer_qrcode = $qrcode_result['row']['path'] . '/' . $qrcode_result['row']['server_name'];

            $this->makeDocHeader();
            $this->makeMaterialArea();
            $this->makeDocProductArea();

            $query_result = $this->doc_model->updateApprovalDoc([
                'doc_data' => htmlspecialchars( json_encode( $this->doc_info_arr,JSON_UNESCAPED_UNICODE ) )
            ] ," doc_approval_idx = '" . $new_doc_approval_idx . "'" );
            
        } else {
            $this->errorHandler( 'productionDocHandler->saveApprovalDoc()', 'QRcode 정보를 불러 올 수 없습니다.' ); 
        }

        # 트랜잭션 종료
        $this->doc_model->stopTransaction();
        
    }

    private function makeDocFrame(){       

        $doc_frame['tag'] = 'table';
        $doc_frame['attr']['id'] = 'doc_table';
        $doc_frame['attr']['style'] = 'border-collapse: collapse; border-spacing:0; width:800px';

        return $doc_frame;

    }

    /**
    * 문서 원자재 사용정보 입력
    */
    private function makeMaterialArea(){
        
        if( $this->get_materials_data['num_rows'] == 0 ){
            $this->errorHandler( 'productionDocHandler->createDoc()', '원자재 정보가 존재하지않습니다.' ); 
        }

        $tr_info = 'doc_tr';
        $td_info = 'doc_td';
        $start_tr = 5;
        $td_cnt = 8;
        $quantity_sum = 0;

        foreach( $this->get_materials_data['rows'] AS $idx=>$item ) {
            
            // echoBr($item);
            
            $make_tr = $tr_info . '_' .$start_tr;

            // echoBr( $make_tr );
            $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
            $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

            for( $td_loop = 0; $td_loop < $td_cnt; $td_loop++ ){
                
                switch( $td_loop ){
                    case '0' : {
                        $td_value = $item['material_name'];
                        break;
                    }
                    case '3' : {
                        $td_value = $item['receipt_date'];
                        break;
                    }
                    case '6' : {
                        $td_value = $item['quantity'];

                        $quantity_sum += $td_value;

                        break;
                    }
                }
                

                $make_td = $td_info . '_' .$start_tr . '_' . $td_loop;

                if( ( $td_loop == 0 ) || ( $td_loop == 3 ) || ( $td_loop == 6 ) ) {

                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '3';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_4_'. ( $td_loop +1 ).',doc_td_4_'. ( $td_loop +2 );
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'input';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['type'] = 'text';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_input';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_inputs';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:95%';
                    $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['value'] = $td_value;

                } else {

                    $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
                    $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
                    $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
                    $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';
                    
                }

                
                
                
            }

            $start_tr++;
        }

        $make_tr = $tr_info . '_' .$start_tr;
        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '6';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. $start_tr .'_2,doc_td_'. $start_tr .'_3,doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '합계(Kg)';

        for( $loop_cnt = 1; $td_loop < 6; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }

        $make_td = $td_info . '_' . $start_tr . '_6';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '2';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_7';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['type'] = 'text';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_inputs';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:95%';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['value'] = $quantity_sum;

        $make_td = $td_info . '_' . $start_tr . '_7';
        $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        
        $this->tr_count = (++$start_tr);

    }

    /**
     * 제품 포장 작업 영역 생성
     */
    private function makeDocProductArea(){
        
        $tr_info = 'doc_tr';
        $td_info = 'doc_td';

        $start_tr = $this->tr_count;


        $make_tr = $tr_info . '_' .$start_tr;

        // echoBr( $make_tr );
        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '8';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. $start_tr .'_2,doc_td_'. $start_tr .'_3,doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5,doc_td_'. $start_tr .'_6,doc_td_'. $start_tr .'_7';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '포장작업내용';

        for( $loop_cnt = 1; $loop_cnt < 8; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }

        $start_tr += 1;

        $make_tr = $tr_info . '_' .$start_tr;

        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '2';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. ( $start_tr + 1) .'_0';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '생산일';

        $make_td = $td_info . '_' . $start_tr . '_1';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '2';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. ( $start_tr + 1) .'_1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '품목';
        
        $make_td = $td_info . '_' . $start_tr . '_2';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '2';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. ( $start_tr + 1) .'_2';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '작업내용';

        $make_td = $td_info . '_' . $start_tr . '_3';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '5';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5,doc_td_'. $start_tr .'_6,doc_td_'. $start_tr .'_7';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '포장현황';


        for( $loop_cnt = 4; $loop_cnt < 8; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }

        $start_tr += 1;

        $make_tr = $tr_info . '_' .$start_tr;

        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        for( $loop_cnt = 0; $loop_cnt < 3; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }

        $make_td = $td_info . '_' . $start_tr . '_3';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '규격(kg)';

        $make_td = $td_info . '_' . $start_tr . '_4';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '유통기한';

        $make_td = $td_info . '_' . $start_tr . '_5';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '작업시간';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][1]['tag'] = 'br';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][2]['text'] = '(종일/오전/오후)';

        $make_td = $td_info . '_' . $start_tr . '_6';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '봉(파우치)';

        $make_td = $td_info . '_' . $start_tr . '_7';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '박스';

        if( $this->get_products_data['num_rows'] == 0 ){
            $this->errorHandler( 'productionDocHandler->makeDocProductArea()', '제품 생산 정보가 존재하지않습니다.' ); 
        }


        $start_tr += 1;
        $unit_quantity_sum = 0;
        $box_quantity_sum = 0;

        foreach( $this->get_products_data['rows'] AS $idx=>$item ) {
            
            // echoBr($item);
            
            $make_tr = $tr_info . '_' .$start_tr;

            // echoBr( $make_tr );
            $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
            $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

            
            for( $td_loop = 0; $td_loop < 8; $td_loop++ ){
                
                $make_td = $td_info . '_' .$start_tr . '_' . $td_loop;

                switch( $td_loop ){
                    case '0' : {
                        $td_value = $item['production_date'];
                        break;
                    }
                    case '1' : {                        
                        // $td_value = $this->product_name;
                        $td_value = $item['product_name'];
                        break;
                    }
                    case '2' : {
                        $td_value = '파우치 포장';
                        break;
                    }

                    case '3' : {                        
                        $td_value = ( (double)$this->product_unit_info[ $item['product_unit_idx'] ]['product_unit'] * (double)$this->product_unit_info[ $item['product_unit_idx'] ]['packaging_unit_quantity'] ) * $item['box_quantity'];
                        break;
                    }

                    case '4' : {
                        $td_value = $item['expiration_date'];

                        break;
                    }
                    case '5' : {
                        if( $item['timing'] == 'all' ) {
                            $td_value = '종일';
                        } else if( $item['timing'] == 'am' ) {
                            $td_value = '오전';
                        } else {
                            $td_value = '오후';
                        }
                        break;
                    }
                    case '6' : {                        
                        $td_value = $item['unit_quantity'];
                        $unit_quantity_sum += $td_value;                        
                        break;
                    }
                    case '7' : {
                        $td_value = $item['box_quantity'];
                        $box_quantity_sum += $td_value;
                        break;
                    }
                }

                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;                
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'input';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['type'] = 'text';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_input';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_inputs';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:95%';
                $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['value'] = $td_value;

            }
            

            $start_tr++;

        }


        $make_tr = $tr_info . '_' .$start_tr;
        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '6';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. $start_tr .'_2,doc_td_'. $start_tr .'_3,doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '합계';

        for( $loop_cnt = 1; $td_loop < 6; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }

        $make_td = $td_info . '_' . $start_tr . '_6';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['type'] = 'text';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_inputs';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:95%';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['value'] = $unit_quantity_sum;

        $make_td = $td_info . '_' . $start_tr . '_7';
        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['type'] = 'text';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_input';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_inputs';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:95%';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['value'] = $box_quantity_sum;

        
        $start_tr += 1;

        $make_tr = $tr_info . '_' .$start_tr;
        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'th';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->th_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '8';        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. $start_tr .'_2,doc_td_'. $start_tr .'_3,doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5,doc_td_'. $start_tr .'_6,doc_td_'. $start_tr .'_7';        
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['text'] = '메모';

        for( $loop_cnt = 1; $loop_cnt < 8; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }


        
        $start_tr += 1;

        $make_tr = $tr_info . '_' .$start_tr;
        $this->doc_info_arr[ $make_tr ]['tag'] = 'tr';
        $this->doc_info_arr[ $make_tr ]['attr']['id'] = $make_tr;

        $make_td = $td_info . '_' . $start_tr . '_0';

        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['tag'] = 'td';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['id'] = $make_td;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['class'] = 'doc_tds';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['style'] = $this->td_style;
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['rowspan'] = '1';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['colspan'] = '8';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['merge_data'] = 'doc_td_'. $start_tr .'_1,doc_td_'. $start_tr .'_2,doc_td_'. $start_tr .'_3,doc_td_'. $start_tr .'_4,doc_td_'. $start_tr .'_5,doc_td_'. $start_tr .'_6,doc_td_'. $start_tr .'_7';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['attr']['height'] = '100px';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['tag'] = 'textarea';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['id'] = $make_td.'_textarea';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['class'] = '__doc_td_textareas';
        $this->doc_info_arr[ $make_tr ]['child'][$make_td]['child'][0]['attr']['style'] = 'width:90%;height:90%';

        for( $loop_cnt = 1; $loop_cnt < 8; $loop_cnt++ ){

            $make_td = $td_info . '_' . $start_tr . '_' . $loop_cnt;

            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['tag'] = 'td';
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['id'] = $make_td;
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['class'] = 'doc_tds';        
            $this->doc_info_arr[ $make_tr ]['child'][ $make_td ]['attr']['display'] = 'none';

        }


    }

    /**
    * 문서 상단 영역 생성   
    */
    private function makeDocHeader(){

        $this->doc_info_arr['doc_tr_0']['tag'] = 'tr';
        $this->doc_info_arr['doc_tr_0']['attr']['id'] = 'doc_tr_0';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['id']= 'doc_td_0_0';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['class']= 'doc_tds';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['style']= $this->th_style;
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['rowspan']= '2';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['colspan']= '5';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['attr']['merge_data']= 'doc_td_0_1,doc_td_0_2,doc_td_0_3,doc_td_0_4,doc_td_1_0,doc_td_1_1,doc_td_1_2,doc_td_1_3,doc_td_1_4';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_0']['child'][]['text'] = $this->doc_title;

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_1']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_1']['attr']['id'] = 'doc_td_0_1';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_1']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_1']['attr']['style'] = '';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_1']['attr']['display'] = 'none';
        
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_2']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_2']['attr']['id'] = 'doc_td_0_2';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_2']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_2']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_3']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_3']['attr']['id'] = 'doc_td_0_3';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_3']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_3']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_4']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_4']['attr']['id'] = 'doc_td_0_4';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_4']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_4']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['id'] = 'doc_td_0_5';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['rowspan'] = '2';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['colspan'] = '1';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['attr']['merge_data'] = 'th_style';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_5']['child'][]['text'] = '결재';

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_6']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_6']['attr']['id'] = 'doc_td_0_6';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_6']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_6']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_6']['child'][]['text'] = '작성자';

        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_7']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_7']['attr']['id'] = 'doc_td_0_7';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_7']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_7']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_0']['child']['doc_td_0_7']['child'][]['text'] = '승인자';

        $this->doc_info_arr['doc_tr_1']['tag'] = 'tr';
        $this->doc_info_arr['doc_tr_1']['attr']['id'] = 'doc_tr_1';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_0']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_0']['attr']['id'] = 'doc_td_1_0';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_0']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_0']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_1']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_1']['attr']['id'] = 'doc_td_1_1';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_1']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_1']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_2']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_2']['attr']['id'] = 'doc_td_1_2';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_2']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_2']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_3']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_3']['attr']['id'] = 'doc_td_1_3';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_3']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_3']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_4']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_4']['attr']['id'] = 'doc_td_1_4';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_4']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_4']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_5']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_5']['attr']['id'] = 'doc_td_1_5';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_5']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_5']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['attr']['id'] = 'doc_td_1_6';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['attr']['style'] = $this->td_style;
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['attr']['height'] = '80px';

        if( empty( $this->writer_qrcode ) == true ) {
            $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['child'][0]['text'] = '__reporter_qr__';
        } else {
            $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['child'][0]['tag'] = 'img';
            $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['child'][0]['attr']['src'] = $this->writer_qrcode;
            $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_6']['child'][1]['text'] = $this->member_name;            
        }
        

        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_7']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_7']['attr']['id'] = 'doc_td_1_7';
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_7']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_7']['attr']['style'] = $this->td_style;        
        $this->doc_info_arr['doc_tr_1']['child']['doc_td_1_7']['child'][0]['text'] = '__approval_qr__';

        $this->doc_info_arr['doc_tr_2']['tag'] = 'tr';
        $this->doc_info_arr['doc_tr_2']['attr']['id'] = 'doc_tr_2';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_0']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_0']['attr']['id'] = 'doc_td_2_0';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_0']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_0']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_0']['child'][]['text'] = '생산일';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['id'] = 'doc_td_2_1';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['style'] = $this->td_style;
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['colspan'] = '3';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['attr']['merge_data']= 'doc_td_2_2,doc_td_2_3';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['tag'] = 'input';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['attr']['type'] = 'text';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['attr']['id'] = 'doc_td_2_1_input';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['attr']['class'] = '__doc_td_inputs';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['attr']['style'] = 'width:95%';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_1']['child'][0]['attr']['value'] = $this->production_date;

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_2']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_2']['attr']['id'] = 'doc_td_2_2';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_2']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_2']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_3']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_3']['attr']['id'] = 'doc_td_2_3';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_3']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_3']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_4']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_4']['attr']['id'] = 'doc_td_2_4';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_4']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_4']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_4']['child'][]['text'] = '작성자';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['id'] = 'doc_td_2_5';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['class'] = 'doc_tds';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['style'] = $this->td_style;
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['colspan'] = '3';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['attr']['merge_data']= 'doc_td_2_6,doc_td_2_7';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['tag'] = 'input';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['attr']['type'] = 'text';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['attr']['id'] = 'doc_td_2_5_input';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['attr']['class'] = '__doc_td_inputs';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['attr']['style'] = 'width:95%';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_5']['child'][0]['attr']['value'] = $this->member_name;

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_6']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_6']['attr']['id'] = 'doc_td_2_6';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_6']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_6']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_7']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_7']['attr']['id'] = 'doc_td_2_7';
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_7']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_2']['child']['doc_td_2_7']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['tag'] = 'tr';
        $this->doc_info_arr['doc_tr_3']['attr']['id'] = 'doc_tr_3';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['id'] = 'doc_td_3_0';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['colspan'] = '8';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['merge_data'] = 'doc_td_3_1,doc_td_3_2,doc_td_3_3,doc_td_3_4,doc_td_3_5,doc_td_3_6,doc_td_3_7';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['attr']['height'] = '50px';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_0']['child'][]['text'] = '원재료 작업내용(kg)';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_1']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_1']['attr']['id'] = 'doc_td_3_1';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_1']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_1']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_2']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_2']['attr']['id'] = 'doc_td_3_2';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_2']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_2']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_3']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_3']['attr']['id'] = 'doc_td_3_3';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_3']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_3']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_4']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_4']['attr']['id'] = 'doc_td_3_4';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_4']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_4']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_5']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_5']['attr']['id'] = 'doc_td_3_5';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_5']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_5']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_6']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_6']['attr']['id'] = 'doc_td_3_5';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_6']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_6']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_7']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_7']['attr']['id'] = 'doc_td_3_7';
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_7']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_3']['child']['doc_td_3_7']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_4']['tag'] = 'tr';
        $this->doc_info_arr['doc_tr_4']['attr']['id'] = 'doc_tr_4';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['id'] = 'doc_td_4_0';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['colspan'] = '3';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['attr']['merge_data'] = 'doc_td_4_1,doc_td_4_2';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_0']['child'][]['text'] = '원료명';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_1']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_1']['attr']['id'] = 'doc_td_4_1';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_1']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_1']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_2']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_2']['attr']['id'] = 'doc_td_4_2';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_2']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_2']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['id'] = 'doc_td_4_3';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['colspan'] = '3';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['attr']['merge_data'] = 'doc_td_4_4,doc_td_4_5';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_3']['child'][]['text'] = '입고일';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_4']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_4']['attr']['id'] = 'doc_td_4_4';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_4']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_4']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_5']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_5']['attr']['id'] = 'doc_td_4_5';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_5']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_5']['attr']['display'] = 'none';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['tag'] = 'th';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['id'] = 'doc_td_4_6';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['style'] = $this->th_style;
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['rowspan'] = '1';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['colspan'] = '2';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['attr']['merge_data'] = 'doc_td_4_7';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_6']['child'][]['text'] = '사용량';

        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_7']['tag'] = 'td';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_7']['attr']['id'] = 'doc_td_4_7';
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_7']['attr']['class'] = 'doc_tds';        
        $this->doc_info_arr['doc_tr_4']['child']['doc_td_4_7']['attr']['display'] = 'none';

    }

}
?>