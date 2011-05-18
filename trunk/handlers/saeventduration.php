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

class saEventDuration
{

	static function SetEndDate( $contentObjectID, $contentObjectVersion )
	{

		$object = eZContentObject::fetch( $contentObjectID );
		$modifyDate = false;
		
		if ($object)
		{
			$classID = $object->attribute( 'contentclass_id' );
			$class = eZContentClass::fetch( $classID );
			$classIdentifier = $class->attribute( 'identifier' );
			
			$INI = eZINI::instance( 'saeventduration.ini' );
			
			if ($INI)
			{
				$eventClasses = $INI->variable( 'EventDurationSettings', 'EventClasses' );				
				$modifyDate = in_array($classIdentifier, $eventClasses);
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

			if ( $INI->hasVariable( 'EventDurationSettings', 'StartDateAttributes' ) )
				$startDateAttributes = $INI->variable( 'EventDurationSettings', 'StartDateAttributes' );
			else
				$startDateAttributes = array();
				
			if ( $INI->variable( 'EventDurationSettings', 'DefaultStartDateAttribute' ) )
				$defaultStartDateAttribute = $INI->variable( 'EventDurationSettings', 'DefaultStartDateAttribute' );
			else
				$defaultStartDateAttribute = '';

			$endDateAttributes = $INI->variable( 'EventDurationSettings', 'EndDateAttributes' );
			$defaultEndDateAttribute = $INI->variable( 'EventDurationSettings', 'DefaultEndDateAttribute' );
			
			if (isset($startDateAttributes[$classIdentifier]))
				$startDateAttributeName = $startDateAttributes[$classIdentifier];
			else
				$startDateAttributeName = $defaultStartDateAttribute;

			if (isset($endDateAttributes[$classIdentifier]))
				$endDateAttributeName = $endDateAttributes[$classIdentifier];
			else
				$endDateAttributeName = $defaultEndDateAttribute;

			if ($startDateAttributeName && $endDateAttributeName)
			{
				$dataMap = $object->DataMap();

				if (isset($dataMap[$startDateAttributeName]) && isset($dataMap[$endDateAttributeName]))
				{
					$startDateAttribute = $dataMap[$startDateAttributeName];
					$endDateAttribute = $dataMap[$endDateAttributeName];

					if ( !$endDateAttribute->attribute('has_content') && $startDateAttribute->attribute('has_content') )
					{
						$startDate = $startDateAttribute->content();
						$endDateStamp = mktime(23, 59, 59, $startDate->month(), $startDate->day(),  $startDate->year());

//echo "#" . $startDateAttribute->attribute('content') . "#" . $startDateAttribute->content()->DateTime . "#";
//echo $endDateStamp;
//exit;
						$endDateAttribute->setAttribute('data_int', $endDateStamp );
						$endDateAttribute->store();
						$object->store();
					}

				}
				else
					self::DebugError( "Attribute $dateAttributeName doesn't exist in object/class: $contentObjectID/$classIdentifier." );
				
			}

//		exit;
		
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
