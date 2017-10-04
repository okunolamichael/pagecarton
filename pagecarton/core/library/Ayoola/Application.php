<?php

class Ayoola_Application
{
	public static $disableOutput = false; 
	
	public static $playMode; 

    /**
     * Whether to log every visit or not
     * 
     * @var boolean
     */
	public static $accessLogging = true;

	protected static $_autoloader;


    /**
     * The Info of User of the Application
     * 
     * @var array
     */
	protected static $_userInfo;

    /**
     * Use to pass parameter accross application
     * 
     * @var array
     */
	public static $GLOBAL = array();

    /**
     * The Settings of the current running
     * 
     * @var array
     */
	protected static $_runtimeSetting;

    /**
     * The Settings of the current domain
     * 
     * @var array
     */
	protected static $_domainSettings;

    /**
     * The system user account information
     * 
     * @var array
     */
	protected static $_userAccountInfo;
	
    /**
     * The Home and Default page
     * 
     * @var string 
     */
	public static $_homePage = '/';
	public static $_notFoundPage = '/404';
	
    /**
     * 
     * @var string 
     */
	protected static $_requestedUri;
	
    /**
     * 
     * @var string 
     */
	protected static $_urlPrefix;
	
    /**
     * 
     * @var string 
     */
	protected static $_presentUri;
	
    /**
     * The requested domain name
     * 
     * @var string 
     */
	protected static $_domainName;
	
    /**
     * Application mode e.g. document/page
     * 
     * @var string 
     */
	public static $mode;
	
    /**
     * Ayoola Framework software version
     * 
     * @var string 
     */
	public static $version = '1.6.1';
	
    /**
     * Ayoola Framework software installer
     * 
     * @var string 
     */
	public static $installer = 'pc_installer.php';    
 	
	
    /**
     * Returns the current runtime settings
     * 
     * @return array
     */
	public static function getRuntimeSettings( $key = null )
    {
	//	new Application_Domain();
		return ! array_key_exists( $key, self::$_runtimeSetting ) ? (array) self::$_runtimeSetting : self::$_runtimeSetting[$key];
    } 
	
    /**
     * 
     * 
     * @return string
     */
	public static function sanitizeDomainName( $domainName )
    {
	
	} 
	
    /**
     * Returns _domainName
     * 
     * @return string
     */
	public static function getDomainName( array $options = null )
    {
		if( ! is_null( self::$_domainName ) )
		{ 
			return self::$_domainName;
		}
			//		var_export( $domainName );
		$storage = new Ayoola_Storage();
		if( empty( $options['no_cache'] ) )
		{
			require_once 'Ayoola/Storage.php';
			$storage->storageNamespace = __METHOD__;
			$storage->setDevice( 'File' );
			$data = $storage->retrieve(); 
		}
		if( @$data )
		{		
			self::$_domainName =  $data;
			return self::$_domainName;
		}
		$filter = new Ayoola_Filter_DomainName();
		$domainName = $filter->filter( $_SERVER['HTTP_HOST'] );  
		
/* 	//	exit( $domainName );
		
		//	debug
	//	$domainName = 'pagecarton.com';
		$domainName = str_ireplace( 'www.', '', strtolower( $domainName ) ); 
		$domainName = explode( ':', $domainName );
		$domainName = array_shift( $domainName );
		
		if( strpos( $domainName, '.document.' ) )
		{ 
			//	allow the document to look in the parent domain
			list( ,$domainName ) = explode( '.document.', $domainName );
	//		break; 
		} 
 */		
		//	testing
	//	$domainName = $domainName . '.localhost';
				
		//	Store to global use
		$storage->store( $domainName );
		
		//	Store for request use
		self::$_domainName = $domainName;

		return self::$_domainName;
	}
	 
    /**
     * Returns the settings of the current domain
     * 
     * @return array
     */
	public static function getDomainSettings( $key = null )
    {
		if( is_null( self::$_domainSettings ) )
		{ 
			// This is needed in Application_Domain Table
			self::$_domainSettings[APPLICATION_DIR] = APPLICATION_DIR;
			self::$_domainSettings[APPLICATION_PATH] = APPLICATION_PATH;
			@self::$_domainSettings[EXTENSIONS_PATH] = EXTENSIONS_PATH;
			self::setDomainSettings();
	//		var_export( self::$_domainSettings );
		}
		
		return $key ? @self::$_domainSettings[$key] : self::$_domainSettings;
	}
	
