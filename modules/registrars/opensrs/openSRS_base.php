<?php
/**
 *
 * @ WHMCS FULL DECODED & NULLED
 *
 * @ Version  : 5.2.15
 * @ Author   : MTIMER
 * @ Release on : 2013-12-24
 * @ Website  : http://www.mtimer.cn
 *
 * */

if (file_exists( dirname( __FILE__ ) . "/PEAR/PEAR.php" )) {
	require_once dirname( __FILE__ ) . "/PEAR/PEAR.php";
}


if (file_exists( dirname( __FILE__ ) . "/Crypt/CBC.php" )) {
	require_once dirname( __FILE__ ) . "/Crypt/CBC.php";
}


if (!class_exists( "PEAR" )) {
	return false;
}

require_once "OPS.php";
require_once "country_codes.php";
class openSRS_base extends PEAR {
    public $USERNAME = "";
    public $HRS_USERNAME = "";
    public $PRIVATE_KEY = "";
    public $TEST_PRIVATE_KEY = "";
    public $LIVE_PRIVATE_KEY = "";
    public $HRS_PRIVATE_KEY = "";
    public $VERSION = "XML:0.1";
    public $base_class_version = "2.8.0";
    public $environment = "LIVE";
    public $protocol = "XCP";
    public $LIVE_host = "rr-n1-tor.opensrs.net";
    public $LIVE_port = 55000;
    public $LIVE_sslport = 55443;
    public $TEST_host = "horizon.opensrs.net";
    public $TEST_port = 55000;
    public $TEST_sslport = 55443;
    public $HRS_host;
    public $HRS_port;
    public $HRS_sslport;
    public $REMOTE_HOST;
    public $REMOTE_PORT;
    public $connect_timeout = 20;
    public $read_timeout = 20;
    public $log = array( );
	public $_socket = false;
	public $CRLF= "\r\n";

    public $_socket_error_num = false;
    public $_socket_error_msg = false;
    public $_session_key = false;
    public $_authenticated = false;
    public $_OPS;
    public $_CBC;
    public $lookup_all_tlds = false;
    public $_CRYPT;
    public $_iv;
    public $crypt_type = "SSL";
    public $crypt_mode = "CBC";
    public $crypt_rand_source = MCRYPT_DEV_URANDOM;
    public $affiliate_id;
	public $PERMISSIONS = array( "f_modify_owner" => 1, "f_modify_admin" => 2, "f_modify_billing" => 4, "f_modify_tech" => 8, "f_modify_nameservers" => 16 );
	public $REG_PERIODS = array( 1 => "1 Year", 2 => "2 Years", 3 => "3 Years", 4 => "4 Years", 5 => "5 Years", 6 => "6 Years", 7 => "7 Years", 8 => "8 Years", 9 => "9 Years", 10 => "10 Years" );
	public $UK_REG_PERIODS = array( 2 => "2 Years" );
	public $TV_REG_PERIODS = array( 1 => "1 Year", 2 => "2 Years", 3 => "3 Years", 5 => "5 Years", 10 => "10 Years" );
	public $TRANSFER_PERIODS = array( 1 => "1 Year" );
	public $OPENSRS_TLDS_REGEX = "(\\.ca|\\.(bc|ab|sk|mb|on|qc|nb|ns|pe|nf|nt|nv|yk)\\.ca|\\.com|\\.net|\\.org|\\.co\\.uk|\\.org\\.uk|\\.tv|\\.vc|\\.cc|\\.info|\\.biz|\\.name|\\.us)";
	public $CA_LEGAL_TYPES = array( "ABO" => "Aboriginal", "ASS" => "Association", "CCO" => "Canadian Corporation", "CCT" => "Canadian Citizen", "EDU" => "Educational Institute", "GOV" => "Government", "HOP" => "Hospital", "INB" => "Indian Band", "LAM" => "Library, Archive or Museum", "LGR" => "Legal Respresentative", "MAJ" => "Her Majesty the Queen", "OMK" => "Protected by Trade-marks Act", "PLT" => "Political Party", "PRT" => "Partnership", "RES" => "Permanent Resident", "TDM" => "Trade-mark Owner", "TRD" => "Trade Union", "TRS" => "Trust" );
	public $CA_LANGUAGE_TYPES = array( "EN" => "English", "FR" => "French" );
	public $CA_NATIONALITIES = array( "CND" => "Canadian citizen", "OTH" => "Foreign citizenship", "RES" => "Canadian permanent resident" );
	public $OPENSRS_ACTIONS = array( "get_domain" => true, "get_userinfo" => true, "modify_domain" => true, "renew_domain" => true, "register_domain" => true, "get_nameserver" => true, "create_nameserver" => true, "modify_nameserver" => true, "delete_nameserver" => true, "get_subuser" => true, "add_subuser" => true, "modify_subuser" => true, "delete_subuser" => true, "change_password" => true, "change_ownership" => true, "set_cookie" => true, "delete_cookie" => true, "update_cookie" => true, "sw_register_domain" => true, "bulk_transfer_domain" => true, "lookup_domain" => true, "get_price_domain" => true, "check_transfer_domain" => true, "quit_session" => true, "buy_webcert" => true, "refund_webcert" => true, "query_webcert" => true, "cprefget_webcert" => true, "cprefset_webcert" => true, "cancel_pending_webcert" => true, "update_webcert" => true );


