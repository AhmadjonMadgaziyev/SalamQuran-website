<?php
namespace lib\app;


class quran_word
{
	public static $find_by        = null;
	public static $load_translate = false;
	public static $translate      = [];


	public static function find($_url, $_meta = [])
	{
		$default_meta =
		[
			'translate' => null,
		];

		if(!is_array($_meta))
		{
			$_meta = [];
		}

		$_meta = array_merge($default_meta, $_meta);

		if(strpos($_url, '/') === false)
		{

			$first_character = mb_strtolower(substr($_url, 0, 1));
			$number          = substr($_url, 1);

			if($first_character === 's' && ctype_digit($number))
			{
				return self::sure($number, null, $_meta);
			}
			elseif($first_character === 'j' && ctype_digit($number))
			{
				return self::juz($number, $_meta);
			}
			elseif($first_character === 'p' && ctype_digit($number))
			{
				return self::page($number, $_meta);
			}
			elseif($first_character === 'a' && ctype_digit($number))
			{
				return self::aye($number, $_meta);
			}
			elseif($first_character === 'h' && ctype_digit($number))
			{
				return self::hezb($number, $_meta);
			}
		}
		else
		{
			$split           = explode('/', $_url);
			$first_character = null;
			$number          = 0;

			if(isset($split[0]) && isset($split[1]))
			{
				$first_character = mb_strtolower(substr($split[0], 0, 1));
				$number          = substr($split[0], 1);
				$number2         = $split[1];
				if($first_character === 's' && ctype_digit($number) && ctype_digit($number2))
				{
					return self::sure($number, $number2, $_meta);
				}

			}
		}

		return false;
	}


	private static function make($_type = null, $_page_number = null)
	{

		$page   = $_page_number;
		$text   = $_page_number;
		$title  = null;
		$class  = null;
		$link   = true;

		switch ($_type)
		{
			case 'first':
				$class  = 'first';
				break;

			case 'spliter':
				$link   = false;
				$page   = null;
				$text   = '...';
				$class  = 'spliter';
				break;

			case 'end':
				$class  = 'end';
				break;

			case 'current':
				$link   = false;
				$class  = 'active';
				break;

			case 'next':
				if(\dash\language::dir() === 'ltr')
				{
					$text = '<span class="sf-chevron-right"></span>';
				}
				else
				{
					$text = '<span class="sf-chevron-left"></span>';
				}
				$class  = 'next s0';
				break;

			case 'prev':
				if(\dash\language::dir() === 'ltr')
				{
					$text = '<span class="sf-chevron-left"></span>';
				}
				else
				{
					$text = '<span class="sf-chevron-right"></span>';
				}
				$class  = 'prev s0';
				break;

		}

		$result =
		[
			'page'   => $page,
			'link'	 => $link,
			'text'   => $text ? \dash\utility\human::fitNumber($text) : null,
			'title'  => $title,
			'class'  => $class,
		];
		return $result;
	}