    /**
     * Returns the settings of the current domain
     * 
     * @param boolean Whether to force a reset
     * @return array
     */
	public static function setDomainSettings( $forceReset = null )
    {
	//		var_export( $domainName );
		require_once 'Ayoola/Storage.php';
		$storage = new Ayoola_Storage();
		$protocol = 'http';
		if( $_SERVER['SERVER_PORT'] == 443 && ! empty( $_SERVER['HTTPS'] ) )
		{
			$protocol = 'https';
		}


		$storage->storageNamespace = __CLASS__ . '---x--' . $_SERVER['HTTP_HOST'] . $protocol;
		$storage->setDevice( 'File' );
		$data = $storage->retrieve(); 
		if(  $data && ! $forceReset && ! @$_GET['reset_domain_information'] )
		{		
		//	var_export( $data );
 			//	Allows the sub-domains to have an include path too.
		//	set_include_path( $data['domain_settings'][APPLICATION_PATH] . PS . $data['domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );    
			if( @$data['parent_domain_settings'] )
			{
				set_include_path( $data['parent_domain_settings'][APPLICATION_PATH] . PS . $data['parent_domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );
			}
			//	Allows the sub-domains to have an include path too.
			set_include_path( $data['domain_settings'][APPLICATION_PATH] . PS . $data['domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );
			self::$_domainSettings =  $data['domain_settings'];  
			if( ! empty( $data['domain_settings']['username'] ) )
			{
				Ayoola_Application::$GLOBAL = $data['domain_settings'];
			//	var_export( Ayoola_Application::$GLOBAL );
			}
		//	var_export( $data );
			return true;      
		}  
	//	var_export( $_SERVER );

		//	Search the domain name in the domain table
		do
		{
			$data = array();
			//	var_export( $data );
			$domainName = self::getDomainName();
			//	var_export( $domainName );
			
			//	Ignore localhosts
		//	if( count( explode( '.', $domainName ) ) == 1 ){ break; }
			if( '127.0.0.1' == $_SERVER['REMOTE_ADDR'] )
			{ 
		//		$domainName = 'localhost';
			//	break; 
			}
			
			require_once 'Application/Domain.php';
			$domain = new Application_Domain();
		//	var_export( $_SERVER );
		//	exit( var_export( $domainName ) );
			$where = array( 'domain_name' => $domainName );
			$tempDomainName = $domainName;
			$tempWhere = $where;
			while( ! $data['domain_settings'] = $domain->selectOne( null, $tempWhere ) ) 
			{
				if( '127.0.0.1' == $_SERVER['REMOTE_ADDR'] )
				{ 
			//		$domainName = 'localhost';
					break; 
				}
					$tempDomainName = explode( '.', $tempDomainName );
				if( count( $tempDomainName ) < 2 ){ break; }
				$subDomain = array_shift( $tempDomainName );	// Fix wildcard domainnames			
				$tempDomainName = implode( '.', $tempDomainName );
				$tempWhere = array( 'domain_name' => $tempDomainName );
			//	var_export( $tempWhere );
				
			}
			if( ! @$subDomain && @in_array( 'ssl', @$data['domain_settings']['domain_options'] ) && $protocol != 'https' )
			{
				header( 'Location: https://' . Ayoola_Page::getDefaultDomain() . Ayoola_Application::getPresentUri() . '?' . http_build_query( $_GET ) );
				exit();
			}
			if( ! @$subDomain && @strlen( $data['domain_settings']['enforced_destination'] ) > 3 )
			{
			//	var_export( $subDomain );
			//	exit();
				$enforcedDestination = $data['domain_settings']['enforced_destination'];
			//	var_export( $enforcedDestination );
				if( count( explode( '.', $enforcedDestination ) ) >= 2 )
				{ 
					if( strtolower( $_SERVER['HTTP_HOST'] ) !== strtolower( trim( $enforcedDestination ) ) )
					{
						header( 'Location: ' . $protocol . '://' . $enforcedDestination . Ayoola_Application::getPresentUri() . '?' . http_build_query( $_GET ) );
						exit();
					}
				}
			
			}
			if( ! $data['domain_settings'] && '127.0.0.1' !== $_SERVER['REMOTE_ADDR'] )
			{
				if( ! $domain->select() && ( '127.0.0.1' !== $_SERVER['REMOTE_ADDR'] ) )
				{
					$domain->insert( $where );
					$data['domain_settings'] = $where;
					break;
				}
				header( "HTTP/1.0 404 Not Found" );
				header( "HTTP/1.1 404 Not Found" );
				Header('Status: 404 Not Found');
				exit( 'DOMAIN NOT FOUND' );
			}
			else
			{
				//	We have found our domain name
				//	Inventing a personal APPLICATION_PATH also for the "default" Installation
				
				//	Get settings for the primary domain for inheritance
				if( ! $primaryDomainInfo = $domain->selectOne( null, array( 'domain_type' => 'primary_domain' ) ) )
				{
					$primaryDomainInfo = $data['domain_settings'];
				}
			//	var_export( $primaryDomainInfo );
				
			//	$domainDir = Application_Domain_Abstract::getSubDomainDirectory( Ayoola_Page::getDefaultDomain() );
				$oldDomainDir = Application_Domain_Abstract::getSubDomainDirectory( @$primaryDomainInfo['domain_name'] ? : Ayoola_Page::getDefaultDomain() );   

			//	var_export( @$primaryDomainInfo['domain_name'] ? : Ayoola_Page::getDefaultDomain()  );
		//		var_export( $oldDomainDir );
				//	we need to change this to main dir
				$domainDir = Application_Domain_Abstract::getSubDomainDirectory( 'default' );
		//		var_export( $domainDir );  
				if( is_dir( $oldDomainDir ) )  
				{
					if( ! is_dir( $domainDir ) )
					{
						mkdir( $domainDir, 0700, true );
						Ayoola_Doc::recursiveCopy( $oldDomainDir, $domainDir );
					}
					rename( $oldDomainDir, $oldDomainDir . '.old.' . time() );
				}  
				
				
				if( @strlen( $primaryDomainInfo['application_dir'] ) > 3 )
				{
					$customDir =  Application_Domain_Abstract::getSubDomainDirectory( $primaryDomainInfo['application_dir'] );
					$oldCustomDir = APPLICATION_DIR . $primaryDomainInfo['application_dir'];
					
					//	compatibility.
					if( defined( 'PC_BASE' ) )
					{
						if( is_dir( $oldCustomDir ) )
						{
							mkdir( $customDir, 0700, true );
							Ayoola_Doc::recursiveCopy( $oldCustomDir, $customDir );
							rename( $oldCustomDir, $oldCustomDir . '.old' );
						}
					}
					else
					{
						$customDir =  $oldCustomDir;
					}
					
					$data['domain_settings'][APPLICATION_DIR] = $primaryDomainInfo[APPLICATION_DIR] = $customDir; 
					$data['domain_settings'][APPLICATION_PATH] = $primaryDomainInfo[APPLICATION_PATH] = $primaryDomainInfo[APPLICATION_DIR] . DS . 'application';
					$data['domain_settings'][EXTENSIONS_PATH] = @$primaryDomainInfo[EXTENSIONS_PATH] = $primaryDomainInfo[APPLICATION_DIR] . DS . 'extensions';
				}
				elseif( is_dir( $domainDir ) )
				{
				//	For backward compatibility, the directory must be "consciously" set
				//	var_export( __LINE__ );
					$data['domain_settings'][APPLICATION_DIR] = $primaryDomainInfo[APPLICATION_DIR] = $domainDir;  
					$data['domain_settings'][APPLICATION_PATH] = $primaryDomainInfo[APPLICATION_PATH] = $primaryDomainInfo[APPLICATION_DIR] . DS . 'application';
					@$data['domain_settings'][EXTENSIONS_PATH] = @$primaryDomainInfo[EXTENSIONS_PATH] = $primaryDomainInfo[APPLICATION_DIR] . DS . 'extensions';
				}
			//	var_export( $primaryDomainInfo );
				
				
				//	How do we allow the user accounts in the primary domain visible here?
 			//	if( @$primaryDomainInfo['domain_name'] !== @$data['domain_settings']['domain_name'] )
				{
					$data['parent_domain_settings'] = $primaryDomainInfo;
					@set_include_path( $data['parent_domain_settings'][APPLICATION_PATH] . PS . $data['parent_domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );  
				}
				
 			//	var_export( $primaryDomainInfo );
			//	exit();
				
			//	$domainDir = Application_Domain_Abstract::getSubDomainDirectory( Ayoola_Page::getDefaultDomain() );
	//			$domainDir = Application_Domain_Abstract::getSubDomainDirectory( $tempWhere['domain_name'] );
	//		var_export( $domainDir );  
				
/* 				
				if( @strlen( $data['domain_settings']['application_dir'] ) > 3 )
				{
					$data['domain_settings'][APPLICATION_DIR] = APPLICATION_DIR . $data['domain_settings']['application_dir']; 
					$data['domain_settings'][APPLICATION_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'application';
					@$data['domain_settings'][EXTENSIONS_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'extensions';
				}
				elseif( is_dir( $domainDir ) )
				{
				//	For backward compatibility, the directory must be "consciously" set
				//	var_export( __LINE__ );
					$data['domain_settings'][APPLICATION_DIR] = $domainDir;  
					$data['domain_settings'][APPLICATION_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'application';
					@$data['domain_settings'][EXTENSIONS_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'extensions';
				//	var_export( $data );
				}
 *//* 					if( $domainName == 'test.pagecarton.com' )
					{
						var_export( $data );
					}
 */			}
			//	check subdomain
			if( @$subDomain )
			if( $subDomainInfo = $domain->selectOne( null, array( 'domain_name' => $subDomain ) ) )
			{
				//	var_export( $subDomainInfo );
				if( @$subDomainInfo['sub_domain'] || @$subDomainInfo['domain_type'] === 'sub_domain' )
				{
			//		var_export( $data['domain_settings'] );
					$data['domain_settings'] = $subDomainInfo;
				//	var_export( $subDomainInfo );
					$data['domain_settings']['main_domain'] = $tempWhere['domain_name'];
					$data['domain_settings']['domain_name'] = $subDomain . '.' . $tempWhere['domain_name'];
					$data['domain_settings'][APPLICATION_DIR] = Application_Domain_Abstract::getSubDomainDirectory( $subDomainInfo['domain_name'] );
					$data['domain_settings'][APPLICATION_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'application';
					@$data['domain_settings'][EXTENSIONS_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'extensions';
				//	$storage->store( $data );
				//	setcookie( 'SUB_DIRECTORY', $subDomainInfo['domain_name'], time() + 9999999, '/' );
				}
				elseif( $data['domain_settings']['*'] )
				{
			//		setcookie( 'SUB_DIRECTORY', false, time() - 9999999, '/', $domainName );				header( "HTTP/1.0 404 Not Found" );
					
				}
				else
				{
				//	setcookie( 'SUB_DIRECTORY', false, time() - 9999999, '/', $domainName );
				//	header( 'HTTP/1.1 301 Moved Permanently' );
			//		var_export( $subDomainInfo );
			//		var_export( $data );
				//	header( 'Location: http://' . Ayoola_Page::getDefaultDomain() . Ayoola_Application::getPresentUri() );
					exit( 'INVALID SUB-DOMAIN' );   
				}
			}
			else
			{
				
				//	do we have user domains
				$userInfo = Ayoola_Access::getAccessInformation( $subDomain );
			//	$userOptions = Application_Settings_Abstract::getSettings( 'Domain', 'domain_options' );
				
			//		var_export( in_array( 'user_subdomains', @$data['domain_settings']['domain_options'] ) );
			//		var_export( $data['domain_settings'] );
				//	var_export( $userInfo );
				//	exit();  
					
			//	if( $userInfo && @$userInfo['access_level'] != 99 )
				if( @in_array( 'user_subdomains', @$data['domain_settings']['domain_options'] ) AND ( $userInfo = Ayoola_Access::getAccessInformation( $subDomain ) ) AND @$userInfo['access_level'] != 99  )
				{
			//		var_export( $data['domain_settings'] );
					Ayoola_Application::$GLOBAL = $userInfo;
					$data['domain_settings'] = $userInfo;
				//	var_export( $subDomain );     
				//	Application_Profile_Abstract::saveProfile( $information );
					$data['domain_settings']['main_domain'] = $tempWhere['domain_name'];
			//		$data['domain_settings'][APPLICATION_DIR] = Application_Profile_Abstract::getProfileDir( $userInfo['username'] );
					$data['domain_settings'][APPLICATION_DIR] = $primaryDomainInfo[APPLICATION_DIR] . DS . AYOOLA_MODULE_FILES .  DS . 'profiles' . DS . strtolower( implode( DS, str_split( $userInfo['username'], 2 ) ) );    
					$data['domain_settings'][APPLICATION_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'application';
					@$data['domain_settings'][EXTENSIONS_PATH] = $data['domain_settings'][APPLICATION_DIR] . DS . 'extensions';
				//	var_export( $data['domain_settings'] );
				//	$storage->store( $data );
				//	setcookie( 'SUB_DIRECTORY', $subDomain['domain_name'], time() + 9999999, '/' );
				}
				elseif( ! empty( $tempWhere['domain_name'] ) )
				{
			//		header( 'HTTP/1.1 301 Moved Permanently' );
					header( 'Location: ' . $protocol . '://' . $tempWhere['domain_name'] . Ayoola_Application::getPresentUri() );    
				//	var_export( $data );
					
					exit( 'DOMAIN NOT IN USE' );
				}
				else
				{
			//		header( 'HTTP/1.1 301 Moved Permanently' );
				//	var_export( $data );
					
					exit( 'DOMAIN NOT IN USE' );
				}
			}
		}
		while( false );
		@$data['domain_settings'][APPLICATION_DIR] = $data['domain_settings'][APPLICATION_DIR] ? : APPLICATION_DIR;
		@$data['domain_settings'][APPLICATION_PATH] = $data['domain_settings'][APPLICATION_PATH] ? : APPLICATION_PATH;
		@$data['domain_settings'][EXTENSIONS_PATH] = $data['domain_settings'][EXTENSIONS_PATH] ? : EXTENSIONS_PATH;
		$data['domain_settings']['protocol'] = $protocol;
		
		//	Check if theres a forwarding needed.
	//	unset( $_SESSION['ignore_domain_redirect'] );
		if( @is_array( $data['domain_settings']['domain_options'] ) && in_array( 'redirect', $data['domain_settings']['domain_options'] ) && ! @$_REQUEST['ignore_domain_redirect'] && ! @$_SESSION['ignore_domain_redirect'] )
		{
			header( 'HTTP/1.1 ' . $data['domain_settings']['redirect_code'] );
			$toGo = $protocol . '://' . $data['domain_settings']['redirect_destination'] . Ayoola_Application::getPresentUri();
			header( 'Location: ' . $toGo );
		//	var_export( $data );
			exit( 'REDIRECTING TO' );
		}
		elseif( @$_REQUEST['ignore_domain_redirect'] || @$_SESSION['ignore_domain_redirect'] )
		{
		//	unset( $_SESSION['ignore_domain_redirect'] );
		//	var_export( $_REQUEST['ignore_domain_redirect'] );
		//	var_export( $_SESSION['ignore_domain_redirect'] );
			$_SESSION['ignore_domain_redirect'] = true;
		//	var_export( $_REQUEST['ignore_domain_redirect'] );
		//	$storage->clear();
		//	exit();
		}
		else
		{
		//	var_export( $primaryDomainInfo );
		//	var_export( $data );
			
			if( @$primaryDomainInfo['domain_name'] !== @$data['domain_settings']['domain_name'] )
			{
				$data['parent_domain_settings'] = $primaryDomainInfo;
				@set_include_path( $data['parent_domain_settings'][APPLICATION_PATH] . PS . $data['parent_domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );
			}
		//	var_export( $data );
			$storage->store( $data );
		}
		//	Allows the sub-domains to have an include path too.
		set_include_path( $data['domain_settings'][APPLICATION_PATH] . PS . $data['domain_settings'][APPLICATION_PATH] . '/modules' . PS . get_include_path() );
		self::$_domainSettings = $data['domain_settings'];
		//	var_export( $data );
		return true;
    } 

    public static function boot()
    {
		
		date_default_timezone_set( 'UTC' );
		self::loadPrerequisites();
		require_once 'Ayoola/Loader/Autoloader.php';
		self::$_autoloader = Ayoola_Loader_Autoloader::getInstance();
		
		//  
		Ayoola_Event_NewSession::viewInLine();

		//	Error / Exception handling
		$errorMessage = '"Uncaught Exception " . get_class( $object ) . " with message " . $object->getMessage() . " in  " . $object->getFile() . " on line " . $object->getLine() . " Stack trace: " . $object->getTraceAsString() ';
		$errorHandler = create_function
		( 
			'$object', 
			'Application_Log_View_Error::log( ' . $errorMessage . ' );' 
		);   
	//	var_export( $errorHandler );
		set_exception_handler( $errorHandler );
		
		//	Handle encryption
		switch( @$_SERVER['HTTP_AYOOLA_PLAY_MODE'] ) 
		{
			case 'ENCRYPTION':
				$_POST = array();				
				$data = file_get_contents( "php://input" );
			//	echo $data;
			//	var_export( $data );
			//	exit();
				if( $decrypted = OpenSSL::decrypt( $data, $_SERVER['HTTP_PAGECARTON_REQUEST_ENCRYPTION'] ) )
				{
					parse_str( $decrypted, $result ); 
					$_POST = is_array( $result ) ? $result : array();
					if( isset( $_POST['pagecarton_request_timezone'], $_POST['pagecarton_request_time'], $_POST['pagecarton_request_timeout'] ) )
					{
					//	$_POST['pagecarton_request_datetime'] = date_create( $_POST['pagecarton_request_datetime'] );
					//	$_POST['pagecarton_request_time'] = date_timestamp_get( $_POST['pagecarton_request_datetime'] );
						date_default_timezone_set( $_POST['pagecarton_request_timezone'] );
						//	var_export( time() );
						//	var_export( $_POST['pagecarton_request_time'] );
						//	var_export( time() - $_POST['pagecarton_request_time'] );
						if( ( time() - $_POST['pagecarton_request_time'] ) > $_POST['pagecarton_request_timeout'] )
						{
							$_POST = array();
						}
					
					}
				//	var_export( $decrypted );
			//		var_export( $_POST );
			//		exit();
				}
			//	var_export( $encrypted );
			//	echo $decrypted;
				
			//	var_export( $data );
			//	exit();
				//	Log early before we exit
		//		Ayoola_Application::log();
			//	if( ! self::hasPriviledge() )
				{
				//	exit();
				}
			break; 
		}
		
	//	throw new Exception( 'aaaa' );
    }
    public static function run()
    {
//		exit( 'here' );
        // run application
	//	sleep(ini_get('max_execution_time') + 10);
		$time_start = microtime( true );
	//	var_export( memory_get_usage ( true ) . '<br />' );
		self::$_runtimeSetting['start_time'] = $time_start; //	Record start time
		
		//	Record IP Address
		@self::$_runtimeSetting['user_ip'] = array( 'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'], 'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'], 'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'], );
;
		self::boot();
	//	self::$_runtimeSetting['real_url'] = URI;   
	//	self::$_runtimeSetting['url'] = URI;
		self::$_runtimeSetting['real_url'] = Ayoola_Application::getPresentUri();
		self::$_runtimeSetting['url'] = Ayoola_Application::getPresentUri();

		//	Domain settings
	//	self::getDomainSettings();
	//	var_export( microtime( true ) - Ayoola_Application::$_runtimeSetting['start_time'] );
		
	//	var_export( memory_get_usage ( true ) . '<br />' );
		self::display();
	//	var_export( microtime( true ) - Ayoola_Application::$_runtimeSetting['start_time'] );
	//	exit();
	//	var_export( memory_get_usage ( true ) . '<br />' );
		$time_end = microtime( true );
		
	//	self::v( microtime( true ) - Ayoola_Application::$_runtimeSetting['start_time'] );
		self::$_runtimeSetting['total_runtime'] = $time_end - $time_start; //	Record total runtime
	//	var_export( self::$_runtimeSetting['total_runtime'] );  
		
		//	Ignore localhosts and super users
 //		if( ! Ayoola_Page::hasPriviledge() )
 		if( ! in_array( $_SERVER['REMOTE_ADDR' ], array( '127.0.0.1', '::1' ) ) )
		{
			self::log();
		}
 	//	var_export( self::$_runtimeSetting['total_runtime'] . '<br />' );
	//	var_export( $time_end . '<br />' );
 
    }
	
    /**
     * Display The Page
     *
     * @param void
     * @return void
     */
    public static function display()
    {
		// Delete PHP version
	//	header( "X-Powered-By:" );
		//	var_export( Ayoola_Application::getUserInfo() );
			//	new session
		$uri = Ayoola_Application::getPresentUri();
	//	var_export( $uri );	
		
		//	var_export( strpos( $uri, PC_PATH_PREFIX ) );
		
		//	var_export( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );

		//	Handle Files and Documents differently, as per type of file
		$explodedUri = explode( '.', $uri );
		$extension = strtolower( array_pop( $explodedUri ) );
	//	var_export( $extension );
		if( count( $explodedUri ) > 0 && strlen( $extension ) <= 5 )  
		{
			try
			{
				do 
				{
					self::$mode = 'document';
					
					//	Check if this is an article
					$article = Application_Article_Abstract::getFolder() . $uri;
					if( is_file( $article ) )
					{
						self::$mode = 'post';
					//	$articleInfo = @include $article;
						$articleInfo = Application_Article_Abstract::loadPostData( $article );
						if( $articleInfo['username'] )
						{
							try
							{
								if( $userInfoForArticle = Ayoola_Access::getAccessInformation( $articleInfo['username'] ) )
								{
									$articleInfo += $userInfoForArticle;
								}
							}
							catch( Exception $e )
							{
							//	echo $e->getMessage();
							//	var_export( $articleInfo['username'] );
							}
						}
						self::$GLOBAL += is_array( $articleInfo ) ? $articleInfo : array(); // store this in the global var 
						
						
						//	introducing x_url so that user can determine the url to display a post
						if( @$_REQUEST['x_url'] )
						{
							$moduleInfo = Ayoola_Page::getInfo( $_REQUEST['x_url'] ); 
							if( ( ! empty( $moduleInfo ) && in_array( 'module', $moduleInfo['page_options'] ) ) )
							{
								self::$_runtimeSetting['real_url'] = $_REQUEST['x_url'];
							}
							else
							{
								self::$_runtimeSetting['real_url'] = Application_Article_Abstract::getPostUrl() ? : '/';
							}
						}
						else
						{
						//	Ayoola_Abstract_Table::v( self::$_runtimeSetting['real_url'] );
					//		$moduleInfo = Ayoola_Page::getInfo( self::$_runtimeSetting['real_url'] ); 
						//	Ayoola_Page::v( $moduleInfo );
						//	Ayoola_Page::v( '/' . $articleInfo['true_post_type'] . '/post' );     
						//	Ayoola_Page::v( '/' . $articleInfo['article_type'] . '/post' );       
							if( ( ! empty( $articleInfo['article_type'] ) ) AND ( $moduleInfo = Ayoola_Page::getInfo( '/' . $articleInfo['article_type'] . '/post' ) ) AND ( ! empty( $moduleInfo ) && @in_array( 'module', $moduleInfo['page_options'] ) ) )
							{
								//	allow dedicated url for all post types like /download/posts/
								
								self::$_runtimeSetting['real_url'] = '/' . $articleInfo['article_type'] . '/post';							
								
							}
							elseif( ( ! empty( $articleInfo['true_post_type'] ) ) AND  ( $moduleInfo = Ayoola_Page::getInfo( '/' . $articleInfo['true_post_type'] . '/post' ) ) AND ( ! empty( $moduleInfo ) && @in_array( 'module', $moduleInfo['page_options'] ) ) )
							{
								//	allow dedicated url for all post types like /download/posts/
								
								self::$_runtimeSetting['real_url'] = '/' . $articleInfo['true_post_type'] . '/post';							
							}   
							else
							{
								self::$_runtimeSetting['real_url'] = '/post/view';								
								$moduleInfo = Ayoola_Page::getInfo( self::$_runtimeSetting['real_url'] ); 
								if( ( ! empty( $moduleInfo ) && in_array( 'module', $moduleInfo['page_options'] ) ) )
								{
									//	allow dedicated url for all post types like /download/posts/
									
								}
								else
								{
									self::$_runtimeSetting['real_url'] = Application_Article_Abstract::getPostUrl() ? : '/';
								}
							}
						}
						self::view( self::$_runtimeSetting['real_url'] );
						break;
					//	exit();
					}
					
					//	Enable Cache for Documents
					// seconds, minutes, hours, days
					$expires = 60 * 60 * 24 * 14; // 14 days
					require_once 'Ayoola/Doc.php';
			//		var_export( $uri );
					$fn = DOCUMENTS_DIR . DS . $uri;
				//	var_export( $fn );
				//	exit();
					if( ! $fn = Ayoola_Loader::checkFile( $fn, array( 'prioritize_my_copy' => true ) ) )
					{
					//	throw new Exception( 'File not found' );
					}
					
	//	var_export( $fn );    
	//				exit();
					//	cache some files forever to reduce connection rates
					$catchForever = false;
					$firstPart = strtolower( array_shift( explode( '/', trim( $uri, '/' ) ) ) );
					switch( $firstPart )
					{
						case 'css':
						case 'js':
						case 'open-iconic':
						case 'js':
						case 'loading.gif':
						case 'loading2.gif':    
						case 'js':
							//	files already using document time
							$catchForever = true;
						break;
						case 'layout':
							switch( $extension )
							{
								case 'css':
								case 'js':
									//	files already using document time
									$catchForever = true;
								break;
							}
						break;
					}
/*					var_export( $firstPart );
					var_export( $extension );
					var_export( $catchForever );
					exit();
*/					if( $_REQUEST['document_time'] )
					{
						//	files already using document time
						$catchForever = true;
					}
					if( $catchForever )
					{
						#  https://stackoverflow.com/questions/7324242/headers-for-png-image-output-to-make-sure-it-gets-cached-at-browser
						header('Pragma: public');
						header('Cache-Control: max-age=8640000');
						header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 8640000));
					}
					else
					{
						header('Cache-Control: private');
						// Checking if the client is validating his cache and if it is current.
						if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($fn))) {
							// Client's cache IS current, so we just respond '304 Not Modified'.
							header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
							exit(); 
						} else {
							// Image not cached or cache outdated, we respond '200 OK' and output the image.
							header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
						}
					}
					header( 'Content-Length: ' . filesize( $fn ) );
					

			//		var_export( headers_list() );
	//			var_export( $uri );
			
					//	DONT LOGG DOCUMENTS
					self::$accessLogging = false;
					$doc = new Ayoola_Doc( array( 'option' => $uri ) );	;
					if( ! $view = $doc->view() )
					{
						//	affecting LIVE server
					//	throw new Ayoola_Exception( 'DOCUMENT COULD NOT BE DISPLAYED: ' . $uri );
					}
				//	echo $view;
					break;
				//	exit();
				}
				while( false );
				return true;
			}
		//	catch( Ayoola_Doc_Exception $e )
			catch( Ayoola_Exception $e )
			{
				
		//		echo $e->getMessage();
				// 	Possibly File not found
				//	This may not work unless a setting is made with the webserver
/* 				$uri = '/404';
				header( "HTTP/1.0 404 Not Found" );
				header( "HTTP/1.1 404 Not Found" );
				Header('Status: 404 Not Found');
				self::view();
 */			//	break;
			//	exit();
			}
			//	Decided not to halt the script here 
			//	This is mainly because of PHP Documents
			//	If not, this chunk should stop here, because the file has been executed/
		//	exit();
			
			require_once 'Ayoola/Loader.php';
		}
		if( stripos( $_SERVER['HTTP_HOST'], '.document.' ) )
		{ 
		//		header( 'HTTP/1.1 301 Moved Permanently' );
				header( 'Location: http://' . Ayoola_Page::getDefaultDomain() . Ayoola_Page::getPortNumber() . Ayoola_Application::getPresentUri() );
				exit(); 
		}
		else
		{

		//	$pagePaths = Ayoola_Page::getPagePaths( $uri );
		//	var_export( $pageOptions );
		//	exit( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
			do 
			{
				
				
				self::$mode = 'uri';
			//	var_export( $uri );
			//	var_export( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
				
				if( self::view( $uri ) )
				{
					break;
			//		exit();
				}
			//	var_export( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
				//	Check if this is a module url that carries $_GET parameters e.g. /article/category/business/
			//	$a = explode( '/', trim( $uri, '/' ) );
				$a = explode( '/', $uri );
				$nameForModule = array_shift( $a );
				$module = '/' . $nameForModule;
			//	var_export( $a );
			
				//	If the first level url is a valid page, then its the module, else, its the homepage
				$firstLevelPage = '/' . $a[0];
				if( Ayoola_Page::getInfo( $firstLevelPage ) )
				{
					//	We are not using "/"
					$nameForModule = array_shift( $a );
					$module = '/' . $nameForModule;
					
				}
				$moduleInfo = Ayoola_Page::getInfo( $module ); 
			//	var_export( $nameForModule );
			//	var_export( $module );
			//	var_export( $moduleInfo );
				if( ( ! empty( $moduleInfo ) && @in_array( 'module', $moduleInfo['page_options'] ) ) || $module === '/article' )
				{
					//	Not carrying valid query string pairs
					$get = array();
					$get['pc_module_url_values'] = $a;
					if( count( $a ) % 2 === 0 )
					{
						while( $a )
						{
							$get[array_shift( $a )] = array_shift( $a );
						}
					}

				//	$get = http_build_query( $get );
					$_GET = array_merge( $_GET, $get ); // Combines our generated params with the original 
					$_REQUEST = array_merge( $_REQUEST, $get ); // Combines our generated params with the original 
				//	var_export( $_GET );
					self::$_runtimeSetting['real_url'] = $module;
					self::$mode = 'module';
					if( self::view( $module ) )
					{
						break;
				//		exit();
					}
				}
				else
				{
					//	Allow /username
					try
					{
						if( $nameForModule === '' )
						{
							$nameForModule = array_shift( $a );
						}
				//		var_export( $nameForModule ); 
						if( $nameForModule )  
						{
/* 							$filename = Application_Profile_Abstract::getProfilePath( $nameForModule );
							//	getAccessInformation
							
							$userInfo = @include $filename;
							if( is_array( $userInfo ) )
							{
							//	var_export( $data );
								if( $userInfo['display_picture_base64'] )
								{
								//	if( @$userInfo['document_url_base64'] )
									{ 
										$userInfo['display_picture'] = '/tools/classplayer/get/object_name/Application_Profile_PhotoViewer/profile_url/' . @$userInfo['profile_url'] . '/time/' . filemtime( Application_Profile_Abstract::getProfilePath( @$userInfo['profile_url'] ) );
									}
								//	$userInfo['display_picture'] = $userInfo['display_picture_base64'];
								}
								Ayoola_Page::$title = $userInfo['display_name'];
								Ayoola_Page::$description = $userInfo['profile_description'];
								Ayoola_Page::$thumbnail = $userInfo['display_picture'];
								self::$GLOBAL += $userInfo; // store this in the global var 
								self::$_runtimeSetting['real_url'] = rtrim( '/profile/' . implode( '/', $a ), '/' );
								self::$mode = 'profile_url';
								if( self::view( self::$_runtimeSetting['real_url'] ) )  
								{
									break;
								}
								
							}
 */						}
						
						$userInfo = $nameForModule ? Ayoola_Access::getAccessInformation( $nameForModule, array( 'set_canonical_url' => true ) ) : null;
		//		var_export( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
			//	exit();
				//		var_export( $userInfo ); 
						
						//	Hide superusers
						if( $userInfo && $userInfo['access_level'] != 99 )
						{
						
							
						//	var_export( $module );
							Ayoola_Page::$title = $userInfo['display_name'];
							Ayoola_Page::$description = $userInfo['profile_description'];
							Ayoola_Page::$thumbnail = $userInfo['display_picture'];
					//		$_GET = array_merge( $_GET, array( 'username' => $nameForModule ) ); // Combines our generated params with the original 
					//		$_REQUEST = array_merge( $_REQUEST, array( 'username' => $nameForModule ) ); // Combines our generated params with the original 
							self::$GLOBAL += $userInfo; // store this in the global var 
							self::$_runtimeSetting['real_url'] = rtrim( '/profile/' . implode( '/', $a ), '/' );
						//	var_export( self::$_runtimeSetting['real_url'] );
							self::$mode = 'profile';
							if( self::view( self::$_runtimeSetting['real_url'] ) )
							{
								break;
						//		exit();
							}
						}
					}
					catch( Exception $e )
					{
						null;
					}
				
				}
			//	var_export( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
			//	var_export( $module );
				
				//	Look in the links table for SEO friendly and short URLS
				$table = new Application_Link();
				$linkName = trim( $uri, '/' );
			//	var_export( $table->select() );
				if( $link = $table->selectOne( null, array( 'link_name' => $linkName ) ) )
				{
					$filter = new Ayoola_Filter_Get;
					$get = $filter->filter( $link['link_url'] );
					$_GET = array_merge( $_GET, $get ); // Combines our generated params with the original 
					$_REQUEST = array_merge( $_REQUEST, $get ); // Combines our generated params with the original 
					require_once 'Ayoola/Filter/Uri.php';
					$filter = new Ayoola_Filter_Uri;
					$uri = $filter->filter( $link['link_url'] );
				//	var_export( $uri );
					self::$_runtimeSetting['real_url'] = $link['link_url'];
				///	var_export( self::$_runtimeSetting['real_url'] );
					self::$_runtimeSetting['url'] = $uri;
					self::view( $uri );
					break; 
				}
			//	exit( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
				self::$mode = '404';
				self::view();	//	404 NOT FOUND
			//	var_export( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
			}
			while( false );
		}
	//	self::view();	//	404 NOT FOUND
		
    }

    /**

     * Include required file to view page
     *
     * @param string URI to view
     * @return void
     */
    public static function view( $uriToView = null )
    {	
		//	var_export( $_SERVER );
		//	exit();
		$uri = $uriToView; 
		if( ! $uri )
		{
			$uri =  self::$_notFoundPage;
			self::$_runtimeSetting['real_url'] = self::$_notFoundPage;
			header( "HTTP/1.0 404 Not Found" );
			header( "HTTP/1.1 404 Not Found" );
			Header('Status: 404 Not Found');
	//		var_export( headers_list() );
		//	exit();
		}
		//	now because of situation where we have username domains
		//	we should be able to overide page inheritance
		
		//	my copy first
		$pagePaths = Ayoola_Page::getPagePaths( $uri );
	//	var_export( $pagePaths['include'] );
	//	var_export( $pagePaths['template'] );
	//	var_export( is_file( $PAGE_INCLUDE_FILE ) );
	//	var_export( is_file( $PAGE_TEMPLATE_FILE ) );  
		$PAGE_INCLUDE_FILE = Ayoola_Application::getDomainSettings( APPLICATION_PATH ) . DS .  $pagePaths['include'];
		$PAGE_TEMPLATE_FILE = Ayoola_Application::getDomainSettings( APPLICATION_PATH ) . DS .  $pagePaths['template'];
	//	var_export( $pagePaths );
	//	var_export( $PAGE_INCLUDE_FILE );
	//	var_export( $PAGE_TEMPLATE_FILE );
	//	var_export( is_file( $PAGE_INCLUDE_FILE ) );
	//	var_export( is_file( $PAGE_TEMPLATE_FILE ) ); 
	//	exit(); 
		$previewTheme = function() use ( $pagePaths, $uri, &$PAGE_INCLUDE_FILE, &$PAGE_TEMPLATE_FILE )
		{

			$pageThemeFileUrl = $uri;
			if( $pageThemeFileUrl == '/' )
			{
				$pageThemeFileUrl = '/index';
			}
			//	page may just be present in the theme
			$themeName = @$_REQUEST['pc_page_layout_name'];
			$themeName = $themeName ? : Ayoola_Page_Editor_Layout::getDefaultLayout();
			$pagePaths['include'] = 'documents/layout/' . $themeName . '/theme' . $pageThemeFileUrl . '/include';
			$pagePaths['template'] = 'documents/layout/' . $themeName . '/theme' . $pageThemeFileUrl . '/template';
			
			//	theme copy
			$PAGE_INCLUDE_FILE = Ayoola_Loader::getFullPath( $pagePaths['include'], array( 'prioritize_my_copy' => true ) );
			$PAGE_TEMPLATE_FILE = Ayoola_Loader::getFullPath( $pagePaths['template'], array( 'prioritize_my_copy' => true ) );
		//	var_export( $pagePaths['include'] );
		//	var_export( $pagePaths['template'] );
//			var_export( $pageThemeFileUrl );
//			var_export( $PAGE_INCLUDE_FILE );
//			var_export( $PAGE_TEMPLATE_FILE );
			if( ! $PAGE_INCLUDE_FILE AND ! $PAGE_TEMPLATE_FILE )
			{
				//	not found
				return false;
			}
			return true;
		};
		do
		{
			if( ! empty( $_REQUEST['pc_page_layout_name'] ) )     
			{
				if( $previewTheme() )
				{
					break;
				}
			}
		
		//	var_export( $pagePaths );
			if
			( 
				! is_file( $PAGE_INCLUDE_FILE ) AND ! is_file( $PAGE_TEMPLATE_FILE )
			)
			{
				
				// intended copy next
				$intendedCopyPaths = Ayoola_Page::getPagePaths( '/' . trim( $uri . '/default', '/' ) );
				$PAGE_INCLUDE_FILE = Ayoola_Loader::getFullPath( $intendedCopyPaths['include'], array( 'prioritize_my_copy' => true ) );
				$PAGE_TEMPLATE_FILE = Ayoola_Loader::getFullPath( $intendedCopyPaths['template'], array( 'prioritize_my_copy' => true ) );
			//	var_export( $intendedCopyPaths['include'] );
			//	var_export( $intendedCopyPaths['template'] );
				if
				( 
					! $PAGE_INCLUDE_FILE AND ! $PAGE_TEMPLATE_FILE
				)
				{
					
					//	global copy
					$PAGE_INCLUDE_FILE = Ayoola_Loader::getFullPath( $pagePaths['include'], array( 'prioritize_my_copy' => true ) );
					$PAGE_TEMPLATE_FILE = Ayoola_Loader::getFullPath( $pagePaths['template'], array( 'prioritize_my_copy' => true ) );
				//	var_export( $pagePaths['include'] );
				//	var_export( $pagePaths['template'] );
					if
					( 
						! $PAGE_INCLUDE_FILE AND ! $PAGE_TEMPLATE_FILE
					)
					{
						//	not found
					//	if( ! $previewTheme() )
						{
							return false;
						}
					}
				}
			}
		}
		while( false );
	//	var_export( $PAGE_INCLUDE_FILE );     
	//	var_export( $PAGE_TEMPLATE_FILE );     
	//	var_export( $pagePaths['template'] );     
	//	var_export( Ayoola_Loader::checkFile( $pagePaths['template'] ) );     
		//	The normal page
		try
		{
			//	Check if page options permits
/* 			$pageOptions = Ayoola_Page::getCurrentPageInfo();
			@$pageOptions = $pageOptions['page_options'] ? : array();
			$access = new Ayoola_Access();
			if(	( in_array( 'logged_in_hide', $pageOptions )  && $access->isLoggedIn() ) 
			|| 	( in_array( 'logged_out_hide', $pageOptions ) && ! $access->isLoggedIn() )
			)
			{
				self::view();
				exit();
			}
 */		}
		catch( Ayoola_Exception $e ){ self::view(); exit(); }
		 
	//	$table = new Application_User_CloudCopy();
	//	var_export( file_get_contents( "php://input" ) );
	//	$table->select();
		
		//	Put in Access Restriction
		self::restrictAccess();
	//	exit( microtime( true ) - self::$_runtimeSetting['start_time'] . '<br />' );
	
		//	check if redirect
		$pageInfo = Ayoola_Page::getInfo( $uri ); 
	//		var_export( $pageInfo ); 
		if( @$pageInfo['redirect_url'] && ! @$_REQUEST['pc_redirect_url'] )
		{
			if( self::getUrlPrefix() && $pageInfo['redirect_url'][0] === '/' )
			{
				$pageInfo['redirect_url'] = self::getUrlPrefix() . $pageInfo['redirect_url'];
			}
		//	var_export( $pageInfo );
			
			header( 'Location: ' . $pageInfo['redirect_url'] . '?pc_redirect_url=' . $uri );
			exit(); 
		}

		//	Client-side	scripting		
	//	Application_Javascript::addFile( '' . self::getUrlPrefix() . '/object/name/Application_Javascript/?v=' . filemtime( __FILE__ ) );
		Application_Javascript::addFile( '' . self::getUrlPrefix() . '/tools/classplayer/get/name/Application_Javascript/?v=' . filemtime( __FILE__ ) );
/*		Application_Javascript::addFile( '/js/js.js' );
		Application_Javascript::addFile( '/js/objects/files.js' );
		Application_Javascript::addFile( '/js/objects/events.js' );
		Application_Javascript::addFile( '/js/objects/spotLight.js' );
		Application_Javascript::addFile( '/js/objects/style.js' );
		Application_Javascript::addFile( '/js/objects/xmlHttp.js' );
		Application_Javascript::addFile( '/js/objects/div.js' );
		Application_Javascript::addFile( '/js/objects/js.js' );
		Application_Javascript::addFile( '/ayoola/js/form.js' ); 
		Application_Javascript::addFile( '/ayoola/js/image.js' );
*/		Application_Style::addFile( Ayoola_Page::getPageCssFile() );
//		Application_Javascript::addCode( 'ayoola.spotLight.popUp( "Welcome to Nigeria!" );' );
//		Application_Javascript::addCode( 'ayoola.spotLight.showLink( "/css/ayoola_default_style.css" );' );

		//	Pass the artificial query string to the client-side			
		Application_Javascript::addCode  
		( 
			"
			ayoola.pcPathPrefix = '" . self::getUrlPrefix() . "';  
			ayoola.events.add
			(
				window, 'load', function(){ ayoola.setArtificialQueryString( '" . Ayoola_Application::getRuntimeSettings( 'real_url' ) . "' ); } 
			);" 
		);
	//	var_export( Ayoola_Page::getCurrentPageInfo() );
	
		//	Set TimeZone
		date_default_timezone_set( Application_Settings_CompanyInfo::getSettings( 'CompanyInformation', 'time_zone' ) ? : 'UTC' );
		
		//	Send content type to avoid mozilla reloading when theres and error message
		header("Content-Type: text/html; charset=utf-8");
	
		include_once $PAGE_INCLUDE_FILE;
//		exit( microtime( true ) - Ayoola_Application::getRuntimeSettings( 'start_time' ) . '<br />' );
		include_once $PAGE_TEMPLATE_FILE;
		return true;
	} 
   
    /**
     * Restrict Access to Application
     *
     * @param void
     * @return void
     */
    public static function restrictAccess()
    {	
	//	var_export( $_COOKIE );	echo "<br />\n";
//		var_export( $_SERVER['HTTP_USER_AGENT'] );	echo "<br />\n";
		$auth = new Ayoola_Access();
		$userInfo = $auth->getUserInfo();
		
		//	Show error to logged in admin 
		switch( @$_SERVER['HTTP_AYOOLA_PLAY_MODE'] )
		{
			case null:
			case '':
			case 0:
				//	By default, don't display error'
				{
					error_reporting( E_ALL & ~E_STRICT & ~E_NOTICE & ~E_USER_NOTICE );
					ini_set( 'display_errors', "0" );    
				}
			
				//	If the mode is selected, we don't want to see errors.
				if( $userInfo['access_level'] > 98 || '127.0.0.1' == $_SERVER['REMOTE_ADDR'] )
				{ 
					error_reporting( E_ALL & ~E_STRICT );
			//		ini_set( 'display_errors', "1" ); 
				//	var_export( $userInfo['access_level'] );
				}

				//	We explicitly asked for it. So let's have it.'
				if( ! empty( $_REQUEST['pc_show_error'] ) )
				{
					error_reporting( E_ALL & ~E_STRICT & ~E_NOTICE & ~E_USER_NOTICE );
					ini_set( 'display_errors', "1" );   
				}
	//			else
			break;
		}
		require_once 'Ayoola/Access.php';
		$auth = new Ayoola_Access();
		
		//	general restriction
	//		var_export( $_SERVER );
		
//		if( $_SERVER['REMOTE_ADDR' ] !== '127.0.0.1' )
		{
			$auth->restrict();
		}
	//	else 
		{
		//	var_export( 'localhost' );
		//	var_export( $_SERVER );
		}
	} 
	
    /**
     * Logs request
     *
     */
    public static function log()
    {
		//	Don't log request for file
/* 		$filter = new Ayoola_Filter_FileExtention();
		$ext = strtolower( $filter->filter( Ayoola_Application::getPresentUri() ) );
	//	var_export( self::$_runtimeSetting['total_runtime'] . '<br />' );
		switch( $ext )
		{
			case 'js':
			case 'css':
			case 'png':
			case 'jpg':
			case 'gif':
			case '/tools/classplayer/get/object_name/application_log_info/': //	Don't log "CHECKING LOG" PROCESS 
			case '/tools/classplayer/get/object_name/application_log_view/': //	Don't log "CHECKING LOG" PROCESS 
			case '/tools/classplayer/get/object_name/application_log_clear/': //	Don't log "CLEARING LOG" PROCESS 
				break;
			default:
				Application_Log_View_Access::log(); //	Log request
				break;
		}
 */		
		//	Don't log on localhost
		if( count( explode( '.', DOMAIN ) ) == 1 )
		{ 
		//	var_export( DOMAIN );
			return; 
		}
		self::$accessLogging ? Application_Log_View_Access::log() : null; //	Log request
    }
	
    /**
     * This method load needed files
     *
     * @param void
     * @return null
     */
    public static function loadPrerequisites()
    {
		require_once( 'configs/definitions.php' );
		require_once( 'functions/init.php' );
		self::setIncludePath();
    }
	
    /**
     * This method sets the include path to a value
     *
     * @param void
     * @return null
     */
    public static function setIncludePath()
    {
		set_include_path( LIBRARY_PATH . PS . MODULES_PATH . PS . APPLICATION_PATH . PS . get_include_path() );
    } 
	
	
    /**
     * Returns header for a request
     *
     * @param string
     * @return mixed Response Code or False on Error
     */
    public static function getHeaders( $link, array $options = null )
    {
		//	http://stackoverflow.com/questions/244506/how-do-i-check-for-valid-not-dead-links-programatically-using-php
		$ch = curl_init(); // get cURL handle

        // set cURL options
        $opts = array( CURLOPT_RETURNTRANSFER => true, // do not output to browser
                                  CURLOPT_URL => $link,            // set URL
                                  CURLOPT_AUTOREFERER => true,            
                                  CURLOPT_USERAGENT => 'Mozilla/5.0 ( compatible; ayoolabot/0.1; +http://ayoo.la/bot/ )',            // set URL
                                  CURLOPT_NOBODY => true	// do a HEAD request only
					);   // set timeout
								  
		@$opts[CURLOPT_TIMEOUT] = $options['timeout'] ? : 10; //	Defaults to 1sec
		@$opts[CURLOPT_FOLLOWLOCATION] = $options['follow_redirect']; // By default we don't follow redirects
	
        curl_setopt_array($ch, $opts); 

        $response = curl_exec($ch); // do it!
        $info = curl_getinfo($ch); // check if HTTP OK
	//	exit( var_export( $options['follow_redirect'] ) );
	//	var_export( $options['follow_redirect'] );
	//	var_export( $response );
		if( $info['http_code'] != 200  ){ return false; }
	//	$info['redirect_url'] = $link == $info['url'] ? null : $info['url'];

        curl_close($ch); // close handle

        return $info;
    } 
	
    /**
     * Flushes the output to the user
     *
     * @param last massage to echo to the user
     */
    public static function outputFlush( $lastOutput )
    {
		if( ! self::$disableOutput )
		{
			echo $lastOutput;    
			ob_flush();
			flush();
		}
    } 
	
    /**
     * Return _mode
     *
     * @param void
     * @return string
     */
    public static function getMode()
    {
		return self::$_mode;
    } 
	
    /**
     * Returns the $_userInfo
     *
     * @param string Key to the Info to return
     * @return mixed
     */
    public static function getUserInfo( $key = null )
    {
	//	if( $key === '' )
		{
	//		$key = null;
		}
		if( is_null( self::$_userInfo ) || $key === false )
		{
			self::$_userInfo = new Ayoola_Access();
			self::$_userInfo = self::$_userInfo->getUserInfo();
			if( ! self::$_userInfo ){ return false; }
		}
	//	var_export( self::$_userInfo );
		if( ! is_array( self::$_userInfo ) || $key === false )
		{
			return array();
		}
		return array_key_exists( $key, self::$_userInfo ) && $key ? self::$_userInfo[$key] : self::$_userInfo;
    } 
	
    /**
     * This method basically removes the /get/ seo query from the requested Uri
     *
     * @param void
     * @return null
     */
    public static function getPresentUri( $url = null )
    {
		if( @self::$_presentUri[$url] )
		{ 
			return self::$_presentUri[$url]; 
		}
		$url = $url ? : self::getRequestedUri();
		require_once 'Ayoola/Filter/Uri.php';
		$filter = new Ayoola_Filter_Uri;
		$result = $filter->filter( $url );
		self::$_presentUri[$url] = $result;
		return self::$_presentUri[$url];
    } 
	
    /**
     * This method returns the requested Uri
     *
     * @param void
     * @return null
     */
    public static function getRequestedUri()
    {
		if( self::$_requestedUri ){ return self::$_requestedUri; }
		$requestedUri = self::$_homePage;	// Default
		
		//	because of url prefix that has space in them
		@$requestedUriDecoded = rawurldecode( $_SERVER['REQUEST_URI'] );
		
		if( isset( $_SERVER['REQUEST_URI'] ) && $_SERVER['REQUEST_URI'] != '/' && $_SERVER['SCRIPT_NAME'] != $requestedUriDecoded )
		{
			$requestedUri = $requestedUriDecoded;
		//	var_export( $requestedUri );
		}
		if( isset( $_SERVER['PATH_INFO'] ) )
		{
			$requestedUri = $_SERVER['PATH_INFO'];
		}
		else  
		{
		//	var_export( $_SERVER['PATH_INFO'] );
		
		}
	//	$requestedUri = rawurldecode( $requestedUri );
	//	var_export( $_SERVER );

	//	var_export( $requestedUri );
	//		var_export( PC_PATH_PREFIX );
	//		var_export( $requestedUri );
		//	REMOVE PATH PREFIX
		if( strpos( $requestedUri, @constant( 'PC_PATH_PREFIX' ) ) === 0 )  
		{
			$requestedUri = explode( PC_PATH_PREFIX, $requestedUri );
	//		var_export( $requestedUri );
			array_shift( $requestedUri );
			$requestedUri = implode( PC_PATH_PREFIX, $requestedUri ) ? : '/';
		//	exit();
		}
	//	var_Export( $_SERVER );
	//	var_Export( $requestedUri );
		$requestedUri = parse_url( $requestedUri );
		$requestedUri = $requestedUri['path'];
		if( $requestedUri == '/' )
		{
			$requestedUri = self::$_homePage;
		}
		//	Fetch the GET parameters from the url
		require_once 'Ayoola/Filter/Get.php';
		$filter = new Ayoola_Filter_Get;
		$get = $filter->filter( $requestedUri );
		$_GET = array_merge( $_GET, $get ); // Combines our generated params with the original 
		$_REQUEST = array_merge( $_REQUEST, $get ); // Combines our generated params with the original 
//		var_export( $requestedUri ); //
		self::$_requestedUri = $requestedUri;
		return self::$_requestedUri;
    } 
	
    /**
     * 
     * 
     */
	public static function getUrlSuffix()  
    {
		$suffix = null;
		if( ! empty( $_REQUEST['pc_page_layout_name'] ) )
		{
			//	we need this so that theme preview could be seemless
			$suffix .= '&pc_page_layout_name=' . $_REQUEST['pc_page_layout_name'];
		}
		return $suffix;
	}
	
    /**
     * 
     * 
     */
	public static function getUrlPrefix()  
    {
//		var_export( $_SERVER );
//		exit();
		if( self::$_urlPrefix ){ return self::$_urlPrefix; }
		self::$_urlPrefix = '';
		$storage = new Ayoola_Storage();
		$storage->storageNamespace = __CLASS__  . 'url_prefix-' . @constant( 'PC_PATH_PREFIX' );
		$storage->setDevice( 'File' );
		$data = $storage->retrieve(); 
	//	var_export( $data );
		if(  ! $data  )
		{		
		//	var_export( $data );
 			//	Detect if we have mod-rewrite
			$urlToLocalInstallerFile = Ayoola_Application::getDomainSettings( 'protocol' ) . '://' . $_SERVER['HTTP_HOST'] . @constant( 'PC_PATH_PREFIX' ) . '/pc_check.txt';
		//	var_export( $urlToLocalInstallerFile );
			$modRewriteEnabled = get_headers( $urlToLocalInstallerFile );
			$responseCode = explode( ' ', $modRewriteEnabled[0] );
		//	var_export( $urlToLocalInstallerFile );
		//	var_export( $responseCode );
			if( in_array( '200', $responseCode ) )
			{
				$data = 1;
			}
			else
			{
				$data = 2;
			}
			$storage->store( $data );
		}
		if( $data == 2 )
		{
			self::$_urlPrefix .= $_SERVER['SCRIPT_NAME'];
		}
		elseif( isset( $_SERVER['PATH_INFO'] ) && $_SERVER['PATH_INFO'] != '/' )
		{
			self::$_urlPrefix .= $_SERVER['SCRIPT_NAME'];
		}
		elseif( @constant( 'PC_PATH_PREFIX' ) )
		{
			self::$_urlPrefix .= constant( 'PC_PATH_PREFIX' );
		//	exit();
		}
		
		
		
		return self::$_urlPrefix;
	}
	
    /**
     * 
     * 
     */
	public static function getUserAccountInfo( $key = null )
    {
		if( ! self::$_userAccountInfo )
		{
			self::$_userAccountInfo['userid'] =  array();
			$functionName = function_exists( 'posix_getuid' ) ? 'posix_getuid' : 'getmyuid';
			self::$_userAccountInfo['userid'] =  $functionName();
			$processUserInfo =  function_exists( 'posix_getpwuid' ) ? posix_getpwuid( self::$_userAccountInfo['userid'] ) : null;
			self::$_userAccountInfo['username'] =  $processUserInfo['name'] ? : 'UNKWOWN';
		}
		return $key ? self::$_userAccountInfo[$key] : self::$_userAccountInfo;
    } 
	
    /**
     * Returns true if the request was by internal cURL
     *
     * @param void
     * @return boolean
     */
    public static function isCurlRequest()
    {
		if
		( 
			( isset( $_SERVER['HTTP_REQUEST_TYPE'] ) && $_SERVER['HTTP_REQUEST_TYPE'] == 'curl' )
		)
		{ return true; }
		return false;
    } 
	
    /**
     * Returns true if the request was by ajax
     *
     * @param void
     * @return boolean
     */
    public static function isXmlHttpRequest()
    {
	//	var_export( $_SERVER['HTTP_REQUEST_TYPE'] );
		if
		( 
			( isset( $_SERVER['HTTP_REQUEST_TYPE'] ) && $_SERVER['HTTP_REQUEST_TYPE'] == 'xmlHttp' ) 
			
		)
		{ return true; }
		return false;      
    } 
	
    /**
     * Returns true if the particular class is being 'played'  
     *
     * @param void
     * @return boolean
     */
    public static function isClassPlayer()   
    {
	//	var_export( $_SERVER['HTTP_APPLICATION_MODE'] );
		if
		( 
			( isset( $_SERVER['HTTP_APPLICATION_MODE'] ) && $_SERVER['HTTP_APPLICATION_MODE'] == 'Ayoola_Object_Play' )
			
		)
		{ 
			return true; 
		}
		return false;
    } 
	
    /**
     * Returns true if we are running on local server 
     *
     * @param void
     * @return boolean
     */
    public static function isLocalServer()   
    {
	//	var_export( $_SERVER['HTTP_APPLICATION_MODE'] );
		if
		( 
			in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) )	
		)
		{ return true; }
		return false;
    } 
	// END OF CLASS
}  