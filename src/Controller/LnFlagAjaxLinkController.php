<?php

namespace Drupal\ln_flag\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\flag\ActionLink\ActionLinkPluginManager;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * An example controller.
 */
class LnFlagAjaxLinkController extends ControllerBase {

  /**
   * The flag service.
   *
   * @var \Drupal\flag\FlagServiceInterface
   */
  private $flagService;

  /**
   * The action link plugin manager.
   *
   * @var \Drupal\flag\ActionLink\ActionLinkPluginManager
   */
  protected $actionLinkManager;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  private $csrfToken;

  public function __construct(
    FlagServiceInterface $flag_service,
    CsrfTokenGenerator $csrf_token,
    ActionLinkPluginManager $action_link_manager
  ) {
    $this->flagService = $flag_service;
    $this->csrfToken = $csrf_token;
    $this->actionLinkManager = $action_link_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @noinspection PhpParamsInspection
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('flag'),
      $container->get('csrf_token'),
      $container->get('plugin.manager.flag.linktype')
    );
  }

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function render(string $flag_id, string $entity_id) {
    /** @var \Drupal\flag\Plugin\ActionLink\AJAXactionLink $ajax_link */
    $flag_type_plugin = $this->actionLinkManager->createInstance('ajax_link');
    /** @var \Drupal\flag\FlagInterface $flag */
    $flag = $this->entityTypeManager()->getStorage('flag')->load($flag_id);
    $entity = $this->entityTypeManager()->getStorage('node')->load($entity_id);
    $flag_link = $flag_type_plugin->getAsFlagLink($flag, $entity);
    $flag_link = $this->refreshCsrfToken($flag_link);

    return new Response(render($flag_link));
  }

  /**
   * We need to refresh the Csrf token.
   */
  private function refreshCsrfToken(array $flag_link) {
    $path = parse_url($flag_link['#attributes']['href'], PHP_URL_PATH);
    $token = $this->csrfToken->get(trim($path, '/'));
    $flag_link['#attributes']['href'] = $path . '?destination&token=' . $token;

    return $flag_link;
  }

}
