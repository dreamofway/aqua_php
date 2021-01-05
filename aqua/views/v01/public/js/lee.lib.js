/**********************************************************************************************

        ------------------------------------------ 최초생성 ------------------------------------------
        # 목적 : 공통 js 스크립트 모음.
        # 일시 : 2016.03.17 18:34
        # 생성자 : 이정훈
        ------------------------------------------ 작업내역 ------------------------------------------
        init_loading_dom : body 에 로딩 이미지영역 삽입
        init_jqueryUi_dialog_dom : body 에 jquery dialog 영역 삽입
        jqueryAjaxCall : jquery ajax 함수를 이용한 custom 함수 
        jQuery_dialog : jqueryui. dialog 함수를 이용한 custom 함수 
        jQueryDialog : jqueryui. dialog 함수를 이용한 custom 함수 ver2
        addTenUnder : 10보다 작은 정수에 0을 붙여 string 형태로 변환.
        geolocation : 웹 위치 확인 함수
        get_byte : string byte 수를 반환한다.
        wm_strcut : element 너비에 맞게 글자수를 자른다.
        cut_str : 문자열 자르는 함수.
        resizeImg : 이미지 리사이즈
        numMoneyType : 숫자를 통화형식으로 변환
        input_RegexCheck : 정규식 체크함수.
        get_urlParam : url의 파라미터 값을 배열로 반환
        networkCheck : 네트워크 상태를 체크한다.
        alert_login : 로그인 유도 (수정 필요)
        share_sns : 소셜네트워크 공유 함수
        clipboard_copy : 클립보드 데이터 복사
        is_ie : 익스플로러 체크
        show_blockUI : jqueryui - blockui를 이용한 함수
        loading_call : 로딩 이미지 호출
        getBrowserType : 브라우져 종류를 반환한다.
        ajaxProcessing :  로딩이미지
        --------------------------------------------------------------------------------------------

*********************************************************************************************/

function init_loading_dom() {
	var loading_html = "";
	
	loading_html += "<div id=\"_loading_run_area\"  style=\"display:none;\" > ";
    loading_html += "<img src=\"/aqua/views/v01/public/images/ajax_loading.gif\" style=\"width:64px;border-radius:100%\" >";
    loading_html += "</div>";
    
	jQuery("body").append(loading_html);
}

function init_jqueryUi_dialog_dom() {
	var html = "";

	html += "	<div id=\"jQuery_dialog\" title=\"\" style=\"display:none\">";
	html += "		<p id=\"jQuery_dialog_p\" ></p> ";
	html += "	</div> ";
	
	jQuery("body").append(html);
}

function init_jqueryUi_dialog_dom_write() {
	var html = "";

	html += "	<div id=\"jQuery_dialog\" title=\"\" style=\"display:none\">";
	html += "		<p id=\"jQuery_dialog_p\" ></p> ";
	html += "	</div> ";
	
	document.write(html);
}

/*****************************************************************
ajax 콜백형 함수.
type : 전송방식(get, post)
url : 요청을 할 페이지
dataType : 리턴타입
paramData : 전송데이터 
callBack : 전송이 완료되었을 때 호출 할 함수
callBackData : 전송이 완료 후 호출할 함수에 인자값으로 넘겨줄 데이터
*****************************************************************/
function jqueryAjaxCall( params ) {
	
    var async = "";
    
    if( params["async"] == "undefined" ) {
        async = true;     
    } else {
        async = params.async;
    }
    
	jQuery.ajaxSetup({cache:false});		//--> ajax의 캐싱방지	
	jQuery.ajax({
		type: params.type,
		url: params.url,
		data: params.paramData,
		dataType: params.dataType,
		async: async,
		success:function(get_data){	
			if(typeof(params.callBack) == "function"  ) {
				params.callBack(get_data, params.paramData.callBackData);
			}
		},
		error : function(code, status, error) {

			// alert("jqueryAjaxCall : "+params.url+" \n\n" + code.status + " \n\n\status :  "+ status +" \n\n\ error : "+ error +" \n\n\ responseText : "+ code.responseText);
			
			console.log("jqueryAjaxCall : "+params.url+" \n\n" + code.status + " \n\n\status :  "+ status +" \n\n\ error : "+ error +" \n\n\ responseText : "+ code.responseText)
			
		}
	});
}

