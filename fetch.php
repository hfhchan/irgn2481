<?php

require_once 'char-utils.php';
require_once 'db.php';

function getCurlInstance() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:88.0) Gecko/20100101 Firefox/88.0');
	curl_setopt($ch, CURLOPT_TIMEOUT, 12);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
		'Accept-Language: en-US,en;q=0.8,zh-HK;q=0.5,zh;q=0.3', 
		'Content-Type: application/x-www-form-urlencoded', 
		'Origin: https://dict.variants.moe.edu.tw', 
		'Connection: keep-alive', 
		'Referer: https://dict.variants.moe.edu.tw/variants/rbt/word_attribute.rbt?quote_code=QjAyNDY3LTAwMQ', 
		'Upgrade-Insecure-Requests: 1', 
		'Sec-Fetch-Dest: document', 
		'Sec-Fetch-Mode: navigate', 
		'Sec-Fetch-Site: same-origin', 
		'Sec-Fetch-User: ?1'
	]);
	return $ch;
}

function getEntriesForCharacter($char) {
	$filename = './.cache/' . sha1('query_result.do:' . $char) . '.html';
	if (file_exists($filename)) {
		$output = file_get_contents($filename);
	} else {
		sleep(4);
		$ch = getCurlInstance();
		$url = "https://dict.variants.moe.edu.tw/variants/rbt/query_result.do?from=standard";
		$postFields = "search_text=" . urlencode($char);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_TIMEOUT, 12);

		$output = curl_exec($ch);
		if (!$output) {
			echo 'Failed to fetch URL: ' . htmlspecialchars($url) . ' - ';
			echo htmlspecialchars($postFields);
			echo '<br>';
			echo curl_error($ch);
			exit;
		}

		curl_close($ch);
		file_put_contents($filename, $output);
	}

	$output = preg_replace('@<footer.*?<\\/footer>@s', '', $output);
	$output = preg_replace('@<script.*?<\\/script>@s', '', $output);
	$output = preg_replace('@<style.*?<\\/style>@s', '', $output);

	$pos1 = strpos($output, '<table class="word_list"');
	$pos2 = strpos($output, "\n" . '</table>', $pos1);

	$output = substr($output, $pos1, $pos2 - $pos1 + 9);

	$output = preg_replace('@<td>\s*<div style="font-size:12px;color:#090;">.*</div>\s+<div style="font-size:12px;color:#900;">.*</div>\s*</td>@', '', $output);
	$output = preg_replace('@<td>\s*<div style="font-size:12px">.*</div>\s+<div style="font-size:12px">.*</div>\s*</td>@', '', $output);

	preg_match_all('@<a.*?id="(.*?)".*?>(.*)</a>@', $output, $matches);

	$matches[2] = array_map(function($link) {
		cacheImageIfRequired($link);
		return useCachedImageIfAvailable($link);
	}, $matches[2]);

	return array_combine(array_map("base64_decode", $matches[1]), $matches[2]);
}

