<?php
/**
 * ---------------------------------------------------
 * AQUA Framework fileUploadHandler  v1.2.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.2.0]
 * - 업로드 폴더 생성 여부 설정 지원
 * - 다중/단일 파일 업로드 지원
 * - 다중/단일 파일 삭제 지원
 * - 중복 파일명 변경 지원
 * - 사용자 지정 서버 파일명 변경 지원
 * - 파일 관리 테이블 삽입/삭제 지원
 * - 테이블 파일 복사 지원
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.2.0] 2020.03.29 - 이정훈
 *  - fileCopy() 개발
 * 
 * [v1.1.0] 2020.03.26 - 이정훈
 *  - file_title 입력 추가
 * 
 * [v1.0.0] 2020.03.03 - 이정훈
 *  - fileUpload() 개발
 *  - uploadValid() 개발
 *  - addFiles() 개발
 *  - getServerFileName() 개발
 *  - uploadProc() 개발
 *  - deleteProc() 개발
 *  - dirExist() 개발
 *  - fileRenameing() 개발
 *  - dbHandler() 개발
 *  - dbDeleteHandler() 개발
 *  - dbInsert() 개발
 * 
 * ---------------------------------------------------
*/
class fileUploadHandler extends aqua {

    public $server_root = ''; # 서버 root 물리경로
    public $path = ''; # 사용자 입력 파일 업로드 경로
    public $uplaod_end_path = ''; # 최종 파일 업로드 경로 ( 폴더 자동 생성인 경우 생성된 경로를 반환 함)
    public $make_dir = true; # 파일 업로드 경로
    public $limit_quantity = 50; # 파일 제한 용량 설정
    public $file_element = ''; # 파일 객체명
    public $set_file_name = ''; # 변경할 파일명
    public $set_file_title = ''; # 파일 명칭을 입력하여 구분 할 수 있게 함 ( 파일 컨트롤과는 무관 )
    public $table_data = []; # 테이블 query 제작에 사용될 값/ insert => ('column' => value) / delete=>('column' => value) 형식
    public $use_db_status = false; # 데이터 베이스 사용여부
    public $use_db = ''; # 데이터 베이스 키값
    public $use_table = ''; # 사용 테이블
    public $db = ''; # DB 연결
    private $max_file_size = ''; # 파일 제한 용량
    private $file_config = ''; # 파일관련 사용자 설정 정보
    private $error_arr = []; # 에러 적재 배열
    private $upload_files_arr = []; # 업로드 대상 적재 배열
    private $uploaded_files_arr = []; # 업로드된 파일정보 배열
    private $uplaod_allow_ext = '/(jpg|jpeg|png|gif|zip|hwp|xlsx|xls|txt|pdf|pptx|doc)/i'; # 업로드 허용파일

    function __construct() {

        # 서버 루트값 대입
        $this->server_root = $_SERVER['DOCUMENT_ROOT'];

        # 파일 관련 사용자 설정 대입
        $this->file_config = $this->getConfig()['file'];

        # 디렉터리 생성 여부 설정
        $this->make_dir = $this->isExistPut( $this->make_dir, $this->file_config['make_dir'] );

        # 데이터 베이스 사용여부 설정
        $this->use_db_status = $this->isExistPut( $this->use_db_status, $this->file_config['use_db_status'] );

        # 사용 DB key 설정
        $this->use_db = $this->isExistPut( $this->use_db, $this->file_config['use_db'] );

        # 사용 테이블 설정
        $this->use_table = $this->isExistPut( $this->use_table, $this->file_config['use_table'] );

        # 업로드 허용 용량
        $this->limit_quantity = $this->isExistPut( $this->limit_quantity, $this->file_config['limit_quantity'] );
       
    }
    
