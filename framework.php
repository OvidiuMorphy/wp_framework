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

class cursBnrXML 	{
	/**
	* xml document
	* @var string
	*/
	var $xmlDocument = "";

	/**
	 * exchange date
	 * BNR date format is Y-m-d
	 * @var string
	**/
	var $date = "";

	/**
	 * currency
	 * @var associative array
	**/
	var $currency = array();

	/**
	 * cursBnrXML class constructor
	 *
	 * @access        public
	 * @param         $url        string
	 * @return        void
	**/
	function cursBnrXML($url)	{
		$this->xmlDocument = file_get_contents($url);
		$this->parseXMLDocument();
	}

	/**
	 * parseXMLDocument method
	 *
	 * @access        public
	 * @return         void
	 */
	function parseXMLDocument()	{
		$xml = new SimpleXMLElement($this->xmlDocument);

		$this->date=$xml->Header->PublishingDate;

		foreach($xml->Body->Cube->Rate as $line)	{
			$this->currency[]=array("name"=>$line["currency"], "value"=>$line, "multiplier"=>$line["multiplier"]);
		}
	}

	/**
	 * getCurs method
	 * 
	 * get current exchange rate: example getCurs("USD")
	 * 
	 * @access        public
	 * @return         double
	 */
	function getCurs($currency)	{
		foreach($this->currency as $line)	{
			if($line["name"]==$currency)	{
				return $line["value"];
			}
		}

		return "Incorrect currency!";
	}
}
