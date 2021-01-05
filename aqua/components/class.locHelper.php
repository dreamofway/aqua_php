<?php
/**
 * ---------------------------------------------------
 * location Halper v1.0.0
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 *  
 * [v1.0.0] 2020.11.26 - 이정훈
 *  - getParameters() : 파라미터를 값을 받아 배열로 반환미한다. 
 * 
 * ---------------------------------------------------
*/

class locHelper {
    
    public $menu;
    public $top_code;
    public $sub_code;
    
    function __construct() {
        
    }
    

    /**
     * DOM 생성
     */
    public function draw() {
        // echoBR( $this->top_code );
        // echoBR( $this->menu );
        // echoBR( $this->menu['sub_menu'][ $this->top_code ] );

        switch( 'a' ){
            
            case 'a' : {

                $get_dom = $this->locTypeA();

                break;
            }
            
            default : {

                $get_dom = '';

            }

        }
        
        echo $get_dom;
            
    }

    private function getCurrentTopTitle(){

        $return_val = '';

        foreach( $this->menu['top_menu'] AS $data ) {
            
            if( $this->top_code ==  $data['code'] ){
                $return_val = $data['title'];
            }
        }

        return $return_val;
    }

    /**
     * 페이지 dom을 구성한다.
     */
    private function locTypeA(){

        $get_sub_arr = $this->menu['sub_menu'][ $this->top_code ];

        $str = '';
        $str .= '<div class="content_box_left">';
        $str .= '<h3>'. $this->getCurrentTopTitle() .'</h3>';
        $str .= '<ul class="sub-menu">';

        foreach( $get_sub_arr AS $menu_data ){
            $str .=     '<li class="'. ( $this->sub_code == $menu_data['code'] ? 'on' : '' ) .'"><a href="'. $menu_data['link'] .'">'. $menu_data['title'] .'</a></li>';
        }

        $str .= '</ul>';

        

        $str .=  '</div>';

        return $str;

    }


}

?>