    /**
     * 업로드 경로의 디렉터리 확인 및 생성
     */
    private function dirExist() {

        $get_path = $this->path;

        if( $this->make_dir == true ) {
            $get_path .=  '/'. date('Ymd'); 
        }

        # 지정된 경로 존재 확인
        $path_array = explode( "/", $get_path );
		$check_path = $this->server_root;
        
        foreach( $path_array AS $idx=>$val ) {
            
            if( $val !== '' ) {
                
                $check_path .= "/" . $val;

                if( is_dir( $check_path ) == false ) {	
                    if( $this->make_dir == false ) {
                        # 에러 노출 후 종료
                        $this->errorHandler( 'fileUploadHandler->dirExist()', '업로드 경로의 폴더가 존재하지 않습니다.' ); 
                    } else {
                        # 폴더가 없으면 생성.                        
                        $mk_result = mkdir($check_path, 0777);
                        
                        if($mk_result){
                            #권한 변경.
                            chmod($check_path, 0777);
                        } else {                            
                            $this->errorHandler( 'fileUploadHandler->dirExist()', '[폴더 생성 실패] - 경로 : ' . $check_path ); 
                        }
                    }
                } 
            }
        }

        $this->uplaod_end_path = $get_path;
    }

    /**
     * 파일 업로드시 필수 값을 확인 한다.
     */
    private function uploadValid(){

        if( empty( $this->path ) == true ) {
            $this->errorHandler( 'fileUploadHandler->uploadValid()', '업로드될 파일의 경로가 설정되지 않았습니다. path ' ); 
        }

        if( empty( $this->file_element ) == true ) {
            $this->errorHandler( 'fileUploadHandler->uploadValid()', '파일 객체의 이름이 지정되지 않았습니다. file_element ' ); 
        }

        if( is_bool( $this->use_db_status ) == false ) {
            $this->errorHandler( 'fileUploadHandler->uploadValid()', 'use_db_status 는 bool 형식 이어야 합니다.' ); 
        }

        if( is_bool( $this->make_dir ) == false ) {
            $this->errorHandler( 'fileUploadHandler->uploadValid()', 'make_dir 는 bool 형식 이어야 합니다.' ); 
        }

        if( $this->use_db_status == true ) {

            # 데이터베이스 키 확인
            if( empty( $this->use_db ) == true ) {
                $this->errorHandler( 'fileUploadHandler->uploadValid()', 'config.php 에서 설정한 데이터 베이스 key 값을 입력해주세요.' ); 
            }

            # 테이블 변수 확인
            if( empty( $this->use_table ) == true ) {
                $this->errorHandler( 'fileUploadHandler->uploadValid()', 'use_table 값을 설정해주세요.' ); 
            }

            # 데이터 insert 및 삭제시 정보 확인
            if( empty( $this->table_data ) == true ) {
                $this->errorHandler( 'fileUploadHandler->uploadValid()', 'table_data 값을 설정해주세요.' ); 
            }
            

            # 데이터베이스 연결
            $this->dbconn();

        }

    }

    /**
     * 서버 파일 객체로 접근
     * 파일 업로드를 수행하기 위한 조건을 파악하고 수행한다.
     */
    public function fileUpload() {

        # 파일 업로드 필수값 확인
        $this->uploadValid();

        #파일 배열 초기화
        $this->upload_files_arr = [];
        $this->uploaded_files_arr = [];
        $this->error_arr = [];

        if( is_array( $_FILES[ $this->file_element ][ 'name' ] ) == true ) {
            # 배열 형식 파일 객체 처리
            foreach( $_FILES[ $this->file_element ][ 'name' ] AS $file_loop => $value ) {

                if( $_FILES[ $this->file_element ]['size'][ $file_loop ] > 0 ) {

                    # 파일의 존재하면.

                    $this->addFiles([
                        'current_file' => $_FILES[ $this->file_element ]['name'][$file_loop]
                        ,'currnet_file_type' => $_FILES[ $this->file_element ]['type'][$file_loop]
                        ,'currnet_file_size' => $_FILES[ $this->file_element ]['size'][$file_loop]
                        ,'currnet_file_tmpname' => $_FILES[ $this->file_element ]['tmp_name'][$file_loop]
                        ,'set_file_name' => (is_array( $this->set_file_name ) == true ? $this->set_file_name[$file_loop] : '' )
                        ,'set_file_title' => (is_array( $this->set_file_title ) == true ? $this->set_file_title[$file_loop] : '' )
                    ]);
                    
                }
            }
        } else {

            # 단일 파일 객체 처리
            if( empty( $_FILES[ $this->file_element ][ 'name' ] ) == false ) {

                $this->addFiles([
                    'current_file' => $_FILES[ $this->file_element ]['name']
                    ,'currnet_file_type' => $_FILES[ $this->file_element ]['type']
                    ,'currnet_file_size' => $_FILES[ $this->file_element ]['size']
                    ,'currnet_file_tmpname' => $_FILES[ $this->file_element ]['tmp_name']
                    ,'set_file_name' => $this->set_file_name
                    ,'set_file_title' => $this->set_file_title
                ]);

            }

        }

        if( count( $this->error_arr ) == 0 ) {
            # 초기화
            $this->set_file_name = '';
            # 파일 업로드 함수 호출            
            return $this->uploadProc();
        } else {
            # 에러 노출 후 종료
            $this->errorHandler( 'fileUploadHandler->fileUpload()', $this->error_arr ); 
        }

    }

