<?php

class main extends baseController {

    private $model;

    function __construct() {

        #모델 인스턴스
        // $this->model = $this->new('mainModel');

    }

    public function index(){

        $page_data['use_top'] = true;        
        $page_data['use_left'] = false;        
        $page_data['use_footer'] = true;        
        $page_data['contents_path'] = '/index.php';

        $this->view( $page_data );
        
    }

}

?>