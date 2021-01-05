<?php

class communication extends baseController {

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
        
        $this->model = $this->new('boardModel');        

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
     * 목록
    */   
    public function list(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # SET Values
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=        
        $query_where = " AND board_type='". $this->page_data['left_code'] ."' ";
        $query_sort = ' ORDER BY idx DESC ';
        $limit = " LIMIT ".(($this->page_data['page']-1)*$this->page_data['list_rows']).", ".$this->page_data['list_rows'];

        if( $this->page_data['sch_keyword'] ) {
            $query_where .= " AND ( 
                                    ( title LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( reg_name LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                                    OR ( contents LIKE '%". $this->page_data['sch_keyword'] ."%' ) 
                            ) ";
        }

        $list_result = $this->model->getBoards([            
            'query_where' => $query_where
            ,'query_sort' => $query_sort
            ,'limit' => $limit
        ]);

        $this->page_data['list'] = $list_result['rows'];        
        $this->paging->total_rs = $list_result['total_rs'];        
        $this->page_data['paging'] = $this->paging;        
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;        
        $this->page_data['contents_path'] = '/communication/list.php';
        
        $this->view( $this->page_data );

    }

    /** 
     * 상세
    */
    public function list_view(){
        
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=
        # 필수값 체크
        #+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=            
        $this->issetParams( $this->page_data, ['idx']);


        $result = $this->model->getBoard( " idx = '". $this->page_data['idx'] ."' " );

        if( count( $result['row'] ) > 0  ) {
            $this->page_data = array_merge( $this->page_data, $result['row'] );
        } else {
            errorBack('해당 게시물이 삭제되었거나 정상적인 접근 방법이 아닙니다.');
        }
 
        $this->page_data['loc'] = $this->loc;

        $this->page_data['use_top'] = true;        
        $this->page_data['use_left'] = false;
        $this->page_data['use_footer'] = true;        
        $this->page_data['contents_path'] = '/communication/view.php';
        
        $this->view( $this->page_data );

    }

   

}

?>