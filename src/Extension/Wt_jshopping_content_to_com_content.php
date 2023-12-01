<?php
/**
 * @package       WT JoomShopping content to Joomla Articles
 * @version       2.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Jshopping\Wt_jshopping_content_to_com_content\Extension;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class Wt_jshopping_content_to_com_content extends CMSPlugin implements SubscriberInterface
{
	protected $autoloadlanguage = true;

    protected $allowLegacyListeners = false;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeDisplayContentView' => 'onBeforeDisplayContentView'
        ];
    }

	/**
	 * @param $event Event Before display content view event
     *
     * @return void
     *
	 * @since 1.0.0
	 */
	public function onBeforeDisplayContentView($event): void
	{
        /* @var $view object Content View */
        $view = $event->getArgument(0);

		if (count((array)$this->params->get('fields')) > 0 ) {
			$joomla_articles = [];

            foreach ($this->params->get('fields') as $field) {
				$joomla_articles[$field->text_type][$field->language] = $field->joomla_article;
			}

			$lang_tag = $this->getApplication()->getLanguage()->getTag();

			$page = $this->getApplication()->getInput()->get('page');
			$article_id = (($joomla_articles[$page][$lang_tag]) ? $joomla_articles[$page][$lang_tag] : $joomla_articles[$page]['*']);

            if ($article_id) {
                /* @var $component \Joomla\CMS\Extension\MVCComponent */
                $component = $this->getApplication()->bootComponent('com_content');

                /* @var $model \Joomla\Component\Content\Site\MOdel\ArticleModel */
                $model = $component->getMVCFactory()->createModel('Article', 'ContentModel');
                /* @var $article object */
                $article = $model->getItem($article_id);

                $view->text = '<h1>'.$article->title.'</h1>'.(($article->fulltext) ? $article->fulltext : $article->introtext);
			}
		}
	}
}
