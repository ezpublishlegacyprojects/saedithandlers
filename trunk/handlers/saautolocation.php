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

class saAutoLocation
{

	static function AddLocations( $contentObjectID, $contentObjectVersion )
	{

		$autolocationINI = eZINI::instance( 'saautolocation.ini' );
		
		if ($autolocationINI)
		{

			$object = eZContentObject::fetch( $contentObjectID );
			$add_locations = false;
			
			if ($object)
			{
				
				$classID = $object->attribute( 'contentclass_id' );
				$class = eZContentClass::fetch( $classID );
				$class_identifier = $class->attribute( 'identifier' );

				$object_classes = $autolocationINI->variable( 'AutoLocationSettings', 'LocationClasses' );
				$add_locations = in_array($class_identifier, $object_classes);
	
			}
			else
			{
				self::DebugError( "Object with ID $contentObjectID doesn't exsist" );
			}

			if ($add_locations)
			{
	
				$class_locations = $autolocationINI->variable( 'AutoLocationSettings', 'ClassLocations' );
				$default_locations = $autolocationINI->variable( 'AutoLocationSettings', 'DefaultLocations' );
				
				if (isset($class_locations[$class_identifier]))
					$additionalLocations = explode(',', $class_locations[$class_identifier]);
				else
					$additionalLocations = explode(',', $default_locations);

				if ($additionalLocations)
				{
					$result = true;

					$parent_nodes = $object->parentNodeIDArray();
					
					foreach ($additionalLocations as $locationID)
					{
						if (!in_array($locationID, $parent_nodes))
						{
							$assignedNodes = $object->assignedNodes();
							$alreadyAssigned = false;

							foreach ($assignedNodes as $assignedNode)
							{

								if ($assignedNode->attribute('parent_node_id') == $locationID)
								{
									$alreadyAssigned = true;
									break;
								}
							}

							if (!$alreadyAssigned)
								$result = $result && $object->AddLocation($locationID);
						}
					}

					return $result;
									
				}
			
			}

		}
		else
			self::DebugError( "No INI file." );

		return false;
		
	}
	
	static function DebugError($msg)
	{
		eZDebug::writeError( $msg, "saautolocations" );
	}

	static function DebugWarning($msg)
	{
		eZDebug::writeWarning( $msg, "saautolocations" );
	}

	static function DebugNotice($msg)
	{
		eZDebug::writeNotice( $msg, "saautolocations" );
	}

}
		
?>
