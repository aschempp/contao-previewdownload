<?php

/*
 * $this->Database->tableExists('tl_galerie_pictures')
 * $this->Database->fieldExists('banner_template', 'tl_banner_category')
 *
 * $this->Database->listFields('tl_module')
 *
 * Database\Updater::convertSingleField('tl_galerie_pictures', 'fullscreenSingleSRC');
 * Database\Updater::convertMultiField('tl_content', 'imagesFolder');
 *
 */

class previewdownloadRunonce extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->import('Database');
    }

    public function run()
    {
        if (version_compare(VERSION, '3.2', '>=') && $this->Database->tableExists('tl_content'))
        {
            $arrFields = $this->Database->listFields('tl_content');

            foreach ($arrFields as $arrField)
            {
                if ($arrField['name'] == 'previewFile' && $arrField['type'] != 'binary')
                {
                    Database\Updater::convertSingleField('tl_content', 'previewFile');
                }
                if ($arrField['name'] == 'previewImage' && $arrField['type'] != 'binary')
                {
                    Database\Updater::convertSingleField('tl_content', 'previewImage');
                }
            }
        }
    }

}

$objpreviewdownloadRunonce = new previewdownloadRunonce();
$objpreviewdownloadRunonce->run();
?>