	/**
	 * Class constructor
	 *
	 * Initialize variables, logs, etc.
	 *
	 * @param string  Which environment to use (LIVE or TEST or HRS)
	 * @param string  Which protocol to use (XCP or TPP)
	 *
	 */
	function openSRS_base($environment = NULL, $protocol = NULL, $regusername = NULL, $regprivatekey = NULL) {
		$this->PEAR();
		$this->crypt_type = strtoupper( $this->crypt_type );

		if ("SSL" == $this->crypt_type) {
			if (!function_exists( "version_compare" ) || version_compare( "4.3", phpversion(), ">=" )) {
				$error_message = "PHP version must be v4.3+ (current version is " . phpversion() . ") to use \"SSL\" encryption";
				trigger_error( $error_message, E_USER_ERROR );
				$this->_log( "i", $error_message );
				return false;
			}


			if (!function_exists( "openssl_open" )) {
				$error_message = "PHP must be compiled using --with-openssl to use \"SSL\" encryption";
				trigger_error( $error_message, E_USER_ERROR );
				$this->_log( "i", $error_message );
				return false;
			}
		}

		$this->_log( "i", "OpenSRS Log:" );
		$this->_log( "i", "Initialized: " . date( "r" ) );
		$this->_OPS = new OPS();

		if ($environment) {
			$this->environment = strtoupper( $environment );
		}


		if ($protocol) {
			$this->protocol = strtoupper( $protocol );
		}

		$this->_log( "i", "Environment: " . $this->environment );
		$this->_log( "i", "Protocol: " . $this->protocol );
		$this->_CBC = false;
		$this->USERNAME = $regusername;
		$this->PRIVATE_KEY = $regprivatekey;
	}


	function setProtocol($proto) {
		$proto = trim( strtoupper( $proto ) );
        switch ( $proto )
        {
            case "XCP" :
            case "TPP" :
                $this->protocol = $proto;
                $this->_log( "i", "Set protocol: ".$this->protocol );
                return true;
                break;
            default :
            	return array( "is_success" => false, "error" => "Invalid protocol: ".$proto );
            	break;
        }

	}


	function logout() {
		if ($this->_socket) {
			$this->send_cmd( array( "action" => "quit", "object" => "session" ) );
			$this->close_socket();
		}

	}