function getEntry($entry) {
	$filename = './.cache/' . sha1('word_attribute.rbt:' . $entry) . '.html';
	if (file_exists($filename)) {
		$output = file_get_contents($filename);
	} else {
		$ch = getCurlInstance();
		$url = "https://dict.variants.moe.edu.tw/variants/rbt/word_attribute.rbt?quote_code=" . base64_encode($entry);
		curl_setopt($ch, CURLOPT_URL, $url);

		$output = curl_exec($ch);
		if (!$output) {
			echo 'Failed to fetch URL: ' . htmlspecialchars($url) . ' - ';
			echo '<br>';
			echo curl_error($ch);
			exit;
		}

		curl_close($ch);
		file_put_contents($filename, $output);
	}

	$output = preg_replace('@<footer.*?<\\/footer>@s', '', $output);
	$output = preg_replace('@<script.*?<\\/script>@s', '', $output);
	$output = preg_replace('@<style.*?<\\/style>@s', '', $output);
	$output = preg_replace('@<!--形體資料表_開始 -->.*?<!--形體資料表_結束 -->@s', '', $output);

	$pos1 = strpos($output, '<!-- word_attribute 插入起始點 -->');
	$pos2 = strpos($output, '<!-- word_attribute 插入結束點 -->', $pos1);
	$output = substr($output, $pos1, $pos2 - $pos1 + 39);

	// Get EDU Code
	$pos1 = strpos($output, 'class="font_en"');
	$pos2 = strpos($output, '</span>', $pos1);
	$output1a = substr($output, $pos1, $pos2 - $pos1 + 7);
	preg_match('@.*?>(.*?)</span>@', $output1a, $matches1a);

	// Get Head Character
	$pos1 = strpos($output, 'class="font_zh_TW"');
	$pos2 = strpos($output, '</span>', $pos1);
	$output1b = substr($output, $pos1, $pos2 - $pos1 + 7);
	cacheImageIfRequired($output1b);
	$output1b = useCachedImageIfAvailable($output1b);
	preg_match('@.*?>(.*?)</span>@', $output1b, $matches1b);

	// Get Variants
	$pos0 = strpos($output, 'class="word_list"');
	$pos1 = strpos($output, 'class="word_list"', $pos0 + 1);
	if ($pos1 === false) {
		$matches2 = [ [], [], [] ];
	} else {
		$pos2 = strpos($output, '</div>', $pos1);
		$output2 = substr($output, $pos1, $pos2 - $pos1 + 6);
		preg_match_all('@<span id=".*?" onclick=".*?educode=(.*?)&.*?" .*?>(.*?)</span>@', $output2, $matches2);
	}

	$matches2[2] = array_map(function($name) {
		cacheImageIfRequired($name);
		$name = useCachedImageIfAvailable($name);
		return preg_replace('@ alt=".*?"@', ' loading=lazy', $name);
	}, $matches2[2]);

	return (object) [
		'educode' => $matches1a[1],
		'headCharacter' => $matches1b[1],
		'variants' => array_combine($matches2[1], $matches2[2])
	];
}

function cacheImageIfRequired($html) {
	preg_match('@<img .*?src="(.*?)".*?>@', $html, $matches);
	if (isset($matches[1])) {
		$filename = './.cache/' . sha1($matches[1]) . '.png';
		if (!file_exists($filename)) {
			$ch = getCurlInstance();
			curl_setopt($ch, CURLOPT_URL, $matches[1]);
			$imageData = curl_exec($ch);
			if (!$imageData) {
				echo curl_error($ch);
				exit;
			}
			curl_close($ch);
			file_put_contents($filename, $imageData);
		}
	}
}

function useCachedImageIfAvailable($html) {
	preg_match('@<img .*?src="(.*?)".*?>@', $html, $matches);
	if (isset($matches[1])) {
		$title = $matches[1];
		$hash = sha1($matches[1]);

		// Override use handwritten version because Songti incorrect:
		if ($hash === '8181c7f0f2e2dad19c9a198e1756a0f4aefb2242') {
			$hash = '8181c7f0f2e2dad19c9a198e1756a0f4aefb2242-2';
			$title .= ' (overriden)';
		}

		$filename = './.cache/' . $hash . '.png';
		if (file_exists($filename)) {
			$html = str_replace($matches[1], $filename . '" title="' . $title, $html);
		}
	}
	return $html;
}

function tryGetCodepoint($html) {
	preg_match('@<img .*?title=".*?/temp_png/([0-9a-f]{4,5}).png".*?>@', $html, $matches);
	if (isset($matches[1])) {
		return 'U+' . strtoupper($matches[1]);
	}
	if (strlen($html) === 3 || strlen($html) === 4) {
		return charToCodepoint($html);
	}
	return null;
}

