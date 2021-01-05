<?php

 /**
 * 배열을 dom 에 출력한다.
 */
function echoPre( $arg_data ){
	echo '<pre>';
	print_r( $arg_data );
	echo '</pre>';        
}

/**
 * <br>을 포함하여 dom에 출력한다
 */
function echoBr( $arg_data ){

	if( gettype( $arg_data ) == 'array' ){
		echoPre( $arg_data );
	} else {
		echo '<br>';
		echo $arg_data;
		echo '<br>';        
	}
	
}



?>