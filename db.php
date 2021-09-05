<?php

class DB {
	public $cache;
	public $data;

	public function __construct() {
		$this->cache = new PDO("sqlite:cache.db");
		$this->cache->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$this->data = new PDO("sqlite:data.db");
		$this->data->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function getGlyphForTwEdu($educode) {
		$q = $this->cache->prepare('SELECT * FROM twedu WHERE educode = ?');
		$q->execute([ $educode ]);
		$entry = $q->fetch(PDO::FETCH_OBJ);
		if ($entry) {
			$entry->glyph = str_replace(' style="border:1px solid #660000"','', $entry->glyph);
			return $entry;
		}
		return null;
	}

	public function getCategoryForChar($char) {
		$q = $this->data->prepare('SELECT * FROM "category" WHERE "group" = ?');
		$q->execute([ $char ]);
		return $q->fetchAll(PDO::FETCH_OBJ);
	}

	public function getCategory($group, $glyph) {
		$q = $this->data->prepare('SELECT * FROM "category" WHERE "group" = ? AND "glyph" = ? LIMIT 1');
		$q->execute([ $group, $glyph ]);
		return $q->fetch(PDO::FETCH_OBJ);
	}
	
	public function getGlyphsForCategory($category_id) {
		$q = $this->data->prepare('SELECT * FROM "glyph" WHERE "category_id" = ?');
		$q->execute([ $category_id ]);
		return $q->fetchAll(PDO::FETCH_OBJ);
	}

	public function hasGroup($educode) {
		$q = $this->data->prepare('SELECT COUNT(*) FROM "glyph" WHERE "source" = ? AND "reference" = ?');
		$q->execute([ 'tw-edu', $educode ]);
		return $q->fetchColumn();
	}
}
