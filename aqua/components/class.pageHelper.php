<?php
/**
 * ---------------------------------------------------
 * pageHalper v1.0.1
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.1] 2020.03.04 - 이정훈
 *  - setParams() : checkbox 와 같이 name_[0-9] 규칙을 가질 수 있는 경우 대응
 * 
 * [v1.0.0] 2020.02.22 - 이정훈
 *  - getParameters() : 파라미터를 값을 받아 배열로 반환미한다.
 *  - setParams() : page에서 사용하는 params String을 생성해 반환한다.
 *  - paging() : pagination DOM 을 구성 한다.
 *  - pagingDomTypeA() : DOM 구조를 생성해 반환.
 * 
 * ---------------------------------------------------
*/

class pageHelper {

    public $current_page;
    public $total_pages;
    public $page_params;
    public $is_prev_block;
    public $go_prev_page;
    public $start_page;
    public $end_page;
    public $is_next_block;
    public $go_next_page;
    public $current_block;
    public $pages_per_block;
    public $total_blocks;
    public $list_rows;
    public $total_rs;
    public $params;
    public $dom_type;
    private $get_params;

    function __construct() {

        $this->current_page = 1;
        $this->pages_per_block = 10;
        $this->list_rows = 20;
        $this->dom_type = 'a';

    }

    /**
     * url parameter 값을 받아온다.
     */
    public function getParameters(){

        $get_param = '';
        
        if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'get' ) {            
            $get_param = $_GET;
        } else {            
            $get_param = $_POST;
        }

        foreach( $get_param AS $key=>$val ){            
            
            if( gettype( $val ) == 'array' ) {

                foreach( $val AS $arr_key=>$arr_val ) {
                    $arr_val = trim( $arr_val );
                    $arr_val = htmlspecialchars( $arr_val );
                    $this->get_params[ $key ][$arr_key] = addslashes( $arr_val );
                }

            } else {
                $this->get_params[ $key ] = trim( $val );
                $this->get_params[ $key ] = htmlspecialchars( $this->get_params[ $key ] );
                $this->get_params[ $key ] = addslashes( $this->get_params[ $key ] );
            }
            
        }
     
        if( empty( $this->get_params['page'] ) == true ) {
            $this->get_params['page'] = 1;
        }

        if( empty( $this->get_params['list_rows'] ) == true  ) {
            $this->get_params['list_rows'] = 20;
        }

        $this->current_page = $this->get_params['page']; 
        $this->list_rows = $this->get_params['list_rows']; 
        
        return $this->get_params;
    }

    /**
     * parameter string 생성
     */
    public function setParams( $arg_keys ){
        
        $this->params = '';
        $get_params_str = '';

        # 현재 파라미터 값의 키값을 문자열로 변환
        $get_params_str = join(',', array_keys( $this->get_params ) );

        # 넘겨받은 배열 값을 반복하면서 파라미터 문자열 생성
        for( $loop_cnt = 0; $loop_cnt < count( $arg_keys ); $loop_cnt++ ) {

            # 파라미터 키 값과 넘겨받은 배열 값으로 확인
            preg_match_all( '/'.$arg_keys[ $loop_cnt ].'_[0-9]/i', $get_params_str, $matches);

            if( count( $matches[0] ) > 0 ) {

                # 해당 키값으로 시작하는 넘버링 규칙의 파라미터가 있는 경우 처리
                
                foreach( $matches[0] AS $item ){
                    
                    $this->params .= '&' . $item . '=' . $this->get_params[ $item ];
                }

            } else {
                $this->params .= '&' . $arg_keys[ $loop_cnt ] . '=' . $this->get_params[ $arg_keys[ $loop_cnt ] ];
            }            
        }


        return $this->params;

    }

    /**
     * paging 처리 함수
     */
    public function draw() {

        if( $this->total_rs == 0 ) {
            return;
        }

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
  
        # 전체 페이지 수 = 전체 레코드 / 페이지당 레코드 수 
        $this->total_pages = ceil($this->total_rs / $this->list_rows);
        # 전체 블럭 = 전체 페이지 수 / 페이지 row size
        $this->total_blocks = ceil($this->total_pages / $this->pages_per_block);
        # 현재 블럭 = 현제 페이지 / 한 블럭당 보여질 페이지 수
        $this->current_block = ceil($this->current_page / $this->pages_per_block);
        # 시작 페이지
        $this->start_page = ($this->current_block - 1) * $this->pages_per_block + 1;
        # 마지막 페이지
        $this->end_page = $this->start_page + $this->pages_per_block - 1;
        
        if ($this->end_page > $this->total_pages) {
            $this->end_page = $this->total_pages;
        }

        # 이전 블럭, 다음 블럭 존재 여부
        $this->is_prev_block = false;
        $this->is_next_block = false;
        
        if ($this->current_block < $this->total_blocks) {
            $this->is_next_block = true;
        }
            
        if ($this->current_block > 1) {
            $this->is_prev_block = true;
        }                

        if ($this->total_blocks == 1) {
            $this->is_prev_block = false;
            $this->is_next_block = false;
        }

        switch( $this->dom_type ){
            
            case 'a' : {

                $get_dom = $this->pagingDomTypeA();

                break;
            }
            
            default : {

                $get_dom = '';

            }

        }
        
        echo $get_dom;
            
    }

    /**
     * 페이지 dom을 구성한다.
     */
    private function pagingDomTypeA(){

        $str = '';
        $str .= '<ul class="pagination justify-content-center">';

        # 처음 페이지 이동 버튼
        if ( $this->current_page > $this->pages_per_block ) {

            $str .= '<li class="page-item "><a href="?page=1'. $this->params .'" class="page-link">&lt&lt</a></li>'.PHP_EOL;

        }
        
        # 이전 페이지 이동 버튼 
        if (  $this->is_prev_block === true  ) {
            $go_prev_page = $this->start_page - $this->pages_per_block;
            $str .= '<li class="page-item "><a href="?page='. $go_prev_page . $this->params .'" class="page-link">&lt</a></li>'.PHP_EOL;
        }        
          
        # 페이지 숫자 영역 생성
        for ($loop_page = $this->start_page; $loop_page <= $this->end_page; $loop_page++ ) {
            if ($this->current_page == $loop_page) {
                $str .= '<li class="page-item active"><a href="#" class="page-link">'. $loop_page .'</a></li>'.PHP_EOL;                
            } else {
                $str .= '<li class="page-item"><a href="?page='. $loop_page . $this->params .'" class="page-link">'. $loop_page .'</a></li>'.PHP_EOL;
            }
        }

        # 다음 페이지 이동 버튼 
        if ( $this->is_next_block === true ) {

            $go_next_page = $this->start_page + $this->pages_per_block;
            $str .= '<li class="page-item"><a href="?page='. $go_next_page . $this->params .'" class="page-link">&gt</a></li>'.PHP_EOL;            

        }         
        
        # 마지막 페이지 이동 버튼
        if ($this->current_block < $this->total_blocks) {        
            $str .= '<li class="page-item"><a href="?page='. $this->total_pages.$this->params .'" class="page-link">&gt&gt</a></li>'.PHP_EOL;            
        }

        $str .=  '</ul>';

        return $str;

    }


}

?>