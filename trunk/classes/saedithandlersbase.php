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

class saEditHandlersBase
{
	const HANDLER_IDENTIFIER = 'saedithandlerbase';
	
	static function DebugError($msg)
	{
		$calledClass = get_called_class();
		eZDebug::writeError( $msg, $calledClass::HANDLER_IDENTIFIER );
	}

	static function DebugWarning($msg)
	{
		$calledClass = get_called_class();
		eZDebug::writeWarning( $msg, $calledClass::HANDLER_IDENTIFIER );
	}

	static function DebugNotice($msg)
	{
		$calledClass = get_called_class();
		eZDebug::writeNotice( $msg, $calledClass::HANDLER_IDENTIFIER );
	}

}
		
?>
