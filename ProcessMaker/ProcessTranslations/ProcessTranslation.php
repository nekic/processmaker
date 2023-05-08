<?php

namespace ProcessMaker\ProcessTranslations;

use DOMXPath;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection as SupportCollection;
use ProcessMaker\Assets\ScreensInProcess;
use ProcessMaker\Assets\ScreensInScreen;
use ProcessMaker\ImportExport\Utils;
use ProcessMaker\Models\Process;
use ProcessMaker\Models\Screen;

class ProcessTranslation
{
    protected $process;

    protected $humanLanguage = [
        'es' => 'Spanish',
        'de' => 'German',
        'fr' => 'French',
        'ja' => 'Japanese',
        'nl' => 'Dutch'
    ];

    public function __construct(Process $process)
    {
        $this->process = $process;    
    }

    public function getTranslations() 
    {
        return $this->getScreens();
    }

    public function getLanguageList($screensTranslations) 
    {
        return $this->getTranslatedLanguageList($screensTranslations);
    }

    public function getTranslatedLanguageList($screensTranslations) 
    {
        $languages = [];

        foreach($screensTranslations as $screenTranslation) {
            if ($screenTranslation['translations']) {
                foreach ($screenTranslation['translations'] as $translation) {
                    $languages[] = [
                        'language' => $translation['language'],
                        'human_language' => Languages::ALL[$translation['language']],
                        'created_at' => $translation['created_at'],
                        'updated_at' => $translation['updated_at'],
                    ];
                }
            }
        }

        return collect($languages)->sortByDesc('updated_at')->unique('language');
    }

    private function getScreens() : SupportCollection
    {
        $screensInProcess = collect($this->getScreenIds())->unique();

        // foreach ($screensInProcess as $screenInProcess) {
        //     $nestedScreens = $this->getNestedScreens($screenInProcess);
        // }
        
        return Screen::whereIn('id', $screensInProcess)->get()->map->only(['id', 'translations'])->values();
    }

    private function getScreenIds()
    {
        $tags = [
            'bpmn:task',
            'bpmn:manualTask',
            'bpmn:startEvent',
            'bpmn:endEvent',
            'bpmn:serviceTask',
            'bpmn:callActivity'
        ];

        foreach (Utils::getElementByMultipleTags($this->process->getDefinitions(true), $tags) as $element) {
            $screenId = $element->getAttribute('pm:screenRef');
            $interstitialScreenId = $element->getAttribute('pm:interstitialScreenRef');
            $allowInterstitial = $element->getAttribute('pm:allowInterstitial');
            $pmConfig = $element->getAttribute('pm:config');
            
            if ($pmConfig) {
                $pmConfig = json_decode($pmConfig, true);
            }

            if (is_numeric($screenId)) {
                $screenIds[] = $screenId;
            }

            // Let's check if interstitialScreen exist
            if (is_numeric($interstitialScreenId) && $allowInterstitial === 'true') {
                $screenIds[] = $interstitialScreenId;
            }

            if (isset($pmConfig) && $pmConfig !== '' && array_key_exists('screenRef', $pmConfig) && is_numeric($pmConfig['screenRef'])) {
                $screenIds[] = $pmConfig['screenRef'];
            }
        }

        return $screenIds;
    }
}