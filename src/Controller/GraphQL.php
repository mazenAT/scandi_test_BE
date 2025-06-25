<?php

namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
use App\Model\Item;

class GraphQL {
    static public function handle() {
        try {
            $itemModel = new Item();

            $itemType = new ObjectType([
                'name' => 'Item',
                'fields' => [
                    'id' => ['type' => Type::int()],
                    'title' => ['type' => Type::string()],
                    'description' => ['type' => Type::string()],
                    'price' => ['type' => Type::float()],
                    'category' => ['type' => Type::string()],
                    'status' => ['type' => Type::string()],
                    'created_at' => ['type' => Type::string()],
                    'updated_at' => ['type' => Type::string()],
                ],
            ]);

            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'echo' => [
                        'type' => Type::string(),
                        'args' => [
                            'message' => ['type' => Type::string()],
                        ],
                        'resolve' => static fn ($rootValue, array $args): string => $rootValue['prefix'] . $args['message'],
                    ],
                    'items' => [
                        'type' => Type::listOf($itemType),
                        'resolve' => static function () use ($itemModel): array {
                            return $itemModel->getAll();
                        },
                    ],
                    'item' => [
                        'type' => $itemType,
                        'args' => [
                            'id' => ['type' => Type::int()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($itemModel): ?array {
                            return $itemModel->getById($args['id']);
                        },
                    ],
                    'searchItems' => [
                        'type' => Type::listOf($itemType),
                        'args' => [
                            'query' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($itemModel): array {
                            return $itemModel->search($args['query']);
                        },
                    ],
                    'categories' => [
                        'type' => Type::listOf(Type::string()),
                        'resolve' => static function () use ($itemModel): array {
                            return $itemModel->getCategories();
                        },
                    ],
                ],
            ]);
        
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'sum' => [
                        'type' => Type::int(),
                        'args' => [
                            'x' => ['type' => Type::int()],
                            'y' => ['type' => Type::int()],
                        ],
                        'resolve' => static fn ($calc, array $args): int => $args['x'] + $args['y'],
                    ],
                    'createItem' => [
                        'type' => $itemType,
                        'args' => [
                            'title' => ['type' => Type::nonNull(Type::string())],
                            'description' => ['type' => Type::string()],
                            'price' => ['type' => Type::float()],
                            'category' => ['type' => Type::string()],
                            'status' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($itemModel): array {
                            $id = $itemModel->create($args);
                            return $itemModel->getById($id);
                        },
                    ],
                    'updateItem' => [
                        'type' => $itemType,
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::int())],
                            'title' => ['type' => Type::nonNull(Type::string())],
                            'description' => ['type' => Type::string()],
                            'price' => ['type' => Type::float()],
                            'category' => ['type' => Type::string()],
                            'status' => ['type' => Type::string()],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($itemModel): ?array {
                            $id = $args['id'];
                            unset($args['id']);
                            $success = $itemModel->update($id, $args);
                            return $success ? $itemModel->getById($id) : null;
                        },
                    ],
                    'deleteItem' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'id' => ['type' => Type::nonNull(Type::int())],
                        ],
                        'resolve' => static function ($rootValue, array $args) use ($itemModel): bool {
                            return $itemModel->delete($args['id']);
                        },
                    ],
                ],
            ]);
        
            // See docs on schema options:
            // https://webonyx.github.io/graphql-php/schema-definition/#configuration-options
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
        
            $rootValue = ['prefix' => 'You said: '];
            $result = GraphQLBase::executeQuery($schema, $query, $rootValue, null, $variableValues);
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