    /**
     * 랜덤 문자열 생성
     */
    private function getServerFileName() {

        $svr_file  = rand(0, 999999999);
		$svr_file  = sprintf('%09d', $svr_file);
		$svr_file  = date("Ymd").$svr_file;
        
        return $svr_file;
    }

    /**
     * 추출된 파일 정보를 배열에 적재한다.
     */
    private function addFiles( $arg_file_info ){

        $current_file = $arg_file_info['current_file'];
        $currnet_file_type = $arg_file_info['currnet_file_type'];
        $currnet_file_size = $arg_file_info['currnet_file_size'];
        $currnet_file_tmpname = $arg_file_info['currnet_file_tmpname'];

        $file_arr = [];
        $file_name = ''; 
        $file_extension	= '';

        if(!(strpos( $current_file, '.' ))) {
            $this->error_arr[] = '[ERROR - 확장자 누락] - 파일명 : '. $current_file;
            return;
        }
        
        $file_arr = $this->getFileSeparationInfo( $current_file );

        $file_name = $file_arr['name']; 
        $file_extension	= $file_arr['ext']; 

        # 1. 업로드 대상 파일의 용량을 체크한다.
        $this->max_file_size = 1024 * 1024 * $this->limit_quantity; # 업로드 최대 사이즈 설정하기(MB)

        if( $this->max_file_size < $currnet_file_size)	{
            $this->error_arr[] = '[용량초과] - 파일명 : '.$current_file . ' / 허용용량 최대 : '. ( $this->max_file_size / (1024*1024 ) ) .' MB';
            return;
        }

        # 2. 업로드 허용 확장자 확인.
        if(preg_match( $this->uplaod_allow_ext ,$file_extension) == false ) {
            $this->error_arr[] = '[ERROR - Extension permission] - 파일명 : '.$current_file;
            return;
        }

        $arg_file_info['server_file_name'] = $this->getServerFileName() . '.' . $file_extension;
        if( empty( $arg_file_info['set_file_name'] ) == false ) {
            $arg_file_info['set_file_name'] .= '.' . $file_extension;
        }

        $this->upload_files_arr[] = $arg_file_info;

    }
    
    /**
     * 파일명에서 확장자와 분리한다.
     */
    private function getFileSeparationInfo( $arg_str ){

        $return_arr = [];

        $str_arr = explode(".", $arg_str ); 
        $extension = strtolower($str_arr[count($str_arr) - 1]);

        $return_arr['name'] = $str_arr[0];
        $return_arr['ext'] = $extension;
        
        return $return_arr;

    }
    
