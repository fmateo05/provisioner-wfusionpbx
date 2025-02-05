<?php 

/**
 * All methods in this class are protected
 * Accounts APIs
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */
class Accounts {

    public $db;
    private $_log;

    /*private $_FIELDS_ACCOUNT = array('settings', 'name', 'provider_id');
    private $_FIELDS_MAC = array('settings', 'brand', 'family', 'model');*/

    function __construct() {
        // Le EPIC logger init
        $this->_log = KLogger::instance('logs', Klogger::DEBUG);

        $this->_log->logInfo('======================================================');
        $this->_log->logInfo('================== Starting process ==================');
        $this->_log->logInfo('======================================================');
        $this->_log->logDebug("Connecting to BigCouch...");
        $this->db = new BigCouch(DB_SERVER, DB_PORT);
    }

    // Will return the formated account_id from the raw account_id
    private function _get_account_db($account_id) {
        // account/xx/xx/xxxxxxxxxxxxxxxx
        return "account/" . substr_replace(substr_replace($account_id, '/', 2, 0), '/', 5, 0);
    }

    // Yep...
    function options() {
        return;
    }

    /**
     * This will allow the user to get the default settings for an account and for a phone 
     *
     * @url GET /{account_id}
     * @url GET /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function retrieveDocument($account_id, $mac_address = null) {
        $account_db = $this->_get_account_db($account_id);

        // Retrieving the default settings for a user
        if (!$mac_address) {
            $this->_log->logDebug(" - GET - retrieve account doc $account_id");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $default_settings = array();
            $default_settings['data'] = $this->db->get($account_db, $account_id);

            if (isset($default_settings['data']['settings']))
                return $default_settings;
            else {
                $this->_log->logDebug("No account with this id - EXIT");
                throw new RestException(404, 'This account_id do not exist or there are no default settings for this user');
            }
        } else { // retrieving phone specific settings
            $this->_log->logDebug(" - GET - retrieve account doc $account_id for mac doc $mac_address");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $mac_settings = array();
            $mac_settings['data'] = $this->db->get($account_db, $mac_address);

            if (isset($mac_settings['data']['settings']))
                return $mac_settings;
            else {
                $this->_log->logDebug("No doc for this mac address - EXIT");
                throw new RestException(404, 'There is no phone with this mac_address for this account or there are no specific settings for this phone');
            }
        }
    }
    
    /**
     * This will allow the user to modify the account/phone settings
     *
     * @url POST /{account_id}
     * @url POST /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function editDocument($account_id, $mac_address = null, $request_data = null) {
$host= '127.0.0.1';
$database = 'fusionpbx';
$user = 'fusionpbx';
$password = ''; // change to your password
$conn = 'postgres://' . $user . ':' . $password . '@' . $host . '/' . $database  ;
	$input = $account_id ;
	$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input);
        $account_db = $this->_get_account_db($account_id);
        if (!$mac_address) {
            $this->_log->logDebug(" - POST - edit account doc $account_id");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);
            $document_name = $account_id;
        }
        else {
            $this->_log->logDebug(" - POST - edit in doc $account_id mac address $mac_address");
            $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

            $document_name = $mac_address;
            $current_doc = $this->db->get($account_db, $mac_address);

            if (isset($current_doc['settings']['local_port']))
                $request_data['settings']['local_port'] = $current_doc['settings']['local_port'];

            if (isset($request_data['settings']['provision'])) {
	$input_id = $request_data['settings']['id'];
	$device_id = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input_id);
                // This update the brand/model/family if needed.
                $request_data['brand'] = $request_data['settings']['provision']['endpoint_brand'];
                $request_data['family'] = $request_data['settings']['provision']['endpoint_family'];
                $request_data['model'] = $request_data['settings']['provision']['endpoint_model'];
// 		$sql = "UPDATE public.v_devices (device_uuid, domain_uuid, device_address, device_vendor, device_model, device_template) VALUES(" . "'" . $device_id . "','" . $account_uuid . "','" . $mac_address  . "','" . $request_data['settings']['provision']['endpoint_brand'] . "','" . $request_data['settings']['provision']['endpoint_model'] . "', true ,'" . $request_data['settings']['provision']['endpoint_brand'] . "/" . $request_data['settings']['provision']['endpoint_model'] . " WHERE device_uuid=".  $device_id ."');";
		$sql = "UPDATE public.v_devices SET domain_uuid='".$account_uuid."', device_profile_uuid=?, device_address='".$mac_address."', device_label='".$request_data['name']."', device_vendor='". $request_data['settings']['provision']['endpoint_brand'] ."', device_model='".$request_data['settings']['provision']['endpoint_model']."', device_enabled=true, device_template='".$request_data['settings']['provision']['endpoint_brand'] . "/" . $request_data['settings']['provision']['endpoint_model']  ."', device_username='".$request_data['settings']['sip']['username']."', device_password='".$request_data['settings']['sip']['password']."'  WHERE device_uuid='".$device_id."';";
		$sql_data = "UPDATE public.v_domains SET  domain_name=(SELECT domain_name FROM public.v_domains WHERE domain_uuid='" . $account_uuid ."'), domain_description='". $request_data['name'] ."'  WHERE domain_uuid='" . $account_uuid . "';";
		$sql_lines = "UPDATE public.v_device_lines SET domain_uuid='". $account_uuid . "', device_uuid='".$device_id."', line_number='1', server_address=(SELECT domain_name FROM public.v_domains WHERE domain_uuid='".$account_uuid ."'), label='". $request_data['settings']['sip']['username'] ."', display_name='".$request_data['name']."', user_id='". $request_data['settings']['sip']['username']."', auth_id='". $request_data['settings']['sip']['username']."', password='". $request_data['settings']['sip']['password']."', sip_port='7000', sip_transport='udp', register_expires=120, shared_line='', enabled=true WHERE device_line_uuid=(SELECT device_line_uuid FROM public.v_device_lines WHERE device_uuid='". $device_id ."' AND domain_uuid='".$account_uuid."');";
            }
        }
        
        foreach ($request_data as $key => $value) {
            if (!$this->db->update($account_db, $document_name, $key, $value)) {
                $this->_log->logDebug("Could not save key:$key - EXIT");
                throw new RestException(500, 'Error while saving');
            }
        }

        if ($mac_address) {
            $this->_log->logDebug("Will now edit the mac_lookup...");
            if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                if ($this->db->add('mac_lookup', $obj))
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_data . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_lines . '"'  );
		    
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql,true) . "'",FILE_APPEND );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_data,true) . "'",FILE_APPEND );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_lines,true) . "'",FILE_APPEND );
                    return array('status' => true, 'message' => 'Document successfully added');
            } else {
                if (!$this->db->update('mac_lookup', $mac_address, 'account_id', $account_id)) {
                    $this->_log->logDebug("Error... Edit for account $account_id and mac_address $mac_address FAIL");
                    throw new RestException(500, 'Error while saving mac_lookup');
                }
            }
            $this->_log->logDebug("done... Edit for account $account_id and mac_address $mac_address SUCCESS");

		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_data . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_lines . '"'  );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql,true) . "'" );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_data,true) . "'",FILE_APPEND );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_lines,true) . "'",FILE_APPEND );
            return array('status' => true, 'message' => 'Document successfully added');

        } else
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_data . '"'  );
		$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_lines . '"'  );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql,true) . "'");
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_data,true) . "'",FILE_APPEND );
	file_put_contents("/var/www/html/request-data-sql","'". print_r($sql_lines,true) . "'",FILE_APPEND );
            return array('status' => true, 'message' => 'Document successfully added');
    }

    /**
     * This will allow the user to add an account or a phone
     *
     * @class  Auth {@requires user}
     * @url PUT /{account_id}
     * @url PUT /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires user}
     */

