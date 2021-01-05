<?php
/**
 * ---------------------------------------------------
 * AQUA Framework access log 적재  v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.0.0]
 * - 접근 기록을 적재한다.
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.04.17 - 이정훈
 *  - fileUpload() 개발
 * 
 * ---------------------------------------------------
*/
class accessLog extends aqua {
	
	private $device;
	private $access_ip;
	private $access_url;
	private $browser_info;	
	private $referer;
	private $host;
	private $request_uri;
	private $os;
	private $db;
	private $db_key;
	private $access_group;
	private $access_company_idx;
	private $exception_url;

    function __construct() {
		
		if( defined( 'DEFAULT_DB' ) == false ) {
			$this->errorHandler( 'accessLog()', 'DEFAULT_DB가 정의되지 않았습니다.' ); 
		} else {
			$this->db_key = DEFAULT_DB;
		}
		
		if( defined( 'COMPANY_CODE' ) == true ) {
			$this->access_group = 'O';
			$this->access_company_idx = COMPANY_CODE;
		} else {
			$this->access_group = 'I';
			$this->access_company_idx = '';
		}
		
		# 아이피 정보
		$this->access_ip = $this->getIP();

		# 이전 페이지 확인
		$this->referer = $_SERVER['HTTP_REFERER'];

		# http host
		$this->host = $_SERVER['HTTP_HOST'];

		# 접근 url
		$this->request_uri = $_SERVER['REQUEST_URI'];

		# 기기 정보 추출
		$this->device = $this->checkDevice();

		# 브라우저 확인
		$this->browser_info = $this->getAccessBrowser();

		# 운영체제 정보 확인
		$this->os = $this->getOS();

		# 로그 제외 url string 설정
		$this->exception_url = [
			'/api/receive_temperature/mffood.php'
			,'/api/receive_temperature/sandle.php'
		];

		//$this->exception_url = [];
    }

	/**
	* 접속 현황 파악
	*/
	public function execute( $arg_task ){


		// echoBr( $_SERVER );
		
		// echoBr( $this->os );
		// echoBr( $this->device );
		// echoBr( $this->access_ip );		
		// echoBr( $this->browser_info );
		// echoBr( $this->host );
		// echoBr( $this->referer );
		// echoBr( $this->request_uri );
		
		foreach( $this->exception_url AS $idx=>$val ) {
			if( strpos( $this->request_uri, $val ) > -1 ) {
				return;
			}
		}
		
		$this->dbconn();

		$this->db->insert( 't_access_log', [
			'access_type' => $arg_task
			,'access_group' => $this->access_group
			,'access_company_idx' => $this->access_company_idx
			,'access_user_idx' => getAccountInfo()['idx']
			,'access_os' => $this->os
			,'access_device' => $this->device
			,'access_ip' => $this->access_ip
			,'access_browser' => $this->browser_info
			,'access_host' => $this->host
			,'access_url' => $this->request_uri
			,'access_ref' => $this->referer
			,'user_agent' => apache_request_headers()['User-Agent']
			,'access_date' => 'NOW()'
		]);
		

	}

	/**
	 * 데이터 베이스 연결
	 */
	private function dbconn() {
        if( empty( $this->db ) == true ) {
            $this->db = $this->connDB( $this->db_key );
        }
	}

	/**
	* 접속자의 접속 기기 정보 확인	
	*/
	private function checkDevice(){
		
		$result = '';
		$use_agent = apache_request_headers()['User-Agent'];	
		$pattern = '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS|iPad|okhttp)/';
		$match_result;

		preg_match($pattern, $use_agent, $match_result);
	
		if( count( $match_result ) > 0 ) {
			$result = 'mobile';
		} else {
			if( empty($use_agent) == true ) {
				$result = 'other';
			} else {
				$result = 'pc';
			}
		}
		
		return $result;

	}

	/**
	 * 접속자의 브라우저를 확인한다
	 */
	private function getAccessBrowser(){

		$result = '';
		$use_agent = apache_request_headers()['User-Agent'];	
		$pattern = '/(Firefox|Chrome|Safari|OPR|Swing|msie|konq|Nets|Edge|Whale|okhttp)/i';
		$match_result;
		$check_state = [
			'Chrome'=>false
			,'Safari'=>false
		];

		preg_match_all($pattern, $use_agent, $match_result);
		
		// echoBr( $use_agent );
		// echoBr( $match_result );

		if( empty( $use_agent ) == false ) {

			if( count( $match_result[0] ) > 0 ) {

				foreach( $match_result[0] AS $idx=>$val){
					
					if( $val == 'Safari') {
						$check_state['Safari'] = true;
					}
	
					if( $val == 'Chrome') {
						$check_state['Chrome'] = true;
					}
	
					$result = $val;
				}
	
				if( ( $check_state['Safari'] == true ) && ( $check_state['Chrome'] == true ) ) {
					if( $result == 'Chrome' || $result == 'Safari' ) {
						$result = 'Chrome';
					}
				}
	
			} else {
				$result = 'IE';
			}

		} else {
			$result = 'other';
		}
		
		
		return $result;

	}

	/**
	 * 접속자 OS정보 확인
	 */
	private function getOS(){

		$result = '';
		$use_agent = apache_request_headers()['User-Agent'];			
		$match_result;

		$pattern = '/(Windows NT .*?\.(.?)|Windows 95|Win98|Macintosh|Mac_PowerPC|mac|Linux|Wget|Unix|Android)/';	

		preg_match_all($pattern, $use_agent, $match_result);
		
		foreach( $match_result AS $idx=>$item) {
			foreach( $item AS $item_idx=>$val) {
				if( ( empty( $val ) == false ) && ( is_numeric( $val ) == false ) ) {
					$result = $val;
				}				
			}
		}

		return $result;
		
	}
   
}
?>