    /**
     * 파일 업로드를 수행한다.
     */
    private function uploadProc() {
        
        if( count( $this->upload_files_arr ) > 0 ) {

            # 파일 업로드 디렉토리 작업
            $this->dirExist();

            foreach( $this->upload_files_arr AS $idx=>$item ) {
                $tmp_name = $item['currnet_file_tmpname'];
                $server_name = ( (empty( $item['set_file_name'] ) == true ) ? $item['server_file_name'] : $item['set_file_name'] );
                $upload_file_path = $this->fileRenameing( $this->server_root . $this->uplaod_end_path . '/' . $server_name );
               
                if(@move_uploaded_file($tmp_name, $upload_file_path )) {

                    chmod($upload_file_path, 0777);
                    
                    $final_file_name_arr = explode('/', $upload_file_path );                    

                    $this->uploaded_files_arr[ $idx ]['origin_name'] = addslashes( $item['current_file'] );
                    $this->uploaded_files_arr[ $idx ]['server_name'] = $final_file_name_arr[ count( $final_file_name_arr ) - 1];
                    $this->uploaded_files_arr[ $idx ]['path'] = $this->uplaod_end_path;
                    $this->uploaded_files_arr[ $idx ]['size'] = $item['currnet_file_size'];
                    $this->uploaded_files_arr[ $idx ]['type'] = $item['currnet_file_type'];
                    $this->uploaded_files_arr[ $idx ]['file_title'] = $item['set_file_title'];


                } else {
                    $this->error_arr[] = '[ERROR - 업로드 실패] - 파일경로 : '. $upload_file_path;
                }

            }

            if( count( $this->error_arr ) > 0 ) {
               # 에러 노출 후 종료
               $this->errorHandler( 'fileUploadHandler->uploadProc()', $this->error_arr ); 
            }

            if( $this->use_db_status == true ) {
                # 데이터 베이스 적재                
                $this->dbHandler();
            }

            return ( count( $this->uploaded_files_arr ) == 1 ) ? $this->uploaded_files_arr[0] : $this->uploaded_files_arr;

        }

    }

    /**
     * 데이터 베이스 insert 처리 준비 및 insert 요청
     */
    private function dbHandler() {
        
        if( count( $this->uploaded_files_arr ) > 0 ) {

            foreach( $this->uploaded_files_arr AS $key=>$val ){
                
                foreach( $this->table_data['insert'] AS $ins_key=>$ins_val ) {
                    $val[ $ins_key ] = $ins_val;
                    $val[ 'company_idx' ] = COMPANY_CODE;
                    $val[ 'reg_idx' ] = getAccountInfo()['idx'];
                    $val[ 'reg_ip' ] = $this->getIP();
                    $val[ 'reg_date' ] = 'NOW()';
                }
                
                $this->dbInsert( $val );
                
            }

            $this->dbDeleteHandler( $this->table_data['delete'] );
        }
    }

    /**
     * 테이블 데이터와 일치하는 파일을 삭제하고 테이블 데이터 삭제처리
     */
    public function dbDeleteHandler( $arg_where ) {

        if( isset( $arg_where ) == true ){
            
            # 데이터베이스 연결
            $this->dbconn();
            # delete 조건에 맞는 데이터를 가져온다.
            $query = " SELECT * FROM " . $this->use_table . " WHERE " . $arg_where;        

            $query_result = $this->db->execute( $query );

            // foreach( $query_result['return_data']['rows'] AS $item ) {
                
            //     if( empty( $item['path'] ) == false ) {
            //         $this->deleteProc( $this->server_root.$item['path'].'/'. $item['server_name'] );
            //     }

            // }

            $this->db->update( $this->use_table, ['del_flag' => 'Y' ], $arg_where );

        }

    }

    /**
     * 데이터 삽입
     */
    public function dbInsert( $arg_insert) {

        # 데이터베이스 연결
        $this->dbconn();

        return $this->db->insert( $this->use_table, $arg_insert );
    }

    private function dbconn() {
        if( empty( $this->db ) == true ) {
            $this->db = $this->connDB( $this->use_db );
        }
    }
    
    /**
     * 테이블 조회
     */
    public function dbGetFile( $arg_where ){

        # 데이터베이스 연결
        $this->dbconn();

        if( strpos( $arg_where, 'del_flag' ) === false ) {

            $arg_where .= " AND del_flag='N' ";

        } 

        $query = " SELECT * FROM " . $this->use_table . " WHERE " . $arg_where . "  ORDER BY idx ASC ";        

        $query_result = $this->db->execute( $query );

        return $query_result['return_data'];
    }

