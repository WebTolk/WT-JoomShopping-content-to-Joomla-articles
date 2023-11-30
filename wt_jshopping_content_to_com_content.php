<?php
/**
 * WT JoomShopping content to Joomla articles - display Joomla articles in JoomShopping content pages
 * @package     WT JoomShopping content to Joomla articles
 * @version     1.0.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL 3.0
 * @since       1.0.0
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
class plgJshoppingWt_jshopping_content_to_com_content extends CMSPlugin
{
	protected $autoloadlanguage = true;

	/**
	 * @param $view
	 *
	 *
	 * @since 1.0.0
	 */
	public function onBeforeDisplayContentView(&$view)
	{

		if(count((array)$this->params->get('fields')) > 0 ){
			$joomla_articles = array();
			foreach ($this->params->get('fields') as $field){
				$joomla_articles[$field->text_type][$field->language] = $field->joomla_article;
			}
			$app = Factory::getApplication();
			$lang_tag = $app->getLanguage()->getTag();

			$page = $app->getInput()->get('page');
			$article_id = (($joomla_articles[$page][$lang_tag]) ? $joomla_articles[$page][$lang_tag] : $joomla_articles[$page]['*']);
			if ($article_id){
				BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models/', 'ContentModel');
				$model             = BaseDatabaseModel::getInstance('Article', 'ContentModel');
				$article = $model->getItem($article_id);
				$view->text = '<h1>'.$article->title.'</h1>'.(($article->fulltext) ? $article->fulltext : $article->introtext);
			}

		}
	}
}
