<?php

namespace PlasticStudio\Search;

use SilverStripe\Dev\BuildTask;
use SilverStripe\View\SSViewer;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\Queries\SQLUpdate;

class IndexPageContentForSearchTask extends BuildTask
{
    protected $title = 'Collate Page Content Task';
 
    protected $description = 'Collate Page Content Task';
 
    public function run($request)
    {

        // Select all sitetree items without search content
        $items = SiteTree::get()->filter(['ElementalSearchContent' => NULL])->limit(5);

        //**********************************************************************************
        // TODO REMOVE LIMIT WHEN READY FOR LIVE
        //**********************************************************************************
    
        foreach($items as $item) {

            // Debug::show($item->Title);

            // get the page content as plain content string
            $content = $this->collateSearchContent($item);

            // Debug::show($content);

            // Update this item in db
            $update = SQLUpdate::create();
            $update->setTable('"SiteTree"');
            $update->addWhere(['ID' => $item->ID]);
            $update->addAssignments([
                '"ElementalSearchContent"' => $content
            ]);
            $update->execute();

            // Debug::show($item->isPublished());

            // IF page is published, update the live table
            if ($item->isPublished()) {
                $update = SQLUpdate::create();
                $update->setTable('"SiteTree_Live"');
                $update->addWhere(['ID' => $item->ID]);
                $update->addAssignments([
                    '"ElementalSearchContent"' => $content
                ]);
                $update->execute();
            }
        }
    }

    /**
     * Generate the search content to use for the searchable object
     *
     * We just retrieve it from the templates.
     */
    private function collateSearchContent($page): string
    {
        // Get the page
        /** @var SiteTree $page */
        // $page = $this->getOwner();

        $content = '';

        if (self::isElementalPage($page)) {
            // Get the page's elemental content
            $content .= $this->collateSearchContentFromElements($page);
        }

        return $content;
    }


    /**
     * @param SiteTree $page
     * @return bool
     */
    private static function isElementalPage($page)
    {
        return $page::has_extension("DNADesign\Elemental\Extensions\ElementalPageExtension");
    }

    /**
     * @return string|string[]|null
     */
    private function collateSearchContentFromElements($page)
    {
        // Get the original theme
        $originalThemes = SSViewer::get_themes();

        // Init content
        $content = '';

        try {
            // Enable frontend themes in order to correctly render the elements as they would be for the frontend
            Config::nest();
            SSViewer::set_themes(SSViewer::config()->get('themes'));

            // Get the elements content
            $content .= $page->getOwner()->getElementsForSearch();

            // Clean up the content
            $content = preg_replace('/\s+/', ' ', $content);

            // Return themes back for the CMS
            Config::unnest();
        } finally {
            // Restore themes
            SSViewer::set_themes($originalThemes);
        }

        return $content;
    }

}