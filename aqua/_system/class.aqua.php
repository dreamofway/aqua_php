<?php
/**
 * ---------------------------------------------------
 * AQUA Framework System Init Processor v1.1.1
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.1.2] 2020.03.02 - 이정훈
 *  - errorHandler() 개발
 *  - issetParams() 개발
 *  - isExistPut() 개발
 * 
 * [v1.1.1] 2020.02.20 - 이정훈
 *  - getViewPhysicalPath() 개발
 *  - getViewsVerPath() 개발
 * 
 * [v1.1.0] 2020.02.19 - 이정훈
 *  - setConfig() 개발
 *  - getConfig() 개발
 *  - echoPre() 개발
 *  - echoBr() 개발
 *  - fileExists() 개발
 * 
 * [v1.0.0] 2020.02.18 - 이정훈
 *  - 기본 설정 초기화
 *  - classExistCheck() 개발
 *  - classmothodExistCheck() 개발
 *  - getExtSeparation() 개발
 *  - errorResultForm() 개발
 *  - connDB() 개발
 *  - view() 개발 
 * 
 * ---------------------------------------------------
*/

class aqua {

    protected $config = [];
    protected $veiw_path = '/aqua/views';
    protected $html_error_path = '/aqua/views/html_errors';
    
    function __construct() {

        //echo '<br> aqua call <br>';
        $this->config['url_rewrite'] = false;
        $this->config['view_ver'] = '01';
        
        $this->init();

    }

    function __destruct() {
        // echo '<br> aqua end <br>';
    }

    /**
     * 사용자 config 변수와 class config 변수를 병합하여 동기화 한다.
     */
    protected function setConfig(){

        global $_aqua_config;

        if( isset( $_aqua_config ) ) {
            # 사용자 설정과 기본 설정과 병합
            $this->config = array_merge( $this->config, $_aqua_config );
            $_aqua_config = $this->config;

        } else {
            # 변수 초기화가 되어있지 않으면 error 호출 후 종료            
            exit( $this->errorResultForm( 'aqua->setConfig()', '/aqua/_config/config.php 파일에서 $_aqua_config[] 에 정보를 설정해주세요.' ) );
        }

    }

    /**
     * 전역변수 $_aqua_config 값을 반환 한다.
     */
    protected function getConfig(){

        global $_aqua_config;

        return $_aqua_config;

    }

    /**
     * 시스템 구동에 필요한 유효성 검사 및 초기화
     */
    private function init(){

        $this->setConfig();
        
        # 필수 class 존재 확인
        $class_check_list = [
            'baseController'
            ,'baseModel'
            ,'mySqlHandler'
            ,'errorHandler'
        ];

      
        if( $this->config['url_rewrite'] == true ) {
            $class_check_list[] = 'routerHandler';
        }

        $this->classExistCheck( $class_check_list );

        # router instance
        if( $this->config['url_rewrite'] == true ){
            new routerHandler;
        }

    }
    /**
     * view 파일을 구성한다.
     */
    protected function view( $arg_data ){

        $config = $this->getConfig();

        # view 옵션 병합
        $view_data = array_merge( $config['view'], $arg_data  );
		$view_data['aqua_view_path'] = $this->getViewsVerPath();

        # 이미지 경로 설정
        if( $view_data['use_cdn'] == false ) {
            $view_data['img_path'] = $view_data['aqua_view_path'] .  $view_data['img_path'];
            $view_data['favicon_path'] = $view_data['aqua_view_path'] .  $view_data['favicon_path'];
        } 
        //$this->echoBr( $view_data );
        
        $view_ver_path = $_SERVER['DOCUMENT_ROOT'].$this->getViewsVerPath();

        if( $view_data['use_layout'] === true ) {

            if( $view_data['layout_path'] == '' ){

                $make_view_path = $view_ver_path . '/layout/container.php';

            } else {
                $make_view_path = $view_ver_path . $config['view']['layout_path'];
            }
            
			$view_data['contents_path'] = $view_ver_path .'/view'. $view_data['contents_path'];
		
			if( $this->fileExists( $view_data['contents_path'] ) === false ) {
				exit( $this->errorResultForm( 
					'aqua->view()'
					, '지정된 경로 : ['. $view_data['contents_path'] .'] 에<br><br>  view 파일이 존재하지 않습니다.<br><br>경로를 다시 확인해주세요. ' )
				);
			}
			

        } else {
			# layout 미사용

            $make_view_path = $view_ver_path .'/view'.$view_data['contents_path'];
        }

        if( $this->fileExists( $make_view_path ) === false ) {

            # 경로에 파일이 없을 때
            exit( $this->errorResultForm( 
                'aqua->view()'
                , '지정된 경로 : ['. $make_view_path .'] 에<br><br>  view 파일이 존재하지 않습니다.<br><br>경로를 다시 확인해주세요. $_aqua_config["view"]["use_layout"] = true 로 하셧다면 <br> $_aqua_config["view"]["layout_path"] 의 경로가 생성한 폴더구조와 일치한지 확인하시고, <br><br> 아니라면 controller에서 설정한 $contents_path 를 확인해주세요.' ) 
            );

        } else {

            # 경로에 파일 있을 때 

           // $this->echoBr( $make_view_path );

            # rewirte 엔진을 사용중일 때만 동작한다.
            if( $config['url_rewrite'] == true ) {

                if( gettype($arg_data) == "array" ){
        
                    # 각 페이지 생성 method 에서 생성된 페이지 내 사용 변수를 동적 할당한다.
                    foreach($view_data AS $data_key=>$data_val) {
                        
                        ${$data_key} = $data_val;
                        
                    }

					include_once( $make_view_path );
                    
                } else {

                    exit( $this->errorResultForm( 'aqua->view()', 'view 에서 사용될 데이터는 연관배열 형식으로 담아주세요.' ) );

                }

            }

        }


        

    }