/*****************************************************************
jquery-ui wiget dialog 사용 함수.
type : 기본 메시지 전달 /  alert : 알림 / confirm : 의사확인
text : 사용자에게 보여질 문구
callback_fn : confirm 타입의 확인 버튼 클릭시 실행될 함수.
*****************************************************************/
function jQuery_dialog(type, text, callback_fn) {
	
	var buttons_option = "";
	var dialog_title = "";
	var dialog_icon = "";

	switch(type) {

		case "confirm" : {
			
			dialog_title = "확인";

			dialog_icon = " <span class=\"ui-icon ui-icon-circle-check\" style=\"float: left; margin-right: .3em;\"></span>";

			buttons_option = {
				"확인": function() {
					if(typeof(callback_fn) == "function") {
						callback_fn();
						jQuery( this ).dialog( "close" );
					} else {
						jQuery( this ).dialog( "close" );
					}
				},
				"취소": function() {
					jQuery( this ).dialog( "close" );
				}
			};

			break;
		}
		case "alert" : {
			
			dialog_title = "알림";

			dialog_icon = " <span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>";

			buttons_option = {
				"확인": function() {
					if(typeof(callback_fn) == "function") {
						callback_fn();
						jQuery( this ).dialog( "close" );
					} else {
						jQuery( this ).dialog( "close" );
					}
				}
			};

			break;
		}
		default : {
			dialog_title = "메세지";

			dialog_icon = " <span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>";


			buttons_option = {};
		}

	}

	console.log("lenght : "+ jQuery("#jQuery_dialog").lenght );

	jQuery("#jQuery_dialog").attr("title", dialog_title);

	jQuery( "#jQuery_dialog" ).dialog({
		autoOpen: false,
		closeOnEscape: true,
		modal: true,
		show: {
			effect: "bounce",
			duration: 500
		},
		hide: {
			effect: "blind",
			duration: 200
		},
		buttons: buttons_option
	});

	
	jQuery("#jQuery_dialog_p").html(dialog_icon + text);
	jQuery( "#jQuery_dialog" ).dialog( "open" );

}



/*****************************************************************
jquery-ui wiget dialog 사용 함수.
type : 기본 메시지 전달 /  alert : 알림 / confirm : 의사확인
text : 사용자에게 보여질 문구
callback_fn : confirm 타입의 확인 버튼 클릭시 실행될 함수.
*****************************************************************/
function jQueryDialog( arg_option ) {
    
    var buttons_option = "";
    var dialog_title = "";
    var dialog_icon = "";

    switch(arg_option["type"]) {

        case "confirm" : {
            
            dialog_title = "확인";

            dialog_icon = " <span class=\"ui-icon ui-icon-circle-check\" style=\"float: left; margin-right: .3em;\"></span>";

            buttons_option = {
                "확인": function() {
                    if(typeof(arg_option["callback_yes"]) == "function") {
                        
                        jQuery( this ).dialog( "close" );
                        
                        setTimeout(function(){
                            arg_option["callback_yes"]();
                        },100);                       
                        
                        
                    } else {
                        
                        jQuery( this ).dialog( "close" );
                        
                    }
                },
                "취소": function() {
                    if(typeof(arg_option["callback_no"]) == "function") {
                        
						setTimeout(function(){
                            arg_option["callback_no"]();
                        },100);

                        jQuery( this ).dialog( "close" );
                        
                    } else {
                        
                        jQuery( this ).dialog( "close" );
                        
                    }
                }
            };

            break;
        }
        case "alert" : {
            
            dialog_title = "알림";

            dialog_icon = " <span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>";

            buttons_option = {
                "확인": function() {

                    if(typeof(arg_option["callback_fn"]) == "function") {
						
						setTimeout(function(){
                            arg_option["callback_fn"]();
                        },100);        
                        
                        jQuery( this ).dialog( "close" );
                        
                    } else {
                        
                        jQuery( this ).dialog( "close" );
                        
                    }
                }
            };

            break;
        }
        default : {
            dialog_title = "메세지";

            dialog_icon = " <span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>";


            buttons_option = {};
        }

    }
	
	dialog_title = "";

    jQuery("#jQuery_dialog").attr("title", dialog_title);
    jQuery( "#jQuery_dialog" ).dialog({
        autoOpen: false,

        modal: true,
        show: {
            effect: "bounce",
            duration: 500
        },
        hide: {
            effect: "blind",
            duration: 200
        },
        buttons: buttons_option,
		open: function( event, ui ) {
			if( jQuery(".ui-dialog-titlebar-close").length > 0 ) {
				jQuery(".ui-dialog-titlebar-close").hide();
			}

			if( jQuery(".ui-widget-header").length > 0 ) {
				jQuery(".ui-widget-header").attr("style", "visibility:hidden");
			}

			
		}


    });
	
	dialog_icon = "";
    jQuery("#jQuery_dialog_p").html(dialog_icon + arg_option["text"]);
    jQuery( "#jQuery_dialog" ).dialog( "open" );

}

/*****************************************************************
10보다 작은 수는 0을 붙여준다.
*****************************************************************/
function addTenUnder(paramValue) {
	var returnValue = "";
	if(paramValue < 10){
		returnValue = "0"+paramValue;
	} else {
		returnValue = paramValue;
	}

	return returnValue;
}

