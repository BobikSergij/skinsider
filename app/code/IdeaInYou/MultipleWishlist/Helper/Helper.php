<?php

namespace IdeaInYou\MultipleWishlist\Helper;

use Amasty\MWishlist\Model\Wishlist\Management as WishlistManagement;
use Magento\Framework\App\Helper\Context;

class Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        Context $context,
        WishlistManagement $wishlistManagement
    )

    {
        parent::__construct($context);
        $this->wishlistManagement = $wishlistManagement;
    }

    public function getWishlist($wishlistId)
    {
        $collection = $this->wishlistManagement->getCustomerWishlists();

        $collection->addFieldToFilter('wishlist_id', $wishlistId);


        return $collection->getLastItem();
    }
}
