<?php

class saNLSubscribe_CJWNL extends saNLSubscribe
{

	static function SetSubscription( $email, $first_name, $last_name, $list_name, $user_object_id = false, $unsubscribe = false, $unsubscribe_all = false)
	{
		return false;
	}

	static private function ModifyUserSubscriptions( $user_object_id, $email, $first_name = '', $last_name = '', $unsubscribe = false)
	{
		return $subscriptionList;
	}
	
}
		
?>