    function addDocument($account_id, $mac_address = null, $request_data = null) {
$host= '127.0.0.1';
$database = 'fusionpbx';
$user = 'fusionpbx';
$password = ''; // change to your password
$conn = 'postgres://' . $user . ':' . $password . '@' . $host . '/' . $database  ;
        $this->_log->logDebug(" - PUT - Adding account first...");
        $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

        if (!$request_data) {
            $this->_log->logDebug("Empty body... Stopping here");
            throw new RestException(400, "The body cannot be empty for this request");
        }

        // making sure that the mac_address is well formated
        $mac_address = strtolower(preg_replace('/[:-]/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);

        if ($mac_address) {
            if (!$this->db->isDBexist($account_db)) {
                $this->_log->logDebug("Account $account_id do not exist and trying to add a mac address... EXIT");
                return array('status' => false, 'message' => 'The account do not exist yet');
            }
        }

        $object_ready = $this->db->prepareAddAccounts($request_data, $account_db, $account_id, $mac_address);
	file_put_contents("/var/www/html/request-data","'". print_r($object_ready,true) . "'" );
//	$input = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
	$input = $account_id ;
	$input_id = $request_data['id'];
	$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input);
	$device_id = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input_id);
	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_parent_uuid, domain_name, domain_enabled, domain_description) VALUES(" . "'" . $account_uuid . "'" .   ', null ,' . "'" . $request_data['realm'] . "'" . ',true,' . "'" .  $request_data['name'] . "'" . ");";
	$sql_device = "INSERT INTO public.v_devices (device_uuid, domain_uuid, device_address, device_label, device_vendor, device_model, device_enabled, device_template, device_username, device_password, device_description) VALUES(" . "'" . $device_id . "','" . $account_uuid . "','" . $mac_address  . "','" . $request_data['sip']['username'] . "','" . $request_data['provision']['endpoint_brand'] . "','" . $request_data['provision']['endpoint_model'] . "', true ,'" . $request_data['provision']['endpoint_brand'] . "/" . $request_data['provision']['endpoint_model'] . "','" . $request_data['sip']['username'] .  "','"  . $request_data['sip']['password'] . "','" . $request_data['name'] . "');";
	$sql_line= "INSERT INTO public.v_device_lines (domain_uuid, device_line_uuid, device_uuid, line_number, display_name, user_id, auth_id,password, sip_port, sip_transport, register_expires, enabled) VALUES('" . $account_uuid . "','". $device_id . "','" . $device_id .  "',1,'" . $request['name'] . "','" . $request_data['sip']['username'] . "','" . $request_data['sip']['username'] . "','" . $request_data['sip']['password'] . "',5060, 'udp', 120 ,  true);";
	$sql_line_domain= "UPDATE public.v_device_lines set server_address = (SELECT domain_name FROM public.v_domains WHERE domain_uuid='" . $account_uuid ."' ) WHERE domain_uuid='". $account_uuid  ."' AND device_uuid='". $device_id  ."';";
//	$sql = "INSERT INTO public.v_domains (domain_uuid, domain_parent_uuid, domain_name, domain_enabled, domain_description) VALUES('5f2430f4-e992-1e64-e7fc-be25e67d89a4',null ,'f7b81c.sip.2600hz.com', true, 'phone system prov test 008');";

