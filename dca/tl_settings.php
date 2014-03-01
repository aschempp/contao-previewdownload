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
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{binary_legend},imPath;{previewdownload_legend},imSize,pageOrientation;';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['imPath'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['imPath'],
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long'),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['imSize'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['imSize'],
    'exclude' => true,
    'inputType' => 'imageSize',
    'options' => $GLOBALS['TL_CROP'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('rgxp' => 'digit', 'nospace' => true, 'helpwizard' => true),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['pageOrientation'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['pageOrientation'],
    'inputType' => 'checkbox',
);