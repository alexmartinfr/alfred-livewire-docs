<?php

use Alfred\Workflows\Workflow;

use AlgoliaSearch\Client as Algolia;
use AlgoliaSearch\Version as AlgoliaUserAgent;

require __DIR__ . '/vendor/autoload.php';

$query = $argv[1];
$subtext = empty($_ENV['alfred_theme_subtext']) ? '0' : $_ENV['alfred_theme_subtext'];

$workflow = new Workflow;
$parsedown = new Parsedown;
$algolia = new Algolia('BH4D9OD16A', 'cec0554d960fa30b4b0b610f372a8636'); // Pest PHP
AlgoliaUserAgent::addSuffixUserAgentSegment('Pest PHP Docs Alfred Workflow', '0.1.0');
$index = $algolia->initIndex('livewire-framework');
$search = $index->search($query);
$results = $search['hits'];

$subtextSupported = $subtext === '0' || $subtext === '2';

if (empty($results)) {
    $google = sprintf('https://www.google.com/search?q=%s', rawurlencode("pestphp {$query}"));

    $workflow->result()
        ->title($subtextSupported ? 'Search Google' : 'No match found. Search Google...')
        ->icon('google.png')
        ->subtitle(sprintf('No match found. Search Google for: "%s"', $query))
        ->arg($google)
        ->quicklookurl($google)
        ->valid(true);

    $workflow->result()
        ->title($subtextSupported ? 'Open Docs' : 'No match found. Open docs...')
        ->icon('icon.png')
        ->subtitle('No match found. Open https://pestphp.com/docs/installation/...')
        ->arg('https://pestphp.com/docs/installation/')
        ->quicklookurl('https://pestphp.com/docs/installation/')
        ->valid(true);

    echo $workflow->output();
    exit;
}

$urls = [];

foreach ($results as $hit) {

    $highestLvl = $hit['hierarchy']['lvl6'] ? 6 : ($hit['hierarchy']['lvl5'] ? 5 : ($hit['hierarchy']['lvl4'] ? 4 : ($hit['hierarchy']['lvl3'] ? 3 : ($hit['hierarchy']['lvl2'] ? 2 : ($hit['hierarchy']['lvl1'] ? 1 : 0)))));

    $title = $hit['hierarchy']['lvl' . $highestLvl];
    $currentLvl = 0;
    $subtitle = $hit['hierarchy']['lvl0'];
    while ($currentLvl < $highestLvl) {
        $currentLvl = $currentLvl + 1;
        $subtitle = $subtitle . ' » ' . $hit['hierarchy']['lvl' . $currentLvl];
    }

    $workflow->result()
        ->uid($hit['objectID'])
        ->title($title)
        ->autocomplete($title)
        ->subtitle($subtitle)
        ->arg($hit['url'])
        ->quicklookurl($hit['url'])
        ->valid(true);
}

echo $workflow->output();

/*
                         ###############                                
                   ##########ENJOY!###########                          
               ###################################                      
            ################@@@@@@(##################                   
          ############(@@@@@@@@@@@@@@@@###############(                 
        ############@@@@     .@@@@@@@@@@@&#############(#               
       ###########%@@@ .@@@@*   @@@@@@@@@@@#############((              
     (###########@@@@# ,@@@@#   @@@@@@@@@@@@#############((.            
     ############@@@@@          @@@@@@@@@@@@@############(((            
    ############@@@@@@@@      @@@@@@@@@@@@@@@(############(((           
   #############@@@@@@@@@@@@@@@@@@@@@@@@@@@@@(############(((*          
   ##############@@@@@@@@@@@@@@@@@@@@@@@@@@@@#############((((          
   ###############@@@@@@@@@@@@@@@@@@@@@@@@@@##############((((          
   ################@@@@@@@@@@@@@@@@@@@@@@@@###############((((          
   (#################@@@@@@@@@@@@@@@@@@@(################((((*          
    #######################(#&&&%########################((((           
     ###################################################((((            
     .#########,,,,.##########,,,,*#########(,,,,/#####((((             
       ######,,*******######,,******#######,,******##(((((              
            ,********      ,********.      ,*******                     
            *********      *********.      ********                     
            *********      *********.      ********                     
             *******       *********.      ********                     
                           *********.      ********                     
                           *********.                                   
                           *********                                    
                             *****                                      
*/