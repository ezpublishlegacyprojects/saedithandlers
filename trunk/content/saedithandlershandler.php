<?PHP
/*

    saEditHandlers
    Copyright (C) 2010 Studio Artlan
	Special thanks to Hrvoje and Neomedia.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

	For any questions contact xmak@studioartlan.com.
	
*/

class saEditHandlersHandler extends eZContentObjectEditHandler
{

	static $CurrentHandlerName = '';
	static $DebugOutput = false;
	static $http;
	
	function fetchInput( $http, &$module, &$class, $object, &$version, $contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage )
	{
		parent::fetchInput( $http, &$module, &$class, $object, &$version, $contentObjectAttributes, $editVersion, $editLanguage, $fromLanguage );
		self::$http  = $http;
	}

	
	function publish( $contentObjectID, $contentObjectVersion )
	{

		$handlersINI = eZINI::instance( 'saedithandlers.ini' );

		if ($handlersINI)
		{
			self::$DebugOutput = $handlersINI->variable( 'HandlerSettings', 'DebugOutput' ) == 'enabled';

			if ($handlersINI->hasVariable( 'HandlerSettings', 'ActivatedHandlers' ))
			{
			
				$handlers = $handlersINI->variable( 'HandlerSettings', 'ActivatedHandlers' );

				$globalHandlersDir = $handlersINI->variable( 'HandlerSettings', 'GlobalHandlersDir' );
				if (!$globalHandlersDir) $globalHandlersDir = 'extension/saedithandlers/handlers';

				$adjustObjectName = $handlersINI->variable( 'HandlerSettings', 'AdjustObjectName' ) == 'true';
				
				$isNewObject = $contentObjectVersion == 1;
				
				foreach ($handlers as $handlerName)
				{

					if ($handlersINI->hasGroup($handlerName))
					{


						self::$CurrentHandlerName = $handlerName;

						if ($handlersINI->hasVariable( $handlerName, 'NewObjectsOnly' ) )
							$newObjectsOnly = $handlersINI->variable( $handlerName, 'NewObjectsOnly' ) == 'true';
						else
							$newObjectsOnly = false;


						if ( ($isNewObject) || !$newObjectsOnly)
						{

							$scriptName = $handlersINI->variable( $handlerName, 'Script' );

							if ($scriptName)
							{

								$className = $handlersINI->variable( $handlerName, 'Class' );
								$methodName = $handlersINI->variable( $handlerName, 'Method' );
							
								if ($className && $methodName)
								{

									if ( $handlersINI->hasVariable( $handlerName, 'HandlerDir' ) )
										$handlerDir = $handlersINI->variable( $handlerName, 'HandlerDir' );
									else
										$handlerDir = false;
										
									if (!$handlerDir) $handlerDir = $globalHandlersDir;
									
									if ($handlerDir && $scriptName)
										include_once( "$handlerDir/$scriptName" );

									if ( method_exists( $className, $methodName ) )
									{
									
										if ( $handlersINI->hasVariable( $handlerName, 'PassAdditionalParameters' ) )
											$passAdditionalParameters = ( $handlersINI->variable( $handlerName, 'PassAdditionalParameters' ) == 'enabled');
										else
											$passAdditionalParameters = false;

										if ($passAdditionalParameters)
										{
											$params = array('http' => self::$http);
											call_user_func("$className::$methodName", $contentObjectID, $contentObjectVersion, $params); 
										}
										else
										{
											call_user_func("$className::$methodName", $contentObjectID, $contentObjectVersion);
										}
										
										if ($adjustObjectName)
										{
											$object = eZContentObject::fetch( $contentObjectID );
											if ($object)
											{
												$objectClass = eZContentClass::fetch( $object->attribute( 'contentclass_id' ) );
												if ($objectClass)
													$object->setName($objectClass->contentObjectName($object));
												//echo $objectClass->contentObjectName($object);
												//exit;
											}
										}

									}
									else
										self::DebugError( "Class method '$className::$methodName' doesn't exist." );
								}
								else
									self::DebugError( "Class or method not specified." );
							}
							else
							{
								self::DebugError( "Script not specified." );
							}

						}

					}
					else
						self::DebugError( "Handler INI group '$handlerName' is missing." );
					
				}

			}
			else
				self::DebugError( "No HandlerSettings group in INI file." );

		}
		else
			self::DebugError( "No INI file." );
//	 require_once( "extension/batchtool/operations/$operation_name.php" );
//if ( method_exists( $className, $method ) )
//call_user_func("$className::$method', $contentObjectID, $contentObjectVersion); 
//		eZPublishDate::SetPublishDate( $contentObjectID, $contentObjectVersion );

	}
	
	static function DebugError($msg)
	{
		if (self::$DebugOutput) eZDebug::writeError( $msg, "saedithandlers-". self::$CurrentHandlerName );
	}

	static function DebugWarning($msg)
	{
		if (self::$DebugOutput) eZDebug::writeWarning( $msg, "saedithandlers-" . self::$CurrentHandlerName );
	}

	static function DebugNotice($msg)
	{
		if (self::$DebugOutput) eZDebug::writeNotice( $msg, "saedithandlers-". self::$CurrentHanlderName );
	}

}


?>
