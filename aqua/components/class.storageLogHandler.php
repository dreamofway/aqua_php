<?php
/**
 * ---------------------------------------------------
 * AQUA Framework storageLogHandler v1.0.0
 * ---------------------------------------------------
 * 설명
 * ---------------------------------------------------
 * 
 * [v1.0.0]
 * - 저장고 온도를 수신한다.
 * 
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.03.30 - 이정훈
 *  - 저장고 온도를 수신한다.
 * 
*/
class storageLogHandler extends aqua {
	
	private $model;
	private $push;
	private $company_code;
	private $storage_info = [];
	private $response_error_arr = [];
	private $insert_data_arr = [];
	public $push_send_condition = [];  #  condition: day/min 

    function __construct() {
		
		$this->model = $this->new('haccpModel');
		$this->push = $this->new('pushHandler');
		$this->push_send_condition = [
			'condition' => 'min'
			,'minute' => 0
			,'count' => 0
		];
    }

	/*
	*	포스트 전송 방식으로 전달받아 데이터 가공 함.
	*/
	public function getPostData( $arg_data ) {
		
		unset( $arg_data['page'] );
		unset( $arg_data['list_rows'] );

		foreach( $arg_data AS $key=>$val ) {

			if( ( empty( $val ) == false )  && ( is_numeric( $val ) == true )  ) {
				# 해당 저장고 정보 요청
				
				$haccp_result = $this->model->getStorage( " AND ( storage_code = '".$key."' ) " );

				if( $haccp_result['num_rows'] == 0 ){

					$this->response_error_arr[] = 'code =>' . $key . ' 의 값에 해당하는 저장고 정보가 존재하지 않습니다.' ;

				} else {

					$haccp_result = $haccp_result['row'];

					$this->storage_info[ $key ]['storage']['storage_idx'] = $haccp_result['storage_idx'];
					$this->storage_info[ $key ]['storage']['company_idx'] = $haccp_result['company_idx'];
					$this->storage_info[ $key ]['storage']['storage_code'] = $haccp_result['storage_code'];
					$this->storage_info[ $key ]['storage']['storage_name'] = $haccp_result['storage_name'];
					$this->storage_info[ $key ]['storage']['min_temperature'] = $haccp_result['min_temperature'];
					$this->storage_info[ $key ]['storage']['max_temperature'] = $haccp_result['max_temperature'];
					$this->storage_info[ $key ]['insert_data']['temperature'] = $val;
					$this->storage_info[ $key ]['insert_data']['storage_idx'] = $haccp_result['storage_idx'];
					$this->storage_info[ $key ]['insert_data']['company_idx'] = $haccp_result['company_idx'];
					$this->storage_info[ $key ]['insert_data']['storage_code'] = $haccp_result['storage_code'];
					$this->storage_info[ $key ]['insert_data']['storage_name'] = $haccp_result['storage_name'];
					$this->storage_info[ $key ]['insert_data']['min_temperature'] = $haccp_result['min_temperature'];
					$this->storage_info[ $key ]['insert_data']['max_temperature'] = $haccp_result['max_temperature'];
					$this->storage_info[ $key ]['insert_data']['reg_date'] = 'NOW()';
					
					$this->company_code = $haccp_result['company_idx'];
				}
				

			} else {				
				$this->response_error_arr[] = '파라미터 '. $key .' : ' . $val . ' 의 허용되지 않은 형식입니다.' ;
			}
			
		}
		
		if( count( $this->response_error_arr )  > 0 ) {
			$result['status'] = 'error';
			$result['msg'] = $this->response_error_arr;
			echo( jsonReturn( $result ) );
		} else {
			
			if( count( $this->storage_info ) > 0 ) {

				$result['status'] = 'success';
				$result['msg'] = '저장완료';
				echo( jsonReturn( $result ) );


				# 온도체크
				$this->checkTemperature();
					
				# 로그 적재
				$this->insertStorageLog();
				
				# 푸시발송 조건 확인 및 푸시 발송
				$this->checkPushCondition();

			} else {
				$result['status'] = 'fail';
				$result['msg'] = '전송값 없음';
				echo( jsonReturn( $result ) );
			}
			
		}

		
		
	}

	/*
	*	온도체크
	*/
	private function checkTemperature(){

		foreach( $this->storage_info AS $key=>$item ){

			if( ( $item['insert_data']['temperature'] < $item['storage']['min_temperature'] ) || ( $item['insert_data']['temperature'] > $item['storage']['max_temperature'] ) ) {
				# 이탈 발생
				$this->storage_info[ $key ]['insert_data']['temp_state'] = 'W';
			} else {
				# 정상
				$this->storage_info[ $key ]['insert_data']['temp_state'] = 'N';
			}

		}

	}

	/*
	*	저장고 온도 로그 적재
	*/
	private function insertStorageLog(){

		foreach( $this->storage_info AS $key=>$item ){
			# 로그 적재
			$this->model->insertStorageLog( $this->storage_info[ $key ]['insert_data'] );
		}

	}

	/*
	*	푸시발송 조건 확인 및 푸시 발송 요청
	*/
	private function checkPushCondition(){
		
		$where = " AND ( temp_state = 'W'  ) ";

		switch( $this->push_send_condition['condition'] ) {
			case 'day' : {
				$where .= " AND  ( reg_date >= ( CONCAT( CURRENT_DATE() , ' 00:00:00' ) ) )  ";
				break;
			}
			case 'min' : {
				$where .= " AND ( reg_date >= DATE_SUB( NOW(), INTERVAL ". $this->push_send_condition['minute'] ." MINUTE ) ) ";
			}
			default : {
			}
		}
		
		
		$push_msg = ' 저장고 온도이탈 발생 - ';
		$push_msg_arr = [];
		foreach( $this->storage_info AS $key=>$item ){

			$query = $where . " AND storage_code ='". $item['storage']['storage_code'] ."' ";
			
			if( $this->model->getStorageLogCount( $query )  >  $this->push_send_condition['count'] ) {
				$push_msg_arr[] =  $item['storage']['storage_name'];

				

			}
		}
	
		if( count( $push_msg_arr ) > 0 ) {

			$this->push->company_code = $this->company_code;
			$this->push->message = $push_msg . join(',' , $push_msg_arr);
			$this->push->storageLogWarningSend();
		}

		
	}

}
?>