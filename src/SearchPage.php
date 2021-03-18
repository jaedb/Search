<?php

namespace PlasticStudio\Search;

use Page;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;

class SearchPage extends Page {
	
	private static $description = "Search engine and results page. You only need one of these page types.";

	private static $defaults = [
		'ShowInMenus' => 0,
		'ShowInSearch' => 0
	];

	/**
	 * We need to have a SearchPage to use it
	 */
	public function requireDefaultRecords()
	{
		parent::requireDefaultRecords();
		
		if (static::class == self::class && $this->config()->create_default_pages) {
			if (count(SearchPage::get()) < 1) {
				$page = SearchPage::create();
				$page->Title = 'Search';
				$page->Content = '';
				$page->ShowInMenus = false;
				$page->ShowInSearch = false;
				$page->write();
				$page->copyVersionToStage(Versioned::DRAFT, Versioned::LIVE);
				$page->flushCache();
				DB::alteration_message('Search page created', 'created');
			}
		}
	}
}