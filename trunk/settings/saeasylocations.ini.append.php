<?php /*
# Global settings fo locations handling
# Theese settings are used as defaults for all clasess for which easy locations is used
# For modifing settings for each single class see "CLASS SETTINGS" below


[EasyLocationsSettings]

# Title of the section where the locations chooser is displayed 
#SectionTitle=

# For which classes to use easy locations
#LocationsClasses[]=<class_identifier>

# How to choose the locations when editing the object
# - ini_nodes - offers only the nodes which IDs are listed in the IniNodes setting
# - nodes_list - offers the nodes that are fetched based on the INI settings (see below)
#LocationsChooseMethod=ini_nodes|node_list

# Label to display in the admin interface under the location chooser
#Label=

# If LocationsChooseMethod=node_list this are the parameters
# which are used for fetching nodes will be offered for possible locations
#
# Parent nodes for the fetch. A list consisting of key value pairs:
#
# subtree_id => node_id;node_id...
#
# Works like this: When editing an object the ParentNodes keys are matched to the nodes in the path array of the
# main node of the edited object. If a match is found, the value of the matched subtree_id is used as a semicolon
# separated list of node ids that will be the parent nodes for fetching available locations.
#ParentNodes[<subtree_id>]=<node_id>;<node_id>;...
#
# Fetch function, list or tree
#FetchFunction=list|tree
#
# Maximum number of items to fetch
#MaxItems=
#
# How to display the locations chooser in the editor when more than one parent node is used for fetch
# - list_only - lists all fetched nodes together
# - grouped - fore each parent node display it's fetched nodes in a group named after the parent node name
#MultipleParentsDisplay=list_only|grouped

# If LocationsChooseMethod=ini_nodes this is the list of IDs od nodes that will be offered for possible locations
# This works in a similar way as ParentNodes above.
#IniNodes[<subtree_id>]=<node_id>;<node_id>;...

# What to do with the existing locations of the edited object that are not among the possible locations
#LocationCollisionHandling=remove|leave



# CLASSS SETTINGS
# Each grup is named after the class identifier of the class to which applies.
# Theese are the same settings as in global settings but is applied only for the specified class

[<class_identifier>]
#SectionTitle=

#LocationsChooseMethod=ini_nodes|node_list

#IniNodes[]=<node_id>
#ParentNodes[]=<node_id>
#FetchFunction=list|tree
#MaxItems=
#MultipleNodesDisplay=list_only|grouped

#LocationCollisionHandling=remove|leave


*/ ?>


