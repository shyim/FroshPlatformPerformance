<?php declare(strict_types=1);

namespace Frosh\Performance\Component;

use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRouteResponse;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class BreadcrumbToProductRoute extends AbstractProductDetailRoute
{
    public function __construct(
        private readonly AbstractProductDetailRoute $decorated,
        private readonly CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder,
        private readonly EntityRepository $categoryRepository
    ) {
    }

    public function getDecorated(): AbstractProductDetailRoute
    {
        return $this->decorated;
    }

    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductDetailRouteResponse
    {
        $response = $this->decorated->load($productId, $request, $context, $criteria);

        $product = $response->getProduct();

        $product->getSeoCategory()->addExtension('breadcrumb', $this->buildBreadcrumb($product->getSeoCategory(), $context));

        return $response;
    }

    private function buildBreadcrumb(CategoryEntity $category, SalesChannelContext $context): EntityCollection
    {
        $seoBreadcrumb = $this->categoryBreadcrumbBuilder->build($category, $context->getSalesChannel());

        if ($seoBreadcrumb === null) {
            return new EntityCollection();
        }

        /** @var list<string> $categoryIds */
        $categoryIds = array_keys($seoBreadcrumb);
        if (empty($categoryIds)) {
            return new EntityCollection();
        }

        $criteria = new Criteria($categoryIds);
        $criteria->setTitle('breadcrumb-extension');
        /** @var CategoryCollection $categories */
        $categories = $this->categoryRepository->search($criteria, $context->getContext())->getEntities();

        $breadcrumb = [];
        foreach ($categoryIds as $categoryId) {
            if ($categories->get($categoryId) === null) {
                continue;
            }

            $breadcrumb[$categoryId] = $categories->get($categoryId);
        }

        return new EntityCollection($breadcrumb);
    }
}
