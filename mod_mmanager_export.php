<?php
/**
 * @package    mod_mmanager_export
 *
 * @author     proximate <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';
$document = JFactory::getDocument();

// incude jQuery
if($params->get('load_jquery',1)){
    // JHtml::_('jquery.framework');
    $document->addScript('https://code.jquery.com/jquery-3.5.1.min.js');

}
$document->addScript('media/com_nxmarathonmanager/font-awesome-514/js/all.min.js');
$document->addStyleSheet('media/com_nxmarathonmanager/font-awesome-514/css/all.min.css');

// Add scripts

$document->addScript('modules/mod_mmanager_export/tmpl/assets/js/main.js?ver=1.1');
$document->addScript('modules/mod_mmanager_export/tmpl/assets/js/search.js?ver=1.1');
$document->addScript('modules/mod_mmanager_export/tmpl/assets/js/ajax.js?ver=1.1');
$document->addStyleSheet('modules/mod_mmanager_export/tmpl/assets/css/checkbox.min.css');

// Include Components Router
require_once JPATH_ROOT . '/components/com_nxmarathonmanager/helpers/route.php';

$events = ModMManagerExportHelper::getEvents($params);

// The below line is no longer used in Joomla 4
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require ModuleHelper::getLayoutPath('mod_mmanager_export', $params->get('layout', 'default'));