	private static function sura_pagination($_id, $_meta)
	{
		$startpage = intval(\lib\app\sura::detail($_id, 'startpage'));
		$endpage   = intval(\lib\app\sura::detail($_id, 'endpage'));

		$p         = isset($_meta['p']) && is_numeric($_meta['p']) ? intval($_meta['p']) : 0;

		// $sura_pagination = [];

		// $get = \dash\request::get();
		// unset($get['p']);
		// for ($page = $startpage; $page <= $endpage ; $page++)
		// {
		// 	$get['p'] = $page;
		// 	$link = \dash\url::that(). '?'. http_build_query($get);
		// 	$result =
		// 	[
		// 		'page'   => $page,
		// 		'link'	 => $link,
		// 		'text'   => $page ? \dash\utility\human::fitNumber($page) : null,
		// 		'title'  => null,
		// 		'class'  => null,
		// 	];
		// 	$sura_pagination[] = $result;
		// }

		// return $sura_pagination;


		$current    = $p ? $p : $startpage;
		$first      = $startpage;
		$count_link = 5;
		$total_page = $endpage - $startpage;

		$result = [];

		if($total_page <= 1)
		{
			// no pagination needed
		}
		elseif($total_page === 2)
		{
			if($current === $startpage)
			{
				$result[] = self::make('current', $current);
				$result[] = self::make(null, $endpage);
			}
			elseif ($current === $endpage)
			{
				$result[] = self::make(null, $startpage);
				$result[] = self::make('current', $current);
			}
		}
		else
		{
			$count_link_fill = 0;
			$sb              = [];
			$sa              = [];
			$i               = 0;
			$pages           = [];

			while ($count_link_fill < $count_link)
			{
				$i++;

				if($i > $count_link)
				{
					break;
				}

				if($current - $i + $startpage > 0)
				{
					if($current - $i +$startpage !== $current)
					{
						array_push($pages, $current - $i + $startpage);
						array_push($sb, $current - $i + $startpage);
					}
					$count_link_fill++;
				}

				if($count_link_fill < $count_link)
				{
					if($current + $i <= $total_page)
					{
						array_push($pages, $current + $i);
						array_push($sa, $current + $i );
						$count_link_fill++;
					}
				}
			}

			asort($pages);

			$sb = array_reverse($sb);

			if($current !== $startpage)
			{
				$result[] = self::make('prev', $current -$startpage);
			}

			if(current($pages) - $startpage == $startpage)
			{
				if(in_array($startpage, $pages) || $current === $startpage)
				{
					// needless to make first page
				}
				else
				{
					$result[] = self::make(null, $startpage);
				}
			}
			elseif(current($pages) - $startpage >= 2)
			{
				$result[] = self::make('first', $startpage);
				$result[] = self::make('spliter');
			}

			foreach ($sb as $key => $value)
			{
				$result[] = self::make(null, $value);
			}

			$result[] = self::make('current', $current);

			foreach ($sa as $key => $value)
			{
				$result[] = self::make(null, $value);
			}

			if(end($pages) + $startpage <= $total_page)
			{
				if(in_array($total_page, $pages) || $current === $total_page)
				{
					// needless to make end page
				}
				else
				{
					if(end($pages) + $startpage < $total_page)
					{
						$result[] = self::make('spliter');
					}

					$result[] = self::make('end', $total_page);
				}
			}

			if($current !== $total_page)
			{
				$result[] = self::make('next', $current + 1);
			}


		}

		$this_link = \dash\url::current();
		$get       = \dash\request::get();
		unset($get['p']);

		foreach ($result as $key => $value)
		{
			if(isset($value['link']) && $value['link'])
			{
				$temp_get             = $get;
				$temp_get['p']     = $value['page'];
				$temp_link            = $this_link . '?'. http_build_query($temp_get);
				$result[$key]['link'] = $temp_link;
			}
			// $result[$key]['total_rows'] = self::detail('total_rows');
		}

		return $result;
	}


