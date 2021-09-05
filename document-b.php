<?php

require_once 'char-utils.php';
require_once 'db.php';

$overrideCodepoint = [
	'A04109-005' => '𮝾',
	'A04109-006' => '𫝕',
	'B00330-002' => '𠵧',
	'C10275-002' => '𦁟',
	'A04111-010' => '𭆒',
	'A04110-004' => '𭔱',
	'C05082-002' => '𭩠',
	'A02110-004' => 'U+306B7',
	'A04206-003' => 'U+2868E',
	'A00126-018' => 'U+2B88F',
	'A02050-004' => 'U+2DB33',

	'A00404-010' => '𭄿',
	'A02065-016' => '𭭕',
	'A03776-009' => '𮗚',
	'A02050-005' => '𫞐',
	'A03201-011' => '𰭆',
	'B01225-004' => '𭞹',
];

$additions = [
	[
		'group_glyph' => 'twedu-a00867-009',
		'path' => './original/zinbun-換-任顓墓誌銘.jpg',
		'desc' => '任顓墓誌銘',
		'related' => '換'
	],
];

if (isset($_GET['action']) && $_GET['action'] == 'category') {
	$db = new DB();
	if (strpos($_GET['glyph'], ',')) {
		$glyphs = [];
		$__a = explode(',', $_GET['glyph']);
		foreach ($__a as $__b) {
			$category = $db->getCategory($_GET['group'], $__b);
			$__c = $db->getGlyphsForCategory($category->category_id);
			foreach ($__c as $__d) {
				$glyphs[] = $__d;
			}
		}
	} else {
		$category = $db->getCategory($_GET['group'], $_GET['glyph']);
		$glyphs = $db->getGlyphsForCategory($category->category_id);
	}

	if ($_GET['group'] === '氐') {
		usort($glyphs, function($a, $b) {
			if ($a->reference === 'A02835-003') return -1;
			if ($b->reference === 'A02835-003') return 1;

			if ($a->reference === 'A02835-008') return -1;
			if ($b->reference === 'A02835-008') return 1;

			if ($a->reference === 'B00513-001') return -1;
			if ($b->reference === 'B00513-001') return 1;

			return strcmp($a->reference, $b->reference);
		});
	}
	if ($_GET['group'] === '枼') {
		usort($glyphs, function($a, $b) {
			if ($a->reference === 'A03863-009') return -1;
			if ($b->reference === 'A03863-009') return 1;

			if ($a->reference === 'C05082-001') return -1;
			if ($b->reference === 'C05082-001') return 1;

			if ($a->reference === 'C05082-002') return -1;
			if ($b->reference === 'C05082-002') return 1;
			return strcmp($a->reference, $b->reference);
		});
	}
	if ($_GET['group'] === '并' || $_GET['group'] === '奐' || $_GET['group'] === '雚') {
		usort($glyphs, function($a, $b) {
			if ($a->reference === 'A00867-008') return -1;
			if ($b->reference === 'A00867-008') return 1;
	
			if ($a->reference === 'A00867-003') return -1;
			if ($b->reference === 'A00867-003') return 1;

			if ($a->reference === 'A03776-045') return -1;
			if ($b->reference === 'A03776-045') return 1;

			if ($a->reference === 'A02065-009') return -1;
			if ($b->reference === 'A02065-009') return 1;

			if ($a->reference === 'A02378-006') return -1;
			if ($b->reference === 'A02378-006') return 1;

			return strcmp($a->reference, $b->reference);
		});
	}

	echo '<div class=category>';
	echo '<table>';
	if (!empty($_GET['original'])) {
		echo '<col width=48><col width=48><col width=120><col width=160><col width=48><col width=160>';
	} else {
		echo '<col width=48><col width=120><col width=160><col width=48><col width=160>';
	}
	echo '<thead><tr>';
	if (!empty($_GET['original'])) {
		echo '<th colspan=2>Variant</th>';
	} else {
		echo '<th>Variant</th>';
	}
	echo '<th>Edu Code</th><th>Codepoint</th><th style="font-size:12px;line-height:1">Related Character</th><th>Codepoint</th></thead>';
	foreach ($glyphs as $glyph) {

		$eduEntry = $db->getGlyphForTwEdu($glyph->reference);

		echo '<tr>';
		if (!empty($_GET['original'])) {
			if (file_exists('./original/' . $glyph->reference . '.png')) {
				echo '<td class=variant-glyph><img src="./original/' . $glyph->reference . '.png" style="height:auto"></td>';
			} else {
				echo '<td>N/A</td>';
			}
		} else {
			if (file_exists('./original/' . $glyph->reference . '.png')) {
				echo '<div style="background:red;padding:40px">Found orphan image:';
				echo '<img src="./original/' . $glyph->reference . '.png">';
				echo '</div>';
			}
		}
		echo '<td class=variant-glyph>';
		if ($eduEntry) echo $eduEntry->glyph;
		echo '</td>';
		echo '<td class=variant-code>';
		echo $glyph->reference;
		echo '</td>';
		echo '<td>';
		if ($eduEntry && isset($overrideCodepoint[$glyph->reference])) {
			try {
				echo charToCodepoint($overrideCodepoint[$glyph->reference]);
			} catch (Exception $e) {
				echo $overrideCodepoint[$glyph->reference];
			}
			echo '*';
		} else if ($eduEntry && $eduEntry->related && $eduEntry->related[2] !== 'F') {
			echo $eduEntry->related;
		} else {
			echo '(not encoded)';
		}
		echo '</td>';
		if (isset($glyph->related_reference)) {
			echo '<td class=variant-glyph>';
			$referenceEduEntry = $db->getGlyphForTwEdu($glyph->related_reference);
			if ($referenceEduEntry) echo $referenceEduEntry->glyph;
			else echo '<span style="font-size:24px;line-height:48px;font-family:&quot;I.Ming&quot;">' . $glyph->related_reference . '</span>';
			echo '</td>';
			echo '<td>';
			if ($referenceEduEntry && $referenceEduEntry->related) echo htmlspecialchars($referenceEduEntry->related);
			else echo '(not encoded)';
			echo '</td>';
		}

		echo '</tr>';
	}

	foreach ($additions as $entry) {
		if ($_GET['glyph'] === $entry['group_glyph']) {
			$codepoint = charToCodepoint($entry['related']);
			echo '<tr>';
			echo '<td class="variant-glyph"><img src="'.htmlspecialchars($entry['path']).'"></td>';
			echo '<td>'.htmlspecialchars($entry['desc']).'</td><td>(not encoded)</td>';
			echo '<td class="variant-glyph">'.htmlspecialchars($entry['related']).'</td>';
			echo '<td>'.$codepoint.'</td>';
			echo '</tr>';
		}
	}

	echo '</table>';
	echo '<div style="font-size:13px">(Total ' . count($glyphs) . ' glyphs)</div>';
	echo '</div>';
	exit;
}

