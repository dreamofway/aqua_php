<?php

/**
 * 페이지 이동처리
 */
function movePage( $arg_type, $arg_msg, $arg_path ){

    $script = '';
    $script_str = '<script>';
    $script_str .= '__script__';
    $script_str .= '</script>';

    if( $arg_msg ){
        $script .=  'alert ("'.$arg_msg. '");';
    }

    switch( $arg_type ) {

        case 'back' : {
            $script .= 'history.go(-1);';
            break;
        }

        case 'href' : {
            $script .= 'location.href="'. $arg_path .'";';
            break;
        }

        case 'replace' : {
            $script .= 'location.replace("'. $arg_path .'");';
            break;
        }

        default : {
            exit( $this->errorResultForm( 
                'aqua->movePage()'
                , ' 정의되지 않은 type 입니다.' )
            );
        }

    }

    exit( preg_replace('/__script__/', $script, $script_str ) );
    
}

/**
 * 이전 페이지로 이동
 */
function errorBack( $arg_msg ){
    movePage( 'back', $arg_msg, '');
}

?>