	private static function sure($_id, $_aye = null, $_meta = [])
	{
		// load sure
		$_id = intval($_id);
		$result           = [];

		if(intval($_id) < 1 && intval($_id) > 114)
		{
			return false;
		}

		$get_quran         = [];
		$get_quran['sura'] = $_id;

		if($_aye)
		{
			$get_quran['aya'] = $_aye;
		}

		$mode              = null;

		if(isset($_meta['mode']))
		{
			$mode = $_meta['mode'];
		}

		if(!in_array($mode, ['quran', 'default']))
		{
			$mode = null;
		}

		$startpage = intval(\lib\app\sura::detail($_id, 'startpage'));
		$endpage   = intval(\lib\app\sura::detail($_id, 'endpage'));

		$p         = isset($_meta['p']) && is_numeric($_meta['p']) ? intval($_meta['p']) : 0;

		if($mode === 'quran')
		{

			unset($get_quran['sura']);
			if($startpage != $endpage)
			{
				$startpagePlus = $startpage + 1;
				$get_quran['1.1'] = ['= 1.1 AND', " `page` IN ($startpage, $startpagePlus)"];
			}
			else
			{
				$get_quran['page'] = $startpage;
			}

			$load           = \lib\db\quran_word::get($get_quran);
			$load_quran_aya = \lib\db\quran::get($get_quran);
		}
		else
		{
			if($p >= $startpage && $p <= $endpage)
			{
				// nothing
			}
			else
			{
				$p = $startpage;
			}

			$get_quran['page'] = $p;

			$load           = \lib\db\quran_word::get($get_quran);
			$load_quran_aya = \lib\db\quran::get($get_quran);
		}
		$quran_aya      = [];

		foreach ($load_quran_aya as $key => $value)
		{
			$quran_aya[$value['sura']. '_'. $value['aya']] = $value;
		}


		self::load_translate($load, $_meta);

		$quran             = [];

		$first_verse_title = null;

		foreach ($load as $key => $value)
		{
			if($mode === 'quran')
			{
				$myKey      = 'line';
				$myArrayKey = $value['sura']. '_'. $value['line'];
			}
			else
			{
				$myKey      = 'aya';
				$myArrayKey = $value['sura']. '_'. $value['aya'];
			}

			if(!isset($quran[$myKey][$myArrayKey]['detail']))
			{
				$quran_aya_key = $value['sura']. '_'. $value['aya'];

				$verse_title = null;
				$verse_title .= T_("Quran");
				$verse_title .= ' - ';
				$verse_title .= T_("Sura");
				$verse_title .= ' ';
				$verse_title .= \dash\utility\human::fitNumber($value['sura']). ' '. T_(\lib\app\sura::detail($value['sura'], 'tname'));
				$verse_title .= ' - ';
				$verse_title .= T_("Aya");
				$verse_title .= ' ';
				$verse_title .= \dash\utility\human::fitNumber($value['aya']);

				$verse_url = \dash\url::kingdom();
				$verse_url .= '/s'. $value['sura'];
				$verse_url .= '/'. $value['aya'];

				if(!$first_verse_title)
				{
					$first_verse_title = $verse_title;
				}

				$quran[$myKey][$myArrayKey]['detail'] =
				[
					'index'         => isset($quran_aya[$quran_aya_key]['index']) ? $quran_aya[$quran_aya_key]['index'] : null,
					'text'          => isset($quran_aya[$quran_aya_key]['text']) ? self::normalize($quran_aya[$quran_aya_key]['text']) : null,
					'simple'        => isset($quran_aya[$quran_aya_key]['simple']) ? $quran_aya[$quran_aya_key]['simple'] : null,
					'juz'           => isset($quran_aya[$quran_aya_key]['juz']) ? $quran_aya[$quran_aya_key]['juz'] : null,
					'hizb'          => isset($quran_aya[$quran_aya_key]['hizb']) ? $quran_aya[$quran_aya_key]['hizb'] : null,
					'word'          => isset($quran_aya[$quran_aya_key]['word']) ? $quran_aya[$quran_aya_key]['word'] : null,
					'sajdah'        => isset($quran_aya[$quran_aya_key]['sajdah']) ? $quran_aya[$quran_aya_key]['sajdah'] : null,
					'sajdah_number' => isset($quran_aya[$quran_aya_key]['sajdah_number']) ? $quran_aya[$quran_aya_key]['sajdah_number'] : null,
					'rub'           => isset($quran_aya[$quran_aya_key]['rub']) ? $quran_aya[$quran_aya_key]['rub'] : null,
					'word'          => isset($quran_aya[$quran_aya_key]['word']) ? $quran_aya[$quran_aya_key]['word'] : null,
					'aya'           => $value['aya'],
					'sura'          => $value['sura'],
					'verse_key'     => $value['verse_key'],
					'verse_title'   => $verse_title,
					'verse_url'     => $verse_url,
					'page'          => $value['page'],
					'audio'         => self::get_aya_audio($value['sura'], $value['aya'], $_meta),
					'translate'     => self::get_translation($value['sura'], $value['aya'], $_meta),
				];
			}

			if(!isset($quran[$myKey][$myArrayKey]['word']))
			{
				$quran[$myKey][$myArrayKey]['word'] = [];
			}
			if(isset($value['audio']))
			{
				$my_sura = intval($value['sura']);

				if($my_sura < 10)
				{
					$my_sura = '00'. $my_sura;
				}
				elseif($my_sura < 100)
				{
					$my_sura = '0'. $my_sura;
				}

				$value['audio'] = $my_sura. $value['audio'];
			}

			if(isset($value['text']))
			{
				$value['text'] = self::normalize($value['text']);
			}

			$quran[$myKey][$myArrayKey]['word'][] = $value;
		}

		$result['text']    = $quran;

		$result['mode_quran'] = false;

		$next_sura = intval($_id) + 1;
		$prev_sura = intval($_id) - 1;

		if($next_sura > 114)
		{
			$next_sura = null;
		}

		if($prev_sura < 1)
		{
			$prev_sura = null;
		}

		$sura_detail = \lib\app\sura::detail($_id);

		if($next_sura)
		{
			$sura_detail['next_sura'] =
			[
				'index' => $next_sura,
				'url'   => \dash\url::kingdom(). '/s'. $next_sura,
				'title' => T_(\lib\app\sura::detail($next_sura, 'tname')),
			];
		}

		if($prev_sura)
		{
			$sura_detail['prev_sura'] =
			[
				'index' => $prev_sura,
				'url'   => \dash\url::kingdom(). '/s'. $prev_sura,
				'title' => T_(\lib\app\sura::detail($prev_sura, 'tname')),
			];
		}

		$sura_detail['first_title'] = $first_verse_title;
		$result['detail']           = $sura_detail;
		$result['sura_pagination'] = self::sura_pagination($_id, $_meta);
		// \dash\notif::api($result);

		self::$find_by    = 'sure';
		return $result;

	}

