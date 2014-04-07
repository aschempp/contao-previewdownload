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
        // Return if there is no file
        if ($this->previewFile == '')
        {
            return '';
        }

        $objFile = \FilesModel::findByUuid($this->previewFile);

        if ($objFile === null)
        {
            if (!\Validator::isUuid($this->previewFile))
            {
                return '<p class="error">' . $GLOBALS['TL_LANG']['ERR']['version2format'] . '</p>';
            }
            return '';
        }

        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        // Return if the file type is not allowed
        if (!in_array($objFile->extension, $allowedDownload))
        {
            return '';
        }

        $file = \Input::get('file', true);

        // Send the file to the browser and do not send a 404 header (see #4632)
        if ($file != '' && $file == $objFile->path)
        {
            \Controller::sendFileToBrowser($file);
        }

        $this->previewFile = $objFile->path;
        return parent::generate();
    }

    /**
     * Generate content element
     */
    protected function compile()
    {
        $objFile = new \File($this->previewFile, true);
        $allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

        if (!in_array($objFile->extension, $allowedDownload))
        {
            return;
        }

        if (!strlen($this->linkTitle))
        {
            $this->linkTitle = $objFile->basename;
        }


        $preview = 'system/tmp/' . $this->id . '-' . substr(md5($this->previewFile), 0, 8) . '.jpg';

        // Preview image is given
        if ($this->previewImage)
        {
            $objImage = \FilesModel::findByUuid($this->previewImage);

            if ($objImage !== null)
            {
                $this->previewImage = $objImage->path;
            }
        }

        if (strlen($this->previewImage) && is_file(TL_ROOT . '/' . $this->previewImage))
        {
            $preview = $this->previewImage;
        }
        elseif ((!is_file(TL_ROOT . '/' . $preview) || filemtime(TL_ROOT . '/' . $preview) < (time() - 604800))) // Image older than a week
        {
            if (class_exists('Imagick', false))
            {
                //!@todo Imagick PHP-Funktionen verwenden, falls vorhanden
            }
            else
            {
                // contao allowed file name with spaces, imagemagick not
                if (strstr($this->previewFile, ' '))
                {
                    $this->log('Creating preview from "' . $this->previewFile . '" failed! File name with spaces', 'ContentPreviewDownload compile()', TL_ERROR);
                    if (TL_MODE == 'BE')
                    {
                        $this->linkTitle .= $GLOBALS['TL_LANG']['MSC']['creatingPreviewFailed'];
                    }
                }
                else
                {

                    $strFirst = ($objFile->extension == 'pdf' ? '[0]' : '' );

                    @exec(sprintf('PATH=\$PATH:%s;export PATH;%s/convert %s/%s' . $strFirst . ' %s/%s 2>&1', $GLOBALS['TL_CONFIG']['imPath'], $GLOBALS['TL_CONFIG']['imPath'], TL_ROOT, $this->previewFile, TL_ROOT, $preview), $convert_output, $convert_code);

                    if (!is_file(TL_ROOT . '/' . $preview))
                    {
                        $convert_output = implode("<br />", $convert_output);
                        $reason = 'ImageMagick Exit Code: ' . $convert_code;

                        if ($convert_code == 127)
                        {
                            $reason = 'ImageMagick is not available at ' . $GLOBALS['TL_CONFIG']['imPath'];
                        }
                        if (strpos($convert_output, 'gs: command not found'))
                        {
                            $reason = 'Unable to read PDF due to GhostScript error.';
                        }

                        $this->log('Creating preview from "' . $this->previewFile . '" failed! ' . $reason . "\n\n" . $convert_output, 'ContentPreviewDownload compile()', TL_ERROR);
                        if (TL_MODE == 'BE')
                        {
                            $this->linkTitle .= $GLOBALS['TL_LANG']['MSC']['creatingPreviewFailed'];
                        }
                    }
                }
            }
        }

        if (is_file(TL_ROOT . '/' . $preview))
        {
            $imgIndividualSize = deserialize($this->previewImageSize);
            $arrImageSize = getimagesize(TL_ROOT . '/' . $preview);

            if ($imgIndividualSize[0] != '' || $imgIndividualSize[1] != '')
            {
                $imgSize = $imgIndividualSize;
            }
            else
            {
                $imgSize = array($GLOBALS['TL_CONFIG']['imageWidth'], $GLOBALS['TL_CONFIG']['imageHeight'], 'proportional');
            }

            $src = $this->getImage($preview, $imgSize[0], $imgSize[1], $imgSize[2]);

            if (($imgSize = @getimagesize(TL_ROOT . '/' . $src)) !== false)
            {
                $this->Template->previewImgSize = ' ' . $imgSize[3];
            }
        }

        if ($this->previewTips)
        {
            $this->Template->showTip = true;
            $GLOBALS['TL_CSS'][] = 'system/modules/previewdownload/assets/tips.css';
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/previewdownload/assets/tips.js';
        }

        $strHref = \Environment::get('request');

        // Remove an existing file parameter (see #5683)
        if (preg_match('/(&(amp;)?|\?)file=/', $strHref))
        {
            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFile->value);

        $this->Template->preview = $src;
        $this->Template->margin = $this->generateMargin(deserialize($this->imagemargin), 'padding');

        $this->Template->link = $this->linkTitle;
        $this->Template->title = specialchars($this->titleText ? : $this->linkTitle);
        $this->Template->href = $strHref;
        $this->Template->filesize = $this->getReadableSize($objFile->filesize, 1);
        $this->Template->icon = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
        $this->Template->mime = $objFile->mime;
        $this->Template->extension = $objFile->extension;
        $this->Template->path = $objFile->dirname;
    }

}
