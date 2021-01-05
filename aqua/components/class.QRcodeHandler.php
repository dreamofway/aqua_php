<?php
/**
 * ---------------------------------------------------
 * AQUA Framework QRcodeHandler   v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * phpqrcode.php( v1.1.4 ) 라이브러리 사용
 * 
 * [v1.0.0]
 * - QR code 생성 하고 fileUploadHandler를 사용하여 테이블에 적재 지원
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.03.03 - 이정훈
 *  - fileUpload() 개발
 * 
 * ---------------------------------------------------
*/
class QRcodeHandler extends aqua {

    private $file_manager = '';    
    private $server_root = '';
    private $upload_default_path = '';
    

    function __construct() {

        $this->server_root = $_SERVER['DOCUMENT_ROOT'];
        $this->file_manager = $this->new('fileUploadHandler'); 
        
    }

    public function createQRcode( $arg_data ) {
        
        if( defined('QR_CODE_PATH') ) {
            $this->upload_default_path = QR_CODE_PATH;
        } else {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', '상수 QR_CODE_PATH 값이 필요합니다.' ); 
        }
        
        if( empty( $arg_data['purpose'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'purpose 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['qrcode_val'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'qrcode_val 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['file_name'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'file_name 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['tb_name'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_name 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['tb_key'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_key 값이 필요합니다.' ); 
        }

                
        $file_name = $arg_data['file_name'] .'.png'; # 파일명
        $upload_path = $this->server_root . $upload_default_path . '/' . $arg_data['purpose']; # 물리경로
        $db_insert_path = $this->upload_default_path . '/' . $arg_data['purpose']; # 테이블에 삽입될 경로 정보
        $upload_file_path = $this->server_root . $this->upload_default_path . '/' . $arg_data['purpose'] . '/' . $file_name; # QRcode 생성에 사용될 경로

        #폴더 생성
        $this->makeDir( $db_insert_path );
        
        # QRcode 생성
        QRcode::png( $arg_data['qrcode_val'], $upload_file_path );

        # 현재 tb_name 과 tb_key 가 동일한 데이터가 있는지 조회한다.
        $get_file_result = $this->file_manager->dbGetFile("
            tb_key = '". $arg_data['tb_key'] ."'
            AND where_used = '". 'qrcode_' . $arg_data['purpose'] ."'
            AND tb_name = '". $arg_data['tb_name'] ."' 
			AND del_flag = 'N'
        ");

        # 동일한 정보가 있으면 삭제처리 한다.
        if( $get_file_result['num_rows'] > 0 ) {
            foreach($get_file_result['rows'] AS $key=>$item) {
                $get_file_result = $this->file_manager->dbDeleteHandler(" idx = '". $item['idx'] ."' ");
            }
        }

        $this->file_manager->dbInsert([
            'tb_key' => $arg_data['tb_key']
            ,'where_used' => 'qrcode_' . $arg_data['purpose']
            ,'tb_name' => trim( $arg_data['tb_name'] )
            ,'origin_name' => $file_name
            ,'server_name' => $file_name
            ,'path' => $db_insert_path
            ,'company_idx' => COMPANY_CODE
            , 'reg_idx' => getAccountInfo()['idx']
            , 'reg_ip' => $this->getIP()
            , 'reg_date' => 'NOW()'
        ]);

    }

    /**
     * QR code 정보를 요청한다.
     */
    public function getQRcode( $arg_data ){

        if( empty( $arg_data['purpose'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'purpose 값이 필요합니다.' ); 
        }
        if( empty( $arg_data['tb_name'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_name 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['tb_key'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_key 값이 필요합니다.' ); 
        }

        $get_file_result = $this->file_manager->dbGetFile("
            tb_key = '". $arg_data['tb_key'] ."'
            AND where_used = '". 'qrcode_' . $arg_data['purpose'] ."'
            AND tb_name = '". $arg_data['tb_name'] ."'     
			AND del_flag = 'N'
        ");

        return $get_file_result;

    }


    /**
     * 기존 QR code 정보를 삭제하고 신규 데이터를 삽입한다.
     */
    public function renewQRcode( $arg_data ){

        if( empty( $arg_data['purpose'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'purpose 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['qrcode_val'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'qrcode_val 값이 필요합니다.' ); 
        }
        
        if( empty( $arg_data['file_name'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'file_name 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['tb_name'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_name 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['tb_key'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'tb_key 값이 필요합니다.' ); 
        }

        if( empty( $arg_data['fild_key'] ) == true ) {
            $this->errorHandler( 'QRcodeHandler->createQRcode()', 'fild_key 값이 필요합니다.' ); 
        }
        
        # 삭제처리
        $get_file_result = $this->file_manager->dbDeleteHandler(" idx = '". $arg_data['fild_key'] ."' ");

        # 신규 생성
        $this->createQRcode( $arg_data );

    }

    /**
     * 업로드 경로의 디렉터리 확인 및 생성
     */
    private function makeDir( $get_path ) {

        # 지정된 경로 존재 확인
        $path_array = explode( "/", $get_path );
		$check_path = $this->server_root;
        
        foreach( $path_array AS $idx=>$val ) {
            
            if( $val !== '' ) {
                
                $check_path .= "/" . $val;

                if( is_dir( $check_path ) == false ) {	
                    # 폴더가 없으면 생성.                        
                    $mk_result = mkdir($check_path, 0777);
                        
                    if($mk_result){
                        #권한 변경.
                        chmod($check_path, 0777);
                    } else {                            
                        $this->errorHandler( 'QRcodeHandler->makeDir()', '[폴더 생성 실패] - 경로 : ' . $check_path ); 
                    }
                } 
            }
        }
        
    }

    function __destruct() {

    }

}
?>