    /**
     * DB connect 함수
     */
    public function connDB( $arg_id ){
        
        // echo '<br> connDB <br>';

        # DB class 존재 여부 파악
        $this->classExistCheck('mySqlHandler');
        
        if( isset( $this->getConfig()['db'] ) === true ) {

            # DB instance            
            $db = new mySqlHandler( $this->getConfig()['db'][ $arg_id ] );

        } else {
            exit( $this->errorResultForm( 'aqua->connDB()', '/aqua/_config/config.php 파일에서 $_aqua_config["db"] 에 db 접속 정보를 설정해주세요.' ) );
        }

        return $db;

    }

    /**
     *  class 존재여부 체크
     */
    protected function classExistCheck( $arg_data ) {

        $err_list = [];

        if( is_array( $arg_data ) ) {

            for( $loop_cnt = 0; $loop_cnt < count( $arg_data ); $loop_cnt++ ){
                if( !( class_exists( $arg_data[ $loop_cnt ] ) ) ) {
                    $err_list[] = '[ class : ' . $arg_data[ $loop_cnt ]. ' ] 가 존재하지 않습니다';
                }
            }

        } else {

            if( !( class_exists( $arg_data ) ) ) {
                $err_list[] = '[ class : ' . $arg_data. ' ] 가 존재하지 않습니다';
            }

        }
        
        if( count( $err_list ) > 0 ) {
            exit( $this->errorResultForm( 'aqua->classExistCheck()' , $err_list) );
        }

    }

    /**
     *  class method 존재여부 체크
     */
    protected function mothodExistCheck( $arg_obj, $arg_method ) {

        $err_list = [];

        if( !( method_exists( $arg_obj, $arg_method ) ) ) {
            $err_list[] = '[ method : ' . $arg_method. ' ] 가 존재하지 않습니다.';
        }
        
        if( count( $err_list ) > 0 ) {
            exit( $this->errorResultForm( 'aqua->mothodExistCheck()' , $err_list) );
        }

    }

    
    /**
     * 파일명에서 확장자와 분리한다.
     */
    protected function getExtSeparation( $arg_str ){

        $return_arr = [];

        $str_arr = explode(".", $arg_str ); 
        $extension = strtolower($str_arr[count($str_arr) - 1]);

        $return_arr['name'] = $str_arr[0];
        $return_arr['ext'] = $extension;
        
        return $return_arr;

    }

    /**
     * error 발생시 안내 문구를 노출하고 종료한다.
     */
    public function errorHandler( $arg_location, $arg_disciption ){

        exit( $this->errorResultForm( $arg_location, $arg_disciption ) );

    }

    /**
     * Error 상황 html 생성
     */
    protected function errorResultForm( $arg_location, $arg_disciption ) {
        
        $error_html = '';
        $error_html .= '<div style="width:100%;margin-top:80px" >';
        $error_html .= '    <div style="margin:0 auto;width:800px;background-color:#330000;" >';
        $error_html .= '        <div style="width:100%;padding:10px;color:#af5656">';
        $error_html .= '            AQUA FrameWork - ALERT INFO <br />';
        $error_html .= '        </div>';
        $error_html .= '        <div style="width:100%;padding:10px;color:#af5656;border-top:3px solid #fff">';
        $error_html .= '           PATH';
        $error_html .= '        </div>';
        $error_html .= '        <div style="width:100%;padding:10px;color:#ff0000;word-break:break-all">';
        $error_html .= $_SERVER['REQUEST_URI'];
        $error_html .= '        </div>';
        if( $arg_location != '' ) {
            $error_html .= '        <div style="width:100%;padding:10px;color:#af5656;border-top:3px solid #fff">';
            $error_html .= '            LOCATION';
            $error_html .= '        </div>';
            $error_html .= '        <div style="width:100%;padding:10px;color:#ff0000">';
            $error_html .= $arg_location;
            $error_html .= '        </div>';
        }
        $error_html .= '        <div style="width:100%;padding:10px;color:#af5656;border-top:3px solid #fff">';
        $error_html .= '            ALERT - DESCRIPTION <br />';
        $error_html .= '        </div>';
        $error_html .= '        <div style="width:100%;padding:10px;color:#ff0000">';
        if( is_array( $arg_disciption ) ) {

            for($loop_cnt = 0; $loop_cnt < count(  $arg_disciption ); $loop_cnt++ ) {
                $error_html .= $arg_disciption[ $loop_cnt ] . '<br>';
            }

        } else {

            $error_html .= $arg_disciption;

        }
        
        $error_html .= '        </div>';
        $error_html .= '    </div>';
        $error_html .= '</div>';
	
        return $error_html;

    }

