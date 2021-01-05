<?php
/**
 * ---------------------------------------------------
 * AQUA Framework mySqlHandler  v1.0.0
 * ---------------------------------------------------
 * History
 * ---------------------------------------------------
 * 
 * [v1.0.0] 2020.02.19 - 이정훈
 *  - execute() 개발
 *  - dbClose() 개발
 * 
 * ---------------------------------------------------
*/
class mySqlHandler extends aqua {
    
    private $dbhost = null; # db 서버 주소
    private $dbuser	= null; # db 계정
    private $dbpasswd = null; # db 비밀번호
    private $dbname	= null; # db 명
    private $dbconn	= null; # db mysqli의 인스턴스 객체
    private $db_errors = []; # db 에러 적재     
    private $transaction_flag = false;	# 트랜잭션 시작/종료 
    
    function __construct( Array $db_info ) {

        // echo '<br> instance mySqlHandler <br>';

        $this->dbhost = $db_info['host'];
        $this->dbuser = $db_info['user'];
        $this->dbpasswd = $db_info['dbpasswd'];
        $this->dbname = $db_info['dbname'];


        # 데이터베이스 연결 및 언어 세팅.
        @$this->dbconn = new mysqli( $this->dbhost, $this->dbuser, $this->dbpasswd, $this->dbname );	

        # 접속에러 체크.
        if( $this->dbconn->connect_errno ) {

            $error_contents = '';
            $error_contents .= 'DB Connection Error <br /><br />';
            $error_contents .= 'Error No : '. $this->dbconn->connect_errno . '<br /><br />';
            $error_contents .= 'Error Message : '. $this->dbconn->connect_error . '<br /><br />';

            exit( $this->errorResultForm( 'mySqlHandler->__construct', $error_contents) );
            
        } else {

            $this->dbconn->query("set names utf8");

        }
        
    }

    /**
     * 처리 : query method 를 실행하고 결과 형식에 따라 결과물을 돌려준다.
     * 결과의 형태
     *  1. object : 연관배열
     *  2. boolean : query method 실행결과 값(boolean)
     */
    public function execute($query) {

        if(Empty( $query ) == true) {            
            # 에러 출력 후 종료. 
            exit( $this->errorResultForm( 'mySqlHandler->execute', 'query 변수 공백' ) );
        }

        $return_val = [];
        # 쿼리실행.
        $result = $this->dbconn->query( $query );
        
        # 쿼리 에러확인
        if( $this->dbconn->errno > 0) {

            $error_contents = '';
            $error_contents = '<br /><br />[ mySqlHandler->execute ] query error <br /><br /><hr />';
            $error_contents .= '<br /><br />'. $query . '<br /><br />';
            $error_contents .= '<hr />';
            $error_contents .= '<br />Error No : '. $this->dbconn->errno . '<br /><br />';
            $error_contents .= 'Error Message : '. $this->dbconn->error . '<br /><br />';

            if( $this->transaction_flag == true ) {
                $this->dbconn->rollback();
            } 
            
            exit( $this->errorResultForm( 'mySqlHandler->execute', $error_contents) );

        }

        # 쿼리결과 확인.
        if( gettype( $result ) === "object" ) {

            $get_arr = [];
            $get_row = [];
            $return_val["state"] = true;
            $return_val["return_data"] = [];

            $return_val["return_data"]["num_rows"] = $result->num_rows;

            if( $this->dbconn->affected_rows > 1 ) {

                # 결과물이 여러개인 경우.  
                $loop_cnt = 0;              
                while($row = $result->fetch_assoc()) {
                     
                    foreach( $row AS $key=>$val ) {

                        $get_arr[ $loop_cnt ][ $key ] = htmlspecialchars_decode( $val  );

                    }
                    
                    $loop_cnt++;
                }

                $return_val["return_data"]["row"] = $get_arr;
                $return_val["return_data"]["rows"] = $get_arr;

            } else {
                
                # 결과물이 하나인 경우.
                $row = $result->fetch_assoc();
                
                if( $row == null ) {
                    $return_val["return_data"]["row"] = [];
                    $return_val["return_data"]["rows"] = [];
                } else {
                    
                    
                    foreach( $row AS $key=>$val ) {

                        $get_row[ $key ] = htmlspecialchars_decode( $val );
                        $get_arr[ 0 ][ $key ] = htmlspecialchars_decode( $val );

                    }

                    $return_val["return_data"]["row"] = $get_row;
                    $return_val["return_data"]["rows"] = $get_arr;
                }
                
            }

           

            $result->free(); # $result 관련 메모리 해제 

            

        } else {

            if($result == true){

                $return_val["state"] = true;
                $return_val["return_data"]["insert_id"] = $this->dbconn->insert_id; # 데이터 베이스 실행 후 insesrt_id 값을 대입.

            } else {

                $return_val["state"] = true;

            }
            
        }
        
        return $return_val;

    } //-- function execute
    
