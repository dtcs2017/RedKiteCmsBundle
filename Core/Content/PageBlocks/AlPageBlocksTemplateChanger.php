<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteLabs\RedKiteCmsBundle\Core\Content\PageBlocks;

/**
 * Extends the AlPageBlocks class to load blocks of previous theme
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @deprecated since 1.1.0
 */
class AlPageBlocksTemplateChanger extends AlPageBlocks
{
    /**
     * @codeCoverageIgnore
     */
    protected function fetchBlocks()
    {
        return $this->blockRepository->retrieveContents(array(1, $this->idLanguage), array(1, $this->idPage), null, array(2, 3));
    }
}
