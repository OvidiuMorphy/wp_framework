<?php 
class vc{
	public function cache($k,$time="+1 hour",$v=NULL){
		if($v===NULL){
			$time_c=get_option("cache_".$k."_time",1);
			if(strtotime($time,$time_c)<strtotime("now")){
				return false;
			}
			$res = get_option("cache_".$k,1);
			if(unserialize($v)===NULL){$res=unserialize($res);}
			return $res;
		}else{
			$ov=$v;
			if(is_array($v)){$v=serialize($v);}
			update_option("cache_".$k,$v);
			update_option("cache_".$k."_time",strtotime("now"));
			return $ov;
		}
	}

	/**
	*
	* @usage add_filter('woocommerce_paypal_args', 'convert_ron_to_eur');
	*
	**/

	public function convert_ron_to_eur($paypal_args)	{ 

		$curs=new cursBnrXML("http://www.bnr.ro/nbrfxrates.xml");

		if ( $paypal_args['currency_code'] == 'RON')	{
			$convert_rate = $curs->getCurs("EUR"); //set the converting rate
			$paypal_args['currency_code'] = 'EUR'; //change RON to EUR  
			$i = 1;

			while (isset($paypal_args['amount_' . $i])) {
				$paypal_args['amount_' . $i] = round( $paypal_args['amount_' . $i] / $convert_rate, 2);
				++$i;
			}
		}
		return $paypal_args;
	}
}
