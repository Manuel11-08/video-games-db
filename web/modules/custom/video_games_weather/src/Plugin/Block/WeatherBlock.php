<?php

namespace Drupal\video_games_weather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Proporciona el bloque 'Weather'.
 *
 * @Block(
 * id = "video_games_weather_block",
 * admin_label = @Translation("Weather Block (Open-Meteo)"),
 * category = @Translation("Custom")
 * )
 */
class WeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * El cliente HTTP.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Construye el bloque inyectando Guzzle.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Coordenadas de Jerez de la Frontera
    $latitude = '36.68';
    $longitude = '-6.13';
    $url = "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&current_weather=true";

    try {
      $response = $this->httpClient->request('GET', $url);
      $data = json_decode($response->getBody()->getContents(), TRUE);

      if (isset($data['current_weather'])) {
        $temp = $data['current_weather']['temperature'];
        $wind = $data['current_weather']['windspeed'];
        $is_day = $data['current_weather']['is_day'];
        
        $icon = $is_day ? '☀️' : '🌙';

        // Diseño en línea usando las variables CSS de nuestro sistema Premium
        $content = "
          <div style='background: var(--bg-card, #ffffff); padding: 24px; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); text-align: center; border: 1px solid #e2e8f0; margin-bottom: 20px;'>
            <h3 style='margin-top: 0; color: var(--text-muted, #64748b); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;'>El tiempo en Jerez</h3>
            <div style='font-size: 3rem; margin: 10px 0;'>{$icon}</div>
            <div style='font-size: 2.5rem; font-weight: 900; color: var(--text-dark, #0f172a); line-height: 1;'>{$temp}°C</div>
            <div style='color: var(--brand-primary, #6366f1); font-weight: 700; margin-top: 10px;'>Viento: {$wind} km/h</div>
          </div>
        ";

        return [
          '#type' => 'markup',
          '#markup' => $content,
          '#cache' => [
            'max-age' => 1800, // Guarda la caché 30 minutos para no saturar la API
          ],
        ];
      }
    }
    catch (\Exception $e) {
      // Si la API falla, no rompemos la web entera
      return [
        '#type' => 'markup',
        '#markup' => '<p>No se pudo conectar con el satélite.</p>',
      ];
    }
  }
}