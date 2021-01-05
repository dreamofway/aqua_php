<?php include_once($_SERVER['DOCUMENT_ROOT']."/index.php"); ?>
<?php
 /**
 * ---------------------------------------------------
 * AQUA Framework file down   v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.0.0]
 * - 파일 다운로드를 수행한다.
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.03.03 - 이정훈
 *  - fileUploadHandler 를 이용하여 데이터 조회 후 파일 다운로드 
 * 
 * ---------------------------------------------------
*/
    $paging = $_aqua->new('pageHelper');
    $filer = $_aqua->new('fileUploadHandler');

    $prams = $paging->getParameters();

    if( empty( $prams['key'] ) == true ) {
        errorBack('잘못된 접근입니다.');
    }

    $result = $filer->dbGetFile( " idx = '". $prams['key'] ."' AND (( del_flag = 'Y' ) OR ( del_flag = 'N' ) ) " );
    
    if( $result['num_rows'] > 0 ) {
        # 파일 존재

        $row = $result['row'];
        $path = $row['path'];
        $file_name = $row['server_name'];
        $file_show_name = $row['origin_name'];

        if (isIE() === true) {
            $file_show_name = urlencode($file_show_name);
        }

        $file_path = $_SERVER['DOCUMENT_ROOT'].$path."/".$file_name;
        if($file_show_name && $file_path) {

            if(file_exists($file_path)) {
                header("Content-Type: doesn/matter");
                header("content-length: ". filesize($file_path));
                header("Content-Disposition: attachment; filename=" . $file_show_name);
                header("Content-Transfer-Encoding: binary");
                header("Pragma: no-cache");
                header("Expires: 0");
                
                
                if(is_file($file_path)) {
                    $fp = fopen($file_path, "r");
        
                    if(!fpassthru($fp)) {
                        fclose($fp);
                    }
                }
                
            } else {
                errorBack('파일이 존재하지 않습니다.');
            }
        } else {
            errorBack('파일이 존재하지 않습니다.');
        }

    } else {
        # 파일 없음
        errorBack('파일이 존재하지 않습니다.');
    }



    function isIE() {
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0') !== false) {
            return true;    // IE 나머지   
        }
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            return true;     
        }
        
        return false;
    }

    

?>