	private static function juz($_id, $_meta = [])
	{
		// load sure
		$_id = intval($_id);
		$result           = [];

		if(intval($_id) < 1 && intval($_id) > 30)
		{
			return false;
		}

		$get_quran         = [];
		$get_quran['juz'] = $_id;


		$load           = \lib\db\quran_word::get($get_quran);
		$load_quran_aya = \lib\db\quran::get($get_quran);
		$quran_aya      = [];

		foreach ($load_quran_aya as $key => $value)
		{
			$quran_aya[$value['sura']. '_'. $value['aya']] = $value;
		}

		self::load_translate($load, $_meta);

		$quran = [];

		$first_verse_title = null;

		foreach ($load as $key => $value)
		{
			if(!isset($quran['aya'][$value['sura'] . '_'. $value['aya']]['detail']))
			{
				$quran_aya_key = $value['sura']. '_'. $value['aya'];

				$verse_title = null;
				$verse_title .= T_("Quran");
				$verse_title .= ' - ';
				$verse_title .= T_("Sura");
				$verse_title .= ' ';
				$verse_title .= \dash\utility\human::fitNumber($value['sura']). ' '. T_(\lib\app\sura::detail($value['sura'], 'tname'));
				$verse_title .= ' - ';
				$verse_title .= T_("Aya");
				$verse_title .= ' ';
				$verse_title .= \dash\utility\human::fitNumber($value['aya']);

				$verse_url = \dash\url::kingdom();
				$verse_url .= '/s'. $value['sura'];
				$verse_url .= '/'. $value['aya'];

				if(!$first_verse_title)
				{
					$first_verse_title = $verse_title;
				}

				$quran['aya'][$value['sura'] . '_'. $value['aya']]['detail'] =
				[
					'index'         => isset($quran_aya[$quran_aya_key]['index']) ? $quran_aya[$quran_aya_key]['index'] : null,
					'text'          => isset($quran_aya[$quran_aya_key]['text']) ? self::normalize($quran_aya[$quran_aya_key]['text']) : null,
					'simple'        => isset($quran_aya[$quran_aya_key]['simple']) ? $quran_aya[$quran_aya_key]['simple'] : null,
					'juz'           => isset($quran_aya[$quran_aya_key]['juz']) ? $quran_aya[$quran_aya_key]['juz'] : null,
					'hizb'          => isset($quran_aya[$quran_aya_key]['hizb']) ? $quran_aya[$quran_aya_key]['hizb'] : null,
					'word'          => isset($quran_aya[$quran_aya_key]['word']) ? $quran_aya[$quran_aya_key]['word'] : null,
					'sajdah'        => isset($quran_aya[$quran_aya_key]['sajdah']) ? $quran_aya[$quran_aya_key]['sajdah'] : null,
					'sajdah_number' => isset($quran_aya[$quran_aya_key]['sajdah_number']) ? $quran_aya[$quran_aya_key]['sajdah_number'] : null,
					'rub'           => isset($quran_aya[$quran_aya_key]['rub']) ? $quran_aya[$quran_aya_key]['rub'] : null,
					'word'          => isset($quran_aya[$quran_aya_key]['word']) ? $quran_aya[$quran_aya_key]['word'] : null,
					'aya'           => $value['aya'],
					'sura'          => $value['sura'],
					'verse_key'     => $value['verse_key'],
					'verse_title'   => $verse_title,
					'verse_url'     => $verse_url,
					'page'          => $value['page'],
					'audio'         => self::get_aya_audio($value['sura'], $value['aya'], $_meta),
					'translate'     => self::get_translation($value['sura'], $value['aya'], $_meta),
				];
			}

			if(!isset($quran['aya'][$value['sura'] . '_'. $value['aya']]['word']))
			{
				$quran['aya'][$value['sura'] . '_'. $value['aya']]['word'] = [];
			}
			if(isset($value['audio']))
			{
				$my_sura = intval($value['sura']);

				if($my_sura < 10)
				{
					$my_sura = '00'. $my_sura;
				}
				elseif($my_sura < 100)
				{
					$my_sura = '0'. $my_sura;
				}

				$value['audio'] = $my_sura. $value['audio'];
			}

			if(isset($value['text']))
			{
				$value['text'] = self::normalize($value['text']);
			}

			$quran['aya'][$value['sura'] . '_'. $value['aya']]['word'][] = $value;
		}

		$result['text']    = $quran;


		$next_juz = intval($_id) + 1;
		$prev_juz = intval($_id) - 1;

		if($next_juz > 30)
		{
			$next_juz = null;
		}

		if($prev_juz < 1)
		{
			$prev_juz = null;
		}

		$juz_detail = [];

		if($next_juz)
		{
			$juz_detail['next_juz'] =
			[
				'index' => $next_juz,
				'url'   => \dash\url::kingdom(). '/j'. $next_juz,
				'title' => T_('juz') . ' '. \dash\utility\human::fitNumber($next_juz),
			];
		}

		if($prev_juz)
		{
			$juz_detail['prev_juz'] =
			[
				'index' => $prev_juz,
				'url'   => \dash\url::kingdom(). '/j'. $prev_juz,
				'title' => T_('juz') . ' '. \dash\utility\human::fitNumber($prev_juz),
			];
		}

		$sura_detail['first_title'] = $first_verse_title;
		$result['detail']           = $juz_detail;

		// \dash\notif::api($result);

		self::$find_by    = 'sure';
		return $result;

	}



