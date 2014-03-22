<?php
#Modified from http://federicoeinhorn.com/2012/11/php-to-work-with-virustotals-url-api/
function vtInteract($_POST)
{
    define("API_URL", "https://www.virustotal.com/vtapi/v2/");
    define("API_KEY", $_POST['vtapi']);
    define("MAX_DAYS_OLD", 1); // the break point between an up to date and an outdated record -- set 0 to disable
 
    /**
     * Make a request to VirusTotal API 2.0
     *
     * @param string $url  the url we're going to request
     * @param string $apicall can be "report" (searchs for an existing record) or "scan" (requests a new scan)
     * @param string $apikey your VirusTotal account's API KEY
     *
     * @return array
     */
    function requestVTAPI($url,$apicall,$apikey) // makes a request to the VirusTotal API. 
    {
        $ch = curl_init();
        $postString = "apikey=".$apikey;
        switch($apicall) 
        {
            case "scan": // we want to request a new scan
            curl_setopt($ch, CURLOPT_URL,API_URL."url/scan");
            $postString .= "&url=".urlencode($url);
            //echo "Request new scan for: " . $url;
            break;
            case "report": // we want to request a report
            curl_setopt($ch, CURLOPT_URL,API_URL."url/report");
            $postString .= "&resource=".urlencode($url);
            break;
            default:
            die("error");
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postString);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output_json = curl_exec ($ch);        
        $server_output_obj = json_decode($server_output_json);        
        $server_output_array = (array)$server_output_obj;        
        return $server_output_array;
        curl_close ($ch);  
    }
       
    /**
     * Custom VirusTotal URL scan 
     *
     * @param string $url the url we want to scan
     * @param int $autoScan queue a new scan if no record is found or if it's outdated -- 1 on
     *
     * @return
     * 0 = record exists and is up-to-date and the page is clean
     * 1 = record exists and is up-to-date and the page has malware
     * 2 = no record exists (request scan)
     * 3 = record exists but was not up-to-date and page is clean (request new scan)
     * 4 = record exists but was not up-to-date and page has malware (request new scan)
     * 5 = anything else (errors, etc.)
     *   
     */
    function myScan($url,$autoScan = 1) 
    {
        $results = requestVTAPI($url,"report",API_KEY);
        $return = array();      
        
        if($results['response_code'] === (int)0)
        {   
            $return = array(
							2 => requestVTAPI($url,"scan",API_KEY),
							);                
            return $return; // NOT FOUND
        } 
        
        if(strtotime($results['scan_date']) !== false && strtotime($results['scan_date']) !== -1) 
        {
            $currentDate = date('m/d/Y H:i:s', time());            
            $daysOld = round(((strtotime("now") - strtotime($results['scan_date'])) / $daySeconds=86400));
        }
                    
        if( $results['positives'] === (int)0
            && $results['response_code'] === (int)1) 
        {
                if(MAX_DAYS_OLD && $daysOld > MAX_DAYS_OLD) {
                    
                    $return = array(
									3 => requestVTAPI($url,"scan",API_KEY),
									);                
					return $return;    // FOUND - CLEAN - TOO OLD
                }
                else {                
                    
                    $return = array(
									0 => $results,
									);                
					return $return;
                    
                    #return 0;    // FOUND - CLEAN - UP TO DATE 
					                                                   
                }
        }
		
		if( $results['positives'] > 1
            && $results['response_code'] === (int)1) 
        { 
            if(MAX_DAYS_OLD && $daysOld > MAX_DAYS_OLD) 
            {
                
                $return = array(
								4 => requestVTAPI($url,"scan",API_KEY),
								);                
				return $return; //  FOUND - MALWARE - TOO OLD
            }
            else 
            {     
				$return = array(
								1 => $results,
								);                
				return $return;          
                #return 6;    // FOUND - MALWARE - UP TO DATE                                                    
            }
        }   
            
        if( $results['positives'] > 0
            && $results['response_code'] === (int)1) 
        { 
            if(MAX_DAYS_OLD && $daysOld > MAX_DAYS_OLD) 
            {
                
                $return = array(
								4 => requestVTAPI($url,"scan",API_KEY),
								);                
				return $return;	//  FOUND - MALWARE - TOO OLD
                
                #requestVTAPI($url,"scan",API_KEY);
                #return 4;    //  FOUND - MALWARE - TOO OLD
            }
            else 
            {     
				      
                $return = array(
								1 => $results,
								);                
				return $return;
                #return 1;    // FOUND - MALWARE - UP TO DATE                                                    
            }
        }
        $return = array(
						5 => "error",
						);                
		return $return;
        
        #return 5; // something went wrong
    }
    
 
$vtScan=myScan($_POST['url']);

return $vtScan;
    
} 
?>
