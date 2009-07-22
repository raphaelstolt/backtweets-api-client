backtweets-api-client
======
The **backtweets-api-client** is a client to the [backtweets](http://backtweets.com/api/) API build with components of the [Zend Framework](http://framework.zend.com/).

Requirements
------------
* An installed version of the Zend Framework

Usage Examples
--------------
<code><?php
require_once 'Recordshelf/Service/Backtweets.php';

$configuration = array('api_key' => 'YOUR_API_KEY', 
    'response_format' => 'xml');

$this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);

$searchFilter = array('url' => 'http://framework.zend.com', 
    'start' => '2009.07.01', 
    'end' => '2009/07/21');
                      
$results = $this->_serviceClient->search($searchFilter);
</code>