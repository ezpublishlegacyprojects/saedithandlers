<?php

$cli->output("Updating meta attributes.");

$limit = 100;

$params = array(
	'Offset' => 0,
	'Limit' => $limit
);

$cli->output("Fetching series of $params[Limit] nodes...");

$parentNode = eZContentObjectTreeNode::fetch(1);

$count = $parentNode->subTreeCount();

while ( $nodes = $parentNode->subTree( $params ) )
{
	$newOffset = $params['Offset'] + $params['Limit'];

	$cli->output("Nodes $params[Offset]-$newOffset/$count");

	foreach ($nodes as $node)
	{
		$cli->output( $node->attribute('name') );
		saAttributeMetaHandler::setMetaData( $node->attribute('object')->attribute('id') );
	}

	$params['Offset'] = $newOffset;
}



$script->shutdown();

?>

