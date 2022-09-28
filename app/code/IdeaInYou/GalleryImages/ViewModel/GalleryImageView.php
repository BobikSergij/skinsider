<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IdeaInYou\GalleryImages\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
class GalleryImageView implements ArgumentInterface
{
    protected $galleryReadHandler;
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    public function __construct(
        GalleryReadHandler $galleryReadHandler,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
    }
    /** Add image gallery to $product */
    public function addGallery($product) {
        $this->galleryReadHandler->execute($product);
    }
    /**
     * @param $image
     * @return \Magento\Catalog\Model\Product\Image
     * @param $product
     * @return \Magento\Framework\Data\Collection|mixed
     */
    public function getGalleryImages($product) {
        $images = $product->getMediaGalleryImages();

        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                $image->setData(
                    'small_image_url',
                    $this->imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->imageHelper->init($product, 'product_page_image_medium')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
    }
}
