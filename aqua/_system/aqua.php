<?php
/**
 * ---------------------------------------------------
 * AQUA Framework System Init Processor  v2.0.0
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v2.0.0] 2020.02.18 - 이정훈
 *  - class aqua 파일 분리 생성
 *  - 연관배열 $_aqua_val 변수를 사용하여 파일 inc 작업 처리
 * 
 * [v1.0.0] 2020.02.17 - 이정훈
 *  - 기본 설정 초기화
 *  - class.aqua 개발
 *  - class.aqua.importFiles 개발
 *  - class.aqua.classExistCheck 개발
 *  - class.aqua.mothodExistCheck 개발
 *  - class.aqua.getExtSeparation 개발
 *  - class.aqua.errorResultForm 개발
 *  - class.aqua.view() 개발
 * 
 * ---------------------------------------------------
*/

# 시간설정
date_default_timezone_set('Asia/Seoul');

# 언어설정
header('Content-Type: text/html; charset=UTF-8');

# 에러전체 그리고 notice는 제외 & : and / | : or / ~ : not
error_reporting(E_ALL&~E_NOTICE);	

# 에러 노출 true : 1 / false : 0
ini_set('display_errors', 1);

session_start();

# aqua folder 초기화
$_aqua_val['dir_path'] = $_SERVER['DOCUMENT_ROOT']. '/aqua';

# 우선 작업 폴더 대상 정의
$_aqua_val['first_dir_arr'] = [];

if( is_dir( $_aqua_val['dir_path'] ) ) {

    $_aqua_val['open_dir_opj'] = opendir( $_aqua_val['dir_path'] );
    
    # aqua 폴더 하위 폴더들을 반복하면서 배열에 적재
    while (false !== ($_aqua_val['entry'] = readdir($_aqua_val['open_dir_opj']))) {
        
        # 영문 폴더명에 한해서 작업 진행
        preg_match('/([a-z])/i', $_aqua_val['entry'], $_aqua_val['mathch_result'], PREG_OFFSET_CAPTURE);
        
        # 영문 폴더명인 경우 mathch_result 가 배열로 반환하므로 count 함수로 체크
        if( count( $_aqua_val['mathch_result'] ) ){
            $_aqua_val['first_dir_arr'][] = $_aqua_val['entry'];
        }
        
    }

    # 오름차순 정렬
    sort( $_aqua_val['first_dir_arr'] );
    
    # aqua 폴더 하위 폴더들 반복 하면서 파일을 include 한다.
    for( $_aqua_val['folders_loop'] = 0; $_aqua_val['folders_loop'] < count( $_aqua_val['first_dir_arr'] ); $_aqua_val['folders_loop']++ ) {
        
        if( $_aqua_val['first_dir_arr'][ $_aqua_val['folders_loop'] ] === 'views' ) {
            continue;
        }

        // echo  '<br/>' . $_aqua_val['first_dir_arr'][ $_aqua_val['folders_loop'] ]. '<br/>';
        # 폴더경로 설정
        $_aqua_val['target_dir'] = $_aqua_val['dir_path'].'/'.$_aqua_val['first_dir_arr'][ $_aqua_val['folders_loop'] ];
        
        if( is_dir( $_aqua_val['target_dir'] ) ) {
                
            $_aqua_val['target_dir_child'] = scandir( $_aqua_val['target_dir'] );
            
            for( $_aqua_val['loop_cnt'] = 2; $_aqua_val['loop_cnt'] < count( $_aqua_val['target_dir_child'] ); $_aqua_val['loop_cnt']++ ) {
                
                #확장자 분리
                $_aqua_val['file_arr'] = explode(".", $_aqua_val['target_dir_child'][ $_aqua_val['loop_cnt'] ] ); 
                $_aqua_val['file_ext'] = strtolower( $_aqua_val['file_arr'][ count( $_aqua_val['file_arr'] ) - 1 ] );

                # php 확장자만 include 작업처리
                if( $_aqua_val['file_ext'] === 'php') {
                    
                    if( $_aqua_val['target_dir_child'][ $_aqua_val['loop_cnt'] ] != 'aqua.php') {
                        // echo '<br>' . $_aqua_val['target_dir']_child[ $_aqua_val['loop_cnt'] ] . '<br>';
                        include_once( $_aqua_val['target_dir'] . '/' . $_aqua_val['target_dir_child'][ $_aqua_val['loop_cnt'] ] );
                    }

                }

            }

        }

    }
    
    # 폴더 닫기
    closedir( $_aqua_val['open_dir_opj'] );
    
}
// echoBr( $_aqua_config );
$_aqua = new aqua;

// $log = $_aqua->new('accessLog');
// $log->execute('access');

?>