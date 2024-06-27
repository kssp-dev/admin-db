<?php

declare(strict_types=1);

namespace Atk4\MasterCrud;

use Atk4\Data\Model;
use Atk4\Ui\Breadcrumb;
use Atk4\Ui\CardTable;
use Atk4\Ui\Crud;
use Atk4\Ui\Exception;
use Atk4\Ui\Js\JsModal;
use Atk4\Ui\Table;
use Atk4\Ui\Tabs;
use Atk4\Ui\View;
use Atk4\Ui\VirtualPage;

class MasterCrud extends View
{
    /** @var Breadcrumb object */
    public $crumb;

    /** @var array Default Breadcrumb seed */
    public $defaultCrumb = ['Unspecified'];//, 'big'];

    /** @var Model the top-most model */
    public $rootModel;

    /** @var string Tab Label for detail */
    public $detailLabel = 'Details';

    /** @var array of properties which are reserved for MasterCrud and can't be used as model names */
    protected $reserved_properties = [
		'_crud'
		, '_tabs'
		, '_card'
		, 'caption'
		, 'columnActions'
		, 'menuActions'
		, 'quickSearch'
	];

    /** @var string Delimiter to generate url path DO NOT USED '?', '#' or '/' */
    protected $pathDelimiter = '-';

    /** @var View Tabs view */
    protected $tabs;

    /** @var array */
    protected $path;

    /** @var array Default Crud for all model. You may override this value per model using['_crud'] in setModel */
    public $defaultCrud = ['ipp' => 25];

    /** @var array Default Tabs for all model. You may override this value per model using['_tabs'] in setModel */
    public $defaultTabs = [Tabs::class];

    /** @var array Default Card for all model. You may override this value per model using['_card'] in setModel */
    public $defaultCard = [CardTable::class];

    /**
     * Initialization.
     */
    protected function init(): void
    {
        if (in_array($this->pathDelimiter, ['?', '#', '/'], true)) {
            throw new Exception('Can\'t use URL reserved charater (?,#,/) as path delimiter');
        }

        // add Breadcrumb view
        if (!$this->crumb) {
            $this->crumb = Breadcrumb::addTo($this, $this->defaultCrumb);
        }
        $this->add([View::class, 'ui' => 'divider']);

        parent::init();
    }

    /**
     * Sets model.
     *
     * Use $defs['_crud'] to set seed properties for Crud view.
     * Use $defs['_tabs'] to set seed properties for Tabs view.
     * Use $defs['_card'] to set seed properties for Card view.
     *
     * For example setting different seeds for Client and Invoice model passing seeds value in array 0.
     * $mc->setModel(new Client($app->db),
     *   [
     *       ['_crud' => ['Crud', 'ipp' => 50]],
     *       'Invoices'=>[
     *           [
     *               '_crud' =>['Crud', 'ipp' => 25, 'displayFields' => ['reference', 'total']],
     *               '_card' =>['Card', 'useLabel' => true]
     *           ],
     *           'Lines'=>[],
     *           'Allocations'=>[]
     *       ],
     *       'Payments'=>[
     *           'Allocations'=>[]
     *       ]
     *   ]
     * );
     */
    public function setModel(Model $model, array $defs = null): void
    {
		global $title;

        $this->rootModel = $model;

        $this->crumb->addCrumb(empty($title) ? $this->getCaption($model) : $title, $this->url());

        // extract path
        $this->path = explode($this->pathDelimiter, $this->getApp()->stickyGet('path') ?? '');
        if ($this->path[0] === '') {
            unset($this->path[0]);
        }

        $defs = $this->traverseModel($this->path, $defs ?? []);

        $arg_name = str_replace('.', '_', $this->model->table) . '_id';
        $arg_val = $this->getApp()->stickyGet($arg_name);
        if ($arg_val && ($entity = $this->model->tryLoad($arg_val))) {
            // initialize Tabs
			$this->model = $entity;
            $this->initTabs($defs);
        } else {
            // initialize CRUD
            $this->initCrud($defs);
        }

        $this->crumb->popTitle();
    }

    /**
     * Return model caption.
     */
    public function getCaption(Model $m): string
    {
        return $m->getModelCaption();
    }

    /**
     * Return title field value.
     */
    public function getTitle(Model $m): string
    {
        return $m->getTitle();
    }

