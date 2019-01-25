<?php

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

