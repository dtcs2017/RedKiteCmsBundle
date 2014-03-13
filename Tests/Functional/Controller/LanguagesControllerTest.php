<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license infpageRepositoryation, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\Functional\Controller;

use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Tests\WebTestCaseFunctional;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\AlLanguageRepositoryPropel;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\AlSeoRepositoryPropel;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel;

/**
 * LanguagesControllerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class LanguagesControllerTest extends WebTestCaseFunctional
{
    private $languageRepository;
    private $seoRepository;
    private $blockRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->languageRepository = new AlLanguageRepositoryPropel();
        $this->seoRepository = new AlSeoRepositoryPropel();
        $this->blockRepository = new AlBlockRepositoryPropel();
    }

    public function testFormElements()
    {
        $crawler = $this->client->request('POST', '/backend/en/al_showLanguages');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#languages_language')->count());
        $this->assertEquals(1, $crawler->filter('#languages_isMain')->count());
        $this->assertEquals(1, $crawler->filter('#al_language_saver')->count());
        $this->assertEquals(1, $crawler->filter('.rk-language-remover')->count());
    }

    public function testAddLanguageFailsWhenLanguageNameParamIsMissing()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'isMain' => '0',);

        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertRegExp(
            '/exception_some_required_options_are_not_provided|The following options are required: %required%. The options you gave are %values%/si',
            $this->client->getResponse()->getContent()
        );
    }

    public function testAddLanguage()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'newLanguage' => 'fr',
                        'isMain' => '0',);

        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertRegExp('/Content-Type:  application\/json/s', $response->__toString());

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($json));
        $this->assertTrue(array_key_exists("key", $json[0]));
        $this->assertEquals("message", $json[0]["key"]);
        $this->assertTrue(array_key_exists("value", $json[0]));
        $this->assertRegExp(
            '/languages_controller_language_saved|The language has been successfully saved/si',
            $json[0]["value"]
        );
        $this->assertTrue(array_key_exists("key", $json[1]));
        $this->assertEquals("languages", $json[1]["key"]);
        $this->assertTrue(array_key_exists("value", $json[1]));
        $this->assertRegExp("/\<a[^\>]+data-language-id=\"2\"\>en\<\/a\>/s", $json[1]["value"]);
        $this->assertRegExp("/en/s", $json[1]["value"]);
        $this->assertRegExp("/\<a[^\>]+data-language-id=\"3\"\>fr\<\/a\>/s", $json[1]["value"]);
        $this->assertTrue(array_key_exists("key", $json[2]));
        $this->assertEquals("languages_menu", $json[2]["key"]);
        $this->assertTrue(array_key_exists("value", $json[2]));        
        $this->assertRegExp("/\<ul class=\"dropdown-menu[^\>]+\>/s", $json[2]["value"]);
        $this->assertRegExp("/\<li id=\"none\"[^\>]+\>\<a href=\"#\"\> \<\/a\>/s", $json[2]["value"]);
        $this->assertRegExp("/\<li id=\"2\"[^\>]+\>\<a href=\"#\"\>en\<\/a\>/s", $json[2]["value"]);
        $this->assertRegExp("/\<li id=\"3\"[^\>]+\>\<a href=\"#\"\>fr\<\/a\>/s", $json[2]["value"]);

        $language = $this->languageRepository->fromPk(3);
        $this->assertNotNull($language);
        $this->assertEquals('fr', $language->getLanguageName());
        $this->assertEquals(0, $language->getMainLanguage());

        $seo = $this->seoRepository->fromPageAndLanguage(3, 2);
        $this->assertNotNull($seo);
        $this->assertEquals('fr-this-is-a-website-fake-page', $seo->getPermalink());

        // Repeated contents have not been added
        $pagesSlots = $this->retrievePageSlots();
        $this->assertEquals(count($pagesSlots), count($this->blockRepository->retrieveContents(3, 2, $pagesSlots)));
    }
    
    public function testRequestingPageFromLanguageAndPage()
    {
        $crawler = $this->client->request('GET', '/backend/fr/index');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->checkPage($crawler);
    }

    public function testRequestingPageFromPermalink()
    {
        $crawler = $this->client->request('GET', '/backend/fr-this-is-a-website-fake-page');
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());        
        $this->checkPage($crawler);
    }
    
    public function testLoadLanguageAttributes()
    {
        $params = array('languageId' => 2);
        $crawler = $this->client->request('POST', '/backend/en/al_loadLanguageAttributes', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertEquals("#languages_language", $json[0]["name"]);
        $this->assertEquals("en", $json[0]["value"]);
        $this->assertEquals("#languages_isMain", $json[1]["name"]);
        $this->assertEquals("1", $json[1]["value"]);
        
        $params = array('languageId' => 3);
        $crawler = $this->client->request('POST', '/backend/en/al_loadLanguageAttributes', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertEquals("#languages_language", $json[0]["name"]);
        $this->assertEquals("fr", $json[0]["value"]);
        $this->assertEquals("#languages_isMain", $json[1]["name"]);
        $this->assertEquals("0", $json[1]["value"]);
    }

    public function testAddLanguageFailsWhenTheLanguageNameAlreadyExists()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'newLanguage' => 'fr',
                        'isMain' => '0',);
        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertRegExp(
            '/exception_language_already_exists|The language you are trying to add, already exists in the website/si',
            $response->getContent()
        );
    }

    public function testAddANewMainLanguage()
    {
        $params = array(
            'page' => 'index',
            'language' => 'en',
            'newLanguage' => 'es',
            'isMain' => '1',
        );
        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(3, count($this->languageRepository->activeLanguages()));
        $this->assertEquals(4, $this->languageRepository->mainLanguage()->getId());

        // Previous home page has been degraded
        $language = $this->languageRepository->fromPk(2);
        $this->assertEquals(0, $language->getMainLanguage());

        $seo = $this->seoRepository->fromPageAndLanguage(4, 2);
        $this->assertNotNull($seo);
    }

    public function testEditLanguage()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'languageId' => 3,
                        'newLanguage' => "it",);

        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $language = $this->languageRepository->fromPk(3);
        $this->assertEquals('it', $language->getLanguageName());
    }
    
    public function testDegradingMainLanguageFails()
    {
        $languageId = $this->languageRepository->mainLanguage()->getId();
        $params = array(
            'page' => 'index',
            'language' => 'en',
            'languageId' => $languageId,
            'isMain' => 0,
        );

        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        $this->assertRegExp(
            '/exception_main_language_cannot_be_degraded|Current main language cannot be degraded. To change the main language you must promote another language as main and this one will be automatically degraded/si',
            $response->getContent()
        );
    }
    
    public function testLanguageHasBeenPromotedToMainLanguage()
    {
        $mainLanguage = $this->languageRepository->mainLanguage();
        
        $params = array(
            'page' => 'index',
            'language' => 'en',
            'languageId' => 3,
            'isMain' => 1,
        );

        $crawler = $this->client->request('POST', '/backend/en/al_saveLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $language = $this->languageRepository->fromPk(3);
        $this->assertEquals(1, $language->getMainLanguage());
        
        $this->assertEquals(0, $mainLanguage->getMainLanguage());
    }

    public function testDeleteLanguageFailsBecauseAnyLanguageIdIsGiven()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'languageId' => 'none');

        $crawler = $this->client->request('POST', '/backend/en/al_deleteLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertRegExp(
            '/languages_controller_any_language_selected_for_removing|Any language has been choosen for removing/si',
            $response->getContent()
        );
    }

    public function testDeleteLanguageFailsBecauseAnInvalidLanguageIdIsGiven()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'languageId' => 999);

        $crawler = $this->client->request('POST', '/backend/en/al_deleteLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertRegExp(
            '/languages_controller_any_language_selected_for_removing|Any language has been choosen for removing/si',
            $response->getContent()
        );
    }

    public function testDeleteTheMainLanaguageIsForbidden()
    {
        $language = $this->languageRepository->mainLanguage();
        $params = array('page' => 'index',
                        'language' => 'en',
                        'languageId' => $language->getId());

        $crawler = $this->client->request('POST', '/backend/en/al_deleteLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertRegExp(
            '/exception_website_main_languages_cannot_be_delete|The website main language cannot be deleted. To delete this language promote another one as main language, then delete it again/si',
            $response->getContent()
        );
    }

    public function testDeleteLanguage()
    {
        $params = array('page' => 'index',
                        'language' => 'en',
                        'languageId' => 2);

        $crawler = $this->client->request('POST', '/backend/en/al_deleteLanguage', $params);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(2, count($this->languageRepository->activeLanguages()));

        $this->assertRegExp('/Content-Type:  application\/json/s', $response->__toString());

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(3, count($json));
        $this->assertTrue(array_key_exists("key", $json[0]));
        $this->assertEquals("message", $json[0]["key"]);
        $this->assertTrue(array_key_exists("value", $json[0]));
        $this->assertRegExp(
            '/languages_controller_language_delete|The language has been successfully deleted/si',
            $json[0]["value"]
        );
        $this->assertTrue(array_key_exists("key", $json[1]));
        $this->assertEquals("languages", $json[1]["key"]);
        $this->assertTrue(array_key_exists("value", $json[1]));
        $this->assertRegExp("/\<a[^\>]+data-language-id=\"3\"\>it<\/a\>/s", $json[1]["value"]);
        $this->assertRegExp("/\<a[^\>]+data-language-id=\"4\"\>es\<\/a\>/s", $json[1]["value"]);
        $this->assertTrue(array_key_exists("key", $json[2]));
        $this->assertEquals("languages_menu", $json[2]["key"]);
        $this->assertTrue(array_key_exists("value", $json[2]));    
        $this->assertRegExp("/\<li id=\"3\"[^\>]+\>\<a href=\"#\"\>it\<\/a\>/s", $json[2]["value"]);
        $this->assertRegExp("/\<li id=\"4\"[^\>]+\>\<a href=\"#\"\>es\<\/a\>/s", $json[2]["value"]);

        $page = $this->languageRepository->fromPk(2);
        $this->assertEquals(1, $page->getToDelete());

        $seo = $this->seoRepository->fromPageAndLanguage(2, 2);
        $this->assertNull($seo);

        // Repeated contents have not been added
        $pagesSlots = $this->retrievePageSlots();
        $this->assertEquals(0, count($this->blockRepository->retrieveContents(2, 2, $pagesSlots)));
    }

    private function retrievePageSlots()
    {
        $pageTree = $this->client->getContainer()->get('red_kite_cms.page_tree');
        $slots = $pageTree->getTemplateManager()->getTemplate()->getSlots();
        $activeThemeManager = $this->client->getContainer()->get('red_kite_cms.active_theme');
        $activeTheme = $activeThemeManager->getActiveTheme();
        $themeSlots = $activeTheme->getThemeSlots();
        
        $pageSlots = array();
        foreach($slots as $slotName) {
            $slot = $themeSlots->getSlot($slotName);
            if ($slot->getRepeated() != "page") {
                continue;
            }
            
            $pageSlots[] = $slotName;
        }
        
        return $pageSlots;
    }
    
    private function checkPage($crawler)
    {
        $this->assertCount(0, $crawler->filter('#block_20'));
        $this->assertCount(1, $crawler->filter('#block_41'));
        $this->assertCount(1, $crawler->filter('#block_41')->filter('[data-name="block_41"]'));
        $this->assertCount(0, $crawler->filter('#block_47'));
        $this->assertCount(1, $crawler->filter('[data-name="block_47"]'));        
        $this->assertGreaterThan(0, count($crawler->filter('[data-editor="enabled"]')));
    }
}