	private static function get_aya_audio($_sura, $_aya, $_meta = [])
	{
		if(!isset($_meta['qari']))
		{
			$_meta['qari'] = 1;
		}

		if(!ctype_digit($_meta['qari']))
		{
			$_meta['qari'] = 1;
		}

		$get_url = \lib\app\qari::get_aya_url($_meta['qari'], $_sura, $_aya);
		return $get_url;


	}

	public static function normalize($_text)
	{
		if(is_callable(['Normalizer', 'normalize']))
		{
			return \Normalizer::normalize($_text);
		}
		return $_text;

	}



	private static function aye($_id, $_meta = [])
	{
		// load aye
		$_id = intval($_id);
		if(intval($_id) >= 1 && intval($_id) <= 6236 )
		{
			$load             = \lib\db\quran_word::get(['index' => $_id]);
			$result           = [];
			$result['aye']    = $load;

			if(isset($load[0]['sura']))
			{
				$result['detail'] = \lib\db\sura::get(['index' => $load[0]['sura'], 'limit' => 1]);
			}

			$result['translate'] = self::load_translate($load, $_meta);

			self::$find_by = 'aye';

			return $result;
		}
		else
		{
			return false;
		}
	}


	private static function page($_id, $_meta = [])
	{
		// load page
		$_id = intval($_id);
		if(intval($_id) >= 1 && intval($_id) <= 604 )
		{
			$load             = \lib\db\quran_word::get(['page' => $_id]);
			$result           = [];
			$result['aye']    = $load;

			if(isset($load[0]['sura']))
			{
				$result['detail'] = \lib\db\sura::get(['index' => $load[0]['sura'], 'limit' => 1]);
			}

			$result['translate'] = self::load_translate($load, $_meta);

			self::$find_by = 'page';

			return $result;
		}
		else
		{
			return false;
		}
	}



