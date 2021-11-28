<?php

namespace Drupal\my_schema\Plugin\GraphQL\DataProducer;

use Drupal\Core\Render\RenderContext;
use Drupal\filter\Element\ProcessedText;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "processed_text",
 *   name = @Translation("Processed text"),
 *   description = @Translation("Render processed text."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Processed text")
 *   ),
 *   consumes = {
 *     "text" = @ContextDefinition("string",
 *        label = @Translation("Text"),
 *        required = TRUE
 *     ),
 *     "format" = @ContextDefinition("string",
 *        label = @Translation("Text format"),
 *        required = TRUE
 *     ),
 *     "langcode" = @ContextDefinition("string",
 *        label = @Translation("Langcode"),
 *        required = FALSE
 *     ),
 *    "filter_types_to_skip" = @ContextDefinition("array",
 *        label = @Translation("Filter types to skip"),
 *        required = FALSE
 *     )
 *   }
 * )
 */
class ProcessedTextProducer extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param string $text
   *   The text.
   * @param string $format
   *   The format.
   * @param null $langcode
   *   The langcode.
   * @param array $filterTypesToSkip
   *   The filter types.
   *
   * @return string
   *   The processed text.
   */
  public function resolve($text, $format, $langcode = NULL, $filterTypesToSkip = null) {
    $filterTypesToSkip = $filterTypesToSkip ?? [];

    $content = \Drupal::service('renderer')->executeInRenderContext(
      new RenderContext(),
      function () use ($text, $format, $langcode, $filterTypesToSkip) {
        $processedText = ProcessedText::preRenderText(
          [
            '#text'     => $text,
            '#format'   => $format,
            '#langcode' => $langcode,
            '#filter_types_to_skip' => $filterTypesToSkip,
          ]
        );

        return $processedText['#markup'];
      });

    return $content;
  }

}
