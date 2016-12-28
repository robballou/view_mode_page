<?php

namespace Drupal\view_mode_page\Form;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\view_mode_page\AliasTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Edit form for view_mode_page patterns.
 */
class PatternEditForm extends EntityForm {

  /**
   * The alias type manager.
   *
   * @var \Drupal\view_mode_page\AliasTypeManager
   */
  protected $manager;

  /**
   * The viewmodepage pattern interface.
   *
   * @var \Drupal\view_mode_page\ViewmodepagePatternInterface
   */
  protected $entity;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity display repository interface.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepositoy;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('view_mode_page.manager.alias_type'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository'),
      $container->get('language_manager')
    );
  }

  /**
   * PatternEditForm constructor.
   *
   * @param \Drupal\view_mode_page\AliasTypeManager $manager
   *   The alias type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info interface.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager interface.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository interface.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager interface.
   */
  public function __construct(AliasTypeManager $manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository, LanguageManagerInterface $language_manager) {
    $this->manager                = $manager;
    $this->entityTypeBundleInfo   = $entity_type_bundle_info;
    $this->entityTypeManager      = $entity_type_manager;
    $this->entityDisplayRepositoy = $entity_display_repository;
    $this->languageManager        = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['label'] = array(
      '#type'          => 'textfield',
      '#title'         => $this->t('Label'),
      '#maxlength'     => 255,
      '#default_value' => $this->entity->label(),
      '#required'      => TRUE,
    );

    $form['id'] = array(
      '#type'          => 'machine_name',
      '#title'         => $this->t('ID'),
      '#maxlength'     => 255,
      '#default_value' => $this->entity->id(),
      '#required'      => TRUE,
      '#disabled'      => !$this->entity->isNew(),
      '#machine_name'  => array(
        'exists' => 'Drupal\view_mode_page\Entity\ViewmodepagePattern::load',
      ),
    );

    $form['pattern'] = array(
      '#type'          => 'textfield',
      '#title'         => 'Path pattern',
      '#default_value' => $this->entity->getPattern(),
      '#size'          => 65,
      '#maxlength'     => 1280,
      '#required'      => TRUE,
      '#description'   => $this->t('Path pattern must include % for the regular entity url (e.g. "<em>/%/teaser</em>")'),
    );

    $options = [];
    foreach ($this->manager->getVisibleDefinitions() as $plugin_id => $plugin_definition) {
      $options[$plugin_id] = $plugin_definition['label'];
    }
    $form['type'] = [
      '#type'                     => 'select',
      '#title'                    => $this->t('Entity type'),
      '#default_value'            => $this->entity->getType(),
      '#options'                  => $options,
      '#required'                 => TRUE,
      '#limit_validation_errors'  => array(array('type')),
      '#submit'                   => array('::submitSelectType'),
      '#executes_submit_callback' => TRUE,
      '#ajax'                     => array(
        'callback' => '::ajaxReplacePatternForm',
        'wrapper'  => 'view_mode_page-pattern',
        'method'   => 'replace',
      ),
    ];

    $form['pattern_container'] = [
      '#type'   => 'container',
      '#prefix' => '<div id="view_mode_page-pattern">',
      '#suffix' => '</div>',
    ];

    // If there is no type yet, stop here.
    if ($this->entity->getType()) {

      $alias_type = $this->entity->getAliasType();

      // Expose bundle and language conditions.
      if ($alias_type->getDerivativeId() && $entity_type = $this->entityTypeManager->getDefinition($alias_type->getDerivativeId())) {

        $default_bundles   = [];
        $default_languages = [];
        foreach ($this->entity->getSelectionConditions() as $condition) {
          if (in_array($condition->getPluginId(), ['entity_bundle:' . $entity_type->id(), 'node_type'])) {
            $default_bundles = $condition->getConfiguration()['bundles'];
          }
          elseif ($condition->getPluginId() == 'language') {
            $default_languages = $condition->getConfiguration()['langcodes'];
          }
        }

        if ($entity_type->hasKey('bundle') && $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type->id())) {
          $bundle_options = [];
          foreach ($bundles as $id => $info) {
            $bundle_options[$id] = $info['label'];
          }
          $form['pattern_container']['bundles'] = array(
            '#title'         => $entity_type->getBundleLabel(),
            '#type'          => 'checkboxes',
            '#options'       => $bundle_options,
            '#default_value' => $default_bundles,
            '#description'   => $this->t('Check to which types this pattern should be applied. Leave empty to allow any.'),
          );
        }

        if ($this->languageManager->isMultilingual() && $entity_type->isTranslatable()) {
          $language_options = [];
          foreach ($this->languageManager->getLanguages() as $id => $language) {
            $language_options[$id] = $language->getName();
          }
          $form['pattern_container']['languages'] = array(
            '#title'         => $this->t('Languages'),
            '#type'          => 'checkboxes',
            '#options'       => $language_options,
            '#default_value' => $default_languages,
            '#description'   => $this->t('Check to which languages this pattern should be applied. Leave empty to allow any.'),
          );
        }

        $view_mode_options = $this->entityDisplayRepositoy->getViewModeOptions($alias_type->getDerivativeId());
        $form['pattern_container']['view_mode'] = [
          '#title'         => $this->t('View mode'),
          '#type'          => 'select',
          '#options'       => $view_mode_options,
          '#default_value' => $this->entity->getViewMode(),
          '#required'      => TRUE,
          '#description'   => $this->t('The view mode for rendering the entity.'),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\view_mode_page\ViewmodepagePatternInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    $default_weight = 0;

    $alias_type = $entity->getAliasType();
    if ($alias_type->getDerivativeId() && $this->entityTypeManager->hasDefinition($alias_type->getDerivativeId())) {
      $entity_type = $alias_type->getDerivativeId();
      // First, remove bundle and language conditions.
      foreach ($entity->getSelectionConditions() as $condition_id => $condition) {
        if (in_array(
          $condition->getPluginId(), [
            'entity_bundle:' . $entity_type,
            'node_type',
            'language',
          ]
        )) {
          $entity->removeSelectionCondition($condition_id);
        }
      }

      if ($bundles = array_filter((array) $form_state->getValue('bundles'))) {
        $default_weight -= 5;
        $plugin_id = $entity_type == 'node' ? 'node_type' : 'entity_bundle:' . $entity_type;
        $entity->addSelectionCondition(
          [
            'id'              => $plugin_id,
            'bundles'         => $bundles,
            'negate'          => FALSE,
            'context_mapping' => [
              $entity_type => $entity_type,
            ],
          ]
        );
      }

      if ($languages = array_filter((array) $form_state->getValue('languages'))) {
        $default_weight -= 5;
        $language_mapping = $entity_type . ':' . $this->entityTypeManager->getDefinition($entity_type)->getKey('langcode') . ':language';
        $entity->addSelectionCondition(
          [
            'id' => 'language',
            'langcodes' => array_combine($languages, $languages),
            'negate' => FALSE,
            'context_mapping' => [
              'language' => $language_mapping,
            ],
          ]
        );
        $entity->addRelationship($language_mapping, $this->t('Language'));
      }
    }

    $entity->setWeight($default_weight);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    drupal_set_message($this->t('Pattern @label saved.', ['@label' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

  /**
   * Handles switching the type selector.
   */
  public function ajaxReplacePatternForm($form, FormStateInterface $form_state) {
    return $form['pattern_container'];
  }

  /**
   * Handles submit call when alias type is selected.
   */
  public function submitSelectType(array $form, FormStateInterface $form_state) {
    $this->entity = $this->buildEntity($form, $form_state);
    $form_state->setRebuild();
  }

}
