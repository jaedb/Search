<?php

namespace PlasticStudio\Search;

use PlasticStudio\Search\SiteTreeSearchExtension;
use SilverStripe\Core\Extension;
use SilverStripe\Versioned\Versioned;

class ElementalSearchExtension extends Extension
{

    /**
     * Force a re-index of the parent page for any given element
     * @param Versioned $original
     */
    public function onAfterPublish(&$original)
    {
        $parent = $this->getOwner()->getPage();
        if ($parent && $parent->hasExtension(SiteTreeSearchExtension::class)) {
            $parent->updateSearchContent();
        }
    }
}