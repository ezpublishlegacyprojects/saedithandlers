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

class saAttributeMetaHandler extends saEditHandlersBase
{

	const HANDLER_IDENTIFIER = 'saattributemeta';

	static function setMetaData( $contentObjectID, $contentObjectVersion )
	{

		$object = eZContentObject::fetch( $contentObjectID );
		$modifyDate = false;
		
		if ($object)
		{
			$classID = $object->attribute( 'contentclass_id' );
			$class = eZContentClass::fetch( $classID );
			$classIdentifier = $class->attribute( 'identifier' );
			
			$INI = eZINI::instance( 'saattributemeta.ini' );
			
			if ($INI)
			{

				$useAllClases = $INI->variable( 'AttributeMetaSettings', 'UseAllClasses' ) == 'true';
				$objectClasses = $INI->variable( 'AttributeMetaSettings', 'MetaDataClasses' );

				$processObject = $useAllClases || in_array($classIdentifier, $objectClasses);
			}
			else
				self::DebugError( "No INI file." );
		}
		else
		{
			self::DebugError( "Object with ID $contentObjectID doesn't exsist" );
		}

		if ($processObject)
		{

			if ( $INI->hasVariable( 'AttributeMetaSettings', 'MetaDataAttributes' ) )
				$metaDataAttributes = $INI->variable( 'AttributeMetaSettings', 'MetaDataAttributes' );
			else
				$metaDataAttributes = array();

			$dataMap = $object->attribute('data_map');

			foreach ($metaDataAttributes as $attributeIdentifier)
			{
				$classAttribute = false;
				
				if ( is_numeric($attributeIdentifier) )
				{
					$classAttribute = eZContentClassAttribute::fetch($attributeIdentifier);
				}
				else
				{
				
					list($classIdentifier, $attributeIdentifier) = explode('/', $attributeIdentifier);
					$tmpClass = eZContentClass::fetchByIdentifier($classIdentifier);

					if ($tmpClass)
					{
						$classAttribute = $tmpClass->fetchAttributeByIdentifier($attributeIdentifier);
					}
				}
				
				if ($classAttribute)
				{

					if ( $dataMap[$classAttribute->attribute('identifier')] )
					{
						$attribute = $dataMap[$classAttribute->attribute('identifier')];

						saAttributeMeta::storeMeta(
							$object->attribute('id'),
							$classAttribute->attribute('id'),
							array(
								'has_content' => $attribute->attribute('has_content') ? 1 : 0
							)
						);

					}
					
				}
				else
					self::DebugError( "Attribute $attributeIdentifier doesn't exist in object/class: $contentObjectID/$classIdentifier." );

			}
		
		}

	}

}
		
?>