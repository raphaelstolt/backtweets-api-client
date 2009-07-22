backtweets-api-client
======
The **backtweets-api-client** is a the TDD way developed client to the [backtweets](http://backtweets.com/api/) API build utilizing components of the [Zend Framework](http://framework.zend.com/). The backtweets service allows you to monitor your website presence on the twittersphere.

Requirements
------------
* An installed version of the Zend Framework

Examples
------------

#### Usage Example 1:
--------------
    
    <?php
    require_once 'Recordshelf/Service/Backtweets.php';
    
    $configuration = array('api_key' => 'YOUR_API_KEY', 
        'response_format' => 'xml');
    
    $this->serviceClient = new Recordshelf_Service_Backtweets($configuration);
    
    $searchFilter = array('url' => 'http://framework.zend.com', 
        'start' => '2009/07/01',
        'end' => '2009/07/21');
    
    $resultsInXml = $this->serviceClient->search($searchFilter);

#### Usage Example 2:
--------------

    <?php
    require_once 'Recordshelf/Service/Backtweets.php';

    $configuration = array('api_key' => 'YOUR_API_KEY', 
        'response_format' => 'json');

    $this->serviceClient = new Recordshelf_Service_Backtweets($configuration);

    $searchFilter = array('url' => 'http://framework.zend.com',
        'from_name' => 'padraicb');

    $resultsFormPadraicbInJson = $this->serviceClient->search($searchFilter);

#### Usage Example 3:
--------------

    <?php
    require_once 'Recordshelf/Service/Backtweets.php';

    $configuration = array('api_key' => 'YOUR_API_KEY', 
        'response_format' => 'json');

    $this->serviceClient = new Recordshelf_Service_Backtweets($configuration);

    $searchFilter = array('url' => 'http://framework.zend.com',
        'since_id' => '2487455095');

    $resultsSinceId2487455095AsAnArray = $this->serviceClient->search($searchFilter, true);