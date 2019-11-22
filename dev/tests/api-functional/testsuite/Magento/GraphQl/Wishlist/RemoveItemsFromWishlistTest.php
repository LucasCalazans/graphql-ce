<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Wishlist;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Wishlist\Model\WishlistFactory;

class RemoveProductFromWishListTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->wishlistFactory = Bootstrap::getObjectManager()->get(WishlistFactory::class);
    }

    /**
     * Verify, removeProductsFromWishlist will remove specified wishlist item.
     *
     * @magentoApiDataFixture Magento/Wishlist/_files/wishlist.php
     * @return void
     */
    public function testRemoveProductFromWishList(): void
    {
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId(1);
        $itemId = $wishlist->getItemCollection()->getFirstItem()->getId();
        $wishListId = $wishlist->getId();
        $query =
            <<<QUERY
mutation  {
  removeProductsFromWishlist(
    input: {
      wishlist_id: "{$wishListId}"
      wishlist_items_ids: [$itemId]
    }
  ) {
    wishlist {
      id
      items {
        id
        product {
          id
          name
        }
      }
      items_count
      sharing_code
      updated_at
    }
  }
}
QUERY;

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->assertEmpty($response['removeProductsFromWishlist']['wishlist']['items']);
    }

    /**
     * Retrieve customer authorization headers.
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
