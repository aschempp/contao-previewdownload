<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    LGPL
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['previewdownload']	= '{type_legend},type,headline;{source_legend},previewFile;{image_legend},previewImage,alt,size,imagemargin;{dwnconfig_legend},linkTitle,addToSitemap,previewTips;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['previewFile'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['previewFile'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'pdf,jpg,jpeg,gif,png', 'mandatory'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['previewImage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['previewImage'],
	'exclude'                 => true,
	'inputType'               => 'fileTree',
	'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>'jpg,jpeg,gif,png', 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['previewTips'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['previewTips'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50 m12'),
);