	function send_cmd($request) {
		global $HTTP_SERVER_VARS;

		if (!is_array( $request )) {
			$data = array( "is_success" => false, "response_code" => 400, "response_text" => "Invalid command (not an array): " . $request );
			$this->_log( "i", $data );
			return $data;
		}

		$action = $request['action'];
		$object = $request['object'];
		$this->prune_private_keys( $request );

		if (!$this->init_socket()) {
			$data = array( "is_success" => false, "response_code" => 400, "response_text" => "Unable to establish socket: (" . $this->_socket_err_num . ") " . $this->_socket_err_msg );
			$this->_log( "i", $data );
			return $data;
		}


		if ($this->environment == "HRS") {
			$auth = $this->authenticate( $this->HRS_USERNAME, $this->PRIVATE_KEY );
		}
		else {
			$auth = $this->authenticate( $this->USERNAME, $this->PRIVATE_KEY );
		}


		if (!$auth['is_success']) {
			if ($this->_socket) {
				$this->close_socket();
			}

			$data = array( "is_success" => false, "response_code" => 400, "response_text" => "Authentication Error: " . $auth['error'] );
			$this->_log( "i", $data );
			return $data;
		}

		$request['registrant_ip'] = $HTTP_SERVER_VARS['REMOTE_ADDR'];

		if (strstr( $request['action'], "lookup" )) {
			$data = $this->lookup_domain( $request );
		}
		else {
			$this->send_data( $request );
			$data = $this->read_data();
		}

		return $data;
	}


	function validate($data, $params = array()) {
		include "country_codes.php";
		$missing_fields = $problem_fields = array();
		$required_contact_fields = array( "first_name" => "First Name", "last_name" => "Last Name", "org_name" => "Organization Name", "address1" => "Address1", "city" => "City", "country" => "Country", "phone" => "Phone", "email" => "E-Mail" );
		$contact_types = array( "owner" => "", "billing" => "Billing" );
		$required_fields = array( "reg_username" => "Username", "reg_password" => "Password", "domain" => "Domain" );

		if (isset( $params['custom_tech_contact'] )) {
			$contact_types['tech'] = "Tech";
		}


		if ( isset( $params['custom_nameservers'] ) && $data['reg_type'] == "new" ) {
			if (!$data['fqdn1']) {
				$missing_fields[] = "Primary DNS Hostname";
			}


			if (!$data['fqdn2']) {
				$missing_fields[] = "Secondary DNS Hostname";
			}
		}

		foreach ($contact_types as $type => $contact_type) {
			foreach ($required_contact_fields as $field => $required_field) {
				$data[$type . "_" . $field] = trim( $data[$type . "_" . $field] );

				if ($data[$type . "_" . $field] == "") {
					$missing_fields[] = $contact_type . " " . $required_field;
					continue;
				}
			}

			$data[$type . "_country"] = strtolower( $data[$type . "_country"] );

			if ($data[$type . "_country"] == "us" || $data[$type . "_country"] == "ca") {
				if ($data[$type . "_postal_code"] == "") {
					$missing_fields[] = $contact_type . " Zip/Postal Code";
				}


				if ($data[$type . "_state"] == "") {
					$missing_fields[] = $contact_type . " State/Province";
				}
			}


			if (!isset( $COUNTRY_CODES[$data[$type . "_country"]] )) {
				$problem_fields[$contact_type . " Country"] = $data[$type . "_country"];
			}


			if (!$this->check_email_syntax( $data[$type . "_email"] )) {
				$problem_fields[$contact_type . " Email"] = $data[$type . "_email"];
			}


			if (!preg_match( '/^\+?[\d\s\-\.\(\)]+$/', $data[$type . "_phone"] )) {
				$problem_fields[$contact_type . " Phone"] = $data[$type . "_phone"];
				continue;
			}
		}

		foreach ($required_fields as $field => $required_field) {

			if ($data[$field] == "") {
				$missing_fields[] = $required_field;
				continue;
			}
		}

		foreach ($data as $field => $value) {

			if ($value == "") {
				continue;
			}


			if ( ( ( ( $field == "first_name" || $field == "last_name" ) || $field == "org_name" ) || $field == "city" ) || $field == "state" ) {
				if (!preg_match( "/[a-zA-Z]/", $value )) {
					$error_msg .= "Field " . $field . " must contain at least 1 alpha character.<br>\n";
					continue;
				}

				continue;
			}
		}

		foreach ($missing_fields as $field) {
			$error_msg .= "Missing field: " . $field . ".<br>\n";
		}

		$domains = explode( "", $data['domain'] );
		foreach ($domains as $domain) {
			$syntaxError = $this->check_domain_syntax( $domain );

			if ($syntaxError) {
				$problem_fields['Domain'] = $domain . " - " . $syntaxError;
				continue;
			}
		}


		if (count( array_keys( $problem_fields ) )) {
			foreach ($problem_fields as $field => $problem) {

				if ($problem != "") {
					$error_msg .= "The field \"" . $field . "\" contained invalid characters: <i>" . $problem . "</i><br>\n";
					continue;
				}
			}
		}


		if ($error_msg) {
			return array( "error_msg" => $error_msg );
		}

		return array( "is_success" => true );
	}


