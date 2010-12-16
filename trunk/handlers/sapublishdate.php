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

class saPublishDate
{

	static function SetPublishDate( $contentObjectID, $contentObjectVersion )
	{

		$object = eZContentObject::fetch( $contentObjectID );
		$modifyDate = false;
		
		if ($object)
		{
			$classID = $object->attribute( 'contentclass_id' );
			$class = eZContentClass::fetch( $classID );
			$classIdentifier = $class->attribute( 'identifier' );
			
			$publishdateINI = eZINI::instance( 'sapublishdate.ini' );
			
			if ($publishdateINI)
			{

				$useAllClases = $publishdateINI->variable( 'PublishDateSettings', 'UseAllClasses' ) == 'true';
				$object_classes = $publishdateINI->variable( 'PublishDateSettings', 'DateClasses' );
				
				$modifyDate = $useAllClases || in_array($classIdentifier, $object_classes);
			}
			else
				self::DebugError( "No INI file." );
		}
		else
		{
			self::DebugError( "Object with ID $contentObjectID doesn't exsist" );
		}

		if ($modifyDate)
		{

			$dateAttributes = $publishdateINI->variable( 'PublishDateSettings', 'DateAttributes' );
			$defaultDateAttribute = $publishdateINI->variable( 'PublishDateSettings', 'DefaultDateAttribute' );
			
			if (isset($dateAttributes[$classIdentifier]))
				$dateAttributeName = $dateAttributes[$classIdentifier];
			else
				$dateAttributeName = $defaultDateAttribute;

			if ($dateAttributeName)
			{
				$dataMap = $object->DataMap();

				if (isset($dataMap[$dateAttributeName]))
				{
					$dateAttribute = $dataMap[$dateAttributeName];

					if ($dateAttribute->attribute('has_content'))
					{
						$publishedDate = $dateAttribute->content()->DateTime;
						$object->setAttribute( 'published', $publishedDate);
						$object->store();
					}
					else
					{
						$defaultFillEmptyAttribute = $publishdateINI->variable( 'PublishDateSettings', 'DefaultFillEmptyAttribute' );
						$fillEmptyAttributes = $publishdateINI->variable( 'PublishDateSettings', 'FillEmptyAttributes' );

						$doFillEmptyAttribute = ($defaultFillEmptyAttribute  == 'true');

						
						
						if (isset($fillEmptyAttributes[$classIdentifier]))
						{
							$doFillEmptyAttribute = ($fillEmptyAttributes[$classIdentifier] == 'true');
						}

						if ($doFillEmptyAttribute)
						{
							// TODO: Staviti ovdje da uzima datum objave, a ne trenutno vrijeme
							//$object->getAttribute( 'published');
							$dateAttribute->setAttribute('data_int', time() );
							$dateAttribute->store();
							$object->store();
						}
					}

				}
				else
					self::DebugError( "Attribute $dateAttributeName doesn't exist in object/class: $contentObjectID/$classIdentifier." );
				
			}
		
		}

	}
	
	static function DebugError($msg)
	{
		eZDebug::writeError( $msg, "sapublishdate" );
	}

	static function DebugWarning($msg)
	{
		eZDebug::writeWarning( $msg, "sapublishdate" );
	}

	static function DebugNotice($msg)
	{
		eZDebug::writeNotice( $msg, "sapublishdate" );
	}

}
		
?>
