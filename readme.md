# AccepticOptimizationJob

## Install

* Clone repo
```
git clone https://github.com/SunDrop/AccepticOptimizationJob.git
```

* Update composer
```
composer update
```
This step will install **Psr/Log** and **Codeception**

## Check tests
```
./codecept run
```
It runs the following tests
```
CampaignAnalyserTest: Campaigns not initialized exception..................Ok
CampaignAnalyserTest: Check notifications..................................Ok
CampaignDataSourceTest: Get campaigns as key array same ids................Ok
CampaignAnalyserTest: Campaign event aggregator not initialized exception..Ok
CampaignDataSourceTest: Get campaigns as key array.........................Ok
CampaignAnalyserTest: Publisher notifier not initialized exception.........Ok
OptimizationJobTest: Run...................................................Ok
EventTest: Is not valid | #0...............................................Ok
EventTest: Is not valid | #1...............................................Ok
EventTest: Is not valid | #2...............................................Ok
EventTest: Is not valid | #3...............................................Ok
EventTest: Is not valid | #4...............................................Ok
EventTest: Is valid | #0...................................................Ok
EventTest: Is valid | #1...................................................Ok
EventTest: Is valid | #2...................................................Ok
EventTest: Is valid | #3...................................................Ok
CampaignEventAggregatorTest: Add event.....................................Ok
CampaignAnalyserTest: Check blacklist......................................Ok
CampaignEventAggregatorTest: Get sum by type...............................Ok
```

## The Solution
* For **CampaignDataSource** added method **getCampaignsAsAssocArray()** that rearrange **getCampaigns()**
 to assocArray with campaigns ids keys
 ```php
 [
    1 => CampaignId1,
    2 => CampaignId2,
    ...
    N => CampaignIdN,
 ]
 ```
* **OptimizationJob's** **run()** method checks all events form **EventsDataSource** and store them into 
**CampaignEventAggregator** that just special structured array
```php
[
  'campaignId' => [
    'publisherId' => [
         'eventType1' => sum1,
         'eventType2' => sum2,
         'eventType3' => sum3,
     ],
  ],
]
```
* **CampaignAnalyser** class get **Campaigns** and **CampaignEventAggregator** as parameters. Into **run()** method it
makes _$blacklist_ and _$whitelist_ for each campaigns and send _notifications_ to publishers

## The Task
 Our advertising platform promotes mobile applications , it contains a campaign for each such application
 our publishers bring users that install and then use these applications
 the platform is reported about the install event and other application usage events of these users
 for example "app_open", "registration" and "purchase" events
 this stream of events is saved in a database

 To achieve quality goals we optimize campaigns by blacklisting publishers who do not qualify  the campaign's expections

 For example, a campaign may expect the number of "purchase" events a publisher brings to be equal or
 greater than 10% of the number of installs that publishers brought,
 or else the publisher should be blacklisted on that campaign

 To maintain these publisher blacklists we have a job process (OptimizationJob) runs every hour

 Campaign objects contain an optimizationProps object that includes the following properties:
 * sourceEvent and measuredEvent - in the above example sourceEvent would be "install" and measuredEvent
   would be "purchase"
 * threshold - the minimum of occurrences of sourceEvent, if a publisher has less sourceEvents that the threshold ,
   then she should not be blacklisted
 * ratioThreshold - the minimum ratio of sourceEvent occurrences to measuredEvent occurrences

 Event objects contain their type, the campaignId and publisherId

 Below is the begining of the implementation of the OptimizationJob class,
 1. complete the implementation maintaining campaigns' publishers blacklists
    Keep in mind that blacklisted publishers can only be removed from the blacklist if they cross the ratio

 1. make sure publishers are notified with an email whenever they are added or removed from a campaign's blacklist
    Please do not implement the email mechanism - we assume you know how to send an email

```php
class OptimizationJob {

	public function run() {
		$campaignDS = new CampaignDataSource();

		// array of Campagin objects
		$campaigns = $campaignDS->getCampaigns();


		$eventsDS = new EventsDataSource();
		/** @var Event $event */
		foreach($eventsDS->getEventsSince("2 weeks ago") as $event) {
			// START HERE
		}

	}
}


class Campaign {
	/** @var  OptimizationProps $optProps */
	private $optProps;

	/** @var  int */
	private $id;

	/** @var  array */
	private $publisherBlacklist;

	public function getOptimizationProps() {
		return $this->optProps;
	}
	public function getBlackList() {
		return $this->publisherBlacklist;
	}
	public function saveBlacklist($blacklist) {
		// dont implement
	}
}

class OptimizationProps {
	public $threshold, $sourceEvent, $measuredEvent, $ratioThreshold;
}

class Event {
	private $type;
	private $campaignId;
	private $publisherId;

	public function getType() {
		// for example "install"
		return $this->type;
	}
	public function getTs() {
		return $this->ts;
	}
	public function getCampaignId() {
		return $this->campaignId;
	}
	public function getPublisherId() {
		return $this->publisherId;
	}
}
```