	function version() {
		return "OpenSRS-PHP Class version " . $this->base_class_version;
	}


	function init_socket() {
		if ($this->_socket) {
			return true;
		}


		if (!$this->environment) {
			return false;
		}

		$this->REMOTE_HOST = $this->$this->environment . "_host";

		if ("SSL" == $this->crypt_type) {
			$this->REMOTE_PORT = $this->$this->environment . "_sslport";
		}
		else {
			$this->REMOTE_PORT = $this->$this->environment . "_port";
		}

		$connection_protocol = "";

		if ("SSL" == $this->crypt_type) {
			$connection_protocol = "ssl://";
		}

		$this->_socket = fsockopen( $connection_protocol . $this->REMOTE_HOST, $this->REMOTE_PORT, $this->_socket_err_num, $this->_socket_err_msg, $this->connect_timeout );

		if (!$this->_socket) {
			return false;
		}

		$this->_log( "i", "Socket initialized: " . $this->REMOTE_HOST . ":" . $this->REMOTE_PORT );
		return true;
	}


	function authenticate($username = false, $private_key = false) {
		if ($this->_authenticated || "SSL" == $this->crypt_type) {
			return array( "is_success" => true );
		}


		if (!$username) {
			return array( "is_success" => false, "error" => "Missing reseller username" );
		}


		if (!$private_key) {
			return array( "is_success" => false, "error" => "Missing private key" );
		}

		$prompt = $this->read_data();

		if ($prompt['response_code'] == 555) {
			return array( "is_success" => false, "error" => $prompt['response_text'] );
		}


		if ( !preg_match( "/OpenSRS\sSERVER/", $prompt['attributes']['sender'] ) || substr( $prompt['attributes']['version'], 0, 3 ) != "XML" ) {
			return array( "is_success" => false, "error" => "Unrecognized Peer" );
		}

		$cmd = array( "action" => "check", "object" => "version", "attributes" => array( "sender" => "OpenSRS CLIENT", "version" => $this->VERSION, "state" => "ready" ) );
		$this->send_data( $cmd );
		$cmd = array( "action" => "authenticate", "object" => "user", "attributes" => array( "crypt_type" => strtolower( $this->crypt_type ), "username" => $username, "password" => $username ) );
		$this->send_data( $cmd );
		$challenge = $this->read_data( array( "no_xml" => true, "binary" => true ) );
		$this->_CBC = new Crypt_CBC( "H*", $private_key )( , $this->crypt_type );
		$response = pack( "H*", md5( $challenge ) );
		$this->send_data( $response, array( "no_xml" => true, "binary" => true ) );
		$answer = $this->read_data();

		if (substr( $answer['response_code'], 0, 1 ) == "2") {
			$this->_authenticated = true;
			return array( "is_success" => true );
		}

		return array( "is_success" => false, "error" => "Authentication failed" );
	}


