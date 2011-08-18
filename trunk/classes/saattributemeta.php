<?php

class saAttributeMeta extends eZPersistentObject
{

	function saAttributeMeta($row = array())
	{
		$this->eZPersistentObject( $row );
	}
	
	
	static function fetch($fields, $conditions)
	{
		return self::fetchObject(
				self::definition(),
				$fields,
				$conditions
			);
	}

	static function fetchByAttributeID($objectID, $attributeID)
	{
		$conds = array(
		'contentobject_id' => $objectID,
		'contentclassattribute_id' => $attributeID
		);

		return self::fetch( NULL, $conds ); 
	}	

	static function storeMeta($objectID, $classAttributeID, $data)
	{
		$object = self::fetchByAttributeID($classAttributeID, $objectID);
		
		if (!$object)
			$object = new saAttributeMeta();
		
		if ($object)
		{
			$object->setAttribute('contentobject_id', $objectID);
			$object->setAttribute('contentclassattribute_id', $classAttributeID);
			

			foreach ($data as $key => $value)
				$object->setAttribute($key, $value);

			$object->store();

			return $object;
		}
		else return false;
	}
	
	static function definition()
	{
		static $definition = array(
									"fields" => array(
											'contentobject_id' => array(
														'name' => 'ObjectID',
														'datatype' => 'integer',
														'default' => 0,
														'required' => true
													),
											"contentclassattribute_id" => array(
														'name' => 'AttributeID',
														'datatype' => 'integer',
														'default' => 0,
														'required' => true
													),
											"has_content" => array(
														'name' => 'HasContent',
														'datatype' => 'integer',
														'default' => 0,
														'required' => true
													),
									),
									"keys" => array( 'contentobject_id', 'contentclassattribute_id' ),
									"class_name" => "saAttributeMeta",
									"sort" => array( "attribute_id" => "asc" ),
									"name" => "sa_attributemeta"
		);
		
		return $definition;

	}
}

?>
