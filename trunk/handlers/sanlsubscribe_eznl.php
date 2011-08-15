<?php

class saNLSubscribe_eZNL extends saNLSubscribe
{

	static private function SetSubscription( $email, $first_name, $last_name, $list_name, $user_object_id = false, $unsubscribe = false, $unsubscribe_all = false)
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

}
		
?>