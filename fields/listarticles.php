<?php defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

FormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldListarticles extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'ListArticles';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.5
	 */
	protected static $items = array();

	/**
	 *
	 * @return string
	 *
	 * @since version
	 */
	public function getInput()
	{
		try
		{
			//HTMLHelper::addIncludePath(JPATH_ROOT . 'plugins/jshopping/wt_jshopping_content_to_com_content/fields/helpers');

			$filters['filter.q']         = $this->getAttribute('q', '');
			$filters['filter.limit']     = $this->getAttribute('limit', '');
			$filters['filter.published'] = $this->getAttribute('published', '');
			$filters['filter.language']  = $this->getAttribute('language', '');
			$filters['filter.ids']       = $this->getAttribute('ids', '');
			$filters['filter.category']  = $this->getAttribute('category', '');
			$filtersClean                = [];

			foreach ($filters as $key => $filter)
			{
				if (empty($filter))
				{
					continue;
				}

				$value = $filter;
				if (in_array($key, ['filter.published', 'filter.ids']))
				{
					$value = explode(',', $value);
				}

				$filtersClean[$key] = $value;
			}

			$options = self::articleOptions($filtersClean);

			foreach ($options as $option)
			{
				$this->addOption($option->text, ['value' => $option->value]);
			}

			return parent::getInput();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option e.g. com_something.
	 * @param   array   $config     An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function articleOptions($config = [])
	{
		$config = array_merge($config, [
			'filter.published' => [0, 1]
		]);
		$hash = md5('lib_fields' . '.' . serialize($config));

		if (!isset(static::$items[$hash]))
		{
			$config = (array) $config;
			$db     = Factory::getDbo();
			$user   = Factory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());

			$query = $db->getQuery(true)
				->select("con.id, REPLACE(con.title, '\"', '') as title, con.language")
				->from('#__content AS con');

			if(isset($config['filter.category'])) {
				$category = (int) $config['filter.category'];
				$query->innerJoin("(SELECT node.id FROM #__categories AS node, #__categories AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt AND (parent.parent_id = " . $category . " OR parent.id = " . $category . ") ORDER BY node.lft) AS cat ON (con.catid = cat.id)");
			}

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('con.state = ' . (int) $config['filter.published']);
				}
				elseif (is_array($config['filter.published']))
				{
					$config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
					$query->where('con.state IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			// Filter on the language
			if (isset($config['filter.language']))
			{
				if (is_string($config['filter.language']))
				{
					$query->where('con.language = ' . $db->quote($config['filter.language']));
				}
				elseif (is_array($config['filter.language']))
				{
					foreach ($config['filter.language'] as &$language)
					{
						$language = $db->quote($language);
					}

					$query->where('con.language IN (' . implode(',', $config['filter.language']) . ')');
				}
			}

			// Filter on the language
			if (isset($config['filter.q']))
			{
				$search = $db->Quote( '%' . $db->escape( $config['filter.q'], true ) . '%' );

				$query->where("title LIKE " . $search);
			}

			// Filter on the language
			if (isset($config['filter.limit']))
			{
				$query->setLimit((int)$config['filter.limit']);
			}

			// Filter on the language
			if (isset($config['filter.ids']))
			{
				$ids = $db->escape( implode(',', $config['filter.ids']), true );
				$query->where("con.id in (" . $ids . ')');
			}

			$query->order("con.id DESC");

			$db->setQuery($query);
			$items = $db->loadObjectList();

			// Assemble the list options.
			static::$items[$hash] = array();

			foreach ($items as &$item)
			{
				static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
			}
		}

		return static::$items[$hash];
	}

}