	function lookup_domain($lookupData) {
		$domain = strtolower( $lookupData['attributes']['domain'] );
		$affiliate_id = $lookupData['attributes']['affiliate_id'];

		if ($domain == "") {
			$data = array( "is_success" => false, "response_code" => 490, "response_text" => "Invalid syntax: no domain given." );
			return $data;
		}

		$syntaxError = $this->check_domain_syntax( $domain );

		if ($syntaxError) {
			$code = 499;

			if (strstr( $syntaxError, "Top level domain in" )) {
				$code = 491;
			}
			else {
				if (strstr( $syntaxError, "Domain name exceeds maximum length" )) {
					$code = 492;
				}
				else {
					if (strstr( $syntaxError, "Invalid domain format" )) {
						$code = 493;
					}
				}
			}

			$data = array( "is_success" => false, "response_code" => $code, "response_text" => "Invalid domain syntax for " . $domain . ": " . $syntaxError . "." );
			return $data;
		}

		$domains = array();
		preg_match( '/(.+)' . $this->OPENSRS_TLDS_REGEX . '$/', $domain, $temp );
		$base = $temp[1];
		$tld = $temp[2];
		$relatedTLDs = $this->getRelatedTLDs( $tld );

		if ( $this->lookup_all_tlds && is_array( $relatedTLDs ) ) {
			$domains = array();
			foreach ($relatedTLDs as $stem) {
				$domains[] = $base . $stem;
			}
		}
		else {
			$domains[] = $domain;
		}

		$data = array();
		foreach ($domains as $local_domain) {
			$lookupData['attributes']['domain'] = $local_domain;
			$this->send_data( $lookupData );
			$answer = $this->read_data();

			if ( ( $answer['attributes']['status'] && stristr( $answer['attributes']['status'], "available" ) ) && !stristr( $local_domain, $domain ) ) {
				$data['attributes']['matches'][] = $local_domain;
			}


			if ($local_domain == $domain) {
				$data['is_success'] = $answer['is_success'];
				$data['response_code'] = $answer['response_code'];
				$data['response_text'] = $answer['response_text'];
				$data['attributes']['status'] = $answer['attributes']['status'];
				$data['attributes']['upg_to_subdomain'] = $answer['attributes']['upg_to_subdomain'];
				$data['attributes']['reason'] = $answer['attributes']['reason'];
				continue;
			}
		}

		return $data;
	}


	function close_socket() {
		fclose( $this->_socket );

		if ($this->_CBC) {
			$this->_CBC->_Crypt_CBC();
		}

		$this->_CBC = false;
		$this->_authenticated = false;
		$this->_socket = false;
		$this->_log( "i", "Socket closed" );
	}


	function read_data($args = array()) {
		$buf = $this->readData( $this->_socket, $this->read_timeout );

		if (!$buf) {
			$data = array( "error" => "Read error" );
			$this->_log( "i", $data );
		}
		else {
			$data = ($this->_CBC ? $this->_CBC->decrypt( $buf ) : $buf);

			if (!$args['no_xml']) {
				$data = $this->_OPS->decode( $data );
			}


			if ($args['binary']) {
				$temp = unpack( "H*temp", $data );
				$this->_log( "r", "BINARY: " . $temp['temp'] );
			}
			else {
				$this->_log( "r", $data );
			}
		}

		return $data;
	}


	function send_data($message, $args = array()) {
		if (!$args['no_xml']) {
			$message['protocol'] = $this->protocol;
			$data_to_send = $this->_OPS->encode( $message );
			$message['action'] = strtolower( $message['action'] );
			$message['object'] = strtolower( $message['object'] );
		}
		else {
			$data_to_send = $message;
		}


		if ($args['binary']) {
			$temp = unpack( "H*temp", $message );
			$this->_log( "s", "BINARY: " . $temp['temp'] );
		}
		else {
			$this->_log( "s", $message );
		}


		if ($this->_CBC) {
			$data_to_send = $this->_CBC->encrypt( $data_to_send );
		}

		return $this->writeData( $this->_socket, $data_to_send );
	}


