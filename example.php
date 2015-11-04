<?php

require_once 'src/janyksteenbeek/COChannels/COChannels.php';

$contentOwner = new janyksteenbeek\COChannels\COChannels('username@gmail.com', 'myP@$$word', 'contentOwnerID');

$contentOwner->addChannel(['janyksteenbeek', 'pewdiepie']);
$contentOwner->removeChannel('JL2pyjJQ_7VbUKItznyTsQ');
