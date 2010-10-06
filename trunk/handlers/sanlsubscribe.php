<?PHP
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
			
			$subscribeINI = eZINI::instance( 'eznlsubscribe.ini' );
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
				
				if ( self::SetSubscription($email, $first_name, $last_name, $list_name, $obj->attribute( 'id' ), false, $unsubscribe_all) )
					self::DebugNotice( "Email '$email' successfully subscribed to '$list_name'." );
				else
					self::DebugError( "Error subscribing '$email' to '$list_name'." );
			}
			else
				self::DebugError( "Error in data - first_name: $first_name, last_name: $last_name, account: $account" );

		}

	}


	static private function SetSubscription( $email, $first_name, $last_name, $list_name, $user_object_id = false, $unsubscribe = false, $unsubscribe_all = false)
	{

		if( eZMail::validate( $email ) )
		{

			if ($unsubscribe_all)
			{
				$unsubscribe = true;
				self::DebugNotice( "User 'email: $email, user_id: $contentObjectID' doesn't want to receive NLs." );
			}
		
			$subscribed_lists = self::ModifyUserSubscriptions( $user_object_id, $email, $first_name, $last_name, $unsubscribe_all);
			
			$subscription_list = eZSubscriptionList::fetch( $list_name );
			
			if ($subscription_list && !$subscribed_lists)
			{

				$subscription = eZSubscription::fetchByEmailSubscriptionListID( $email, $subscription_list->attribute( 'id' ) );
				
				if ( !$subscription && !$unsubscribe)
				{
					// New subscriber, add it
					
					$name = "$first_name $last_name";
					$mobile = '';
	
					$subscription = $subscription_list->registerSubscription( $first_name, $name, $mobile, $email );
					if ($user_object_id)
						$subscription->setAttribute( 'user_id', $user_object_id );
					$subscription->setAttribute( 'status', eZSubscription::StatusConfirmed );
					$subscription->setAttribute( 'output_format', eZSubscription::OutputFormatExternalHTML );
					$subscription->sync();
					return $subscription;
				}
				elseif ($subscription)
				{
					if ($unsubscribe)
					{
						// User has subsrcription and want to unsubscribe
						
						$subscription->unsubscribe();
					}
					else
					{
						// User already has subsrcription and has choosen to be subscribed so we activate it's subscription
						
						self::DebugWarning( "Subscription  'email: $email, user: $user_object_id' already exsists in list '" . $subscription_list->Name . "' ($list_name)." );
						$subscription->setAttribute( 'status', eZSubscription::StatusApproved );
						$subscription->setAttribute( 'output_format', eZSubscription::OutputFormatExternalHTML );
						//$subscription->setAttribute( 'status', eZSubscription::StatusConfirmed );
						$subscription->sync();
						return $subscription;
					}

				}
				
			}
			else
				self::DebugError( "Subscription list id '$list_name' doesn't exist or use already subscribed to other lists." );
		}
		else
			self::DebugError( "Subscription email '$email' is not valid." );
		
		return false;
	}


	static private function ModifyUserSubscriptions( $user_object_id, $email, $first_name = '', $last_name = '', $unsubscribe = false)
	{

		$userList = eZSubscription::fetchListByUserID(
			$user_object_id,
			eZSubscription::VersionStatusPublished,
			array( array(
				eZSubscription::StatusPending,
				eZSubscription::StatusApproved,
				eZSubscription::StatusConfirmed,
				eZSubscription::StatusRemovedSelf,
				eZSubscription::StatusRemovedAdmin
			) )
		);
		$emailList = eZSubscription::fetchListByEmail(
			$email,
			eZSubscription::VersionStatusPublished,
			array( array(
				eZSubscription::StatusPending,
				eZSubscription::StatusApproved,
				eZSubscription::StatusConfirmed,
				eZSubscription::StatusRemovedSelf,
				eZSubscription::StatusRemovedAdmin
			) )
		);
		
		$subscriptionList = array_merge( $userList, $emailList);
		
		foreach( $subscriptionList as $subscription )
		{
#print_r($subscription);
#exit;
			if ($unsubscribe)
			{
				$subscription->unsubscribe();
			}
			else
			{
				$name = "$first_name $last_name";
				$subscription->setAttribute( 'email', $email );
				$subscription->setAttribute( 'firstname', $first_name );
				$subscription->setAttribute( 'name', $name );
				$subscription->setAttribute( 'output_format', eZSubscription::OutputFormatExternalHTML );
				$subscription->setAttribute( 'status', eZSubscription::StatusApproved );
				$subscription->sync();
			}
		}
		
		return $subscriptionList;

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