	$query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
	file_put_contents("/var/www/html/query-sql","'". $sql . "'" );
        if(!$this->db->add($account_db, $object_ready)) {
            $this->_log->logDebug("Fail to add the account... EXIT");
            throw new RestException(500, 'Error while saving');
        } else {
	file_put_contents("/var/www/html/query-sql","'". $sql . "'" );
		$query;
            if ($mac_address) {
                $this->_log->logDebug("Adding the device with mac_address $mac_address...");
                if (!$this->db->isDocExist('mac_lookup', $mac_address)) {
                    $this->_log->logDebug("The mac_lookup...");
                    $obj = array('_id' => $mac_address, 'account_id' => $account_id);
                    if ($this->db->add('mac_lookup', $obj)) {
//		$query;
	$dev_query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_device . '"'  );
	$line_query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_line  . '"'  );
	$line_dom_query = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_line_domain  . '"'  );
	file_put_contents("/var/www/html/query-sql","'". $sql_device . "'" );
	file_put_contents("/var/www/html/query-sql-line","'". $sql_line . "'" );
	file_put_contents("/var/www/html/query-sql-line-domain","'". $sql_line_domain . "'" );
                        $this->_log->logDebug("SUCCESS! exit...");
                        return array('status' => true, 'message' => 'Document successfully added');
                    }
                }
                $this->_log->logDebug("Could not add the mac_lookup entry... EXIT");
                return array('status' => false, 'message' => 'Could not create the mac_lookup document');

            } else {
	file_put_contents("/var/www/html/query-sql","'". $sql . "'" );
		$query;
                $this->_log->logDebug("Successfully Add account");
                return array('status' => true, 'message' => 'Document successfully added');
            }
        }
    }

    /**
     * Delete the whole account or just a phone
     *
     * @url DELETE /{account_id}
     * @url DELETE /{account_id}/{mac_address}
     * @access protected
     * @class  AccessControl {@requires admin}
     */

    function delDocument($account_id, $mac_address = null) {
$host= '127.0.0.1';
$database = 'fusionpbx';
$user = 'fusionpbx';
$password = ''; // change to your password
$conn = 'postgres://' . $user . ':' . $password . '@' . $host . '/' . $database  ;
        // making sure that the mac_address is well fornated
        $mac_address = strtolower(preg_replace('/-/', '', $mac_address));
        $account_db = $this->_get_account_db($account_id);
	$input = $account_id;
	$account_uuid = preg_replace("/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/i", "$1-$2-$3-$4-$5", $input);
	$sql = "DELETE from public.v_domains where domain_uuid=" . "'" . $account_uuid . "'" . ";";
	$sql_mac = "DELETE from public.v_devices where device_address=" . "'" . $mac_address . "'" . ";";
        // Let's first try of the account that we are trying to delete exist
        if ($this->db->isDBexist($account_db)) {
            // If we are trying to delete a device
            if ($mac_address) {
                $this->_log->logDebug(" - DELETE - Deleting a device ($mac_address) for account $account_id...");
                $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);
                // Let's check also if the device that we are trying to delete exist
                if ($this->db->isDocExist($account_db, $mac_address)) {
                    $this->_log->logDebug("The device does exist... let's delete it");
                    // First we delete the device document
                    if (!$this->db->delete($account_db, $mac_address)) {
                        $this->_log->logDebug("Could not delete the device doc ($mac_address) - EXIT");
                        throw new RestException(500, 'Error while deleting');
                    } else {
                        $this->_log->logDebug("Now deleting the mac lookup entry...");
                        // Then we delete the device in the mac_lookup db
                        if (!$this->db->delete('mac_lookup', $mac_address)) {
                            $this->_log->logDebug("Failed to delete the mac_lookup entry ($mac_address) - EXIT");
                            throw new RestException(500, 'Could not delete the lookup entry');
                        }

			$query_mac = shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql_mac . '"'  );
			file_put_contents("/var/www/html/query-del-sql","'". $sql_mac . "'" );
                        $this->_log->logDebug("Successfully deleted device ($mac_address)");
                        return array('status' => true, 'message' => 'Document successfully deleted');
                    }
                } else
                    throw new RestException(404, 'This device do not exist in this account');
            } else { // If we are trying to delete an account
                $this->_log->logDebug(" - DELETE - Deleting an account ($account_id)...");
                $this->_log->logDebug("Request coming from " . $_SERVER['REMOTE_ADDR']);

                $doc_list = $this->db->getAll($account_db);
                $this->_log->logDebug("Retrieved the device list now deleting them...");
                // We get the document list inside of the account database
                foreach ($doc_list['rows'] as $doc) {
                    // /!\ Ghetto hack following...
                    // We check the id of the document to know if it a device doc or the account doc
                    if (preg_match("/^[a-f0-9]{12}$/i", $doc['id'])) {
                        if (!$this->db->delete('mac_lookup', $doc['id'])) {
                            $this->_log->logDebug("Fail to delete the mac_lookup entry for" . $doc['id']);
                            throw new RestException(500, 'Could not delete a lookup entry');
                        }
                    }
                }

                $this->_log->logDebug("All devices for account $account_id deleted, will now delete the account db...");
		shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
		file_put_contents("/var/www/html/query-sql","'". $sql . "'" );
		
                // And let's delete the account database then
                if ($this->db->delete($account_db)) {
		shell_exec("psql -d " . '"' . $conn . '" -c ' . '"' . $sql . '"'  );
                    $this->_log->logDebug("SUCCESS - exit");
                    return array('status' => true, 'message' => 'Account successfully deleted');
                } else {
                    $this->_log->logDebug("Failed to delete the account ($account_id) - EXIT");
		echo	$query;
			$query;
                    throw new RestException(500, 'Could not delete the account database');
                }
            }
        } else {
		$query;
            $this->_log->logDebug("The account ($account_id) do not exist");
            throw new RestException(404, 'This account do not exist');
        }
    }
}
?>