    /**
     * Initialize tabs.
     *
     * @param View $view Parent view
     */
    public function initTabs(array $defs, View $view = null)
    {
        if ($view === null) {
            $view = $this;
        }

        $this->tabs = $view->add($this->getTabsSeed($defs));
        $this->getApp()->stickyGet(str_replace('.', '_', $this->model->table) . '_id');

        $this->crumb->addCrumb($this->getTitle($this->model), $this->tabs->url());

        // Use callback to refresh detail tabs when related model is changed.
        $this->tabs->addTab($this->detailLabel, function ($p) use ($defs) {
            $card = $p->add($this->getCardSeed($defs));
            $card->setModel($this->model);
        });

        if (!$defs) {
            return;
        }

        foreach ($defs as $ref => $subdef) {
            if (is_numeric($ref) || in_array($ref, $this->reserved_properties, true)) {
                continue;
            }
            $m = $this->model->ref($ref);

            $caption = $this->model->getRef($ref)->caption ?? $this->getCaption($m);

            $this->tabs->addTab($caption, function ($p) use ($subdef, $m, $ref) {
                $sub_crud = Crud::addTo($p, $this->getCRUDSeed($subdef));

                $sub_crud->setModel(clone $m);
                $t = $p->urlTrigger ?: $p->name;

                if (isset($sub_crud->table->columns[$m->titleField])) {
                    // DEV-Note
                    // This cause issue since https://github.com/atk4/ui/pull/1397 cause it will always include __atk_callback argument.
                    // $sub_crud->addDecorator($m->titleField, [Table\Column\Link::class, [$t => false, 'path' => $this->getPath($ref)], [str_replace('.', '_', $m->table) . '_id' => 'id']]);

                    // Creating url template in order to produce proper url.
                    $sub_crud->addDecorator($m->titleField, [Table\Column\Link::class, 'url' => $this->getApp()->url(['path' => $this->getPath($ref)]) . '&' . str_replace('.', '_', $m->table) . '_id={$id}']);
                }

                $this->addActions($sub_crud, $subdef);
            });
        }
    }

    /**
     * Initialize CRUD.
     *
     * @param View $view Parent view
     */
    public function initCrud(array $defs, View $view = null)
    {
        if ($view === null) {
            $view = $this;
        }

		$seed = $this->getCRUDSeed($defs);
        $crud = Crud::addTo($view, $seed);
        $crud->setModel($this->model);

        if (isset($crud->table->columns[$this->model->titleField])) {
            $crud->addDecorator($this->model->titleField, [Table\Column\Link::class, [], [str_replace('.', '_', $this->model->table) . '_id' => $this->model->idField]]);
        }

        $this->addActions($crud, $defs);
    }

    /**
     * Provided with a relative path, add it to the current one
     * and return string.
     *
     * @param string|array $rel
     *
     * @return false|string
     */
    public function getPath($rel)
    {
        $path = $this->path;

        if (!is_array($rel)) {
            $rel = explode($this->pathDelimiter, $rel);
        }

        foreach ($rel as $rel_one) {
            if ($rel_one === '..') {
                array_pop($path);

                continue;
            }

            if ($rel_one === '') {
                $path = [];

                continue;
            }

            $path[] = $rel_one;
        }

        $res = implode($this->pathDelimiter, $path);

        return $res === '' ? false : $res;
    }

