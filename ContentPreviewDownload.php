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
 * @property string $previewFile
 * @property string $previewImage
 * @property string $previewExtension
 * @property array  $size
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
        $objModel = \FilesModel::findByUuid($this->previewFile);

        if ($objModel === null)
        {
            if (!\Validator::isUuid($this->singleSRC))
            {
                return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
            }

            return '';
        }

        $this->previewFile = $objModel->path;

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

        if (!$this->previewExtension) {
            $this->previewExtension = 'jpg';
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

        $icon = 'assets/contao/images/' . $objFile->icon;

        if (($imgSize = @getimagesize(TL_ROOT . '/' . $icon)) !== false)
        {
            $this->Template->imgSize = ' ' . $imgSize[3];
        }

        // Generate preview image
        $strCacheKey = substr(md5($this->previewFile), 0, 8);
        $preview = sprintf(
            'assets/images/%s/preview%s-%s.%s',
            substr($strCacheKey, -1),
            $this->id,
            $strCacheKey,
            $this->previewExtension
        );

        $blnHasPreviewImage = false;

        if ($this->previewImage)
        {
            $objPreviewModel = \FilesModel::findByUuid($this->previewImage);

            if ($objPreviewModel !== null)
            {
                $this->previewImage = $objPreviewModel->path;
                $blnHasPreviewImage = true;
            }
        }

        if ($blnHasPreviewImage && is_file(TL_ROOT . '/' . $this->previewImage))
        {
            $preview = $this->previewImage;
        }
        elseif (!is_file(TL_ROOT . '/' . $preview) || filemtime(TL_ROOT . '/' . $preview) < (time()-604800)) // Image older than a week
        {
            $strFirst = '';

            if ('pdf' === $objFile->extension) {
                $strFirst = '[0]';
            }

            if (class_exists('Imagick', false))
            {
                $im = new Imagick();
                $im->readimage(TL_ROOT . '/' . $this->previewFile . $strFirst);
                $im->setImageFormat($this->previewExtension);

                if ('jpg' === $this->previewExtension && method_exists($im, 'setImageAlphaChannel')) {
                    $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_DEACTIVATE);
                }

                $im->writeImage(TL_ROOT . '/' . $preview);
                $im->clear();
                $im->destroy();
            }
            else
            {
                $flags = '';

                if ('jpg' === $this->previewExtension) {
                    $flags = '-alpha remove';
                }

                @exec(
                    sprintf(
                        'PATH=\$PATH:%s;export PATH;%s/convert %s %s/%s%s %s/%s 2>&1',
                        $GLOBALS['TL_CONFIG']['imPath'],
                        $GLOBALS['TL_CONFIG']['imPath'],
                        $flags,
                        TL_ROOT,
                        $this->previewFile,
                        $strFirst,
                        TL_ROOT,
                        $preview
                    ),
                    $convert_output,
                    $convert_code
                );

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
                }
            }
        }

        if (is_file(TL_ROOT . '/' . $preview))
        {
            $imgSize = deserialize($this->size);
            $arrImageSize = getimagesize(TL_ROOT . '/' . $preview);

            // Adjust image size in the back end
            if (TL_MODE == 'BE' && $arrImageSize[0] > 640 && ($imgSize[0] > 640 || !$imgSize[0]))
            {
                $imgSize[0] = 640;
                $imgSize[1] = floor(640 * $arrImageSize[1] / $arrImageSize[0]);
            }

            $src = $this->getImage($preview, $imgSize[0], $imgSize[1]);

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

        $this->Template->preview = $src;
        $this->Template->icon = $icon;
        $this->Template->link = $this->linkTitle;
        $this->Template->rel = $GLOBALS['TL_LANG']['MSC']['previewFileName'] . ' ' . $objFile->basename . ', ' . $GLOBALS['TL_LANG']['MSC']['previewFileSize'] . ' ' . $size;
        $this->Template->margin = $this->generateMargin(deserialize($this->imagemargin), 'padding');
        $this->Template->title = specialchars($this->linkTitle);
        $this->Template->href = $this->urlEncode($this->previewFile); //$this->Environment->request . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($this->Environment->request, '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($this->previewFile);
    }
}