	function check_email_syntax($email) {
		if ( preg_match('/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/', $email) || !preg_match('/^\S+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/', $email) ) {
			return false;
		}

		return true;
	}


	function check_domain_syntax($domain) {
		$domain = strtolower( $domain );
		$MAX_UK_LENGTH = 61;
		$MAX_NSI_LENGTH = 67;

		if (substr( $domain, 0 - 3 ) == ".uk") {
			$maxLengthForThisCase = $MAX_UK_LENGTH;
		}
		else {
			$maxLengthForThisCase = $MAX_NSI_LENGTH;
		}


		if ($maxLengthForThisCase < strlen( $domain )) {
			return "Domain name exceeds maximum length for registry (" . $maxLengthForThisCase . ")";
		}


		if (!preg_match( '/' . $OPENSRS_TLDS_REGEX . '$/', $domain )) {
			return "Top level domain in \"" . $domain . "\" is unavailable";
		}


		if (!preg_match( '/^[a-zA-Z0-9][.a-zA-Z0-9\-]*[a-zA-Z0-9]' . $this->OPENSRS_TLDS_REGEX . '$/', $domain )) {
			return "Invalid domain format (try something similar to \"yourname.com\")";
		}

		return false;
	}


	function prune_private_keys($data) {
		if ( is_array( $data ) || is_object( $data ) ) {
			foreach ($data as $value => ) {

				if (substr( $key, 0, 2 ) == "p_") {
					unset( $data[$key] );
					continue;
				}


				if (is_array( $value )) {
					$this->prune_private_keys( $value );
					continue;
				}
			}
		}

	}


	function getRelatedTLDs($tld) {
		if (is_array( $this->RELATED_TLDS )) {
			foreach ($this->RELATED_TLDS as $relatedTLDs) {
				foreach ($relatedTLDs as $TLDstring) {

					if ($TLDstring == $tld) {
						return $relatedTLDs;
					}
				}
			}
		}

		return array();
	}


	function _log($type, $data) {
		$types = array( "i" => "Info", "r" => "Read", "s" => "Sent" );
        $temp = sprintf("[ %s%s ]\n",
            strtoupper($types[$type]),
            (($type!='i' && $this->_CBC) ? ' - '.$this->crypt_type.' ENCRYPTED' : '')
        );
		ob_start();
		print_r( $data );
		$temp .= ob_get_contents() . "\n";
		ob_end_clean();
		$this->log[] = $temp;
	}


	function showlog() {
		echo "<PRE>";
		foreach ($this->log as $line) {
			echo htmlentities( $line ) . "\n";
		}

		echo "</PRE>";
	}


	/**
	 * Writes a message to a socket (buffered IO)
	 *
	 * @param int     socket handle
	 *
	 * @param string  message to write
	 *
	 */
	function writeData(&$fh, $msg) {
		$header = "";
		$len = strlen( $msg );
		switch ($this->crypt_type) {
		case "SSL":				
			$signature = md5( md5( $msg . $this->PRIVATE_KEY ) . $this->PRIVATE_KEY );
			$header .= "POST / HTTP/1.0" . $this->CRLF;
			$header .= "Content-Type: text/xml" . $this->CRLF;
			$header .= "X-Username: " . $this->USERNAME . $this->CRLF;
			$header .= "X-Signature: " . $signature . $this->CRLF;
			$header .= "Content-Length: " . $len . $this->CRLF . $this->CRLF;
			break;

		case "BLOWFISH":
		case "DES":
		default:
			$header .= "Content-Length: " . $len . $this->CRLF . $this->CRLF;
			break;
		}

		fputs( $fh, $header );
		fputs( $fh, $msg, $len );

		if ("SSL" == $this->crypt_type) {
			$this->_OPS->_log( "http", "w", $header . $msg );
		}

		$this->_OPS->_log( "raw", "w", $msg, $len );
	}


