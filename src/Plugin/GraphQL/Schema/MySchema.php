<?php

namespace Drupal\my_schema\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\my_schema\Wrappers\QueryConnectionWrapper;

/**
 * @Schema(
 *   id = "my_schema",
 *   name = "My Schema"
 * )
 */
class MySchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    // TODO: Implement getResolverRegistry() method.
    $registry = new ResolverRegistry();
    $builder = new ResolverBuilder();

    $this->addArticleQuery($registry, $builder);
    $this->addArticleFields($registry, $builder);
    // Articles.
    $this->addArticlesQuery($registry, $builder);
    $this->addConnectionFields('ArticleConnection', $registry, $builder);

    return $registry;
  }

  /**
   * Ajout de la query article.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   Le registre.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   Le resolver builder.
   */
  protected function addArticleQuery(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver(
      'Query',
      'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );
  }

  /**
   * Ajout de la query articles.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   Le registre.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   Le resolver builder.
   */
  protected function addArticlesQuery(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Define articles type.
    $registry->addFieldResolver('Query', 'articles',
      $builder->produce('query_articles')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
        ->map('ids', $builder->fromArgument('ids'))
    );
  }

  /**
   * Ajout de la query d'article.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   *   Le registre.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   Le resolver builder.
   */
  protected function addArticleFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Champs id
    $registry->addFieldResolver(
      'Article',
      'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    // Champs title
    $registry->addFieldResolver(
      'Article',
      'title',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    // Champ content
    $registry->addFieldResolver(
      'Article',
      'content',
      $builder->produce('processed_text')
        ->map(
          'text',
          $builder->produce('property_path')
            ->map('type', $builder->fromValue('entity:node'))
            ->map('value', $builder->fromParent())
            ->map('path', $builder->fromValue('body.value'))
        )
        ->map(
          'format',
          $builder->produce('property_path')
            ->map('type', $builder->fromValue('entity:node'))
            ->map('value', $builder->fromParent())
            ->map('path', $builder->fromValue('body.format'))
        )
        ->map(
          'langcode',
          $builder->produce('property_path')
            ->map('type', $builder->fromValue('entity:node'))
            ->map('value', $builder->fromParent())
            ->map('path', $builder->fromValue('langcode.value'))
        )
    );

    // Champ image
    $registry->addFieldResolver(
      'Article',
      'image',
      $builder->compose(
        // Load le target ID.
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:node'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.target_id')),
        // Load le file
        $builder->produce('entity_load')
          ->map('type', $builder->fromValue('file'))
          ->map('id', $builder->fromParent()),
        // Load l'url du fichier
        $builder->produce('image_url')
          ->map('entity', $builder->fromParent()
          )
      )
    );
  }


  /**
   * Add connection fields
   *
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields(string $type, ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnectionWrapper $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnectionWrapper $connection) {
        return $connection->items();
      })
    );
  }


}