/***********************************************************************************
지오로케이션 웹 위치 알아오는 함수.
**********************************************************************************/
function geolocation(callBack_fn) {
	if(navigator.geolocation) {
		window.focus();		
		navigator.geolocation.getCurrentPosition( function(position){
			if(typeof(callBack_fn) == "function") {				
				callBack_fn(position);
			} 
		}, function(error){
			//--> 미승인시
			if(typeof(callBack_fn) == "function") {				
				callBack_fn("");
			} 
		});
	}
}

/***********************************************************************************
스트링 바이트 수를 반환
**********************************************************************************/
function get_byte(string_data) {
	 var resultSize = 0;        
	 
	if(string_data == null) {
		 return 0;        
	}
	
	for(var i=0; i < string_data.length; i++) {
		var c = escape(string_data.charAt(i));
		
		if(c.length == 1) {              
			// 아스키 코드
			resultSize ++;            
		} else if(c.indexOf("%u") != -1) {
			//한글 혹은 기타 
			resultSize += 2;            
		} else {
			resultSize ++;            
		}        
	}
	
	return resultSize;
}

/***********************************************************************************
너비에 맞게 글자수를 자른다.
**********************************************************************************/
function wm_strcut(string_data, max_length) {
	 var return_data = "";       
	 
	if(string_data == null) {
		 return 0;        
	}
	
	if(string_data.length > max_length) {

		var string_limit = 0;
		var chr_info = "";

			
		for(var s_index = 0; (s_index < string_data.length); s_index++) {

			chr_info = escape(string_data.charAt(s_index));
			
			if(chr_info.length == 1) {              
				// 아스키 코드 - 숫자 / 영문

				string_limit += 0.5;            
			} else if(chr_info.indexOf("%u") != -1) {
				//한글 혹은 기타 
				string_limit += 1;            
			} else {
				// 공백
				string_limit += 0.5;            
			}

			if(string_limit >= max_length) {
				break;
			}
		}

		string_data = string_data.substring(0, s_index);	
		return_data = string_data + "..";

	} else {
		return_data = string_data;
	}
	
	
	return return_data;
}

/***********************************************************************************
문자열 자르는 함수 
**********************************************************************************/
function cut_str(string_data, max_length) {
	result_str = "";
	if(get_byte(string_data) > max_length ) {		
			result_str = string_data.substring(0, max_length);			
		result_str = result_str + "..";
	} else {
		result_str = string_data;
	}

	return result_str;
}

/***********************************************************************************
이미지 리사이징
**********************************************************************************/
function resizeImg(img, param_width, param_height) {

			// 원본 이미지 사이즈 저장
			var width = img.width;
			var height = img.height;	

			// 가로, 세로 최대 사이즈 설정
			var maxWidth = param_width;
			var maxHeight = param_height;
			var resizeWidth;
			var resizeHeight;		

			// 이미지 비율 구하기
			var basisRatio = maxHeight / maxWidth;
			var imgRatio = height / width;

			if (imgRatio > basisRatio) {
			// height가 기준 비율보다 길다.
				if (height > maxHeight) {
					resizeHeight = maxHeight;
					resizeWidth = Math.round((width * resizeHeight) / height);
				} else {
					resizeWidth = width;
					resizeHeight = height;
				}
			} else if (imgRatio < basisRatio) {
			// width가 기준 비율보다 길다.
				if (width > maxWidth) {
					resizeWidth = maxWidth;
					resizeHeight = Math.round((height * resizeWidth) / width);
				} else {
					resizeWidth = width;
					resizeHeight = height;
				}
			} else {
				// 기준 비율과 동일한 경우
				resizeWidth = width;
				resizeHeight = height;
			}
			// 리사이즈한 크기로 이미지 크기 다시 지정
			img.width = resizeWidth;
			img.height = resizeHeight;
			img.style.position = "";
			img.style.left = "0px";
	} 

/*****************************************************************
숫자 통화형식으로 변환함수.
*****************************************************************/
function numMoneyType(getValue){
	var returnValue = getValue;	
	returnValue = String(returnValue);

	returnValue = returnValue.split(/(?=(?:\d{3})+(?:\.|$))/g).join(',');

	return returnValue;
}

