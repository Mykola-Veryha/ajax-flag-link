<?php

namespace Drupal\ln_flag\Plugin\ActionLink;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\flag\ActionLink\ActionLinkTypeBase;
use Drupal\flag\FlagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the AJAX link type.
 *
 * This class is an extension of the Reload link type, but modified to
 * provide AJAX links.
 *
 * @ActionLinkType(
 *   id = "ln_ajax_link",
 *   label = @Translation("Ln AJAX link"),
 *   description = @Translation("Load Flag link via Ajax.")
 * )
 */
class LnAjaxActionLink extends ActionLinkTypeBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Build a new link type instance and sets the configuration.
   *
   * @param array $configuration
   *   The configuration array with which to initialize this plugin.
   * @param string $plugin_id
   *   The ID with which to initialize this plugin.
   * @param array $plugin_definition
   *   The plugin definition array.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request from the request stack.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    AccountInterface $current_user,
    Request $request
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $current_user);
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   *
   * @noinspection PhpParamsInspection
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAsFlagLink(FlagInterface $flag, EntityInterface $entity) {
    $build = [];
    $action = $this->getAction($flag, $entity);
    $access = $flag->actionAccess($action, $this->currentUser, $entity);
    if ($access->isAllowed()) {
      $attributes = [
        'data-entity-id' => $entity->id(),
        'data-flag-id' => $flag->id(),
        'class' => "ln-flag-ajax-link ln-flag-ajax-link-{$entity->id()}",
      ];
      $attributes_string = '';
      foreach ($attributes as $attribute_name => $attribute_value) {
        $attributes_string .= $attribute_name . '="' . $attribute_value . '" ';
      }
      $build['ajax_link_wrapper'] = [
        '#ajax' => [
          'callback'
        ],
        '#markup' => Markup::create("<span $attributes_string></span>"),
      ];
      $build['#attached']['library'][] = 'ln_flag/ln_flag.ajax_flag_link';
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(
    $action,
    FlagInterface $flag,
    EntityInterface $entity
  ) {
    switch ($action) {
      case 'flag':
        return Url::fromRoute('flag.action_link_flag', [
          'flag' => $flag->id(),
          'entity_id' => $entity->id(),
        ]);

      default:
        return Url::fromRoute('flag.action_link_unflag', [
          'flag' => $flag->id(),
          'entity_id' => $entity->id(),
        ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDestination() {
    if ($destination = $this->request->query->get('destination')) {
      // Workaround the default behavior so we keep the GET[destination] value
      // no matter how many times the flag is clicked.
      return $destination;
    }

    return parent::getDestination();
  }

}
