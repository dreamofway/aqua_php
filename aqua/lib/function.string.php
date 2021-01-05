<?php
	/**********************************************************************************************

		------------------------------------------ 최초생성 ------------------------------------------
		# 목적 : 문자열 처리 함수모음
		# 일시 : 2016.03.17 16:42
		# 생성자 : 이정훈
		------------------------------------------ 작업내역 ------------------------------------------
		mb_str_cut : 문자열을 자른다.
		DateType : db datetime 형식을 받아 파싱한다.
		getOnlyNumberData : 문자열에서 숫자형식만 가져온다.
		mobileNumberFormat : 휴대폰 번호 자리수에 따라 - 삽입
		AddHeadZero : 제한수 보다 작은 수 앞에 0삽입.
		make_rand_str : 무작위 문자열 생성.
		hash_conv : 스트링을 sha256 형식으로 변경하여 리턴한다.
		get_text : text 형식으로 변환하여 리턴한다.
		--------------------------------------------------------------------------------------------

	**********************************************************************************************/
	
	/**
		문자열을 UTF-8 데이터형식을 기준으로 자른다.
	*/
	function mb_str_cut($string, $length, $tail) {
		if(mb_strlen($string, "UTF-8") > $length) {
			$result_val = mb_substr($string, 0, $length, "UTF-8") . $tail;
		} else {
			$result_val = $string;
		}

		return $result_val;
	}


	/**
		- db datetime 형식을 받아 파싱한다.
		datestr : 날짜
		type : 변경 형식 결정
	*/
	function dateType($datestr, $type) {
		if($datestr == "") {
			return;
		}

		list($typeY, $typeM, $typeD, $typeH, $typeMI, $typeSE) = preg_split("/[- :]/", $datestr);

		If (strlen($typeM) == 1) $typeM	= "0".$typeM;
		If (strlen($typeD) == 1) $typeD = "0".$typeD;
		If (strlen($typeH) == 1) $typeH	= "0".$typeH;
		If (strlen($typeMI) == 1) $typeMI = "0".$typeMI;
		If (strlen($typeSE) == 1) $typeSE	= "0".$typeSE;

		switch($type) {
			case 1 : {
				$ReturnDateType = $typeY."-".$typeM."-".$typeD." ".$typeH.":".$typeMI.":".$typeSE;
				break;
			}
			case 2 : {
				$ReturnDateType	= $typeY."년 ".$typeM."월 ".$typeD."일";
				break;
			}
			case 3 : {
				$ReturnDateType	= $typeY."년 ".$typeM."월 ".$typeD."일 ".$typeH."시 ".$typeMI."분";
				break;
			}
			case 4 : {
				$ReturnDateType	= $typeY."/".$typeM."/".$typeD;
				break;
			}
			case 5 : {
				$ReturnDateType	= $typeY.$typeM.$typeD.$typeH.$typeMI.$typeSE;
				break;
			}
			case 6 : {
				$ReturnDateType	=  $typeY."-".$typeM."-".$typeD;
				break;
			}
			case 7 : {
				$ReturnDateType	= $typeY.$typeM.$typeD;
				break;
			}
			case 8 : {
				$ReturnDateType	= $typeY.".".$typeM.".".$typeD;
				break;
			}
			case 9 : {
				$ReturnDateType	= $typeY."년 ".$typeM."월 ".$typeD."일 ".$typeH."시 ".$typeMI."초";
				break;
			}
			case 10 : {
				$date_time = explode(" ", $datestr);
				$date = explode("-",$date_time[0]);
				$time = explode(":",$date_time[1]);
				unset($date_time);
				list($year, $month, $day)=$date;
				list($hour,$minute,$second)=$time;
				$ReturnDateType =  mktime(intval($hour), intval($minute), intval($second), intval($month), intval($day), intval($year));
				break;
			}
			case 11 : {
				$ReturnDateType	= substr($typeY,2,2).".".$typeM.".".$typeD;
				break;
			}
			case 12 : {
				$ReturnDateType	= $typeY."년 ".$typeM."월";
				break;
			}
			case 13 : {
				$ReturnDateType = $typeY.".".$typeM.".".$typeD." ".$typeH.":".$typeMI;
				break;
			}
			case 14 : {
				$ReturnDateType = $typeM."-".$typeD;
				break;
			}
			default : {
				$ReturnDateType	= $datestr;
			}
		}

		return $ReturnDateType;

	}


	/**
		문자열에서 숫자형식만 가져온다.
	*/
	function getOnlyNumberData($str)  {
		$rtnValue   =   preg_replace("/[^0-9]/", "", $str);
		return $rtnValue;
	}   // end function


	/**
		휴대폰 번호 자리수에 따라 - 삽입
	*/
	function mobileNumberFormat($mobileNumber)  {
		$mobileNumber   =   preg_replace("/[^0-9]*/s", "", $mobileNumber);

		switch(strlen($mobileNumber)) {
			case 10:
				$rtn_mobile   =   substr($mobileNumber,0,3)."-".substr($mobileNumber,3,3)."-".substr($mobileNumber,6,4);
				break;
			case 11:
				$rtn_mobile   =   substr($mobileNumber,0,3)."-".substr($mobileNumber,3,4)."-".substr($mobileNumber,7,4);
				break;
			case 0:
				$rtn_mobile   =   "";
				break;
			default:

			$rtn_mobile   =   $mobileNumber;
		}//   end switch

		return $rtn_mobile;
	}//   end function   

	/**
		제한수 보다 작은 수 앞에 0삽입.
	*/
	function AddHeadZero($Length, $String) {
		$StrZero   =   "";
		switch(STRLEN($String))	{
			case $Length:
			RETURN $String;
			break;
		default:
			for($Cnt = 0; ($Length - STRLEN($String)) > $Cnt ; $Cnt++) {
				$StrZero   =   $StrZero."0";
			}//   end function
			$String      =   $StrZero.$String;
			RETURN $String;
		}//   end switch
	}//   end Function
	


	/**
		- 문자열을 무작위로 만든다.
	*/
	function make_rand_str($length) {
		#임시비밀번호 생성 함수
		$ran= "";
		for( $i=0; $i<$length; $i++) {

			 if( rand(0,1) ) $ran .= rand( 0, 9 ); //숫자

			 else $ran .= chr(rand( 97, 122 )); //영어소문자

		}

		return $ran;
	}
	
    /*
        text 형식으로 변환하여 리턴한다.
    */
    function get_text($str, $html=0) {
        
        /* 3.22 막음 (HTML 체크 줄바꿈시 출력 오류때문)
        $source[] = "/  /";
        $target[] = " &nbsp;";
        */
    
        // 3.31
        // TEXT 출력일 경우 &amp; &nbsp; 등의 코드를 정상으로 출력해 주기 위함
        if ($html == 0) {
            $str = html_symbol($str);
        }
    
        $source[] = "/</";
        $target[] = "&lt;";
        $source[] = "/>/";
        $target[] = "&gt;";
        //$source[] = "/\"/";
        //$target[] = "&#034;";
        $source[] = "/\'/";
        $target[] = "&#039;";
        //$source[] = "/}/"; $target[] = "&#125;";
        if ($html) {
            $source[] = "/\n/";
            $target[] = "<br/>";
        }
    
        return preg_replace($source, $target, $str);
    }
    
    /*
        HTML SYMBOL 변환
        &nbsp; &amp; &middot; 등을 정상으로 출력
    */
    
    function html_symbol($str) {
        return preg_replace("/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i", "&#038;\\1;", $str);
    }

	/**
	* json string 변환 후 exit 실행
	*/
	function jsonExit( $arg_arr  ){
		exit( json_encode( $arg_arr, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	* json string 변환 후 반환
	*/
	function jsonReturn( $arg_arr  ){
		return json_encode( $arg_arr, JSON_UNESCAPED_UNICODE );
	}

?>