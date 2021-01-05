<?php
/**
 * ---------------------------------------------------
 * AQUA Framework routerHander  v1.0.0
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.02.17 - 이정훈
 *  - init() 개발
 *  - notFoundProc() 개발
 * 
 * ---------------------------------------------------
*/
class routerHandler extends aqua {

    function __construct() {
        $this->runRouter();
    }
    /**
     * REQUEST_URI 를 확인하여 페이지 처리
     */
    private function runRouter(){
        
        $get_url_arr = explode( '/', $_SERVER['REQUEST_URI'] );

        # 경로의 마지막
        $path_tail = '';

        if( strpos( $get_url_arr[count( $get_url_arr ) - 1], '?' ) > -1 ) {            
            
            $path_tail = trim( explode('?', $get_url_arr[count( $get_url_arr ) - 1] )[0] );
            
        } else {
            $path_tail = $get_url_arr[count( $get_url_arr ) - 1];
        }

        if( strpos( $path_tail, '.' ) > -1 ) {
            #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
            # 확장자 존재.
            #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
            if( file_exists( $_SERVER['DOCUMENT_ROOT'].explode('?', $_SERVER['REQUEST_URI'] )[0] ) == false ) {                
                $this->notFoundProc();
            }

        } else {
            
            # 확장자 없는 경우 컨트롤러 호출
            switch( count( $get_url_arr ) ) {

                case 2 : {
                    
                    #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
                    # 경로가 없거나 하나의 경로인 경우를 처리한다.
                    #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
                    $get_controller = trim( explode('?', $get_url_arr[1] )[0] );

                    if( $get_controller != '' ) {
                        
                        # 현재 컨트롤러를 호출한다.                    
                        $this->classExistCheck( $get_controller );
    
                        $class_obj = new $get_controller;
                        $class_obj->index();
    
                    } else {
    
                        # 기본 컨트롤러를 호출한다.
                        $class_obj = new main;
                        $class_obj->index();

                    }
    
                    break;
                }
    
                case 3 : {
                    
                    #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
                    # 경로 구분이 하나 이상 2개 이하를 처리한다.
                    #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
    
                    if( trim( $get_url_arr[2] ) != '' ) {
                        
                        $get_method = trim( explode( '?', $get_url_arr[2] )[0] );
                        
                        # class 존재 체크
                        $this->classExistCheck( trim( $get_url_arr[1] ) );
                        # 개체 인스턴스
                        $class_obj = new $get_url_arr[1];
    
                        # method 존재 체크
                        $this->mothodExistCheck( $class_obj, $get_method );
    
                        $class_obj->$get_method();
    
                    } else {
    
                        $class_obj = new $get_url_arr[1];
                        $class_obj->index();
    
                    }
    
                    break;
                }
    
                default : {
                    $this->notFoundProc();
                }
            }
        }
        
    }

    /**
     * 요청한 경로에 파일이 있는지 확인하여 404 error 처리
     */
    private function notFoundProc() {
        #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
        # 존재하지 않는 경로 처리.
        #=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+
        
        if( file_exists( $_SERVER['DOCUMENT_ROOT'].$this->html_error_path.'/404.html' ) == true ) {
            include_once( $_SERVER['DOCUMENT_ROOT'].$this->html_error_path.'/404.html' );
            exit;
        } else {            
            exit( $this->errorResultForm( 'routerHandler->init()'
                , '이름이 변경되었거나 일시적으로 사용할 수 없는 <br>페이지가 제거되었을 수 있습니다. <br> '. $this->html_error_path .'/404.html 파일을 생성해주세요.')
                );
        } 
    }


}

?>