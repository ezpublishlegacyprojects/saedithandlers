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

class saRelateCreator
{

	static function Relate( $contentObjectID, $contentObjectVersion )
	{

		$object = eZContentObject::fetch( $contentObjectID );
		$relateCreator = false;
		
		if ($object)
		{

			$classID = $object->attribute( 'contentclass_id' );
			$class = eZContentClass::fetch( $classID );
			$classIdentifier = $class->attribute( 'identifier' );
			
			$relatecreatorINI = eZINI::instance( 'sarelatecreator.ini' );
			
			if ($relatecreatorINI)
			{
				if ( $relatecreatorINI->hasVariable( 'RelateCreatorSettings', 'RelateClasses' ) )
					$objectClasses = $relatecreatorINI->variable( 'RelateCreatorSettings', 'RelateClasses' );
				else
					$objectClasses  = false;
				$relateCreator = $objectClasses && in_array($classIdentifier, $objectClasses);
			}
			else
				self::DebugError( "No INI file." );
		}
		else
		{
			self::DebugError( "Object with ID $contentObjectID doesn't exsist" );
		}

		if ($relateCreator)
		{

			$relationAtributes = $relatecreatorINI->variable( 'RelateCreatorSettings', 'RelationAttributes' );
			$defaultRelationAttribute = $relatecreatorINI->variable( 'RelateCreatorSettings', 'DefaultRelationAttribute' );
			
			if (isset($relationAttributes[$classIdentifier]))
				$relationAttributeName = $relationAttributes[$classIdentifier];
			else
				$relationAttributeName = $defaultRelationAttribute;

			$nameAtributes = $relatecreatorINI->variable( 'RelateCreatorSettings', 'NameAttributes' );
			$defaultNameAttribute = $relatecreatorINI->variable( 'RelateCreatorSettings', 'DefaultNameAttribute' );
			$userAccountAttributeName = $relatecreatorINI->variable( 'RelateCreatorSettings', 'UserAccountAttribute' );

			if (isset($nameAttributes[$classIdentifier]))
				$nameAttributeName = $nameAttributes[$classIdentifier];
			else
				$nameAttributeName = $defaultNameAttribute;
				
				
			if ($relationAttributeName)
			{
				$dataMap = $object->DataMap();

				$nameAttribute = $dataMap[$nameAttributeName];
				
				if ( $nameAttribute && $userAccountAttributeName && isset($dataMap[$nameAttributeName]) )
				{
						
					$owner = $object->attribute('owner');
					
					if ($owner)
					{
						$ownerDataMap = $owner->DataMap();
						$userAccountAttribute = $ownerDataMap[$userAccountAttributeName];
						
						if ($userAccountAttribute && $userAccountAttribute->attribute('content'))
						{
							$nameValue = $userAccountAttribute->attribute('content')->attribute('login');
							
							if ($nameValue)
							{
								$nameAttribute->setAttribute('data_text', $nameValue);
								$nameAttribute->store();
								$object->store();
								//echo "#$nameValue#";
							}
						}

					}
					
				}
				
				if (isset($dataMap[$relationAttributeName]))
				{
					$relationAttribute = $dataMap[$relationAttributeName];

					if ($relationAttribute->attribute('data_type_string') == eZObjectRelationType::DATA_TYPE_STRING)
					{

						$creatorId = $object->attribute('owner_id');

						$objectRelation = new eZObjectRelationType();
						$relationAttribute->setAttribute('data_int', $creatorId);
						$objectRelation->storeObjectAttribute($relationAttribute);
						
						$relationAttribute->store();
						$object->store();

					}
					else self::DebugError( "Attribute $relationAttributeName is not object relation in object/class: $contentObjectID/$classIdentifier." );


				}
				else
					self::DebugError( "Attribute $relationAttributeName doesn't exist in object/class: $contentObjectID/$classIdentifier." );
				
			}

//		exit;
		
		}
		
	}
	
	static function DebugError($msg)
	{
		eZDebug::writeError( $msg, "sarelatecreator" );
	}

	static function DebugWarning($msg)
	{
		eZDebug::writeWarning( $msg, "sarelatecreator" );
	}

	static function DebugNotice($msg)
	{
		eZDebug::writeNotice( $msg, "sarelatecreator" );
	}

}
		
?>