    /**
     * Adds CRUD action buttons.
     */
    public function addActions(View $crud, array $defs)
    {
		if ($defs['quickSearch']) {
			$crud->addQuickSearch($defs['quickSearch'], true);
		}

        if ($ma = $defs['menuActions'] ?? null) {
            is_array($ma) || $ma = [$ma];

            foreach ($ma as $key => $action) {
				$icon = null;
                if (is_numeric($key)) {
                    $key = $action;
                }
                if (is_array($action)) {
					if (!empty($action[0])) {
						$key = $action[0];
					}
					if (!empty($action['icon'])) {
						$icon = $action['icon'];
					}
					if (isset($action['action'])) {
						$action = $action['action'];
					}
                }

                if (is_string($action)) {
                    $crud->menu->addItem([$key, 'icon' => $icon])->on(
                        'click',
                        new JsModal($key, $this->add([VirtualPage::class])
							->set(
								static function ($p) use ($action, $crud)
								{
									// TODO: this does ont work within a tab :(
									$p->add(
										new MethodExecutor($crud->model, $action)
									);
								}
							)
                        )
                    );
                }

                if ($action instanceof \Closure) {
                    $crud->menu->addItem([$key, 'icon' => $icon])->on(
                        'click',
                        new JsModal($key, $this->add([VirtualPage::class])
							->set(
								function ($p) use ($key, $action) {
									$action($p, $this->model, $key);
								}
							)
						)
                    );
                }

                if ($action instanceof JsModal) {
                    $crud->menu->addItem([$key, 'icon' => $icon])->on(
                        'click', $action
                    );
                }
            }
        }

        if ($ca = $defs['columnActions'] ?? null) {
            is_array($ca) || $ca = [$ca];

            foreach ($ca as $key => $action) {
				$seed = [];
				$confirmation = '';
				$modal = null;
				$isDisabled = false;

                if (is_numeric($key)) {
                    $key = $action;
                }

                if (is_array($action)) {
					$seed = $action;
					$action = null;
					if (!empty($seed[0])) {
						$key = $seed[0];
					}
					if (!empty($seed['caption'])) {
						$seed[0] = $seed['caption'];
						unset($seed['caption']);
					}
					if (!empty($seed['confirmation'])) {
						$confirmation = $seed['confirmation'];
						unset($seed['confirmation']);
					}
					if (isset($seed['modal'])) {
						$modal = $seed['modal'];
						unset($seed['modal']);
					}
					if (isset($seed['action'])) {
						$action = $seed['action'];
						unset($seed['action']);
					}
					if (isset($seed['disabled'])) {
						$isDisabled = $seed['disabled'];
						unset($seed['disabled']);
					}
                }

                $button = null;

                if (is_string($modal)) {
                    $button = $crud->addModalAction($seed, $key,
						static function ($p, $id) use ($modal, $crud) {
							$p->add(new MethodExecutor($crud->model->load($id), $modal));
						}
                    );
                }

                if ($modal instanceof \Closure) {
                    $button = $crud->addModalAction($seed, $key,
						static function ($p, $id) use ($modal, $crud) {
							return $modal($p, $crud->model->load($id), $crud);
						}
                    );
                }

                if ($action instanceof \Closure) {
					$button = $crud->addActionButton($seed,
						static function (\Atk4\Ui\Js\Jquery $j, $id) use ($action, $crud) {
							return $action($crud->model->load($id), $crud);
						}
						, $confirmation
					);
                }

                if ($action instanceof JsModal || $action instanceof ExecutorInterface) {
                    $button = $crud->addActionButton(
						$seed
						, $action
						, $confirmation
                    );
                }

                if ($action instanceof \Atk4\Data\Model\UserAction) {
                    $button = $crud->addExecutorButton(
						$crud->getExecutorFactory()->createExecutor($action, $crud)
                    );
                    $seed['content'] = $seed[0];
                    unset($seed[0]);
                    $button->setDefaults($seed);
                }

                if ($isDisabled && $button) {
					$button->addClass('disabled');
				}


                //if (is_string($action)) {
                //    $label = ['icon' => $action];
                //}

                //is_array($action) || $action = [$action];

                //if (isset($action['icon'])) {
                //    $label = ['icon' => $action['icon']];
                //    unset($action['icon']);
                //}

                //if (isset($action[0]) && $action[0] instanceof \Closure) {
                //    $crud->addModalAction($label ?: $key, $key, function ($p, $id) use ($action, $crud) {
                //        $this->issetApp(); // prevent PHP CS Fixer to make this anonymous function static

                //        call_user_func($action[0], $p, $crud->model->load($id));
                //    });
                //} else {
                //    $crud->addModalAction($label ?: $key, $key, static function ($p, $id) use ($action, $key, $crud) {
                //        $p->add(new MethodExecutor($crud->model->load($id), $key, $action));
                //    });
                //}
            }
        }
    }

    /**
     * Return seed for CRUD.
     *
     * @return array|View
     */
    protected function getCRUDSeed(array $defs)
    {
        return $defs[0]['_crud'] ?? $this->defaultCrud;
    }

    /**
     * Return seed for Tabs.
     *
     * @return array|View
     */
    protected function getTabsSeed(array $defs)
    {
        return $defs[0]['_tabs'] ?? $this->defaultTabs;
    }

    /**
     * Return seed for Card.
     *
     * @return array|View
     */
    protected function getCardSeed(array $defs)
    {
        return $defs[0]['_card'] ?? $this->defaultCard;
    }

    /**
     * Given a path and arguments, find and load the right model.
     */
    public function traverseModel(array $path, array $defs): array
    {
        $m = $this->rootModel;

        $path_part = [''];

        foreach ($path as $p) {
            if (!$p) {
                continue;
            }

            if (!isset($defs[$p])) {
                throw (new Exception('Path is not defined'))
                    ->addMoreInfo('path', $path)
                    ->addMoreInfo('defs', $defs);
            }

            $defs = $defs[$p];

            // argument of a current model should be passed if we are traversing
            $arg_name = str_replace('.', '_', $m->table) . '_id';
            $arg_val = $this->getApp()->stickyGet($arg_name);

            if ($arg_val === null) {
                throw (new Exception('Argument value is not specified'))
                    ->addMoreInfo('arg', $arg_name);
            }

            // load record and traverse
            $m->load($arg_val);

            $this->crumb->addCrumb(
                $this->getTitle($m),
                $this->url(['path' => $this->getPath($path_part)])
            );

            $m = $m->ref($p);
            $path_part[] = $p;
        }

        parent::setModel($m);

        return $defs;
    }
}