/*****************************************************************
정규식 체크함수.
*****************************************************************/
function input_RegexCheck(type, check_val) {
	var num_Regex = /[0-9]+/;                               //--> 숫자
	var only_num_Regex = /[^0-9]+/;                               //--> 숫자
	var eng_Regex = /[a-zA-Z]+/;                            //--> 영문 대/소문자
	var ko_Regex = /[가-힣ㄱ-ㅎ ㅏ-ㅣ]+/;                   //--> 한글(완성형)
	var s_chr_Regex = /[^a-zA-Z0-9가-힣]+/;                 //--> 한글/영문/숫자 를 제외한 문자(특수문자)
	var sNko_chr_Regex = /[^a-zA-Z0-9]+/;                   //--> 영문/숫자 를 제외한 문자(특수문자, 한글)
	var email_Regex = /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/i;
	var space_Regex = /\s/g;                               //--> 공백
	var yyyy_mm_dd_Regex = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/; //--> yyyy-mm-dd 날짜 형식

	var bool_result = true;

	switch(type) {
		case "engNum_4" : {
			/* 영대소문자/숫자 조합 4자리 이상.*/
			bool_result = (( (((eng_Regex.test(check_val)) && (num_Regex.test(check_val))) || (eng_Regex.test(check_val))) && (check_val.length >= 4)) && !(sNko_chr_Regex.test(check_val)));
			break;
		}
		case "engNum_6" : {
			/* 영대소문자/숫자 조합 6자리 이상.*/
			bool_result = ((((num_Regex.test(check_val)) || (eng_Regex.test(check_val))) && (check_val.length >= 6)) && !(sNko_chr_Regex.test(check_val)));
			break;
		}
		case "email" : {
			if(check_val.match(email_Regex) == null) {
				bool_result = false;
			}
			break;
		}
		case "number" : {
			if(check_val.match(num_Regex) == null) {
				bool_result = false;
			}
			break;
		}
		case "signup_pw" : {
			//# 입력기본 8~12자 여부 확인

			if((check_val.length >= 8) && (check_val.length <= 12)) {

				if((eng_Regex.test(check_val)) && (num_Regex.test(check_val)) && !(s_chr_Regex.test(check_val))) {
					//# 보통
					bool_result = "normal";

				} else if((eng_Regex.test(check_val)) && (num_Regex.test(check_val)) && (s_chr_Regex.test(check_val)) ) {
					//# 강력
					bool_result = "power";
				} else if( ((eng_Regex.test(check_val))  || (num_Regex.test(check_val))  || (check_val.length < 10) ) && !(s_chr_Regex.test(check_val)) ) {
					//# 위험	
					bool_result = "danger";
				} else {
					bool_result = "normal";
				}
			} else {
				bool_result = "length_notpass";
			}

			break;
		}

		case "nick" : {
			if((s_chr_Regex.test(check_val)) && (check_val.length < 10)) {
				bool_result = true;
			} else {
				bool_result = false;
			}
			break;
		}

		case "snko" : {
			/* 영문, 숫자 외에 다른 입력값이 포함되면 true를 리턴 */
			if( sNko_chr_Regex.test(check_val) ) {
				bool_result = true;
			} else {
				bool_result = false;
			}
			
			break;
			
		}

		case "space" : {
		    
			/* 문자열 공백 체크 */
			if( space_Regex.test(check_val) ) {
				// 공백 존재
				bool_result = true;
			} else {
				// 공백 없음.
				bool_result = false;
				
			}
			
			break;
			
		}


		case "only_number" : {
			/* 문자열 공백 체크 */
			if( only_num_Regex.test(check_val) ) {
				// 숫자이외의 값이 있음				
				bool_result = false;
			} else {
				// 숫자로만 구성
				bool_result = true;
				
			}
			
			break;
			
		}

		case "date" : {
			/* 날짜형식 */
			bool_result = yyyy_mm_dd_Regex.test(check_val);
			
			break;
			
		}

		



	}

	return bool_result;
}

