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

class saEasyLocations
{

	static function ParseLocations( $contentObjectID, $contentObjectVersion, $params )
	{

		$http = $params['http'];
//		exit;
/*
print_r($http);
echo $variable;
print_r($variable);
echo "###";exit;
*/		
//		$locations = $http->postVariable('ContentObjectAttribute_id');
		if ($http && $http->hasPostVariable('saEasyLocations_LocationsList'))
		{
			$locationsIDs = $http->postVariable('saEasyLocations_LocationsList');

			if (is_array($locationsIDs))
			{
				$object = eZContentObject::fetch( $contentObjectID );
				$nodeID = $object->attribute('main_node_id');
				$classIdentifier = $object->attribute('class_identifier');
				$objectID = $object->attribute('id');
				$availableLocationsIDs = $http->postVariable('saEasyLocations_AvailableLocationsList');
				
				$INI = eZINI::instance( 'saeasylocations.ini' );
				
				if (in_array($classIdentifier, $INI->variable('EasyLocationsSettings','LocationsClasses') ) )
				{

					if ($INI->hasGroup($classIdentifier))
						$removeOnCollision = ($INI->variable( $classIdentifier, 'LocationCollisionHandling' ) == 'remove');
					else
						$removeOnCollision = ($INI->variable( 'EasyLocationsSettings', 'LocationCollisionHandling') == 'remove');
	
					// Loop through exsisting node assignments and :
					// - cerate a collision list of node IDs that are not in available locations list but are already assigned
					// - create a remove list of nodes that are in available locations list but not in locations list
					$assignedNodes = $object->assignedNodes();
					$parentNodeIDs = array();
					$removeList = array();
					$collisionList = array();
					
					foreach ($assignedNodes as $assignedNode)
					{
						// We skip the main node so that it never gets removed
						if ( !$assignedNode->attribute('is_main') )
						{
							$parentID = $assignedNode->attribute('parent_node_id');
	
							if (in_array($parentID, $availableLocationsIDs))
							{
								if (!in_array($parentID, $locationsIDs))
									$removeList[] = $assignedNode;
							}
							else
							{
								$collisionList[] = $assignedNode;
							}
						}
					}

					// Remove the nodes in $removeList - thoose that were in available locations but were not checked in locations list
					if ($removeList)
						eZContentOperationCollection::removeAssignment( $nodeID, $objectID, $removeList, false );
					
					// Remove the collision nodes (if the INI setting was set to remove)
					if ($removeOnCollision && $collisionList)
						eZContentOperationCollection::removeAssignment( $nodeID, $objectID, $collisionList, false );

					$parentNodeIDs = $object->attribute('parent_nodes');

					foreach ($locationsIDs as $locationID)
					{
						if (!in_array($locationID, $parentNodeIDs))
							$object->AddLocation($locationID);
					}
					
				}
				else self::DebugError( "Object class is not in LocationsClasses, but LocationsList variable exists." );
			}
			else self::DebugError( "Location list POST variable is not an array." );
		}
		
	}
	
	static function DebugError($msg)
	{
		eZDebug::writeError( $msg, "saeasylocations" );
	}

	static function DebugWarning($msg)
	{
		eZDebug::writeWarning( $msg, "saeasylocations" );
	}

	static function DebugNotice($msg)
	{
		eZDebug::writeNotice( $msg, "saeasylocations" );
	}

}
		
?>