if (!isset($_POST['charlistarea']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
	header('Content-type: application/json');

	if (isset($_POST['category_id']) && isset($_POST['source']) && isset($_POST['reference'])) {
		$db = new DB();
		$q = $db->data->prepare('INSERT INTO glyph VALUES(null, ?, ?, ?, ?)');
		$result = $q->execute([ $_POST['category_id'], $_POST['source'], $_POST['reference'], null ]);
		echo json_encode([ "success" => $result ]);
		exit;
	}

	if (isset($_POST['glyph_id']) && isset($_POST['related_reference'])) {
		$db = new DB();
		$q = $db->data->prepare('UPDATE glyph SET related_reference = ? WHERE glyph_id = ?');
		$result = $q->execute([ $_POST['related_reference'], $_POST['glyph_id'] ]);
		echo json_encode([ "success" => $result ]);
		exit;
	}

	echo json_encode([ "error" => "Unknown param" ]);
	exit;
}

if (isset($_POST['charlistarea'])) {
	$chars = [];
	$rows = explode("\n", $_POST['charlistarea']);
	foreach ($rows as $row) {
		if (preg_match('@U\\+[0-9A-F]+\t(.)\t.+@u', $row, $matches)) {
			$chars[] = $matches[1];
		}
	}
	$chars = array_values(array_filter(array_map("trim", $chars)));
	header('HTTP/1.1 303 See Other');
	header('Location: ?charlist=' . implode(',', $chars));
	exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'lookup') {
	$char = $_GET['char'];

	$db = new DB();
	$q = $db->cache->prepare('REPLACE INTO twedu ( "educode", "glyph", "related") VALUES (?, ?, ?)');

	$entries = getEntriesForCharacter($char);
	echo "<h2>Lookup for " . $char . '</h2>';
	if (empty($entries)) {
		echo '(no entries found)';
	}
	foreach ($entries as $code => $entryName) {
		$entry = getEntry($code);
		
		echo '<div class=query-character>→ ' . $entryName . ' (<a target=_blank href="https://dict.variants.moe.edu.tw/variants/rbt/word_attribute.rbt?quote_code='. base64_encode($code) . '">' . $code . '</a>)</div>' . "\r\n";
		echo '<table>';
		echo '<col width=120><col width=80><col>';
		if ($db->hasGroup($entry->educode)) {
			$className = 'grouped';
		} else {
			$className = '';
		}
		echo '<tr class="' . $className . '">';
		echo '<td class=head-character-code>' . $entry->educode . ':</td>';
		echo '<td class=head-character>' . $entry->headCharacter . '</td>';
		echo '<td>';
		$codepoint = tryGetCodepoint($entry->headCharacter);
		if ($codepoint) {
			$char = codepointToChar($codepoint);
			if (charToUSV($char) >= 0xF0000) {
				echo $codepoint;
			} else {
				echo $codepoint . ' ' . $char;
			}
		}
		echo '</td>';
		echo '</tr>';			
		$q->execute([ $entry->educode, $entry->headCharacter, $codepoint ]);

		if (empty($entry->variants)) {
			echo '<tr><td colspan=2>(no variants found)</td></tr>';
		} else {
			foreach ($entry->variants as $code => $glyph) {
				if ($db->hasGroup($code)) {
					$className = 'grouped';
				} else {
					$className = '';
				}
				echo '<tr class="' . $className . '">';
				echo '<td class=variant-code>';
				echo $code;
				echo ':</td>';
				echo '<td class=variant-glyph>';
				echo $glyph;
				echo '</td>';

				echo '<td>';
				$codepoint = tryGetCodepoint($glyph);
				if ($codepoint) {
					$char = codepointToChar($codepoint);
					if (charToUSV($char) >= 0xF0000) {
						echo $codepoint;
					} else {
						echo $codepoint . ' ' . $char;
					}
				}
				echo '</td>';

				echo '</tr>';

				$glyph = str_replace(' style="border:1px solid #660000"','', $glyph);

				$q->execute([ $code, $glyph, $codepoint ]);
			}
		}
		echo '</table>';
	}
	exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'category') {
	$db = new DB();
	$category = $db->getCategory($_GET['group'], $_GET['glyph']);

	echo '<div class=category>';
	if (preg_match('@twedu-[a-c][0-9]+(-[0-9]+)*@', $category->glyph) || preg_match('@cbeta-[0-9a-z-]*@', $category->glyph) || preg_match('@u[0-9a-f]+(-[0-9a-z]+)*@', $category->glyph)) {
		echo '<div class=head-character><img src="https://glyphwiki.org/glyph/' . htmlspecialchars($category->glyph) . '.svg" width=40></div>';
	} else {
		echo '<div class=head-character>' . htmlspecialchars($category->glyph) . '</div>';
	}

	$glyphs = $db->getGlyphsForCategory($category->category_id);
	echo '<table>';
	echo '<col width=120><col width=80><col width=160><col>';
	foreach ($glyphs as $glyph) {

		$eduEntry = $db->getGlyphForTwEdu($glyph->reference);

		echo '<tr>';
		echo '<td class=variant-code>';
		echo $glyph->reference;
		echo ':</td>';
		echo '<td class=variant-glyph>';
		if ($eduEntry) echo $eduEntry->glyph;
		echo '</td>';
		echo '<td>';
		if ($eduEntry) echo $eduEntry->related;
		echo '</td>';
		if (isset($glyph->related_reference)) {
			echo '<td class=variant-glyph>';
			$referenceEduEntry = $db->getGlyphForTwEdu($glyph->related_reference);
			if ($referenceEduEntry) echo $referenceEduEntry->glyph;
			echo '</td>';
		} else {
			echo '<td>';
			echo '<button type=button class=add-related-reference data-glyph-id="' . $glyph->glyph_id . '">Add Related</button>';
			echo '</td>';
		}

		echo '</tr>';
	}
	echo '</table>';

	echo '<button type=button class=add-glyph data-category-id="' . $category->category_id . '">Add Glyph</button>';
	echo '</div>';
	exit;
}

$presets = [
	'枼' => '枼,屧,䈎,𠝝,𧄠,喋,堞,媟,牒,碟,艓,蝶,褋,蹀,鰈,㻡,䁋,䐑,䮜,𤚊,𥷕,𧃹,𧄠,𪑧,弽,惵,煠,緤,諜,鍱,鞢,韘,䭎,𠗨,𥻈,𩐱,偞,屟,揲,楪,殜,渫,牃,䑜,䚢,䢡,葉,䥡,𣛻,䕈,𧀢,僷,擛,瞸,蠂,鐷,㵩,䜓,䭟',
	'㒼' => '㒼,顢,鬗,𢟮,𥲈,慲,樠,滿,襔,鏋,㙢,𡠪,𩞘,𩺴,暪,璊,瞞,蹣,㨺,䊟,䐽,䝡,䤍,𡦖,𤡁,𥡹,𧫩,𧱼,蟎,𤅎,𤂉,濷,𣂎,𤾯,𥵥,懣,𡒗,𡣩,𤃞',
	'辰' => '辰,宸,辱,𡝌,𦸳,𨑉,敐,㰮,㲀,䣅,䫃,𢦿,𪁧,娠,帪,振,桭,浱,祳,蜄,裖,辴,鋠,陙,㖘,䀼,䟴,𦁄,侲,屒,脤,賑,唇,脣,蜃,㫳,䢈,𪓧,晨,莀,農,震,麎,䆣,䢅,䢉,𨑆,蓐,𩱨,鄏,㦺,𠢑,𢾯,𫯕,𬷨,𰚩,嗕,媷,槈,縟,褥,鎒,䢆,䢇,𡏌,𡫦,𢟹,𤹘,𥛑,𧏯,𧗈,𨃽,𪑾,𬌽,𬢾,傉,搙,溽,耨,䅶,薅,𧂭,𩽔,䳲,𢤟,𢸍,𤂪,𨯂,滣,䞅,𠸸,𩺦,𫋏,𬫷,漘,磭,䔚,䥎,𩕁,𤡠,鷐,𩀭,𪪸',
	'詹' => '詹,甔,簷,薝,䦲,𣀁,儋,噡,嶦,幨,憺,擔,曕,檐,澹,癚,瞻,聸,膽,舚,蟾,襜,譫,贍,韂,黵,㙴,㜬,䃫,䄡,䟋,䠨,䪜,𢕻,𣠳,𤖝,𤗻,𦉜,𨊍,𨼮,𩟋,𧀻',
	'并' => '并,瓶,艵,荓,頩,㤣,䈂,䦕,𡾛,𩂦,郱,鵧,㔙,𢆣,𫷘,垪,姘,栟,皏,硑,缾,蛢,跰,軿,鉼,餅,骿,鮩,㻂,䑫,䴵,𢏳,𥞩,𪋋,𭇴,𮇛,併,帡,庰,恲,絣,聠,胼,誁,賆,駢,𢆟,𤳊,𪘀,拼,洴,𭴡,迸,屏,䔊,蓱,𧁕,𩅅',
];

if (isset($_GET['charlist'])) {
	if (isset($presets[$_GET['charlist']])) {
		$chars = explode(',', $presets[$_GET['charlist']]);
	} else {
		$chars = explode(',', $_GET['charlist']);
		$chars = array_values(array_filter(array_map("trim", $chars)));
	}
} else {
	$chars = explode(',', $presets['㒼']);
}

?>
<!doctype html>
<title>Variant Groups</title>
<style>
html{height:100%}
body{font-family:Arial,sans-serif;margin:0;display:grid;grid-template-columns:1fr 1fr;grid-template-rows:auto 1fr;height:100%}

#charlist_form{background:#daf0ff;padding:10px;display:flex;gap:8px;position:sticky;top:0;grid-column:span 2}
#charlist_form input[name="charlist"]{width:480px;font-size:32px}
#charlist_form textarea{width:480px}
#charlist_form input[type="submit"]{padding:4px 30px}
#charlist_form button{padding:4px 30px}

#charlist{padding:20px;overflow:auto}

section{margin:0 0 40px}
h2{margin:0 0 20px}
table{border-collapse:collapse;width:100%;margin:4px 0}
table td{padding:4px}
table+.query-character{margin-top:10px}
tr.grouped{background:#eee}
.variant-code{font-size:16px}

.head-character{font:40px/1 Arial, PMingLiU}
.head-character img{display:block;width:40px;height:40px}

.variant-glyph{font:40px/1 Arial, PMingLiU}
.variant-glyph img{display:block;width:40px;height:40px}

#chargroups{border-left:1px solid #8fd3fe;padding:20px;overflow:auto}
.category{margin:10px 0}
</style>

<script>
window.addEventListener('click', e => {
	const submitIds = e.target.closest('button.submit-ids');
	if (submitIds != null) {
		const textareaExists = document.querySelector('#charlist_form textarea')
		if (textareaExists == null) {
			document.querySelector('#charlist_form').method = 'post';

			const textarea = document.createElement('textarea');
			textarea.name = 'charlistarea'
			const charlist = document.querySelector('#charlist_form input[name="charlist"]')
			charlist.parentNode.insertBefore(textarea, charlist);
			charlist.remove();
			submitIds.remove();
		}
	}

	const categoryAddGlyph = e.target.closest('button.add-glyph');
	if (categoryAddGlyph != null) {
		let educode = prompt('Enter TW-EDU code');
		if (educode) {
			educode = educode.trim().toUpperCase();
			if (educode.endsWith(":")) {
				educode = educode.slice(0, -1);
			}

			const params = new URLSearchParams();
			params.set("category_id", categoryAddGlyph.dataset.categoryId);
			params.set("source", "tw-edu");
			params.set("reference", educode);

			fetch(window.location.href, {
				method: 'POST',
				body: params
			}).then(async res => {
				const body = await res.json()
				if (body.success) e.target.closest('ucv-category').loadData();
				else alert("error occured");
				document.querySelectorAll('twedu-lookup').forEach(section => {
					if (section.codes && section.codes.includes(educode)) section.loadData();
				});
			}).catch(e => {
				console.error(e);
				alert(e);
			});
		}
	}
	
	const categoryAddRelatedReference = e.target.closest('button.add-related-reference');
	if (categoryAddRelatedReference != null) {
		let educode = prompt('Enter TW-EDU code');
		if (educode) {
			educode = educode.trim().toUpperCase();
			if (educode.endsWith(":")) {
				educode = educode.slice(0, -1);
			}

			const params = new URLSearchParams();
			params.set("glyph_id", categoryAddRelatedReference.dataset.glyphId);
			params.set("related_reference", educode);

			fetch(window.location.href, {
				method: 'POST',
				body: params
			}).then(async res => {
				const body = await res.json()
				if (body.success) e.target.closest('ucv-category').loadData();
				else alert("error occured");
			}).catch(e => {
				console.error(e);
				alert(e);
			});
		}
	}
})
</script>

<form method=get id=charlist_form>
	<input name="charlist" value="<?=htmlspecialchars(implode(',', $chars))?>">
	<input type=submit value="Submit">
	<button type=button class=submit-ids>Submit IDS</button>
</form>

<main id=charlist>
<?php

foreach ($chars as $char) {	
	echo '<section>';
	echo '<twedu-lookup data-char="' . htmlspecialchars($char) . '"></twedu-lookup>';
	echo '</section>';
}

?>
</main>

<aside id="chargroups">
<?

$db = new DB();

foreach ($chars as $char) {
	$categories = $db->getCategoryForChar($char);
	if (!empty($categories)) {
		echo '<section>';
		echo '<h2>Group ' . htmlspecialchars($categories[0]->group) . '</h2>';
		foreach ($categories as $category) {
			echo '<ucv-category data-group="' . htmlspecialchars($category->group) . '" data-glyph="' . htmlspecialchars($category->glyph) . '"></ucv-category>';
		}
		echo '</section>';
	}
}

?>
</aside>
<script>
class UcvCategory extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
	}

	connectedCallback() {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.style.display = 'block';
		this.loadData();
	}

	async loadData() {
		const url = new URLSearchParams();
		url.append('action', 'category');
		url.append('group', this.dataset.group);
		url.append('glyph', this.dataset.glyph);
		const res = await fetch('?' + url.toString());
		const html = await res.text();
		this.innerHTML = html;
	}
}
customElements.define('ucv-category', UcvCategory);

class TweduLookup extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
	}

	connectedCallback() {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.style.display = 'block';
		this.innerHTML = '<h2>Lookup for ' + this.dataset.char + '</h2><p>Loading...</p>';
		this.queueLoadData();
	}

	async queueLoadData() {
		let pendingJob = TweduLookup.currentItem;
		await pendingJob;
		while (pendingJob != TweduLookup.currentItem) {
			pendingJob = TweduLookup.currentItem
			await pendingJob;
		}

		TweduLookup.currentItem = new Promise(async (resolve, reject) => {
			await this.loadData();
			resolve();
		});
	}

	async loadData() {
		const url = new URLSearchParams();
		url.append('action', 'lookup');
		url.append('char', this.dataset.char);
		const res = await fetch('?' + url.toString());
		const html = await res.text();
		this.innerHTML = html;
		this.codes = [...this.querySelectorAll('.head-character-code,.variant-code')].map(el => el.textContent.trim().replace(":", ""));
	}
}
customElements.define('twedu-lookup', TweduLookup);
</script>