	private static function hezb($_id, $_meta = [])
	{
		// load hezb
		$_id = intval($_id);
		if(intval($_id) >= 1 && intval($_id) <= 120 )
		{
			$load                = \lib\db\quran_word::get(['hezb' => $_id]);
			$result              = [];
			$result['aye']       = $load;
			$result['translate'] = self::load_translate($load, $_meta);
			self::$find_by       = 'hezb';

			return $result;
		}
		else
		{
			return false;
		}
	}


	private static function load_translate($_data, $_meta)
	{
		if(!is_array($_data))
		{
			return null;
		}

		if(!is_array($_meta))
		{
			return null;
		}

		if(!isset($_meta['translate']))
		{
			return null;
		}

		if(!$_meta['translate'])
		{
			return null;
		}

		$get = explode('-', $_meta['translate']);
		$result = [];
		$i         = 0;

		foreach ($get as $key => $value)
		{
			$translate = \lib\app\translate::table_name($value);

			if(!isset($translate['table_name']))
			{
				continue;
			}

			$i++;
			if($i > 20)
			{
				\dash\notif::warn(T_("Only 20 translate can be show"));
				break;
			}
			$sura = array_column($_data, 'sura');
			$sura = array_filter($sura);
			$sura = array_unique($sura);

			$aya = array_column($_data, 'aya');
			$aya = array_filter($aya);
			$aya = array_unique($aya);

			if($sura && $aya)
			{
				$load = \lib\db\translate::load($translate['table_name'], ['sura' => ["IN", "(". implode(',', $sura).")"], 'aya' => ["IN", "(". implode(',', $aya).")"]]);
				$data = [];

				unset($translate['table_name']);
				unset($translate['id']);

				if(!\dash\url::content())
				{
					$get       = \dash\request::get();
					$getTrans  = isset($get['t']) ? $get['t'] : '';
					$getTrans  = explode('-', $getTrans);
					$getTrans  = array_filter($getTrans);
					$getTrans  = array_unique($getTrans);

					$thisTransKey = $translate['language']. $translate['index'];

					if(in_array($thisTransKey, $getTrans))
					{
						unset($getTrans[array_search($thisTransKey, $getTrans)]);
					}

					$get['t']        = implode('-', $getTrans);
					$url             = \dash\url::that(). '?'. http_build_query($get);
					$translate['remove_url'] = $url;

				}

				foreach ($load as $key => $value)
				{
					if(!isset($data[$value['sura'].'_'. $value['aya']]))
					{
						$data[$value['sura'].'_'. $value['aya']]['text']   = $value['text'];
						$data[$value['sura'].'_'. $value['aya']]['detail'] = $translate;
					}
				}

				$translate['data'] = $data;

				$result[] = $translate;
			}
		}
		self::$load_translate = true;
		self::$translate = $result;

	}


	private static function get_translation($_sura, $_aya, $_meta = null)
	{
		if(!self::$load_translate)
		{
			return false;
		}

		$result = [];
		foreach (self::$translate as $key => $value)
		{
			if(isset($value['data'][$_sura. '_'. $_aya]))
			{
				$result[] = $value['data'][$_sura. '_'. $_aya];
			}
		}

		return $result;
	}
}
?>