	/**
	 * Reads header data
	 *
	 * @param int     socket handle
	 *
	 * @param int     timeout for read
	 *
	 * @return hash hash containing header key/value pairs
	 *
	 */
	function readHeader($fh, $timeout = 5) {
		$header = array();
		switch ($this->crypt_type) {
		case "SSL":
				$http_log = "";
				$line = fgets( $fh, 4000 );
				$http_log .= $line;

				if (!preg_match( '/^HTTP\/1.1 ([0-9]{0,3}) (.*)\r\n$/', $line, $matches )) {
					$this->_OPS->_log( "raw", "e", "UNEXPECTED READ: Unable to parse HTTP response code" );
					$this->_OPS->_log( "raw", "r", $line );
					return false;
				}

				$header['http_response_code'] = $matches[1];
				$header['http_response_text'] = $matches[2];

				while ($line != $this->CRLF) {
					$line = fgets( $fh, 4000 );
					$http_log .= $line;

					if (feof( $fh )) {
						$this->_OPS->_log( "raw", "e", "UNEXPECTED READ: Error reading HTTP header" );
						$this->_OPS->_log( "raw", "r", $line );
						return false;
					}

					$matches = explode( ": ", $line, 2 );

					if (sizeof( $matches ) == 2) {
						$header[trim( strtolower( $matches[0] ) )] = $matches[1];
						continue;
					}
				}

				$header['full_header'] = $http_log;
				break;

		case "BLOWFISH":
		case "DES":
		default:
			$line = fgets( $fh, 4000 );

			if ($this->_OPS->socketStatus( $fh )) {
				return false;
			}


			if (preg_match( '/^\s*Content-Length:\s+(\d+)\s*\r\n/i', $line, $matches )) {
				$header['content-length'] = (int)$matches[1];
			}
			else {
				$this->_OPS->_log( "raw", "e", "UNEXPECTED READ: No Content-Length" );
				$this->_OPS->_log( "raw", "r", $line );
				return false;
			}

			$line = fread( $fh, 2 );

			if ($this->_OPS->socketStatus( $fh )) {
				return false;
			}


			if ($line != $this->CRLF) {
				$this->_OPS->_log( "raw", "e", "UNEXPECTED READ: No CRLF" );
				$this->_OPS->_log( "raw", "r", $line );
				return false;
			}

			break;
		}


		return $header;
	}


	/**
	 * Reads data from a socket
	 *
	 * @param int     socket handle
	 *
	 * @param int     timeout for read
	 *
	 * @return mixed buffer with data, or an error for a short read
	 *
	 */
	function readData(&$fh, $timeout = 5) {
		$len = 0;
		socket_set_timeout( $fh, $timeout );
		$header = $this->readHeader( $fh, $timeout );

		if ( ( !$header || !isset( $header['content-length'] ) ) || empty( $header['content-length'] ) ) {
			$this->_OPS->_log( "raw", "e", "UNEXPECTED ERROR: No Content-Length header provided!" );
		}

		$len = (int)$header['content-length'];
		$line = "";

		while (strlen( $line ) < $len) {
			$line .= fread( $fh, $len );

			if ($this->_OPS->socketStatus( $fh )) {
				return false;
			}
		}


		if ($line) {
			$buf = $line;
			$this->_OPS->_log( "raw", "r", $line );
		}
		else {
			$buf = false;
			$this->_OPS->_log( "raw", "e", "NEXT LINE SHORT READ (should be " . $len . ")" );
			$this->_OPS->_log( "raw", "r", $line );
		}


		if ("SSL" == $this->crypt_type) {
			$this->_OPS->_log( "http", "r", $header['full_header'] . $line );
			$this->close_socket();
		}

		return $buf;
	}


}


?>