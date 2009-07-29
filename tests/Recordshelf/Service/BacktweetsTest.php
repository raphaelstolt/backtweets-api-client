<?php

require_once 'PHPUnit/Framework.php';
require_once 'Recordshelf/Service/Backtweets.php';

class BacktweetsTest extends PHPUnit_Framework_TestCase
{
    const API_KEY = '____YOUR_API_KEY____';

    protected $_serviceClient = null;
    
    protected function setUp() {
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    protected function tearDown() {
        $this->_serviceClient = null;
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenMandatoryApiKeyIsMissingInConfiguration()
    {        
        $configuration = array('response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenMandatoryResponseFormatIsMissingInConfiguration()
    {        
        $configuration = array('api_key' => 'abc');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenUnsupportedResponseFormatIsConfigured()
    {
        $configuration = array('api_key' => 'xbsg', 'response_format' => 'csv');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenUsingAnApiKeyLongerThan20Characters()
    {
        $configuration = array('api_key' => 'xbsgxbsgxbsgxbsgxbsgD', 
            'response_format' => 'json');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenUsingAnApiKeyLessThan20Characters()
    {
        $configuration = array('api_key' => 'xbsgxbsgxbsgxbsgxbs', 
            'response_format' => 'json');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenUnauthorizedApiAccessIsMade()
    {
        $configuration = array('api_key' => '2df75ee157d28b424fFF', 
            'response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
        $this->_serviceClient->search(
            array('url' => 'http://raphaelstolt.blogspot.com'));
    }
    /**
     * @test
     * @expectedException Exception     
     */
    public function shouldThrowExceptionWhenMandatoryUrlIsNotSetInSearchFilter()
    {
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
        $this->_serviceClient->search(array());
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenANotWellformedUrlIsUsedInSearchFilter()
    {
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
        $this->_serviceClient->search(array('url' => 'www.foodomain.org'));
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenInvalidUrlIsUsedInSearchFilter()
    {
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'xml');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);
        $this->_serviceClient->search(array('url' => 1212121212));
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenUnsupportedKeyIsUsedInSearchFilter()
    {
        $searchFilter = array('url' => 'http://framework.zend.com',
            'unsupported_key' => 'a', 
            'from_name' => 'b', 
            'to_name' => 'c');
        $this->_serviceClient->search($searchFilter);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenInvalidDateIsUsedInSearchFilter()
    {
        $searchFilter = array('url' => 'http://framework.zend.com', 
            'start' => '01/07/2009', 
            'end' => '31.02.2009');
        
        $this->_serviceClient->search($searchFilter);
    }
    /**
     * @test
     * @expectedException Exception
     */
    public function shouldThrowExceptionWhenWrongDateFormatIsUsedInSearchFilter()
    {
        $searchFilter = array('url' => 'http://framework.zend.com', 
            'start' => '2009.07.01', 
            'end' => '2009/07/21');
                              
        $this->_serviceClient->search($searchFilter);    
    }
    /**
     * @test
     */
    public function shouldSearchAndFindOnlyBacktweetsBetweenAGivenPeriodWhenToldTo()
    {
        $searchFilter = array('url' => 'http://framework.zend.com',
            'start' => '2009/07/10', 
            'end' => '2009/07/15');
        
        $result = $this->_serviceClient->search($searchFilter, true);
        
        $lastBacktweet = $result['feed']['tweets']['entry'][0];
        $lastBacktweetCreationDate = $lastBacktweet['tweet_created_at'];
        $firstBacktweet = end($result['feed']['tweets']['entry']);
        $firstBacktweetCreationDate = $firstBacktweet['tweet_created_at'];
        
        $this->assertTrue(strtotime($lastBacktweetCreationDate) < 
            strtotime('2009-07-15 23:59:59'), 
                'Last backtweet creation date is not younger than set end date filter.');
        $this->assertTrue(strtotime($firstBacktweetCreationDate) > 
            strtotime('2009-07-10 00:00:01'),
                'First backtweet creation date is not older than set start date filter.');
    }
    /**
     * @test
     */
    public function shouldSearchAndFindOnlyBacktweetsUpFromAGivenTweetId() 
    {
        $searchFilter = array('url' => 'http://raphaelstolt.blogspot.com',
            'since_id' => '2487455095');
        
        $result = $this->_serviceClient->search($searchFilter, true);
        
        $message = "Found tweets having a tweet id less "
            . "than {$searchFilter['since_id']}.";
        foreach ($result['feed']['tweets']['entry'] as $tweet) {
            $this->assertTrue($tweet['tweet_id'] > $searchFilter['since_id'], 
                $message);
        }        
    }
    /**
     * @test
     */
    public function shouldSearchAndFindOnlyBacktweetsOfAGivenTwitterUser()
    {
        $searchFilter = array('url' => 'http://framework.zend.com',
            'from_name' => 'padraicb');
        
        $result = $this->_serviceClient->search($searchFilter, true);
        
        $message = "Found tweets which are not from the expected twitter "
            . "user {$searchFilter['from_name']}.";
        foreach ($result['feed']['tweets']['entry'] as $tweet) {
            $this->assertTrue($tweet['tweet_from_user'] === $searchFilter['from_name'], 
                $message);
        }
    }
    /**
     * @test
     */
    public function shouldSearchAndFindOnlyBacktweetsToAGivenTwitterUser()
    {
        $searchFilter = array('url' => 'http://framework.zend.com',
            'to_name' => 'weierophinney');
        
        $result = $this->_serviceClient->search($searchFilter, true);
        
        $message = "Found tweets that are not directed to the expected "
            .  "twitter user {$searchFilter['to_name']}.";
        foreach ($result['feed']['tweets']['entry'] as $tweet) {
            $this->assertContains('@' . $searchFilter['to_name'], 
                $tweet['tweet_text'], $message);
        }
    }
    /**
     * @test
     */
    public function shouldTransformTheResultIntoAnArrayWhenToldTo()
    {        
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'json');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);

        $result = $this->_serviceClient->search(
            array('url' => 'http://raphaelstolt.blogspot.com'), 
            true);
        
        $this->assertThat($result, $this->isType('array'), 
            "Result got not transformed into an array.");
    }
    /**
     * @test
     */
    public function shouldReturnTheResultInTheConfiguredJsonFormat()
    {
        $configuration = array('api_key' => self::API_KEY, 
            'response_format' => 'json');
        $this->_serviceClient = new Recordshelf_Service_Backtweets($configuration);

        $result = $this->_serviceClient->search(
            array('url' => 'http://raphaelstolt.blogspot.com'));

        $this->assertTrue(substr($result, 0, 2) === '{"', 
            'Expected result format seems to be no Json.');
    }
    /**
     * @test
     */
    public function shouldReturnTheResultInTheConfiguredXmlFormat()
    {
        $result = $this->_serviceClient->search(
            array('url' => 'http://raphaelstolt.blogspot.com'));
        
        $this->assertTrue(substr($result, 0, 5) === '<?xml', 
            'Expected result format seems to be no Xml.');
    }    
}