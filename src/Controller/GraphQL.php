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
use App\Model\Order;

class GraphQL
{
    public static function handle()
    {
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

            $orderAttributeInputType = new \GraphQL\Type\Definition\InputObjectType([
                'name' => 'OrderAttributeInput',
                'fields' => [
                    'id' => ['type' => Type::nonNull(Type::string())],
                    'value' => ['type' => Type::nonNull(Type::string())],
                ],
            ]);

            $orderProductInputType = new \GraphQL\Type\Definition\InputObjectType([
                'name' => 'OrderProductInput',
                'fields' => [
                    'productId' => ['type' => Type::nonNull(Type::string())],
                    'quantity' => ['type' => Type::nonNull(Type::int())],
                    'attributes' => ['type' => Type::listOf($orderAttributeInputType)],
                ],
            ]);

            $orderInputType = new \GraphQL\Type\Definition\InputObjectType([
                'name' => 'OrderInput',
                'fields' => [
                    'products' => ['type' => Type::nonNull(Type::listOf($orderProductInputType))],
                ],
            ]);

            $orderAttributeType = new ObjectType([
                'name' => 'OrderAttribute',
                'fields' => [
                    'id' => ['type' => Type::string()],
                    'value' => ['type' => Type::string()],
                ],
            ]);

            $orderItemType = new ObjectType([
                'name' => 'OrderItem',
                'fields' => [
                    'productId' => ['type' => Type::string()],
                    'quantity' => ['type' => Type::int()],
                    'attributes' => ['type' => Type::listOf($orderAttributeType)],
                ],
            ]);

            $orderType = new ObjectType([
                'name' => 'Order',
                'fields' => [
                    'id' => ['type' => Type::id()],
                    'createdAt' => ['type' => Type::string()],
                    'items' => ['type' => Type::listOf($orderItemType)],
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

            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => $orderType,
                        'args' => [
                            'input' => ['type' => Type::nonNull($orderInputType)],
                        ],
                        'resolve' => function (
                            $root,
                            $args
                        ) {
                            $input = $args['input'];
                            // Validation
                            if (empty($input['products']) || !is_array($input['products'])) {
                                throw new \Exception('Order must contain at least one product.');
                            }
                            $productModel = new \App\Model\Product();
                            foreach ($input['products'] as $i => $item) {
                                if (empty($item['productId'])) {
                                    throw new \Exception("Product ID is required for item #" . ($i + 1));
                                }
                                $product = $productModel->getById($item['productId']);
                                if (!$product) {
                                    throw new \Exception("Product not found: " . $item['productId']);
                                }
                                if (empty($item['quantity']) || !is_int($item['quantity']) || $item['quantity'] < 1) {
                                    throw new \Exception("Quantity must be a positive integer for product: " . $item['productId']);
                                }
                                if (!empty($item['attributes'])) {
                                    foreach ($item['attributes'] as $attr) {
                                        if (empty($attr['id']) || !isset($attr['value'])) {
                                            throw new \Exception("Each attribute must have an id and value for product: " . $item['productId']);
                                        }
                                    }
                                }
                            }
                            $order = \App\Model\SimpleOrder::fromArray($input);
                            $orderId = $order->save();
                            return [
                                'id' => $orderId,
                                'createdAt' => date('c'),
                                'items' => array_map(function ($item) {
                                    return [
                                        'productId' => $item->getProductId(),
                                        'quantity' => $item->getQuantity(),
                                        'attributes' => array_map(function ($attr) {
                                            return [
                                                'id' => $attr->getId(),
                                                'value' => $attr->getValue(),
                                            ];
                                        }, $item->getAttributes()),
                                    ];
                                }, $order->getItems()),
                            ];
                        },
                    ],
                ],
            ]);

            $schema = new Schema(
                (new SchemaConfig())
                ->setQuery($queryType)
                ->setMutation($mutationType)
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
