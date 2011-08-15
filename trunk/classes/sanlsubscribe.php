<?php

class saNLSubscribe
{

	static function SubscribeUserObject($contentObjectID, $contentObjectVersion)
	{

		$obj = eZContentObject::fetch( $contentObjectID );
		$class_identifier = '';
		$user_classes =array();
		
		if ($obj)
		{
			$classID = $obj->attribute( 'contentclass_id' );
			$class = eZContentClass::fetch( $classID );
			$class_identifier = $class->attribute( 'identifier' );
			
			$subscribeINI = eZINI::instance( 'sanlsubscribe.ini' );
			if ($subscribeINI)
			{
				$user_classes = $subscribeINI->variable( 'SubscribeSettings', 'UserClasses' );
			}
			else
				self::DebugError( "No INI file." );
		}
		else
		{
			self::DebugError( "Object with ID $contentObjectID doesn't exsist" );
		}
		if (in_array($class_identifier, $user_classes))
		{

			$account_attributes = $subscribeINI->variable( 'SubscribeSettings', 'AccountAttributes' );
			$receive_nl_attributes = $subscribeINI->variable( 'SubscribeSettings', 'ReceiveNewsletterAttributes' );
			$first_name_attributes = $subscribeINI->variable( 'SubscribeSettings', 'FirstNameAttributes' );
			$last_name_attributes = $subscribeINI->variable( 'SubscribeSettings', 'SecondNameAttributes' );

			$default_list = $subscribeINI->variable( 'SubscribeSettings', 'DefaultList' );
			$list_name = $default_list;
			
			$data_map = $obj->DataMap();

			if (isset($receive_nl_attributes[$class_identifier]) && isset($data_map[$receive_nl_attributes[$class_identifier]]))
				$receive_nl= $data_map[$receive_nl_attributes[$class_identifier]]->content();

			if (isset($account_attributes[$class_identifier]) && isset($data_map[$account_attributes[$class_identifier]]))
				$account= $data_map[$account_attributes[$class_identifier]]->content();			
			if (isset($first_name_attributes[$class_identifier]) && isset($data_map[$first_name_attributes[$class_identifier]]))
				$first_name = $data_map[$first_name_attributes[$class_identifier]]->content();
			if (isset($last_name_attributes[$class_identifier]) && isset($data_map[$last_name_attributes[$class_identifier]]))
				$last_name = $data_map[$first_name_attributes[$class_identifier]]->content();
			
			if ($account)
				$email = $account->attribute( 'email' );
			else $email = false;
			$unsubscribe_all = !$receive_nl;

			if ($email && $first_name && $last_name)
			{

				if( eZMail::validate( $email ) )
				{

					$result = call_user_func(
						array( get_called_class(), 'SetSubscription'),
						$email, $first_name, $last_name, $list_name, $obj->attribute( 'id' ), false, $unsubscribe_all
					);

					if ( $result )
						self::DebugNotice( "Email '$email' successfully subscribed to '$list_name'." );
					else
						self::DebugError( "Error subscribing '$email' to '$list_name'." );
				}
				else
					self::DebugError( "Subscription email '$email' is not valid." );
			}
			else
				self::DebugError( "Error in data - first_name: $first_name, last_name: $last_name, account: $account" );

		}

	}
	
	static function DebugError($msg)
	{
		eZDebug::writeError( $msg, "sanlsubscribe" );
	}

	static function DebugWarning($msg)
	{
		eZDebug::writeWarning( $msg, "sanlsubscribe" );
	}

	static function DebugNotice($msg)
	{
		eZDebug::writeNotice( $msg, "sanlsubscribe" );
	}

}
		
?>