    /**
     * insert 처리
     */
    public function insert( $arg_table, $arg_data ) {

        $make_query = "";
        $make_items = [];

        if( empty( $arg_table ) ) {
            exit( $this->errorResultForm( 'mySqlHandler->insert', 'table 정보를 입력해주셔야 합니다.' ) );
        }

        if( is_array( $arg_data ) == false ) {
            exit( $this->errorResultForm( 'mySqlHandler->insert', '데이터 정보는 배열 형식으로 입력해주셔야 합니다.' ) );
        }

        $make_query = " INSERT INTO ". $arg_table ." SET ";

        $make_query .= $this->makeSetQuery( $arg_data );

        return $this->execute( $make_query );

    }

    /**
     * update 처리
     */
    public function update( $arg_table, $arg_data, $arg_where  ){

        if( empty( $arg_table ) ) {
            exit( $this->errorResultForm( 'mySqlHandler->update', 'table 정보를 입력해주셔야 합니다.' ) );
        }

        if( is_array( $arg_data ) === false ) {
            exit( $this->errorResultForm( 'mySqlHandler->update', '데이터 정보는 배열 형식으로 입력해주셔야 합니다.' ) );
        }

        if( empty( $arg_where ) === true ) {
            exit( $this->errorResultForm( 'mySqlHandler->update', '수정 조건절은 필수 입력값 입니다.' ) );
        }

        $make_query = " UPDATE ". $arg_table ." SET ";

        $make_query .= $this->makeSetQuery( $arg_data );

        $make_query .= " WHERE " . $arg_where;

        return $this->execute( $make_query );

    }

    /**
     * delete 처리
     */
    public function delete( $arg_table, $arg_where  ){

        if( empty( $arg_table ) ) {
            exit( $this->errorResultForm( 'mySqlHandler->delete', 'table 정보를 입력해주셔야 합니다.' ) );
        }

        if( empty( $arg_where ) === true ) {
            exit( $this->errorResultForm( 'mySqlHandler->delete', '조건절은 필수 입력값 입니다.' ) );
        }

        $make_query = " DELETE FROM ". $arg_table ." WHERE " . $arg_where ;

        return $this->execute( $make_query );

    }
    /**
     * insert / update 변경 항목 생성 
     */
    private function makeSetQuery( $arg_set_data ){

        foreach ($arg_set_data as $key=>$value):      
        
            
            // if( empty( $value ) == false ){
            //     $value = strtoupper( $value );
            // }

            if( ( preg_replace('/\s+/', '', $value ) == 'NOW()') || ( preg_replace('/\s+/', '', $value ) == 'NULL' ) ) {
                $make_items[] = $key ."= ". $value;
            } else {
                $make_items[] = $key ."= '". $value . "' ";
            }
        endforeach; 
        
        return join( ', ', $make_items );

    }

    /**
     * 트랜잭션을 시작한다.
     */
    public function runTransaction(){

        $this->transaction_flag = true;
        $this->dbconn->query( 'SET AUTOCOMMIT=0' );        
        @$this->dbconn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    }
     /**
     * 트랜잭션을 종료한다.
     */
    public function stopTransaction(){
        
        $this->transaction_flag = false;

        if( $this->dbconn->errno > 0 ) {
            $this->dbconn->rollback();
        } else {
            $this->dbconn->commit();            
        }

        $this->dbconn->query( 'SET AUTOCOMMIT=1' );
        
    }
    

    public function dbClose(){

        $this->dbconn->close();
    }
    
}

?>