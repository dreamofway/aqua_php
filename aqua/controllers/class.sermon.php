<?php

class sermon extends baseController {

    private $model;
    private $page_data;
    private $paging;            
    private $page_name;
    
    function __construct() {

        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # model instance
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        $this->paging = $this->new('pageHelper');
        $this->loc = $this->new('locHelper');
        // $this->model = $this->new('companyModel');        

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
        ]);

        $this->loc->menu = $this->getConfig()['view']['menu_info'];
        $this->loc->top_code = $this->page_data['top_code'];
        $this->loc->sub_code = $this->page_data['left_code'];

    }

    /** 
     * 주일 설교
    */
    public function sunday_worship(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
        
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;                
        $this->page_data['contents_path'] = '/sermon/sermon.php';
        
        $this->view( $this->page_data );

    }

    /** 
     * 성경이 보인다.
    */
    public function bile(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;        
        $this->page_data['contents_path'] = '/sermon/sermon02.php';
        
        $this->view( $this->page_data );

    }

    /** 
     * 조직신학
    */
    public function theology(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;        
        $this->page_data['contents_path'] = '/sermon/sermon03.php';
        
        $this->view( $this->page_data );

    }

    /** 
     * 칼럼
    */
    public function column(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
        
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;        
        $this->page_data['contents_path'] = '/sermon/post.php';
        
        $this->view( $this->page_data );

    }

}

?>