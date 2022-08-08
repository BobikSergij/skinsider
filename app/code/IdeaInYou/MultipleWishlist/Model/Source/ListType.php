<?php
namespace IdeaInYou\MultipleWishlist\Model\Source;


use Amasty\MWishlist\Model\Source\Type;

class ListType extends \Amasty\MWishlist\Model\Source\ListType

{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            Type::WISH => __('Wish Lists'),
        ];
    }




}