?>

<!doctype html>
<title>IRGN2481 Proposal to add new UCV rules</title>
<style>
body{font:16px / 1.4 Arial}
p:first-child{margin-top:0}
@page {
	size: A4; 
    margin: 10mm 10mm 10mm 10mm;  
} 
@media screen {
	body{width:21cm;margin:0 auto;padding:1cm;box-sizing:border-box;box-shadow:0 0 1px}
	section{margin-bottom:20px;border-bottom:4px solid #ccc}
}
section:not(:first-child){break-inside:avoid}

table{border-collapse:collapse;width:100%;margin:4px -8px;border:1px solid #999}
table tr{border-bottom:1px solid #999}
table td{padding:4px 8px}
table th{padding:4px 8px;text-align:left}

hr{border-top:1px solid #ccc;border-bottom:none;border-left:none;border-right:none;margin:16px 0}

div.p{break-inside:avoid}
div.p > .head-character{margin:5px 0 0}
p, div.p{margin:16px 0}
p img{height:20px;vertical-align:bottom}

.head-character{font:40px/1 Arial, "I.Ming", PMingLiU;display:flex;flex-wrap:wrap}
.head-character img{display:block;width:40px;height:40px}

.variant-glyph{font:40px/1 Arial, PMingLiU}
.variant-glyph img{display:block;width:40px;height:40px}
</style>

<script src="document-ucv-category.js"></script>

<body>
<section>
	<p>
		<b>IRGN2481 Proposal to add new UCV rules</b><br>
		Source: Henry Chan, Eiso Chan, Yi Bai<br>
		Status: Individual Contribution<br>
		Action: To be considered by IRG<br>
		Date: 2021-09-05<br>
		Pages: 43
	</p>
	<p>
		<b>Introduction</b><br>
		The document proposes a number of additions to the Unifiable Component Variations (UCV) list to better handle historical variants.
	</p>
	<p>
		<b>Methodology</b><br>
		Characters which appeared in multiple Ideographic Description Sequences (IDS) were selected for analysis. The IDS used is hosted at <a href="https://github.com/hfhchan/ids">https://github.com/hfhchan/ids</a>. A list of variants for each character were extracted from various sources, including the online MOE Variants Dictionary version 6 (《教育部異體字字典》(正式六版)) and other reference materials. Tables of the variant characters linked to the canonical forms were generated manually based on the extracted data.
	</p>
	<div class=p>
		<b>Item 1</b><br>
		Unify the following shapes:

		<div class="head-character">
			鹿
			𢉖
		</div>
	</div>
	<div class=p>
		<b>Item 2</b><br>
		Unify the following shapes:
		<div class="head-character">辰𮝾<img src="https://glyphwiki.org/glyph/u2e77e-var-002.svg">𫝕</div>
	</div>
	<div class=p>
		<b>Item 3</b><br>
		Unify the following shapes:
		<div class="head-character">
			枼
			𭩠
			<img src="https://glyphwiki.org/glyph/twedu-c05082-002.svg">
			<img src="https://glyphwiki.org/glyph/u2da60-var-004.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-004.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-002.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-003.svg">
			<img src="https://glyphwiki.org/glyph/u233cb-itaiji-001.svg">
			𣏋
			𣐂
			枽
		</div>
	</div>
	<div class=p>
		<b>Item 4</b><br>
		Unify the following shapes:
		<div class="head-character">
			氐
			<img src="./original/A02110-004.png" style="width:auto">
			<img src="./original/A02110-999-02.png" style="width:auto">
			<img src="./original/A02110-999-01.png" style="width:auto">
			<img src="./original/A02110-999-08.png" style="width:auto">
			<img src="./original/A02110-999-06.png" style="width:auto">
			<img src="./original/A02110-999-07.png" style="width:auto">
		</div>
		<div class="head-character">
			<span style="color:#fff">氐</span>
			<img src="./original/A02110-999-05.png" style="width:auto">
			<img src="./original/A02110-999-03.png" style="width:auto">
			<img src="./original/A02110-999-04.png" style="width:auto">
			𢆰
		</div>
		An additional requirement that the character should be etymologically related to 氐 should be added, as characters with the shape of 
			<img src="./original/A02110-999-05.png" style="width:auto;height:20px;vertical-align:bottom">,
			<img src="./original/A02110-999-03.png" style="width:auto;height:20px;vertical-align:bottom">,
			<img src="./original/A02110-999-04.png" style="width:auto;height:20px;vertical-align:bottom">
			and 𢆰 may use 互 as its phonetic component instead of 氐.  Please also refer to the document  <i>IRGN2474 Feedback: Proposal to update IRG PnP for Non-cognate and UCV handling</i>.
	</div>
	<div class=p>
		<b>Item 5a</b><br>
		Unify the following shapes:
		<div class="head-character">
			㒼
			<img src="https://glyphwiki.org/glyph/u34bc-var-001.svg">
			<img src="https://glyphwiki.org/glyph/u34bc-ue0102.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552-var-002.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-010.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-008.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-011.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-012.svg">
			<img src="https://glyphwiki.org/glyph/u2ffb-u2ff1-cdp-85f0-u5dfe-cdp-89ae-var-002.svg">
		</div>
	</div>
	<div class=p>
		<b>Item 5b</b><br>
		Unify the following shapes:
		<div class="head-character">
			滿
			<img src="./.cache/8f9d0d3513e3279c0ef71cf409422e4d0531dcb1.png">
			<img src="./.cache/7a90fa287f7eecb486306c98e8bab6777c1f2116.png">
			<img src="./.cache/ffeb92cdca7e179693ed3ee9ba8da594bf543845.png">
			<img src="./.cache/e3febb2d974e2fb3e351c2489815b855eaa9d342.png">
		</div>
		<div class="head-character">
			<span style="color:#fff">滿</span>
			<img src="./.cache/bd5cb62659fe7e7eaebd2f0dfafab878e0dbe7d8.png">
			<img src="./.cache/bd1ca6ffe882c5f6d5e1d338c95860b6e32e2d9b.png">
			<img src="./.cache/748737a574053c169b85e6eff73cb4437fb40efb.png">
		</div>
		<div class="head-character">
			<span style="color:#fff">滿</span>
			<img src="./.cache/252f77a574979b807c11b490bacb5c1d4887bab6.png">
			<img src="./.cache/785724daff4c55144701374b6aca4c45c55a8bd9.png">
			<img src="./.cache/5d463ffb633dd9268186899e6fedfae9204f20e8.png">
			<img src="./.cache/fad4d353a91c6c0dbbed35bddb5d6d4029a86f6d.png">
			<img src="./.cache/b75e2291679867cab62eb10caf5027d48524ddd9.png">
			<img src="./.cache/d55bc38c603299cb4d6de2aede7616f29c6c03c4.png">
			<img src="./.cache/0544028a2cb1129fb060e0657d68261246027fb6.png">
		</div>
		<div class="head-character">
			<span style="color:#fff">滿</span>
			<img src="./.cache/32a816ae084dab6bac2af631220c1375424b816e.png">
			<img src="./.cache/e401d5c4205f27c2767bfde5c6e5874551b7cd0f.png">
			<img src="./.cache/ec1b09e7acd8631bb2ac84c5cb54e6af23b069a4.png">
			<img src="./.cache/182a90fb7fe755194d474d253d5b3c58da2e8265.png">
			<img src="./.cache/4e7fa1b67e9963d8b61cff7850b9c1c4ebb2c305.png">
			<img src="./.cache/3c1055053af152c78f61e9f9716d98a19053de0c.png">
		</div>
	</div>
	<div class=p>
		<b>Item 6</b><br>
		Unify the following shapes:
		<div class=head-character>
			詹
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u2201b.svg">
			<img src="https://glyphwiki.org/glyph/u269d5-var-001.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u5360.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u53e4.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a03830-005.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff3-u513f-u30006-u53e3.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u8a00.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u4e37-u2201b.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u8a79-itaiji-004.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u2201b.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u8a00.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u2201b.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u5360.svg">
		</div>
	</div>

	<div class=p>
		<b>Item 7</b><br>
		Unify the following shapes:
		<div class=head-character>
			<span style="font-family:PMingLiU">奐</span>
			<img src="https://glyphwiki.org/glyph/twedu-a00867-009.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-008.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-003.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-021.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u2008a-u7f52-u652f.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">奐</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ac.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ae.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u652f.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u7f52-u72ac.svg">
		</div>
	</div>

	<div class=p>
		<b>Item 8</b><br>
		Unify the following shapes:
		<div class=head-character>
			并
			幷
			<!--img src="https://glyphwiki.org/glyph/u303e4.svg">
			<img src="https://glyphwiki.org/glyph/u303e4-var-002.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a01193-006.svg">
			𢆙-->
			<img src="https://glyphwiki.org/glyph/u2ff1-u2008a-u5f00.svg">
		</div>
	</div>
	<div class=p>
		<b>Item 9a</b><br>
		Unify the following shapes:
		<div class=head-character>
			雚
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u5405-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u2ba4f-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2e97f.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">雚</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u53b8-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u53b8-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4ea0-u53b8-u96b9.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">雚</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u516b-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-cdp-85f0-u516b-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u516d-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e1a-u516b-u96b9.svg">
		</div>
	</div>
	<div class=p>
		<b>Item 9b</b><br>
		Unify the following shapes:
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u4e00-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u9fb7-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u4e1a-u96b9.svg">
		</div>
	</div>
</section>

<section id=rule-1>
	<div class=p>
		<b>Data for Item 1</b><br>
		<div class="head-character">鹿𢉖</div>
	</div>

	<p>Here is a list of variants which use the 𢉖 shape and have identical semantics with another glyph using the "鹿" shape. 4 out of 20 glyphs are coded, all in Extension B.</p>
	<ucv-category data-group="鹿" data-glyph="𢉖"></ucv-category>
</section>

<section>
	<div class=p>
		<b>Data for Item 2</b><br>
		<div class="head-character">辰𮝾<img src="https://glyphwiki.org/glyph/u2e77e-var-002.svg">𫝕</div>
	</div>

	<p>At least 100 characters containing the "辰" component were found in IDS.  Due to time constraints only 50 characters were checked.</p>

	<p>Here is a non-exhaustive list of variants which use the "𮝾" shape and have identical semantics with another glyph using the "辰" shape. 3 out of 52 characters are coded: 2 in Extension B and 1 in Extension F.</p>
	<ucv-category data-group="辰" data-glyph="𮝾"></ucv-category>

	<p>Here is a non-exhaustive list of variants which use the "<img src="https://glyphwiki.org/glyph/u2e77e-var-002.svg" height=16 style="vertical-align:middle">" shape and have identical semantics with another glyph using the "辰" shape.</p>
	<ucv-category data-group="辰" data-glyph="u2e77e-var-002"></ucv-category>

	<p>Here is a non-exhaustive list of variants which use the "𫝕" shape and have identical semantics with another glyph using the "辰" shape. 2 out of 22 characters are coded, 1 in Extension D while 2 in Extension F.</p>
	<ucv-category data-group="辰" data-glyph="𫝕"></ucv-category>
</section>

<section>
	<div class=p>
		<b>Data for Item 3</b><br>
		<div class="head-character">
			枼
			𭩠
			<img src="https://glyphwiki.org/glyph/twedu-c05082-002.svg">
			<img src="https://glyphwiki.org/glyph/u2da60-var-004.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-004.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-002.svg">
			<img src="https://glyphwiki.org/glyph/u67bc-itaiji-003.svg">
			<img src="https://glyphwiki.org/glyph/u233cb-itaiji-001.svg">
			𣏋
			𣐂
			枽
		</div>
	</div>

	<p>Here is a list of variants which use the 𭩠 or "<img src="https://glyphwiki.org/glyph/twedu-c05082-002.svg" height=20 style="vertical-align:middle">" shape and have identical semantics with another glyph using the "枼" shape. 1 glyph is encoded in Extension F.</p>
	<ucv-category data-group="枼" data-glyph="twedu-c05082-002"></ucv-category>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u2da60-var-004.svg" height=20 style="vertical-align:middle">" shape and have identical semantics with another glyph using the "枼" shape. No glyphs are encoded yet.</p>
	<ucv-category data-group="枼" data-glyph="u2da60-var-004"></ucv-category>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u67bc-itaiji-004.svg" height=20 style="vertical-align:middle">" or "<img src="https://glyphwiki.org/glyph/u67bc-itaiji-002.svg" height=20 style="vertical-align:middle">" shape and have identical semantics with another glyph using the "枼" shape. No glyphs are encoded yet.</p>
	<ucv-category data-group="枼" data-glyph="⿱龷木"></ucv-category>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u67bc-itaiji-003.svg" height=20 style="vertical-align:middle">" shape and have identical semantics with another glyph using the "枼" shape. 9 out of 15 glyphs are encoded, all are in Extension B. Many of these characters were written as "<img src="https://glyphwiki.org/glyph/u67bc-itaiji-002.svg" height=20 style="vertical-align:middle">" in the original source from 《龍龕手鑒》 but changed to <img src="https://glyphwiki.org/glyph/u67bc-itaiji-003.svg" height=20 style="vertical-align:middle"> in later texts.</p>
	<ucv-category data-group="枼" data-glyph="⿱廿木"></ucv-category>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u233cb-itaiji-001.svg">" shape and have identical semantics with another glyph using the "枼" shape.</p>
	<ucv-category data-group="枼" data-glyph="u233cb-itaiji-001"></ucv-category>

	<p>Here is a list of variants which use the "𣏋" shape and have identical semantics with another glyph using the "枼" shape. 23 out of 37 glyphs are encoded, all are in Extension B.</p>
	<ucv-category data-group="枼" data-glyph="𣏋"></ucv-category>

	<p>Here is a list of variants which use the "𣐂" shape and have identical semantics with another glyph using the "枼" shape. 1 out of 3 glyphs are encoded, all are in Extension B.</p>
	<ucv-category data-group="枼" data-glyph="𣐂"></ucv-category>

	<p>Here is a list of variants which use the "枽" shape and have identical semantics with another glyph using the "枼" shape. 2 out of 4 glyphs are encoded.</p>
	<ucv-category data-group="枼" data-glyph="枽"></ucv-category>

</section>

<section id="rule-3">
	<div class=p>
		<b>Data for Item 4</b><br>
		<div class="head-character">
			氐
			<img src="./original/A02110-004.png" style="width:auto">
			<img src="./original/A02110-999-02.png" style="width:auto">
			<img src="./original/A02110-999-01.png" style="width:auto">
			<img src="./original/A02110-999-08.png" style="width:auto">
			<img src="./original/A02110-999-06.png" style="width:auto">
			<img src="./original/A02110-999-07.png" style="width:auto">
		</div>
		<div class="head-character">
			氐
			<img src="./original/A02110-999-05.png" style="width:auto">
			<img src="./original/A02110-999-03.png" style="width:auto">
			<img src="./original/A02110-999-04.png" style="width:auto">
			𢆰
		</div>
	</div>

	<p>Here is a list of variants which use the "<img src="./original/A02110-004.png" height=20 style="vertical-align:bottom">" or "<img src="./original/A02110-999-02.png" height=20 style="vertical-align:bottom">" or "<img src="./original/A02110-999-01.png" height=20 style="vertical-align:bottom">" shape and have identical semantics with another glyph using the "氐" shape. These characters are mainly found from Clerical Script sources such as 《漢隸字源》 or 《隸辨》.</p>
	<ucv-category data-group="氐" data-glyph="u2ffb-u5df1-u571f" data-original="true"></ucv-category>

	<p>Here is a list of variants which use the "<img src="./original/A02110-999-08.png" height=20 style="vertical-align:bottom">" or "弖" shape and have identical semantics with another glyph using the "氐" shape.</p>
	<ucv-category data-group="氐" data-glyph="弖" data-original="true"></ucv-category>

	<p>Here is a list of variants which use the "<img src="./original/A02110-999-06.png" height=20 style="vertical-align:bottom">" or "<img src="./original/A02110-999-07.png" height=20 style="vertical-align:bottom">" shape and have identical semantics with another glyph using the "氐" shape.</p>
	<ucv-category data-group="氐" data-glyph="u6c10-itaiji-006" data-original=true></ucv-category>

	<p>Here is a list of variants which use the "<img src="./original/A02110-999-05.png" height=20 style="vertical-align:bottom">" or "<img src="./original/A02110-999-03.png" height=20 style="vertical-align:bottom">" shape and have identical semantics with another glyph using the "氐" shape.</p>
	<ucv-category data-group="氐" data-glyph="互" data-original=true></ucv-category>

	<p>Here is a list of variants which use the "<img src="./original/A02110-999-04.png" height=20 style="vertical-align:bottom">" shape and have identical semantics with another glyph using the "氐" shape. These forms are usually found in 《龍龕手鑒》 and 《直音篇》.  These forms can also be found in large quantity in stone rubbings.  Note some of the normalized Sungti forms by the MOE Dictionary seem to be inaccurate and may be better drawn as <img src="https://glyphwiki.org/glyph/koseki-000240.svg" height=20 style="vertical-align:bottom"> or <img src="https://glyphwiki.org/glyph/u221b0-07-itaiji-002.svg" height=20 style="vertical-align:bottom">.</p>
	<ucv-category data-group="氐" data-glyph="u221b0-07-itaiji-001" data-original=true></ucv-category>

	<p>Here is a list of variants which use the "𢆰" shape and have identical semantics with another glyph using the "氐" shape. These forms are usually found in 《龍龕手鑒》 and 《直音篇》, later also in the Kangxi Dictionary, which is why many of them are in Extension B. 24 of 31 glyphs are encoded: 1 in Extension A and 23 in Extension B.</p>
	<ucv-category data-group="氐" data-glyph="𢆰" data-original=true></ucv-category>
</section>

<section id="rule-4">
	<div class=p>
		<b>Data for Item 5a</b><br>
		<div class="head-character">
			㒼
			<img src="https://glyphwiki.org/glyph/u34bc-var-001.svg">
			<img src="https://glyphwiki.org/glyph/u34bc-ue0102.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552-var-002.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-010.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-008.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-011.svg">
			<img src="https://glyphwiki.org/glyph/u26cb8-var-012.svg">
			<img src="https://glyphwiki.org/glyph/u2ffb-u2ff1-cdp-85f0-u5dfe-cdp-89ae-var-002.svg">
		</div>
	</div>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u34bc-var-001.svg">" or "<img src="https://glyphwiki.org/glyph/u34bc-ue0102.svg">" shape and have identical semantics with another glyph using the "㒼" shape. No glyphs has been coded.</p>
	<ucv-category data-group="㒼" data-glyph="u34bc-var-001"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u34bc-ue0102"></ucv-category>
	<p>
		Note: <img src="https://glyphwiki.org/glyph/u34bc-ue0102.svg" style="height:40px"> has been coded in the Ideographic Variation Database with a sequence of &lt;U+34BC U+E0102&gt; in the Mojijoho IVD collection but is not present in the MOE Dictionary.
	</p>
	<p>
		Note: <img src="https://glyphwiki.org/glyph/u6eff-ue0104.svg" style="height:40px"> has been coded in the Ideographic Variation Database with a sequence of &lt;U+6EFF U+E0104&gt; in the Mojijoho IVD collection but is not present in the MOE Dictionary.
	</p>

	<hr>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552-var-002.svg">" or "<img src="https://glyphwiki.org/glyph/u2ff1-u5eff-u20552.svg">" or "<img src="https://glyphwiki.org/glyph/u26cb8-var-010.svg">" or "<img src="https://glyphwiki.org/glyph/u26cb8-var-008.svg">" or "<img src="https://glyphwiki.org/glyph/u26cb8-var-011.svg">" shape and have identical semantics with another glyph using the "㒼" shape. 2 out of 24 glyphs are coded, all are in Extension B.</p>
	<ucv-category data-group="㒼" data-glyph="u2ff1-u5eff-u20552-var-002"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u2ff1-u5eff-u20552"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u26cb8-var-010"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u26cb8-var-008"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u26cb8-var-011"></ucv-category>

	<p>Here is a list of variants which use the "<img src="https://glyphwiki.org/glyph/u26cb8-var-012.svg">" or "<img src="https://glyphwiki.org/glyph/u2ffb-u2ff1-cdp-85f0-u5dfe-cdp-89ae-var-002.svg">" shape and have identical semantics with another glyph using the "㒼" shape. No glyphs are coded.</p>
	<ucv-category data-group="㒼" data-glyph="u26cb8-var-012"></ucv-category>
	<ucv-category data-group="㒼" data-glyph="u2ffb-u2ff1-cdp-85f0-u5dfe-cdp-89ae-var-002"></ucv-category>

	<hr>

	<div class=p>
		<b>Data for Item 5b</b><br>
		<div class="head-character">
			滿
			<img src="./.cache/8f9d0d3513e3279c0ef71cf409422e4d0531dcb1.png">
			<img src="./.cache/7a90fa287f7eecb486306c98e8bab6777c1f2116.png">
			<img src="./.cache/ffeb92cdca7e179693ed3ee9ba8da594bf543845.png">
			<img src="./.cache/e3febb2d974e2fb3e351c2489815b855eaa9d342.png">
			<img src="./.cache/252f77a574979b807c11b490bacb5c1d4887bab6.png">
			<img src="./.cache/bd5cb62659fe7e7eaebd2f0dfafab878e0dbe7d8.png">
			<img src="./.cache/bd1ca6ffe882c5f6d5e1d338c95860b6e32e2d9b.png">
			<img src="./.cache/748737a574053c169b85e6eff73cb4437fb40efb.png">
			<img src="./.cache/785724daff4c55144701374b6aca4c45c55a8bd9.png">
			<img src="./.cache/5d463ffb633dd9268186899e6fedfae9204f20e8.png">
			<img src="./.cache/fad4d353a91c6c0dbbed35bddb5d6d4029a86f6d.png">
			<img src="./.cache/b75e2291679867cab62eb10caf5027d48524ddd9.png">
			<img src="./.cache/d55bc38c603299cb4d6de2aede7616f29c6c03c4.png">
			<img src="./.cache/0544028a2cb1129fb060e0657d68261246027fb6.png">
			<img src="./.cache/32a816ae084dab6bac2af631220c1375424b816e.png">
			<img src="./.cache/e401d5c4205f27c2767bfde5c6e5874551b7cd0f.png">
			<img src="./.cache/ec1b09e7acd8631bb2ac84c5cb54e6af23b069a4.png">
			<img src="./.cache/182a90fb7fe755194d474d253d5b3c58da2e8265.png">
			<img src="./.cache/4e7fa1b67e9963d8b61cff7850b9c1c4ebb2c305.png">
			<img src="./.cache/3c1055053af152c78f61e9f9716d98a19053de0c.png">
		</div>
	</div>

	<p>Here are an additional list of 20 miscellaneous unencoded variants of 滿.</p>
	<ucv-category data-group="滿" data-glyph="Misc 1,Misc 2,Misc 3,Misc 4"></ucv-category>
</section>

<section id="rule-6">

	<div class=p>
		<b>Data for Item 6</b><br>
		<div class=head-character>
			詹
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u2201b.svg">
			<img src="https://glyphwiki.org/glyph/u269d5-var-001.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u5360.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u53e4.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a03830-005.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff3-u513f-u30006-u53e3.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u8a00.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u4e37-u2201b.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u8a79-itaiji-004.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u2201b.svg">
		</div>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u8a00.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u20bb7.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u2201b.svg">
			<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u5360.svg">
		</div>
	</div>

	<p>
		Here is a list of variants with the  
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u20bb7.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u2201b.svg"> or
		<img src="https://glyphwiki.org/glyph/u269d5-var-001.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u5360.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u513f-u53e4.svg"> or
		<img src="https://glyphwiki.org/glyph/twedu-a03830-005.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff3-u513f-u30006-u53e3.svg"> shape and have identical semantics with another glyph using the "詹" shape.  The first three of these shapes were discussed in previous IRG meetings and unified but they has not been added to the UCV. 5 out of 42 glyphs have been coded, all are in Extension B.
	</p>

	<ucv-category data-group="詹" data-glyph="⿸厃⿳八土口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳八工口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳八干口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳八占"></ucv-category>
	<ucv-category data-group="詹" data-glyph="twedu-a03830-005"></ucv-category>
	<ucv-category data-group="詹" data-glyph="u2ff8-u5383-u2ff3-u513f-u30006-u53e3"></ucv-category>

	<hr>

	<p>
		Here is a list of variants with the  
		<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u8a00.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u516b-u20bb7.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u2ff1-u4e37-u5382-u2ff1-u4e37-u2201b.svg"> shape and have identical semantics with another glyph using the "詹" shape.  They are identical with first suggested set but only the top part is different. No glyphs have been coded.
	</p>

	<ucv-category data-group="詹" data-glyph="⿱丷⿸厂⿱八言"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿱丷⿸厂⿳八土口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿱丷⿸厂⿳八工口"></ucv-category>

	<hr>

	<p>
		Here is a list of variants with the  
		<img src="https://glyphwiki.org/glyph/u8a79-itaiji-004.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u20bb7.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u6bd4-u2201b.svg"> shape and have identical semantics with another glyph using the "詹" shape.  They are identical with the first suggested set but only the middle part has been swapped out with 比. 4 out of 11 glyphs have been coded, all are in Extension B.
	</p>

	<ucv-category data-group="詹" data-glyph="⿸厃⿳比言"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳比土口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳比工口"></ucv-category>

	<hr>

	<p>
		Here is a list of variants with the  
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u8a00.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u20bb7.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u2201b.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff8-u5383-u2ff1-u5317-u5360.svg"> shape and have identical semantics with another glyph using the "詹" shape.  They are identical with the first suggested set but only the middle part has been swapped out with 北. No glyphs have been coded.
	</p>

	<ucv-category data-group="詹" data-glyph="⿸厃⿱北言"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳北土口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳北工口"></ucv-category>
	<ucv-category data-group="詹" data-glyph="⿸厃⿳北占"></ucv-category>
</section>

<section id=rule-7>
	<div class=p>
		<b>Data for Item 7</b><br>
		<div class=head-character>
			<span style="font-family:PMingLiU">奐</span>
			<img src="https://glyphwiki.org/glyph/twedu-a00867-009.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-008.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-003.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a00867-021.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u2008a-u7f52-u652f.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">奐</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ac.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ae.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u652f.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u7f52-u72ac.svg">
		</div>

		<p>
			Here is a list of variants with the  
			<img src="https://glyphwiki.org/glyph/twedu-a00867-009.svg"> or
			<img src="https://glyphwiki.org/glyph/twedu-a00867-008.svg"> or
			<img src="https://glyphwiki.org/glyph/twedu-a00867-003.svg"> or
			<img src="https://glyphwiki.org/glyph/twedu-a00867-021.svg"> or
			<img src="https://glyphwiki.org/glyph/u2ff3-u2008a-u7f52-u652f.svg"> shape and have identical semantics with another glyph using the "奐" shape. 1 out of 26 glyphs has been coded, it is in Extension B. Some glyphs are WS2017 candidates.
		</p>
		<p>
			Note: According to UCV #401, 奂奐烉 are already unifiable.</p>

		<ucv-category data-group="奐" data-glyph="twedu-a00867-009"></ucv-category>
		<ucv-category data-group="奐" data-glyph="twedu-a00867-008"></ucv-category>
		<ucv-category data-group="奐" data-glyph="twedu-a00867-003"></ucv-category>
		<ucv-category data-group="奐" data-glyph="twedu-a00867-021"></ucv-category>
		<ucv-category data-group="奐" data-glyph="u2ff3-u2008a-u7f52-u652f"></ucv-category>

		<hr>

		<p>
			Here is a list of variants with the  
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ac.svg"> or
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u72ae.svg"> or
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e37-u7f52-u652f.svg"> or
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u7f52-u72ac.svg"> shape and have identical semantics with another glyph using the "奐" shape.  No glyphs have been coded. Some glyphs are WS2017 candidates. A02429-010 (<img src=".cache/1febaf4b4966039e9e7493d30b82a16b8920ba8f.png">) was unified to A02429-022 (<img src=".cache/dd25b974b98a53ce2742430a398a00ce3121c345.png">) in WS2017.
		</p>

		<ucv-category data-group="奐" data-glyph="u2ff3-u4e37-u7f52-u72ac"></ucv-category>
		<ucv-category data-group="奐" data-glyph="u2ff3-u4e37-u7f52-u72ae"></ucv-category>
		<ucv-category data-group="奐" data-glyph="u2ff3-u4e37-u7f52-u652f"></ucv-category>
		<ucv-category data-group="奐" data-glyph="u2ff3-u4491-u7f52-u72ac"></ucv-category>
	</div>
</section>

<section id=rule-8>
	<div class=p>
		<b>Data for Item 8</b><br>
		<div class=head-character>
			并
			幷
			<!--img src="https://glyphwiki.org/glyph/u303e4.svg">
			<img src="https://glyphwiki.org/glyph/u303e4-var-002.svg">
			<img src="https://glyphwiki.org/glyph/twedu-a01193-006.svg">
			𢆙-->
			<img src="https://glyphwiki.org/glyph/u2ff1-u2008a-u5f00.svg">
		</div>
	</div>

	<p>
		Here is a list of variants with the shapes 
		<img src="https://glyphwiki.org/glyph/u2ff1-u2008a-u5f00.svg"> and have identical semantics with another glyph using the "并"/"幷" shape.  No glyphs have been coded.
	</p>
	
	<!--ucv-category data-group="并" data-glyph="u303e4"></ucv-category>
	<ucv-category data-group="并" data-glyph="u303e4-var-002"></ucv-category>
	<ucv-category data-group="并" data-glyph="twedu-a01193-006"></ucv-category>
	<ucv-category data-group="并" data-glyph="𢆙"></ucv-category-->
	<ucv-category data-group="并" data-glyph="u2ff1-u2008a-u5f00"></ucv-category>
</section>

<section>
	<div class=p>
		<b>Data for Item 9a</b><br>
		<div class=head-character>
			雚
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u5405-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u2ba4f-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2e97f.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">雚</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u53b8-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u53b8-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4ea0-u53b8-u96b9.svg">
		</div>
		<div class=head-character>
			<span style="color:#fff">雚</span>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u516b-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-cdp-85f0-u516b-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u516d-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff3-u4e1a-u516b-u96b9.svg">
		</div>
	</div>
	<p>
		Here is a list of variants with the 
		<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u5405-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u2ba4f-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2e97f.svg">
		shape which are semantically identical with characters using the 雚 shape. 2 out of 23 characters are coded: 1 in Extension B and 1 in Extension F.
	</p>
	<ucv-category data-group="雚" data-glyph="u2ff3-u4491-u5405-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2ff3-u4491-u2ba4f-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2e97f"></ucv-category>
	<hr>
	<p>
		Here is a list of variants with the 
		<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u53b8-u96b9.svg"> or 
		<img src="https://glyphwiki.org/glyph/u2ff3-u4491-u53b8-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff3-u4ea0-u53b8-u96b9.svg"> shape. No variants are coded.
	</p>
	<ucv-category data-group="雚" data-glyph="u2ff3-u4491-u53b8-u96b9"></ucv-category>
	<hr>
	<p>
		Here is a list of variants with the 
		<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u516b-u96b9.svg"> or 
		<img src="https://glyphwiki.org/glyph/u2ff3-cdp-85f0-u516b-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff1-u516d-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff3-u4e1a-u516b-u96b9.svg">
		shape. No variants are coded.
	</p>
	<ucv-category data-group="雚" data-glyph="u2ff3-u535d-u516b-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2ff3-cdp-85f0-u516b-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2ff1-u516d-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2ff3-u4e1a-u516b-u96b9"></ucv-category>
	<hr>
	<div class=p>
		<b>Data for Item 9b</b><br>
		<div class=head-character>
			<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u4e00-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u9fb7-u96b9.svg">
			<img src="https://glyphwiki.org/glyph/u2ff1-u4e1a-u96b9.svg">
		</div>
	</div>
	<p>
		Here is a list of variants with the 
		<img src="https://glyphwiki.org/glyph/u2ff3-u535d-u4e00-u96b9.svg"> or 
		<img src="https://glyphwiki.org/glyph/u2ff1-u9fb7-u96b9.svg"> or
		<img src="https://glyphwiki.org/glyph/u2ff1-u4e1a-u96b9.svg">
		shape. 6 out of 22 variants are coded: 1 in Extension D, 4 are in Extension F, 1 in Extension G.
	</p>
	<ucv-category data-group="雚" data-glyph="u2ff3-u535d-u4e00-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="u2ff1-u9fb7-u96b9"></ucv-category>
	<ucv-category data-group="雚" data-glyph="⿱业隹"></ucv-category>
</section>