<?php
namespace content\home;

class controller
{
	public static function routing()
	{
		$module = \dash\url::module();

		$url    = $module;

		$child  = \dash\url::child();

		if($child)
		{
			$url .= '/'. $child;
		}

		if(in_array($module, ['aye', 'random','page', 'randompage']) && !$child)
		{
			self::random_quran($module);
		}

		$meta = [];

		if(\dash\request::get('t'))
		{
			$meta['translate'] = \dash\request::get('t');
		}

		if(\dash\request::get('qari'))
		{
			$meta['qari'] = \dash\request::get('qari');
		}

		if(\dash\request::get('mode'))
		{
			$meta['mode'] = \dash\request::get('mode');
		}

		$quran = \lib\app\quran_word::find($url, $meta);

		if($quran)
		{
			\dash\data::sureLoaded(true);
			\dash\data::quranLoaded($quran);
			\dash\open::get();

			if(isset($quran['detail']))
			{
				\dash\data::suraDetail($quran['detail']);
			}


			if(isset($quran['translate']))
			{
				\dash\data::translateList($quran['translate']);
			}
		}
	}


	private static function random_quran($_type)
	{
		switch ($_type)
		{
			case 'aye':
				$detail = \lib\app\quran::day_aya();
				break;

			case 'page':
				$detail = \lib\app\quran::day_page();
				break;

			case 'random':
				$detail = \lib\app\quran::random_aya();
				break;

			case 'randompage':
				$detail = \lib\app\quran::random_page();
				break;

			default:
				return false;
				break;
		}

		if(isset($detail['url']))
		{
			\dash\redirect::to($detail['url']);
		}
	}
}
?>