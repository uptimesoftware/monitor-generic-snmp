<?php
	//error_reporting(E_ERROR | E_WARNING);
	$SNMP_Host = getenv('UPTIME_HOSTNAME');
	$SNMP_Port = getenv('UPTIME_SNMP-PORT');
	$SNMP_OID = getenv('UPTIME_SNMP-OID');
	$SNMP_WALK_INDEX_OID = getenv('UPTIME_SNMP-WALK-INDEX');
	$SNMP_WALK_INDEX_OID_INCLUDE = getenv('UPTIME_SNMP-WALK-INDEX-INCLUDE');
	$SNMP_WALK_INDEX_OID_INCLUDE = "/".$SNMP_WALK_INDEX_OID_INCLUDE."/";
	
	$SNMP_Data_Type = getenv('UPTIME_DATA_TYPE');
	
	$SNMP_Community = getenv('UPTIME_READ-COMMUNITY');
	
	
	$SNMP_action = getenv('UPTIME_SNMPACTION');
	$SNMP_version = getenv('UPTIME_SNMPVERSION');
	$SNMP_v3_agent = getenv('UPTIME_AGENT-USERNAME');
	$SNMP_v3_auth_type = getenv('UPTIME_AUTH-TYPE');
	$SNMP_v3_auth_pass = getenv('UPTIME_AUTH-PASS');
	$SNMP_v3_priv_type = getenv('UPTIME_PRIVACY-TYPE');
	$SNMP_v3_priv_pass = getenv('UPTIME_PRIVACY-PASS');
	$MONITOR_TIMEOUT = getenv('UPTIME_TIMEOUT');
	
	$CURRENT_TIME = time();
	
	$SNMP_Connection_String = $SNMP_Host . ":" . $SNMP_Port;
	
	$SNMP_Walk_Matched = false;
	
	if (!extension_loaded("snmp")) {
		echo "PHP SNMP Extension not loaded!";
		exit(2);
	}
	
	if($SNMP_OID == "") {
		echo "Please enter the OID";
		exit(2);
	} else {
		// PHP SNMP functions takes in OIDs like "1.3.6", not ".1.3.6".  Remove leading .
		if(substr($SNMP_OID,0,1) == ".") {
			$SNMP_OID=substr($SNMP_OID,1);
		}
	}
	if(($SNMP_action == "Walk")&&($SNMP_WALK_INDEX_OID == "")) {
		echo "Please enter the SNMP index OID";
		exit(2);
	} elseif(($SNMP_action == "Walk")&&($SNMP_WALK_INDEX_OID != "")) {
		if(substr($SNMP_WALK_INDEX_OID,0,1) == ".") {
			$SNMP_WALK_INDEX_OID=substr($SNMP_WALK_INDEX_OID,1);
		}
	}
	if(($SNMP_version == "v1")||($SNMP_version == "v2")) {
		if ($SNMP_Community == "") {
				echo "Please enter the SNMP community string.";
				exit(2);
		}
	}

	//lets force errors to get raised as exceptions,
	//so that we can catch timeouts/connection issues from php_snmp
	set_error_handler('handleError');
	
	if($SNMP_version == "v1") {
		if($SNMP_action == "Get") {
			try {
			$returnedDataRaw = snmpget($SNMP_Connection_String,$SNMP_Community,$SNMP_OID);
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}

			$returnedData = parseData($returnedDataRaw);
		} elseif($SNMP_action == "Walk") {
			try {
				$returnedDataRaw = snmprealwalk($SNMP_Connection_String,$SNMP_Community,$SNMP_OID);			//modified by Isaiah - used the 'snmprealwalk' function instead of 'snmpwalk'
				$returnedIndex = snmprealwalk($SNMP_Connection_String,$SNMP_Community,$SNMP_WALK_INDEX_OID);		//modified by Isaiah - used the 'snmprealwalk' function instead of 'snmpwalk'
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}
			$returnedData = parseData($returnedDataRaw);
			$returnedIndex = parseData($returnedIndex);
		}
	} elseif($SNMP_version == "v2") {
		if($SNMP_action == "Get") {
			try {
			$returnedDataRaw = snmp2_get($SNMP_Connection_String,$SNMP_Community,$SNMP_OID);
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}
			$returnedData = parseData($returnedDataRaw);
		} elseif($SNMP_action == "Walk") {
			try {
			$returnedDataRaw = snmp2_real_walk($SNMP_Connection_String,$SNMP_Community,$SNMP_OID);			//modified by Isaiah - used the 'snmp2_real_walk' function instead of 'snmp2_walk'
			$returnedIndex = snmp2_real_walk($SNMP_Connection_String,$SNMP_Community,$SNMP_WALK_INDEX_OID);		//modified by Isaiah - used the 'snmp2_real_walk' function instead of 'snmp2_walk'
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}

			$returnedData = parseData($returnedDataRaw);
			$returnedIndex = parseData($returnedIndex);
		}

	}	elseif ($SNMP_version == "v3") {
	
		if ($SNMP_v3_agent == "") {
			echo "Please enter the SNMP v3 username";
			exit(2);
		}
	
		if ($SNMP_v3_priv_type == "") {
			if ($SNMP_v3_auth_type == "") {
				$SNMP_sec_level = "noAuthNoPriv";
			} else {
				$SNMP_sec_level = "authNoPriv";
				if ($SNMP_v3_auth_pass == "") {
					echo "Please enter the SNMP v3 authentication passphrase.";
					exit(2);
				}
			}
		} else {
			$SNMP_sec_level = "authPriv";
			if (($SNMP_v3_auth_pass == "") && ($SNMP_v3_priv_pass != "")) {
					echo "Please enter the SNMP v3 authentication passphrase.";
					exit(2);
			}
			if (($SNMP_v3_auth_pass != "") && ($SNMP_v3_priv_pass == "")) {
					echo "Please enter the SNMP v3 privacy passphrase.";
					exit(2);
			}
			if (($SNMP_v3_auth_pass == "") && ($SNMP_v3_priv_pass == "")) {
					echo "Please enter the SNMP v3 authentication & privacy passphrase.";
					exit(2);
			}
		}
		
		if($SNMP_action == "Get") {
			try {
			$returnedDataRaw = snmp3_get($SNMP_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,$SNMP_OID);
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}
			$returnedData = parseData($returnedDataRaw);
		} elseif($SNMP_action == "Walk") {

			try {
			$returnedDataRaw = snmp3_real_walk($SNMP_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,$SNMP_OID);		//modified by Isaiah - used the 'snmp3_real_walk' function instead of 'snmp3_walk'
			$returnedIndex = snmp3_real_walk($SNMP_Connection_String,$SNMP_v3_agent,$SNMP_sec_level,$SNMP_v3_auth_type,$SNMP_v3_auth_pass,$SNMP_v3_priv_type,$SNMP_v3_priv_pass,$SNMP_WALK_INDEX_OID);	//modified by Isaiah - used the 'snmp3_real_walk' function instead of 'snmp3_walk'		
			}
			catch (Exception $e) {
				exitWithUnknownStatus("Unable to Retrieve OID");
			}
			$returnedData = parseData($returnedDataRaw);
			$returnedIndex = parseData($returnedIndex);
		}
		
		
	}

