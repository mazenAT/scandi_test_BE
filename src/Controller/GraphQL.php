<?php

namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
use App\Model\Product;
use App\Model\Category;

class GraphQL {
    static public function handle() {
        try {
            $productModel = new Product();
            $categoryModel = new Category();

            $currencyType = new ObjectType([
                'name' => 'Currency',
                'fields' => [
                    'label' => ['type' => Type::string()],
                    'symbol' => ['type' => Type::string()],
                ],
            ]);

            $priceType = new ObjectType([
                'name' => 'Price',
                'fields' => [
                    'amount' => ['type' => Type::float()],
                    'currency' => ['type' => $currencyType],
                ],
            ]);

            $attributeItemType = new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'displayValue' => ['type' => Type::string()],
                    'value' => ['type' => Type::string()],
                    'id' => ['type' => Type::string()],
                ],
            ]);

            $attributeSetType = new ObjectType([
                'name' => 'AttributeSet',
                'fields' => [
                    'id' => ['type' => Type::string()],
                    'name' => ['type' => Type::string()],
                    'type' => ['type' => Type::string()],
                    'items' => ['type' => Type::listOf($attributeItemType)],
                ],
            ]);

            $productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => ['type' => Type::string()],
                    'name' => ['type' => Type::string()],
                    'inStock' => ['type' => Type::boolean()],
                    'gallery' => ['type' => Type::listOf(Type::string())],
                    'description' => ['type' => Type::string()],
                    'category' => ['type' => Type::string()],
                    'attributes' => ['type' => Type::listOf($attributeSetType)],
                    'prices' => ['type' => Type::listOf($priceType)],
                    'brand' => ['type' => Type::string()],
                ],
            ]);

            $categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'name' => ['type' => Type::string()],
                ],
            ]);

            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => static function () use ($categoryModel): array {
                            $cats = $categoryModel->getAll();
                            // Always include 'all' as the first category
                            array_unshift($cats, ['name' => 'all']);
                            return $cats;
                        },
                    ],
                    'products' => [
                        'type' => Type::listOf($productType),
                        'resolve' => static function () use ($productModel): array {
                            return $productModel->getAll();
                        },
                    ],
                    'product' => [
                        'type' => $productType,
                        'args' => [
                            'id' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($productModel): ?array {
                            return $productModel->getById($args['id']);
                        },
                    ],
                    'productsByCategory' => [
                        'type' => Type::listOf($productType),
                        'args' => [
                            'category' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($productModel): array {
                            return $productModel->getByCategory($args['category']);
                        },
                    ],
                    'searchProducts' => [
                        'type' => Type::listOf($productType),
                        'args' => [
                            'query' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($productModel): array {
                            return $productModel->search($args['query']);
                        },
                    ],
                ],
            ]);

            $mutationType = null;

            $schema = new Schema(
                (new SchemaConfig())
                ->setQuery($queryType)
            );

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'];
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}