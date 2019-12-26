<?php
class EnvelopeAction extends CommonAction{
    public function index(){
         $x=$this->randnum(1800,20);
  	     print_R($x);
   }
    

	public function randnum($total,$div){
	   $total = $total; //待划分的数字
	   $div = $div; //分成的份数
	   $area = 50; //各份数间允许的最大差值
	   $average = round($total / $div);
	   $sum = 0;
	   $result = array_fill( 1, $div, 0 );
		 
	   for( $i = 1; $i < $div; $i++ ){
		//根据已产生的随机数情况，调整新随机数范围，以保证各份间差值在指定范围内
		if( $sum > 0 ){
		 $max = 0;
		 $min = 0 - round( $area / 2 );
		}elseif( $sum < 0 ){
		 $min = 0;
		 $max = round( $area / 2 );
		}else{
		 $max = round( $area / 2 );
		 $min = 0 - round( $area / 2 );
		}
		 
		//产生各份的份额
		$random = rand( $min, $max );
		$sum += $random;
		$result[$i] = $average + $random;
	   }
		 
	   //最后一份的份额由前面的结果决定，以保证各份的总和为指定值
	   $result[$div] = $average - $sum;
	   foreach( $result as $temp ){
	   $data[]=$temp;
	   }
	   return $data;
	}
	
 
   /*调用方法*/
 
  


}