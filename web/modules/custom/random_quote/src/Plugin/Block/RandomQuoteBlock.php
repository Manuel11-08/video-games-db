<?php

namespace Drupal\random_quote\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'Random Quote' Block.
 *
 * @Block(
 * id = "random_quote_block",
 * admin_label = @Translation("Random Quote Block"),
 * category = @Translation("Custom"),
 * )
 */
class RandomQuoteBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $client = \Drupal::httpClient();

    $api_key = 'loPaeHxxDv3wKVF7Fw0cLn9M0cN6c9G8xUMZ6w0Q';

    $quote_text = 'ha fallado';

    try {
      $request = $client->get('https://api.api-ninjas.com/v2/randomquotes', [

        'headers' => [
          'X-Api-Key' => $api_key,
        ],
      ]);

      $response = json_decode($request->getBody());

      if (!empty($response[0])) {
        $quote_text = '"' . $response[0]->quote . '" <br><strong>- ' . $response[0]->author . '</strong>';
      }
    }
    catch (RequestException $e) {
    }

    return [
      '#markup' => '<div class="custom-random-quote" style="padding: 20px; font-style: italic; text-align: center;">' . $quote_text . '</div>',
      '#cache' => [
        'max-age' => 0, // This forces the block to fetch a new quote on every page refresh
      ],
    ];
  }

}
