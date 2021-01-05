<?php
/**
 * ---------------------------------------------------
 * AQUA Framework 기본 설정
 * ---------------------------------------------------
 * 필수 옵션 설명
 * ---------------------------------------------------
 * 
 * 변수 : $_aqua_config
 * 변수 유형 : array
 * $_aqua_config['url_rewrite'] : RewriteEngine 을 사용할 경우 true 로 설정합니다.
 * $_aqua_config['view'] : url_rewrite = true 일 때 사용되는 view 필수 옵션 view 파일 내에서 변수 처럼 사용 가능
 * $_aqua_config['view']['ver'] : 개발시 /aqua/views/ 하위에 생성해야하는 사용자 html 파일 구분용도의 명칭 (실제 만든 폴더명과 일치시키기만 하면 됨)
 * $_aqua_config['view']['use_layout'] : true / false - html 파일을 하나의 파일에서 유동적으로 구조를 변경할 수 있는 구조를 사용할 것인가에 대한 옵션 
 * $_aqua_config['view']['use_cdn'] : true / false - CDN 서버를 사용하는지에 대한 옵션 이 값에 때라 class.aqua 에서 view 를 생성 할 때 $img_path 변수 경로가 달라짐니다. 
 * $_aqua_config['view']['img_path'] : 공통 이미지 경로 use_cdn = true - 외부 url / use_cdn = false - 내부 /aqua/views/[ver]/ 하위의 경로를 적어줍니다 ex) '/public/img'
 * $_aqua_config['view']['favicon_path'] : 파비콘 이미지 경로 use_cdn = true - 외부 url / use_cdn = false - 내부 /aqua/views/[ver]/ 하위의 경로를 적어줍니다 ex) '/public/img/favicon.ico'
 * $_aqua_config['view']['meta_title'] : mata tag 의 값
 * $_aqua_config['view']['meta_description'] : mata tag 의 값
 * $_aqua_config['view']['meta_keywords'] : mata tag 의 값
 * $_aqua_config['view']['ogp_title'] : sns 공유시 표시될 제목
 * $_aqua_config['view']['ogp_stitle_name'] : sns 공유시 표시될 부제목
 * $_aqua_config['view']['ogp_image'] : sns 공유시 노출될 대표 이미지 경로
 * $_aqua_config['view']['ogp_url'] : sns 공유시 접근 url
 * $_aqua_config['db'][{DB 구분 키값}] : 접속 db 정보 설정 {DB 구분 키값} 은 DB 접속시 인자값으로 전달 해야합니다.
 * $_aqua_config['db'][{DB 구분 키값}]['host'] 
 * $_aqua_config['db'][{DB 구분 키값}]['user']
 * $_aqua_config['db'][{DB 구분 키값}]['dbname']
 * $_aqua_config['db'][{DB 구분 키값}]['dbpasswd']
 * 
 * ---------------------------------------------------
*/

define('UPLOAD_PATH', '/upload'); # 파일 업로드 위치
// define('QR_CODE_PATH', UPLOAD_PATH. '/company/qrcode'); # qrcode 업로드 위치


?>