// Test if connection info is correct
if ($returnedData === false) {
	
	echo $SNMP_OID."Fail to get SNMP Data! Please check credentials\n";
	exit(2);
}

if($SNMP_action == "Get") {

	if($SNMP_Data_Type == "Integer") {
		echo "1.returnedDataInt ".$returnedData."\n";
	} else {
		echo "returnedDataString ".$returnedData."\n";
	}

} elseif($SNMP_action == "Walk") {

	$dataType = getDataType(reset($returnedDataRaw));				//Added by Isaiah - a different method of getting the first element in the array that may not be indexed with a zero (0)
	foreach (array_keys($returnedData) as $i) {					//Added by Isaiah - iterate through each element of the array non-numerically
		//echo $returnedIndex[$i]."\n";
		$returnedIndex[$i] = str_replace(".","-",$returnedIndex[$i]);
		$returnedIndex[$i] = str_replace("\"","",$returnedIndex[$i]);
		$returnedIndex[$i] = str_replace(" ","_",$returnedIndex[$i]);
		$returnedIndex[$i] = str_replace(":","-",$returnedIndex[$i]);
				
		if(preg_match($SNMP_WALK_INDEX_OID_INCLUDE, $returnedIndex[$i])) {
			$SNMP_Walk_Matched = true;
			if($SNMP_Data_Type == "Integer") {
				echo $returnedIndex[$i].".returnedDataInt ". $returnedData[$i]."\n";

				
			} else {

				echo $returnedIndex[$i].".returnedDataString ".$returnedData[$i]."\n";
			}

		}

	}
	
	if ($SNMP_Walk_Matched == false) {
		echo "No matching index found.  Please check the OID index.\n";
		exit(2);
	}
}