/***********************************************************************************
현재 url 의 get parameter 정보를 가져와 객체형태로 변환한다.
**********************************************************************************/
function getUrlParam() {
	
	var params = {};
	var search_obj = null;
	var queryString = (document.location+"").substring(1);
	
	var regex = /([^#?&=]+)=([^&]*)/g;
	var match;

	while ((match = regex.exec(queryString)) !== null) {
		params[decodeURIComponent(match[1])] = decodeURIComponent(match[2]);
		
	}
	search_obj = params;

	return search_obj;
}

/***********************************************************************************
네트워크 상태 체크
**********************************************************************************/
function networkCheck() {
	if(navigator.onLine) {
		return true;
	} else {
		return false;
	}
}

function alert_login(tab) {
	if(confirm("로그인이 필요한 서비스 입니다 \n로그인 하시겠습니까?") == true) {
		var tab_val = "";
		if(tab) {
			tab_val = "&tab="+tab;
		} else {
			tab_val = "";
		}
		location.href="/member/login.php?login_rtn_page="+encodeURIComponent(window.location.pathname + window.location.search + tab_val);
	}
}

/***********************************************************************************
공유하기
**********************************************************************************/
var kakao_init_status = false;
function share_sns(type, share_title, img_path) {
    var origin_url = window.location.origin+"/"+window.location.pathname + window.location.search;
    var page_url = encodeURIComponent( origin_url );
    var cr = encodeURIComponent('\r\n');
    var kakaoAppID = "d6ac637d9587c6d69fb1565fc61934ca"; // 발급필요.
    var page_share_text = encodeURIComponent(share_title);
    var share_obj = {};
    
    
    switch(type) {
        case "facebook" : { 
            share_obj = {
                method : "popup",
                url : "https://www.facebook.com/sharer.php?u="+page_url
            };
            break;
        }
        
        case "pinterest" : { 
            share_obj = {
                method : "popup",
                url : "http://www.pinterest.com/pin/create/button/?url="+ page_url + "&media="+ img_path +"&description=" + page_share_text
            };
            break;
        }
        
        case "twitter" : { 
            share_obj = {
                method : "popup",
                url : "https://twitter.com/share?url="+page_url+"&text="+page_share_text+cr
            };
            break;
        }
        case "band_web" : { 
            share_obj = {
                method : "popup",
                url : "http://band.us/plugin/share?body=" + page_share_text + cr  + page_url + "&route=" + page_url
            };
            
            break;
        }
        case "line" : { 
            share_obj = {
                method : "popup",
                url : "http://line.me/R/msg/text/?" + page_share_text + cr + page_url
            };
            
            break;
        }
        case "google" : { 
            share_obj = {
                method : "popup",
                url : "https://plus.google.com/share?url={"+page_url+"}"
            };
            
            break;
        }
        case "naver_web" : { 
            share_obj = {
                method : "popup",
                url : "http://share.naver.com/web/shareView.nhn?url="+page_url+"&title="+page_share_text+cr
            };
            
            break;
        }
        
        case "kakao_talk" : { 
            
            share_obj = {
                method : "sdk"
            };
            
            if( kakao_init_status == false ) {
                
                Kakao.init(kakaoAppID);    
                
                kakao_init_status = true;
            }
            
            
            Kakao.Link.sendTalkLink({
                label: share_title,
                image: {
                    src: img_path,
                    width: '300',
                    height: '200'
                },
                webButton: {
                    text: "헬스앤라이프로 이동",
                    url: origin_url
                }
            });
            
            break;
        }
        
        case "kakao_story" : { 
            
            share_obj = {
                method : "sdk"
            };
            
             if( kakao_init_status == false ) {
                
                Kakao.init(kakaoAppID);    
                
                kakao_init_status = true;
            }
            
            Kakao.Story.share({
                url: origin_url,
                text: share_title
            });
                
            break;
        }
  
        default : {
            alert("잘못된 호출입니다.");
            return false;
        }
    }
    
    if(share_obj.method == "popup") {
        window.open(share_obj.url, "share_"+type, "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");
    } else {
        //# 모바일 기기 체크
    }

}

function clipboard_copy() {
	var url = "http://" + window.location.host + window.location.pathname + window.location.search;
	if(is_ie()) {				
		window.clipboardData.setData("Text", url);
		alert("복사되었습니다.");
	} else {
		 prompt("Ctrl+C를 눌러 복사하세요.", url);
	}
	
}

function is_ie() {
  if(navigator.userAgent.toLowerCase().indexOf("chrome") != -1) return false;
  if(navigator.userAgent.toLowerCase().indexOf("msie") != -1) return true;
  if(navigator.userAgent.toLowerCase().indexOf("windows nt") != -1) return true;
  return false;
}

/***********************************************************************************
    브라우져의 종류를 반환 한다.
**********************************************************************************/

function getBrowserType() {
    
   var _ua = navigator.userAgent;
   var rv = -1;
    
       //IE 11,10,9,8
   var trident = _ua.match(/Trident\/(\d.\d)/i);
   
           
           if( trident != null )
           {
               if( trident[1] == "7.0" ) return rv = "IE" + "11";
       if( trident[1] == "6.0" ) return rv = "IE" + "10";
       if( trident[1] == "5.0" ) return rv = "IE" + "9";
       if( trident[1] == "4.0" ) return rv = "IE" + "8";
   }
    
   //IE 7...
   if( navigator.appName == 'Microsoft Internet Explorer' ) return rv = "IE" + "7";
   
   //other
   var agt = _ua.toLowerCase();
   if (agt.indexOf("chrome") != -1) return 'Chrome';
   if (agt.indexOf("firefox") != -1) return 'Firefox';
   if (agt.indexOf("safari") != -1) return 'Safari';
   if (agt.indexOf("opera") != -1) return 'Opera';
   if (agt.indexOf("staroffice") != -1) return 'Star Office';
   if (agt.indexOf("webtv") != -1) return 'WebTV';
   if (agt.indexOf("beonex") != -1) return 'Beonex';
   if (agt.indexOf("chimera") != -1) return 'Chimera';
   if (agt.indexOf("netpositive") != -1) return 'NetPositive';
   if (agt.indexOf("phoenix") != -1) return 'Phoenix';
   if (agt.indexOf("skipstone") != -1) return 'SkipStone';
   if (agt.indexOf("netscape") != -1) return 'Netscape';
   if (agt.indexOf("mozilla/5.0") != -1) return 'Mozilla';
   
   
}//   end function

/***********************************************************************************
jquery block ui를 이용한 함수.
**********************************************************************************/
function show_blockUI(option) {
	/**
		option = {
			showType : [img]-하나의 이미지를 보여준다. / [그 외]-기본
			showID : 보여질 element 의 id값
			width : showType이 img 인 경우에만 해당.
			height : showType이 img 인 경우에만 해당.
			onBlock : {
				type : callback - 다른 함수를 호출 / close - 닫는다는 의미
				value : function name 또는 close buttom id
			}
			onOverlayClick : {
				type : callback - 다른 함수를 호출 / close - 닫는다는 의미
				value : function name 또는 공백
			}
		}
	*/
	var overlayCSS = "", 
		css = "", 
		onBlock="", 
		onOverlayClick="";

	var showID = option.showID;

	//#-------------------------------------------  css set start

	overlayCSS = {
		backgroundColor: '#000'
		,float: 'left'
		,opacity: 0.5
		,cursor: 'default'
	};
	
	switch(option.showType) {
		case "loading" : {
			css = {
					cursor: 'default',
					border: 'none', 
					margin: ' 0 18%',
					width: option.width,
					height: option.height,
					backgroundColor: '#ffffff',
					'border-radius': '50px',
					'-webkit-border-radius': '50px', 
					'-moz-border-radius': '50px', 
					

			}
			break;
		}
		
		case "loading_run" : {
            css = {
                    cursor: 'default'
                    ,border: 'none'
                    ,width: "100%"
                    ,left: "0px"
                    ,backgroundColor: "none"

            }
            break;
        }
        

		case "type01" : {
			css = {
					border: 'none' 
					,textAlign: 'left'
					,width: "100%"
					,backgroundColor: 'none'
					,'-webkit-border-radius': '10px'
					,'-moz-border-radius': '10px'
					,top:'30%'
					,left:'0px'
					,cursor: 'default'
					

			}
			break;
		}

		case "type02" : {
			css = {
					border: 'none' 
					,textAlign: 'left'
					,backgroundColor: 'none'
					,'-webkit-border-radius': '10px'
					,'-moz-border-radius': '10px'
					,top:'5%'
					,left:'0px'
					,cursor: 'default'
					,position : 'relative'
					,margin: '0px 34%'
					,padding:"5% 0px"
					
			}
			break;
		}


		case "type03" : {
			css = {
					border: 'none' 
					,textAlign: 'left'
					,backgroundColor: 'none'
					,'-webkit-border-radius': '10px'
					,'-moz-border-radius': '10px'
					,top:'5%'
					,left:'0px'
					,cursor: 'default'
					,position : 'relative'
					,margin: '0px 30%'
					,padding:"5% 0px"
					
			}
			break;
		}
		
		case "dr_layer" : {
            css = {
                    cursor: 'default'
                    ,border: 'none'
                    ,width: "100%"
                    ,top: "400px"
                    ,left: "0px"
					,position : 'absolute'
                    ,backgroundColor: "none"

            }
            break;
        }
        
		case "ReservChild_box" : {
            css = {
                    cursor: 'default'
                    ,border: 'none'
                    ,width: "100%"
                    ,top: "200px"
                    ,left: "0px"
                    ,backgroundColor: "none"

            }
            break;
        }
		
		case "doctor_share" : {
            css = {
                    cursor: 'default'
                    ,border: 'none'
                    ,width: "30%"
                    ,backgroundColor: "none"

            }
            break;
        }

		case "webzine_result" : {
            css = {
                    cursor: 'default'
                    ,border: 'none'
                    ,backgroundColor: "none"
					,width: "100%"
					,top: "20%"
					,left: "0px"
					,margin : "0 auto"

            }
            break;
        }


		default : {
			css = {
					border: 'none' 
					,textAlign: 'left'
					,width: '100%'
					,backgroundColor: '#000'
					,'-webkit-border-radius': '10px'
					,'-moz-border-radius': '10px'
					/*
					padding: '15px', 
					backgroundColor: '#000', 
					'-webkit-border-radius': '10px', 
					'-moz-border-radius': '10px', 
					//opacity: .5, 
					color: '#fff',
					*/
					,cursor: 'default'
			};
		}
	}
	//#-------------------------------------------  css set end

	//#------------------------------------------- option set start
	if(option.onBlock.type == "callback") {
		onBlock = option.onBlock.value;
	} else if(option.onBlock.type == "close") {
		onBlock = function(){ jQuery("#"+option.onBlock.value).click(jQuery.unblockUI) };
	}

	if(option.onOverlayClick.type == "callback") {
		onOverlayClick = option.onOverlayClick.value;
	} else if(option.onOverlayClick.type == "close") {
		onOverlayClick = jQuery.unblockUI;
	}

	//#------------------------------------------- option set end
	jQuery.blockUI({
		message: jQuery('#'+showID),
		fadeIn: 50,
		centerY: true,
		overlayCSS: overlayCSS,
		css: css, 
		onBlock: onBlock,
		onOverlayClick: onOverlayClick
	});
}


/***********************************************************************************
로딩이미지 호출 - 관리자 또는 일반
**********************************************************************************/
function loading_call() {
	jQuery("#btn_submit").hide();
	option = {
		"showType" : "loading"
		,"showID" : "loading_area"
		,"width" : "80px"
		,"height" : "80px"
		,"onBlock" : ""
		,"onOverlayClick" : ""
	}
	show_blockUI(option);
}

/***********************************************************************************
로딩이미지 호출 - ajax 공통
**********************************************************************************/
function ajaxProcessing( arg_type ) {
    
	if( jQuery("#_loading_run_area").length == 0 ) {			
		init_loading_dom();		
	}

	if( arg_type == "open" ) {
        
		option = {
			"showType" : "loading_run"
			,"showID" : "_loading_run_area"
			,"width" : "80px"
			,"height" : "80px"
			,"onBlock" : ""
			,"onOverlayClick" : ""
		}
		show_blockUI(option);
			
	} else {
		jQuery.unblockUI("_loading_run_area");    
	}
    
}

/***********************************************************************************
지역 설정 레이어 handler
**********************************************************************************/
function setLocationLayer( arg_type, arg_use_type ) {

	jQuery("#location_layer_use_type").val( arg_use_type );

    if( arg_type == "open" ) {
        
		getLocationData();

        option = {
            "showType" : "set_location_layer"
            ,"showID" : "set_location_layer"
            ,"onBlock" : ""
            ,"onOverlayClick" : ""
        }
        show_blockUI(option);
            
    } else {
        jQuery.unblockUI("set_location_layer");    
    }

}

/***********************************************************************************
지역 설정 레이어 - 확인 버튼 클릭시 호출
**********************************************************************************/
function setLocationOk() {
	var location_layer_use_type = jQuery("#location_layer_use_type").val();
	
	var city_code = jQuery("#location_layer_city_info").val();
	var	city_title = jQuery("#location_layer_city_info option:selected").text();
	var	province_code = jQuery("#location_layer_province_info").val();
	var	province_title = jQuery("#location_layer_province_info option:selected").text();

	if( location_layer_use_type == "search" ) {
		// 어린이집 목록 검색 적용
		
		search_city_code = city_code;
		search_city_title = city_title;
		search_province_title = province_title;

		initList();

	} else {
		// 관심지역 설정 변경

		var params = {
			mode : "save"
			,cookie_code : "city_info"
			,cookie_key : "city_code,city_title,province_code,province_title"
			,cookie_value : city_code + ","+ city_title + ","+ province_code + "," + province_title
		};
		
		resetCityInfo( params );
	}

	setLocationLayer('close', '');
}

/***********************************************************************************
지역 설정 레이어 호출시 데이터 요청 및 dom 재구성
**********************************************************************************/
var province_info_obj = [];
function getLocationData() {
	
	var city_title = jQuery("#location_layer_city_info option:selected").text();
	var province_title = jQuery("#location_layer_province_info option:selected").text();
	var selected = "";
	
	if( ( city_title == "" ) || ( city_title == undefined ) ) {
		city_title = jQuery("#location_layer_city_info").attr("data-cookies");
	}

	if( ( province_title == "" ) || ( province_title == undefined ) ) {
		province_title = jQuery("#location_layer_province_info").attr("data-cookies");
	}

	jqueryAjaxCall({
		type : "post",
		url : "/_ajax/get_location_data.asp",
		dataType : "json",
		paramData : "",
		callBack : function(getResult, param_mode) {
			
			
			if( getResult.state == "success" ) {

				jQuery.each(getResult["city_info"], function(idx, item){
					
					if( province_info_obj[item.city_key] == undefined ) {
						province_info_obj[item.city_key] = {};
					}
					
					province_info_obj[item.city_key]["city_item"] = item.city_item;
					
					if( item.province_list.length > 0 ) {
						
						jQuery.each(item.province_list, function(province_loop, province_item) {

							if( province_info_obj[item.city_key]["province_list"] == undefined ) {
								province_info_obj[item.city_key]["province_list"] = {};
							}
							province_info_obj[item.city_key]["province_list"][province_item.province_key] = province_item.province_item;
						});
						
					}

					if( city_title == item.city_item) {
						selected = "selected='selected'";
					} else {
						selected = "";
					}
					
					jQuery("#location_layer_city_info").append( "<option value='"+ item.city_key +"' "+ selected +" >"+item.city_item+"</option>" );
				});

				makeProvinceInfoOption( jQuery("#location_layer_city_info").val(),  province_title );

				

			} else {
				jQueryDialog({
					type : "alert"
					,text : getResult.msg
					,callback_fn : function(){ setLocationLayer('close', ''); }
				});
			}

		},
		callBackData : ""
	});
	
}
	
/***********************************************************************************
지역 설정 레이어 호출시 구/군 dom 재구성
**********************************************************************************/
function makeProvinceInfoOption( city_key , old_title ) {
	jQuery("#location_layer_province_info").html("");
	var selected = "";
	if( province_info_obj[ city_key ][ "province_list" ] != undefined ) {
		jQuery.each(province_info_obj[ city_key ][ "province_list" ], function(province_loop, province_item) {

			if( old_title == province_item) {
				selected = "selected='selected'";
			} else {
				selected = "";
			}

			jQuery("#location_layer_province_info").append("<option value='"+province_loop+"' "+ selected +" >"+province_item+"</option>");
		});
	} else {
		jQuery("#location_layer_province_info").html("<option value='' >구 / 군</option>");
	}
	
}



/***********************************************************************************
쿠키 설정 함수
**********************************************************************************/
function setCookie( cookie_name, value) {
	var todayDate = new Date();
	todayDate.setDate( todayDate.getDate() + 7 );
	document.cookie = cookie_name + "=" + value + "; path=/; expires=" + todayDate.toGMTString() + ";"
}

/***********************************************************************************
쿠키 확인 함수
**********************************************************************************/
function getCookie( cookie_name ) {

	var search = cookie_name + "=";

	if (document.cookie.length > 0) {                  // 쿠키가 설정되어 있다면
	 offset = document.cookie.indexOf(search);      // name의 이름에 쿠키가 있는지 여부 판단.

	 if (offset != -1) {                               // 쿠키가 존재하면
		offset += search.length;                   // 쿠기 시작 포인트
		end = document.cookie.indexOf(";", offset);  // 쿠키 값의 마지막 위치 인덱스 번호 설정
		if (end == -1)
		 end = document.cookie.length;              // 쿠키가 하나뿐이거나 (쿠키 옵션 설정 없을때)..

		return unescape(document.cookie.substring(offset, end));  //쿠키값 리턴..
	 }
	}
	
}

/***********************************************************************************
접속 매체 체크
**********************************************************************************/
function checkFlatform() {
	var result = [];
	var userAgentInfo ="win16|win32|win64|mac|macintel";

	if(userAgentInfo.indexOf(navigator.platform.toLowerCase()) > -1 ) {
		result["flatform"] = "pc";
	} else {
		result["flatform"] = "mobile";
		if(navigator.userAgent.indexOf("Android") > -1) {
			result["os"] = "android";
		} else {
			result["os"] = "ios";
		}
		
	}

	return result;
}

function notification( type, msg, move_path, callback_fn ) {
       
	
	/* 
	 * alert : 알림창  
	 * alert_back : 알림창 후 이전 페이지로 이동
	 * alert_replace : 알림창 후 이동
	 * conform : 확인창 후 분기처리 
	 * permission : 접근권한 없음 
	 * popup_close : 알림창 후 창 닫음
	 * */
	switch( type ) {
		
		case "alert" : {
			
			jQueryDialog({
				type : "alert"
				, text :  msg
				, callback_fn : function(){					
					setTimeout(function(){
						callback_fn();
					},50);
				}
			});
			
			break;
		}

		case "replace" : {
			
			if( msg ) {

				jQueryDialog({
					type : "alert"
					, text :  msg
					, callback_fn : function(){
						
						setTimeout(function(){
							location.replace( move_path );  
						},50);
					}
				});
			} else {
				location.replace( move_path );
			}

			break;
		}
		case "back" : {

			if( msg ) {

				jQueryDialog({
					type : "alert"
					, text :  msg
					, callback_fn : function(){
						setTimeout(function(){
							history.go(-1); 
						},50);
					}
				});

			} else {

				history.go(-1);

			}
			
			

			break;
		}
		case "confirm" : {
			
			jQueryDialog({
				type : "confirm"
				, text :  msg
				, callback_yes : function(){
					setTimeout(function(){
						callback_fn();
					},50);
				}
			});
			break;
		}
		
		case "popup_close" : {

			if( msg ) {

				jQueryDialog({
					type : "alert"
					, text :  msg
					, callback_fn : function(){
						setTimeout(function(){
							window.opener = 'nothing';
							window.open('', '_parent', '');
							window.close();
						},50);
					}
				});

			} else {

				window.opener = 'nothing';
				window.open('', '_parent', '');
				window.close();

			}

			

			break;
		}

		case "permission" : {

			window.opener = 'nothing';
			window.open('', '_parent', '');
			window.close();

			break;
		}

		default : {
			console.log("정의 되지 않은 type");
		}

	}
   
	
}

function jqueryAddEvent( arg_event_data ){
    $( arg_event_data.selector ).off( arg_event_data.event, arg_event_data.fn );
    $( arg_event_data.selector ).on( arg_event_data.event, arg_event_data.fn );
}