<?php 
# 세팅될 설정파일
$_aqua_config = [];

# 설정 파일을 읽어드린다. 
$masic_solution_init_path = $_SERVER['DOCUMENT_ROOT']. "/init.json";

// $get_file = fopen( $file_path, "r") or die("서비스를 시작 할 수 없습니다. 서버 관리자에게 문의하세요");
$get_file = @fopen( $masic_solution_init_path, "r");

if( $get_file ) {

    $get_file_content = fread( $get_file, filesize( $masic_solution_init_path ));
    
    fclose($get_file);
    $get_file = '';

    # 배열 형태로 변환한다.
    $init_file_arr = json_decode( $get_file_content, true );

    $_aqua_config = $init_file_arr['config'];
    
    foreach( $init_file_arr['define'] AS $key=>$val ){
        define($key, $val);
    }
    
} else {

    # 파일이 없으므로 에러 페이지 노출
    include_once($_SERVER['DOCUMENT_ROOT']."/aqua/views/html_errors/503_solution.html");
    exit;
}

?>
<?php include_once($_SERVER['DOCUMENT_ROOT']."/aqua/_system/aqua.php"); ?>