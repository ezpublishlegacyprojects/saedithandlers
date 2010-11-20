<?php /*

# DO NOT change this file. Use an ini override instead.

# Global handlers setting
[HandlerSettings]
ActivatedHandlers[]=
# Uncomment to activate the publishdate handler (check the sapublishdate.ini first)
#ActivatedHandlers[]=sapublishdate
# Uncomment to activate the autolocation handler (check the saautolocation.ini first)
#ActivatedHandlers[]=saautolocation
# Uncomment to activate the relatecreator handler (check the sarelatecreator.ini first)
#ActivatedHandlers[]=sarelatecreator
# Uncomment to activate the easylocations handler (check the saeasylocations.ini first)
#ActivatedHandlers[]=saeasylocations

# Enable/disable debug. Disabled by default
DebugOutput=disabled

# The directory where the handler files are located, if not set, extension/saedithandlers/handlers is used
GlobalHandlersDir=

# Wether to adjust object name (based on class name pattern) after execution of edit handlers
AdjustObjectName=true

# Settings for running publish date handler
[sapublishdate]
Script=sapublishdate.php
Class=saPublishDate
Method=SetPublishDate

# Settings for running autolocation handler
[saautolocation]
NewObjectsOnly=true
Script=saautolocation.php
Class=saAutoLocation
Method=AddLocations

# Settings for running relatecreator handler
[sarelatecreator]
NewObjectsOnly=true
Script=sarelatecreator.php
Class=saRelateCreator
Method=Relate

# Settings for running easylocations handler
[saeasylocations]
Script=saeasylocations.php
Class=saEasyLocations
Method=ParseLocations
PassAdditionalParameters=enabled

# Settings for running nlsubscribe handler
[sanlsubscribe]
Script=sanlsubscribe.php
Class=saNLSubscribe
Method=SubscribeUserObject

*/ ?>