    /**
     * 중복 된 파일명을 재설정한다.
     */
    private function fileRenameing( $arg_file_path ) {

        $result_file_path = '';
        

        if( file_exists($arg_file_path) == true ) {
            
            # 동일한 파일명이 존재하는 경우.

            $new_file_name = '';
            $new_file_path = '';

            $path_arr = explode("/", $arg_file_path ); 
            $get_file_info = $this->getFileSeparationInfo( $path_arr[count($path_arr) - 1] );

            // $get_file_info['name'] = 'a1(2)';
            preg_match('/\(([0-9])\)$/', $get_file_info['name'], $matches);
            $get_file_info['name'] = preg_replace('/\(([0-9])\)$/', '', $get_file_info['name'] );

            if( count( $matches ) > 0 ){
                $new_file_name = $get_file_info['name'].'('. ( $matches[1] + 1 ) .').' .  $get_file_info['ext'];
            } else {
                $new_file_name = $get_file_info['name'].'(1).' .  $get_file_info['ext'];
            }

            for( $loop_cnt = 0; $loop_cnt < count($path_arr) - 1; $loop_cnt++ ){
                if( empty( $path_arr[$loop_cnt] ) == false ){
                    $new_file_path .= $path_arr[$loop_cnt]. '/';
                }
            }

            $new_file_path .= $new_file_name;

            return $this->fileRenameing( $new_file_path );

        } else {
            return $arg_file_path;
        }

        exit;

    }

    /**
     * 파일 삭제를 수행한다.
     */
    public function deleteProc( $arg_unlink_data ) {

        $result = [];
		
		if(file_exists($arg_unlink_data)) {

			if(unlink($arg_unlink_data)) {

				$result["state"] = true;

			} else {

				$result["state"] = false;

                $this->errorHandler( 'fileUploadHandler->deleteProc()', ' 파일 삭제 오류 <br /><br />"'.$arg_unlink_data.'" <br /><br />위 경로의 파일 삭제에 실패하였습니다. <br /><br /> ' );

			}
		} else {
			$result["state"] = false;
		}

		return $result;

    }

    /**
     * 파일 삭제를 수행한다.
     */
    public function fileCopy( $arg_upload_path, $arg_file_idx ) {

        $orign_path = $this->path;
        
        $result = [];
        # 데이터베이스 연결
        $this->dbconn();
        # delete 조건에 맞는 데이터를 가져온다.
        $query = " SELECT * FROM " . $this->use_table . " WHERE idx=" . $arg_file_idx;        

        $query_result = $this->db->execute( $query );

        if( $query_result['return_data']['num_rows'] > 0  ) {

            $origin_row = $query_result['return_data']['row'];
            unset($origin_row['idx']);

            $origin_file_path = $this->server_root . $origin_row['path'] . '/' . $origin_row['server_name'];

            if( file_exists( $origin_file_path ) == true ) {

                $this->path = $arg_upload_path;

                # 파일 업로드 디렉토리 작업
                $this->dirExist();

                $file_arr = $this->getFileSeparationInfo( $origin_row['server_name'] );

                $file_name = $file_arr['name']; 
                $file_extension	= $file_arr['ext']; 
                
                $new_file_path = $this->fileRenameing( $this->server_root . $this->uplaod_end_path . '/' . $this->getServerFileName() .'.'. $file_extension );

                $final_file_name_arr = explode('/', $new_file_path );                    
                $final_file_name = $final_file_name_arr[ count( $final_file_name_arr ) - 1];
                
                $origin_file_obj = fopen( $origin_file_path, 'r' ); 

                if( is_resource( $origin_file_obj ) ){

                    $new_file_path_obj = fopen($new_file_path,'w+'); 
                    $len = stream_copy_to_stream($origin_file_obj, $new_file_path_obj ); 
                    fclose($origin_file_obj); 
                    fclose($new_file_path_obj); 


                    $origin_row['tb_key'] = $this->table_data['insert']['tb_key'];
                    $origin_row['path'] = $this->uplaod_end_path;
                    $origin_row['server_name'] = $final_file_name;
                    $origin_row['company_idx'] = COMPANY_CODE;
                    $origin_row['reg_idx'] = getAccountInfo()['idx'];
                    $origin_row['reg_ip'] = $this->getIP();
                    $origin_row['reg_date'] = 'NOW()';

                    $this->dbInsert( $origin_row );
                    
                    $result['status'] = 'success';

                } else {
                    $result['status'] = 'fail';
                    $result['msg'] = 'resource 파일이 아님 - ' .  $origin_file_path ;
                }
                
                

            } else {
                $result['status'] = 'fail';
                $result['msg'] = '경로에 파일이 존재하지 않음 - ' .  $origin_file_path ;
            }


        } else {
            $result['status'] = 'fail';
            $result['msg'] = '일치하는 파일 정보가 존재하지 않음';
        }
        
        $this->path = $orign_path;

        return $result;
    }

}
?>