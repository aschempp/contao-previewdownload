<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ContentPreviewDownload extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_previewdownload';


	/**
	 * Return if the file does not exist
	 * @return string
	 */
	public function generate()
	{
		// Return if there is no file
		if (!strlen($this->previewFile) || !is_file(TL_ROOT . '/' . $this->previewFile))
		{
			return '';
		}

		// Send file to the browser
		if (strlen($this->Input->get('file')) && $this->Input->get('file') == $this->previewFile)
		{
			$this->sendFileToBrowser($this->previewFile);
		}

		return parent::generate();
	}


	/**
	 * Generate content element
	 */
	protected function compile()
	{
		$objFile = new File($this->previewFile);
		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

		if (!in_array($objFile->extension, $allowedDownload))
		{
			return;
		}
		

		$size = number_format(($objFile->filesize/1024), 1, $GLOBALS['TL_LANG']['MSC']['decimalSeparator'], $GLOBALS['TL_LANG']['MSC']['thousandsSeparator']).' kB';

		if (!strlen($this->linkTitle))
		{
			$this->linkTitle = $objFile->basename;
		}

		$icon = 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon;

		if (($imgSize = @getimagesize(TL_ROOT . '/' . $icon)) !== false)
		{
			$this->Template->imgSize = ' ' . $imgSize[3];
		}
		
		// Generate preview image
		$preview = 'system/html/preview' . $this->id . '_' . md5($this->previewFile) . '.jpg';
		
		if (!is_file(TL_ROOT . '/' . $preview) || filemtime(TL_ROOT . '/' . $preview) < (time()-604800)) // Image older than a week
		{
			$strFirst = '';
			
			if ($objFile->extension == 'pdf')
				$strFirst = '[0]';
			
			
			$strExec = shell_exec(sprintf('PATH=\$PATH:%s;export PATH;%s/convert %s/%s'.$strFirst.' %s/%s', $GLOBALS['TL_CONFIG']['imPath'], $GLOBALS['TL_CONFIG']['imPath'], TL_ROOT, $this->previewFile, TL_ROOT, $preview));
			
/*
			if (!$strExec)
			{
				$this->log('Creating preview from "' . $this->previewFile . '" failed!<br />'.$code.'<br />'.print_r($output, true), 'ContentPreviewDownload compile()', TL_GENERAL);
			}
*/
		}
		
		
		$imgSize = deserialize($this->size);
		$arrImageSize = getimagesize(TL_ROOT . '/' . $preview);

		// Adjust image size in the back end
		if (TL_MODE == 'BE' && $arrImageSize[0] > 640 && ($imgSize[0] > 640 || !$imgSize[0]))
		{
			$imgSize[0] = 640;
			$imgSize[1] = floor(640 * $arrImageSize[1] / $arrImageSize[0]);
		}

		$src = $this->getImage($this->urlEncode($preview), $imgSize[0], $imgSize[1]);

		if (($imgSize = @getimagesize(TL_ROOT . '/' . $src)) !== false)
		{
			$this->Template->previewImgSize = ' ' . $imgSize[3];
		}
		
		if ($this->previewTips)
		{
			$this->Template->showTip = true;
			$GLOBALS['TL_CSS'][] = 'system/modules/previewdownload/html/tips.css';
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/previewdownload/html/tips.js';
		}
		
		$this->Template->preview = $src;
		$this->Template->icon = $icon;
		$this->Template->link = $this->linkTitle;
		$this->Template->rel = $GLOBALS['TL_LANG']['MSC']['previewFileName'] . ' ' . $objFile->basename . '<br />' . $GLOBALS['TL_LANG']['MSC']['previewFileSize'] . ' ' . $size;
		$this->Template->margin = $this->generateMargin(deserialize($this->imagemargin), 'padding');
		$this->Template->title = specialchars($this->linkTitle);
		$this->Template->href = $this->urlEncode($this->previewFile); //$this->Environment->request . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($this->Environment->request, '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($this->previewFile);
	}
}