<?php
namespace content_api\v6\page;


class controller
{
	public static function routing()
	{
		$wbw = false;

		if(\dash\url::subchild() === 'wbw' || \dash\url::subchild() === 'wbw-raw')
		{
			if(\dash\url::dir(3))
			{
				\content_api\v6::no(404);
			}

			$wbw = true;
		}
		else
		{
			if(\dash\url::subchild())
			{
				\content_api\v6::no(404);
			}
		}

		$index = \dash\request::get('index');
		if(!$index)
		{
			\content_api\v6::no(400, T_("Index not set"));
		}

		if(!ctype_digit($index) || intval($index) < 1 || intval($index) > 604)
		{
			\content_api\v6::no(400, T_("Invalid index"));
		}


		if($wbw)
		{
			if(\dash\url::subchild() === 'wbw-raw')
			{
				$page = \lib\db\quran_word::get(['page' => $index]);
			}
			else
			{
				$page = \lib\app\quran\page::load('page', $index, null, null);
				if(isset($page['text']['page1']['line']))
				{
					$page = $page['text']['page1']['line'];
				}
			}
		}
		else
		{
			$page = \lib\db\quran::get(['page' => $index]);
		}

		\content_api\v6::bye($page);
	}
}
?>