function getLastValue($metricName,$LAST_VALUE_FILE) {
	
	//Initialize Variable
	$data[0] = $metricName;
	$data[1] = 0;
	$data[2] = 0;
	
	if (file_exists($LAST_VALUE_FILE)) {
		$handle = fopen($LAST_VALUE_FILE,"r+") or die("Can't open last value file for read");
		if ($handle) {
			while (!feof($handle)) // Loop til end of file.
			{
				$buffer = fgets($handle, 4096); // Read a line.
				if (preg_match("/".$metricName.".*/", $buffer)) // Check for string.
				{
					$data = preg_split("/-utNetapp-/", $buffer);
				}
			}
			fclose($handle); // Close the file.
		}
	}
	
	return $data;
}

function putLastValue($metricName, $value, $LAST_VALUE_FILE,$CURRENT_TIME) {
	
	// Look for the old value, remove it
	if (file_exists($LAST_VALUE_FILE)) {
		$contents = file_get_contents($LAST_VALUE_FILE);
		$contents = preg_replace("/".$metricName.".*/",'',$contents);
		$contents = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $contents);
		file_put_contents($LAST_VALUE_FILE,$contents);
	}

	$fh = fopen($LAST_VALUE_FILE,"a+") or die("Can't open last value file to write");
	if (flock($fh, LOCK_EX)) {
	
		$stringData = $metricName."-utNetapp-".$value."-utNetapp-".	$CURRENT_TIME."\n";
		
		
		fwrite($fh,$stringData);
		fflush($fh);
		flock($fh, LOCK_UN);
	} else {
	
		echo "Can't lock file!\n";
	}
	fclose($fh);
}


function parseData($data) {
	if(is_array($data)) {
		foreach (array_keys($data) as $i) {					//Added by Isaiah - iterate through the returned data
			$id=substr($i,strripos($i,"."));				//Added by Isaiah - parse the returned oid to retain only the last integer as the row id
			$data_output[$id] = trim(substr(strstr($data[$i], ':'), 1));	//Added by Isaiah - index the output data by the row id
		}
		
	} else {
		$data_output = strstr($data, ':');
		$data_output = substr($data_output, 1);
		$data_output=trim($data_output);
	}
	return $data_output;
}

function getDataType($dataString) {
	$dataType = substr($dataString, 0, strrpos($dataString,':'));
	if (strpos($dataType,'STRING') !== false) {
		$returnDataType = "string";
	} else {
		$returnDataType = "integer";
	}
	return $returnDataType;
}

function get64($msb, $lsb) {
	$count = count($lsb);
	for($i=0; $i < $count; $i++) {
		$value[$i] = bcadd(bcmul($msb[$i], bcpow(2, 32)), $lsb[$i] >= 0?$lsb[$i]:bcsub(bcpow(2, 32), $lsb[$i])); // $a most significant bits, $b least significant bits
	}
	return $value;
}

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function exitWithUnknownStatus($msg)
{
	echo $msg;
	exit(3);
}



?>