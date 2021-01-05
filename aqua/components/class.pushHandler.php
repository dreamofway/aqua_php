<?php
/**
 * ---------------------------------------------------
 * AQUA Framework storageLogHandler v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.03.30 - 이정훈
 * - 푸시 발송
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.03.30 - 이정훈
 *  - 앱 정보확인
 *  - 푸시 발송
 * 
*/
class pushHandler extends aqua {

	private $db = ''; # DB 연결
	private $table_push; 
	private $table_app; 
	private $table_member; 
	private $fcm_access_url; 
	private $fcm_access_key; 
	private $fcm_request_arr = []; # push 발송 배열
	private $push_result; # push 결과
	private $recipient_infos; # 수신 대상자
	private $app_id; 
	public $company_code; 
	public $push_title; 
	public $message; 

    function __construct() {
		
		$this->fcm_access_url = 'https://fcm.googleapis.com/fcm/send';
		$this->table_push = ' t_push_log '; 
		$this->table_app = ' t_app_Info '; 
		$this->table_member = ' t_company_members '; 
		$this->db = $this->connDB( 'masic' );


    }

	/*
	*	이탈 알림 발송
	*/
	public function storageLogWarningSend() {
	
		if( $this->push_title == '' ) {
			$this->push_title = '이탈경고';
		}
		$this->recipientInfo();		
		$this->getAppInfo();
		$this->send();
		$this->insertLog();

	}
	
	/**
	* 수신 대상자를 검색한다.
	*/
	private function recipientInfo() {

		if( empty( $this->company_code )  == true ){
			$this->errorHandler( 'pushHandler->recipientInfo()', '기업 코드가 설정되지 않았습니다. ' ); 
		}

		$query = " SELECT app_id, app_token FROM " . $this->table_member . " WHERE company_idx='". $this->company_code ."' AND ( use_flag='Y' ) AND ( del_flag='N' ) AND ( app_id <> '' ) AND ( app_token <> '' ) ";        

        $query_result = $this->db->execute( $query )['return_data'];
		
		if( $query_result['num_rows'] > 0  ) {

			$this->app_id = $query_result['rows'][0]['app_id'];
			$this->recipient_infos = $query_result['rows'];
			
		}
		
	}
	/*
	*	앱 정보를 확인한다.
	*/
	private function getAppInfo() {

		if( empty( $this->company_code )  == true ){
			$this->errorHandler( 'pushHandler->getAppInfo()', '기업 코드가 설정되지 않았습니다. ' ); 
		}

		if( empty( $this->app_id )  == true ){
			$this->errorHandler( 'pushHandler->getAppInfo()', 'app_id 값이 설정되지 않았습니다.' ); 
		}

		$query = " SELECT fcm_access_key FROM " . $this->table_app . " WHERE aos_package='". $this->app_id ."' ";        

        $query_result = $this->db->execute( $query )['return_data'];

		if( $query_result['num_rows'] > 0  ) {

			$this->fcm_access_key = $query_result['row']['fcm_access_key'];
			
		} else {
			$this->errorHandler( 'pushHandler->getAppInfo()', 'FCM token 값이 설정되지 않았습니다.' ); 
		}
		
	}

	/**
	*	푸시 발송 작업 전 처리 및 발송
	*/
	private function send() {

		foreach( $this->recipient_infos AS $idx=>$item ){
			$this->fcm_request_arr['registration_ids'][$idx] = $item['app_token'];
			$this->fcm_request_arr['data']['title'] = $this->push_title;
			$this->fcm_request_arr['data']['message'] = $this->message;
		}
		
		$this->push_result = jsonReturn( $this->requestFCM() );
			
	}
	

	/**
	*	푸시 발송 로그 적재
	*/
	private function insertLog() {

		$insert_query = ' INSERT INTO '. $this->table_push . ' 
                        (   
                            app_id
                            , title
                            , contents
                            , app_token
							, fcm_send_data
							, fcm_return_data
                            , send_date
                        ) VALUES ';

        $insert_add_query = [];


		foreach( $this->recipient_infos AS $idx=>$val ){            

            if( ( empty( $val ) == false )  ) {
                $insert_add_query[] = " ( 
                    '". $val['app_id'] ."'
                    ,'". $this->push_title ."'
                    ,'". $this->message ."'
                    ,'". $val['app_token'] ."'
					,'". jsonReturn ( $this->fcm_request_arr )."'
					,'". $this->push_result ."'
                    , NOW() 
                ) ";
            }

        }
        
        if( count($insert_add_query) > 0 ) {

            $insert_query .= join( ', ', $insert_add_query );
            $return_data = $this->db->execute( $insert_query );

        } 

	}
	
	/**
	*	FCM 서버로 전송
	*/
	private function requestFCM() {

		$request = [
            CURLOPT_URL => $this->fcm_access_url
            ,CURLOPT_POST => true
            ,CURLOPT_RETURNTRANSFER => true
            ,CURLOPT_CONNECTTIMEOUT => 10
            ,CURLOPT_TIMEOUT => 10
            ,CURLOPT_HTTPHEADER => [
                 'Authorization: key=' . $this->fcm_access_key
                , 'Content-Type: application/json'
            ]
			,CURLOPT_POSTFIELDS => jsonReturn( $this->fcm_request_arr )
        ];

        $request_ping = curl_init(); 
		
        curl_setopt_array($request_ping, $request); 
		$request_result = curl_exec($request_ping);

		if( curl_getinfo( $request_ping )['http_code'] == 200 ) {
	        $result_data['status'] = 'success';
	        $result_data['data'] = json_decode( $request_result , true ); 
		} else {
			$result_data['status'] = 'error';
		}

        curl_close($request_ping);

		return $result_data;

	}
	

	
}
?>