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
		// Contao 3 compatibility
		if (version_compare(VERSION, '3.0', '>='))
		{
			$objModel = \FilesModel::findById($this->previewFile);

			if ($objModel !== null)
			{
				$this->previewFile = $objModel->path;
			}
		}

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

		// Contao 3 compatibility
		if (version_compare(VERSION, '3.0', '>='))
		{
			$icon = 'assets/contao/images/' . $objFile->icon;
		}

		if (($imgSize = @getimagesize(TL_ROOT . '/' . $icon)) !== false)
		{
			$this->Template->imgSize = ' ' . $imgSize[3];
		}

		// Generate preview image
		$preview = 'system/html/preview' . $this->id . '-' . substr(md5($this->previewFile), 0, 8) . '.jpg';

                // Contao 3 compatibility
                if (version_compare(VERSION, '3.0', '>=')) {
                    $preview = 'system/tmp/' . $this->id . '-' . substr(md5($this->previewFile), 0, 8) . '.jpg';
                }

		// Contao 3 compatibility
		if (version_compare(VERSION, '3.0', '>='))
		{
			$objModel = \FilesModel::findById($this->previewImage);

			if ($objModel !== null)
			{
				$this->previewImage = $objModel->path;
			}
		}

                // contao allowed file name with spaces, imagemagick not
                $booImagemagick = true;
                if (strstr($this->previewFile, ' '))
                {
                     $this->log('Creating preview from "' . $this->previewFile . '" failed! File name with spaces', 'ContentPreviewDownload compile()', TL_ERROR);
                     $booImagemagick = false;
                     if (TL_MODE == 'BE')
                     {
                        $this->linkTitle .= '<p>Creating preview failed! <a href="/contao/main.php?do=log">System log</a></p>';
                     }
                }


                if (strlen($this->previewImage) && is_file(TL_ROOT . '/' . $this->previewImage))
		{
			$preview = $this->previewImage;
		}
		elseif ( ( !is_file(TL_ROOT . '/' . $preview) || filemtime(TL_ROOT . '/' . $preview) < (time()-604800) ) && $booImagemagick ) // Image older than a week
		{
			if (class_exists('Imagick', false))
			{
				//!@todo Imagick PHP-Funktionen verwenden, falls vorhanden
			}
			else
			{
				$strFirst = '';

				if ($objFile->extension == 'pdf')
					$strFirst = '[0]';

				@exec(sprintf('PATH=\$PATH:%s;export PATH;%s/convert %s/%s'.$strFirst.' %s/%s 2>&1', $GLOBALS['TL_CONFIG']['imPath'], $GLOBALS['TL_CONFIG']['imPath'], TL_ROOT, $this->previewFile, TL_ROOT, $preview), $convert_output, $convert_code);

				if (!is_file(TL_ROOT . '/' . $preview))
				{
					$convert_output = implode("<br />", $convert_output);
					$reason = 'ImageMagick Exit Code: '.$convert_code;

					if ($convert_code == 127)
					{
						$reason = 'ImageMagick is not available at ' . $GLOBALS['TL_CONFIG']['imPath'];
					}
					if (strpos($convert_output, 'gs: command not found'))
					{
						$reason = 'Unable to read PDF due to GhostScript error.';
					}

					$this->log('Creating preview from "' . $this->previewFile . '" failed! '.$reason."\n\n".$convert_output, 'ContentPreviewDownload compile()', TL_ERROR);
                                        $this->linkTitle .= '<p>Creating preview failed! <a href="/contao/main.php?do=log">System log</a></p>';
				}
			}
		}

		if (is_file(TL_ROOT . '/' . $preview))
		{
                    $imgIndividualSize = deserialize($this->size);
                    $imgDefaultSize = deserialize($GLOBALS['TL_CONFIG']['imSize']);
                    $arrImageSize = getimagesize(TL_ROOT . '/' . $preview);


                    if ($imgIndividualSize[0] != '' || $imgIndividualSize[1] != '') { // individualSize
                        $imgSize = $imgIndividualSize;
                    } elseif ($imgDefaultSize[0] != '' || $imgDefaultSize[1] != '') { // preferences -> defaultSize
                        if ($GLOBALS['TL_CONFIG']['pageOrientation'] && $arrImageSize[0] >= $arrImageSize[1]) {
                            $imgSize = array($imgDefaultSize[1], $imgDefaultSize[0], $imgDefaultSize[2]);
                      } else {
                            $imgSize = $imgDefaultSize;
                        }
                    } else {
                        if ($arrImageSize[0] >= $arrImageSize[1]) {
                            $imgSize = array('148', '105', 'center_center');
                        } else {
                            $imgSize = array('105', '148', 'center_center');
                        }
                    }

                    $src = $this->getImage($preview, $imgSize[0], $imgSize[1], $imgSize[2]);

                    if (($imgSize = @getimagesize(TL_ROOT . '/' . $src)) !== false) {
                        $this->Template->previewImgSize = ' ' . $imgSize[3];
                    }
		}

		if ($this->previewTips)
		{
			$this->Template->showTip = true;
			$GLOBALS['TL_CSS'][] = 'system/modules/previewdownload/assets/tips.css';
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/previewdownload/assets/tips.js';
		}

                $strHref = $this->Environment->request;

		// Remove an existing file parameter (see #5683)
		if (preg_match('/(&(amp;)?|\?)file=/', $strHref))
		{
			$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
		}

		$strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($this->previewFile);

		$this->Template->preview = $src;
		$this->Template->icon = $icon;
		$this->Template->link = $this->linkTitle;
		$this->Template->rel = $GLOBALS['TL_LANG']['MSC']['previewFileName'] . ' ' . $objFile->basename . ', ' . $GLOBALS['TL_LANG']['MSC']['previewFileSize'] . ' ' . $size;
		$this->Template->margin = $this->generateMargin(deserialize($this->imagemargin), 'padding');
		$this->Template->title = specialchars($this->linkTitle);
		$this->Template->href = $strHref;
	}
}