    /**
     * 배열을 dom 에 출력한다.
     */
    public function echoPre( $arg_data ){
        echo '<pre>';
        print_r( $arg_data );
        echo '</pre>';        
    }

    /**
     * <br>을 포함하여 dom에 출력한다
     */
    public function echoBr( $arg_data ){

        if( gettype( $arg_data ) == 'array' ){
            $this->echoPre( $arg_data );
        } else {
            echo '<br>';
            echo $arg_data;
            echo '<br>';        
        }
        
    }

    /**
     * file 존재 여부 파악
     */
    public function fileExists( $arg_path ){

        $return_val = false;

        if( file_exists( $arg_path ) == true ) {
            $return_val = true;
        } else {
            $return_val = false;
        }

        return $return_val;

    }

    /**
     * layout 구조를 사용할 때 설정된 view 물리경로를 반환 한다.
     */
    protected function getViewPhysicalPath( $arg_path ){ 

        $return_val = $_SERVER['DOCUMENT_ROOT'] . $this->getViewsVerPath() . $arg_path;

        if( $this->fileExists( $return_val ) == false ) {
            exit( $this->errorResultForm( 
                'aqua->getViewPhysicalPath()'
                , '지정된 경로 : ['. $return_val .'] 에<br><br>  view 파일이 존재하지 않습니다.<br><br>경로를 다시 확인해주세요. ' )
            );
        } 
        
        return $return_val;

    }

    /**
     * views/ 하위 버전 정보를 반환 한다.
     */
    protected function getViewsVerPath(){
        return $this->veiw_path . '/v' . $this->getConfig()['view']['ver'];
    }

    /**
     * class 를 인스턴스 해서 반환한다.
     */
    public function new( $arg_class ){

        $this->classExistCheck( $arg_class );

        return new $arg_class;

    }

    /**
     * 영문+숫자 형식의 코드를 전달받아 숫자에 +1 한 값을 반환한다.
     */
    public function makeNewCode( $arg_service_str, $arg_origin, $arg_len ){

        if( empty( $arg_origin ) ) {

            $result = $this->addZeroToString( 1, $arg_len );

        } else {
            
            $result = $this->addZeroToString( ( (int)preg_replace( '/'.$arg_service_str.'/', '' ,$arg_origin ) ) + 1, $arg_len );
        }

        return $arg_service_str.$result;

    }

    /**
     * 제한수보다 작은 수 앞에 0 삽입
     */
    protected function addZeroToString( $arg_num, $arg_len ) {

        $str_zero = '';
        $lmit_no = 10;

        for( $loop_cnt = 1; $loop_cnt < $arg_len; $loop_cnt++ ){
            $lmit_no *= 10 ;
        }

        if( $lmit_no <= ( $arg_num ) ){
            exit( $this->errorResultForm( 'aqua->addZeroToString', '새로 생성될 코드가 기존 코드 제한을 넘어섭니다.') );
        } else {
            
            for($loop_cnt = 0; ( $arg_len - strlen( $arg_num ) ) > $loop_cnt; $loop_cnt++) {
                $str_zero = $str_zero.'0';
            }
            
        }
        
        return $str_zero.$arg_num;
		
    }//   end addZeroToString

    /**
     * 현재 접속자의 아이피 정보를 반환 한다.
     */
    public function getIP(){
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 파라미터 값을 확인한다.
     */
    public function issetParams( $arg_params, $arg_check_info ){
        foreach( $arg_check_info AS $check_item ) {            
            if( isset( $arg_params[ $check_item ] ) == false ) {
                errorBack('잘못된 접근입니다.');
            }
        }
    }

    /**
     * 공백이 아닌 경우에 확인 대상 변수를 반환 한다.
     * arg_target : 기본 변수
     * arg_val : 검사 변수
     */
    public function isExistPut( $arg_target, $arg_val ) {

        $result = '';

        if( isset( $arg_val ) == true ) {
            $result = $arg_val;
        } else {
            $result = $arg_target;
        }

        return $result;
    }
    
}

?>