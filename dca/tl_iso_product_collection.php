<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * @copyright  Intelligent Spark 2017
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_product_collection']['palettes']['default'] = str_replace(',date_shipped;', ',date_shipped,delivery_date,', $GLOBALS['TL_DCA']['tl_iso_product_collection']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_iso_product_collection']['fields']['delivery_date'] = array
(
    'label'		=> &$GLOBALS['TL_LANG']['tl_iso_product_collection']['delivery_date'],
    'inputType'	=> 'text',
    'eval'		=> array('disabled'=>true),
    'sql'       => "varchar(255) NOT NULL default ''"
);