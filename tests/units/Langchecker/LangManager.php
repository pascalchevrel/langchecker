<?php
namespace tests\units\Langchecker;

use atoum;
use Langchecker\LangManager as _LangManager;
use Langchecker\DotLangParser as _DotLangParser;

require_once __DIR__ . '/../bootstrap.php';

class LangManager extends atoum\test
{
    public function testAnalyzeLangFile()
    {
        require_once TEST_FILES . 'config/sources.php';
        $obj = new _LangManager();

        $reference_data = $obj->loadSource($sites[2], 'en-US', 'page.lang');
        $locale_data = $obj->loadSource($sites[2], 'it', 'page.lang');
        $analysis_data = $obj->analyzeLangFile($sites[2], 'it', 'page.lang', $reference_data);

        $this
            ->boolean($analysis_data['activated'])
                ->isTrue();

        // Identical should be equal to 2, also empty string is considered identical
        $this
            ->integer(count($analysis_data['Identical']))
                ->isEqualTo(2);

        $this
            ->integer(count($analysis_data['Missing']))
                ->isEqualTo(1);

        $this
            ->integer(count($analysis_data['Obsolete']))
                ->isEqualTo(1);

        $this
            ->integer(count($analysis_data['Translated']))
                ->isEqualTo(13);

        $this
            ->integer(count($analysis_data['errors']['python']))
                ->isEqualTo(5);

        $this
            ->string($analysis_data['errors']['python']['String with %(num)s tags']['text'])
                ->isEqualTo('Stringa con etichette e variabile sbagliata');

        $this
            ->string($analysis_data['errors']['python']['String with %(num)s tags']['var'])
                ->isEqualTo('%(num)s');

        $this
            ->boolean($obj->isStringLocalized('Hello', $locale_data, $reference_data))
                ->isTrue();

        $this
            ->boolean($obj->isStringLocalized('Test', $locale_data, $reference_data))
                ->isFalse();
    }


    public function testBuildLangFile()
    {
        require_once TEST_FILES . 'config/sources.php';
        $obj = new _LangManager();

        $reference_data = $obj->loadSource($sites[2], 'en-US', 'page.lang');
        $locale_data = $obj->loadSource($sites[2], 'it', 'page.lang');

        $updated_content = $obj->buildLangFile($reference_data, $locale_data, 'it', "\n");
        $comparison_content = file_get_contents(TEST_FILES . 'dotlang/it/updated_page.lang');

        $this
            ->boolean($updated_content == $comparison_content)
                ->isTrue();
    }

    public function testCountErrors()
    {
        require_once TEST_FILES . 'config/sources.php';
        $obj = new _LangManager();

        $reference_data = $obj->loadSource($sites[2], 'en-US', 'page.lang');
        $locale_data = $obj->loadSource($sites[2], 'it', 'page.lang');
        $analysis_data = $obj->analyzeLangFile($sites[2], 'it', 'page.lang', $reference_data);

        $this
            ->integer($obj->countErrors($analysis_data['errors'], 'all'))
                ->isEqualTo(6);

        $this
            ->integer($obj->countErrors($analysis_data['errors']))
                ->isEqualTo(6);

        $this
            ->integer($obj->countErrors($analysis_data['errors'], 'python'))
                ->isEqualTo(5);

        $this
            ->integer($obj->countErrors($analysis_data['errors'], 'length'))
                ->isEqualTo(1);

        $this
            ->integer($obj->countErrors($analysis_data['errors'], 'random'))
                ->isEqualTo(0);
    }
}
