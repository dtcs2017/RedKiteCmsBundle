<?php
/*
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 * 
 * @license    GPL LICENSE Version 2.0
 * 
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Event\Query;

/**
 * Defines the names for events dispatched when applying a filter on languages
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
final class LanguagesEvents
{
    const ACTIVE_LANGUAGES = 'query_languages.active_languages';
    const FROM_LANGUAGE_NAME = 'query_languages.from_language_name';
    const MAIN_LANGUAGE = 'query_languages.main_language';
}    
