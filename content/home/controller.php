<?php
namespace content\home;

class controller
{
	public static function routing()
	{
		// save history
		if(\dash\request::is('post') && \dash\request::post('type') === 'history' && \dash\request::post('aya'))
		{
			\lib\app\history::save(\dash\request::post('aya'));
			\dash\code::end();
		}

		$module = \dash\url::module();

		$url    = $module;

		$child  = \dash\url::child();

		if($child)
		{
			$url .= '/'. $child;
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

		// $meta = \lib\app\user_setting::get($meta);

		$quran = \lib\app\quran\find::find($url, $meta);

		if($quran)
		{
			\dash\data::sureLoaded(true);
			\dash\data::quranLoaded($quran);
			\dash\open::get();

			// \lib\app\user_setting::save();

			if(isset($quran['detail']))
			{
				\dash\data::suraDetail($quran['detail']);
			}


			if(isset($quran['translate']))
			{
				\dash\data::translateList($quran['translate']);
			}
		}

		if(!$url)
		{
			\dash\data::ayaDay(\lib\app\aya_day::get());
		}
	}
}
?>