<?php

declare(strict_types=1);

namespace Qore\SynapseManager\Plugin\Designer;

use Qore\DealingManager\ResultInterface;
use Qore\DealingManager\ScenarioInterface;
use Qore\Form\FormManager;
use Qore\ORM\Entity\EntityInterface;
use Qore\Qore;
use Qore\SynapseManager\Artificer\Form\DataSource\RequestDataSource;
use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\FormDecorator;
use Qore\SynapseManager\Plugin\Designer\InterfaceGateway\FormViewerDecorator;
use Qore\SynapseManager\Plugin\FormMaker\FormMaker;
use Qore\SynapseManager\SynapseManager;

class Handler implements HandlerInterface
{
    /**
     * @var SynapseManager
     */
    protected $_sm;

    /**
     * Constructor
     *
     * @param \Qore\SynapseManager\SynapseManager $_sm
     */
    public function __construct(SynapseManager $_sm)
    {
        $this->_sm = $_sm;
    }

    /**
     * Handle node of chain
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     * @param \Qore\DealingManager\ScenarioInterface $_next
     *
     * @return \Qore\DealingManager\ResultInterface
     */
    public function handle($_model, ScenarioInterface $_next): ResultInterface
    {
        $request = $_model->getRequest();
        $params = $request->getParsedBody();

        # - Initialize current chain node
        $_model->isInitialize() && $this->initialize($_model);
        # - Initialize control section for current synapse:service
        $_model->isHandle() && $this->control($_model);
        # - Compose current synapse:service objects from canvas
        $_model->isHandle() && $_model->isCompose() && $this->compose($_model);
        # - Process request reaction from user interface
        $_model->isHandle() && $_model->isReaction() && $this->reaction($_model);
        # - Save canvas
        $_model->isHandle() && $_model->isSave() && $this->save($_model);

        return $_next->process($_model);
    }

    /**
     * Initialize current chain node
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    public function initialize($_model) : void
    {
        $request = $_model->getRequest();

        if ($_model->isRoot()) {
            if ($_model->isSave()) {
                $params = $_model->getRequest()->getParsedBody();
                $blocks = $params['data'] ?? [];

                $canvasState = new CanvasState();
                foreach ($blocks as $block) {
                    $block = $canvasState->add($block);
                }

                $_model->getCanvas()->setCanvasState($canvasState);
            }

            return;
        }

        if (! $_model->isRoot()) {
            $referenceName = $_model->getSubject()->getReferenceName();
            $canvas = $_model->getCanvas();
            # - Если запрос на композицию документа,
            #   то берем из базы все объекты и распределяем по структуре документа
            if ($_model->isCompose()) {
                # - Вытаскиваем все объекты связанные с документом
                $this->_sm->mm($canvas)
                    ->with($referenceName)
                    ->all();
                # - Берем структуру документа
                $canvasState = $canvas->getCanvasState();
                # - Распределяем блоки по структуре
                foreach ($canvasState->findAll($_model->getPath()) as $block) {
                    $block['entity'] = isset($block[$block::BLOCK_ID])
                        ? $canvas[$referenceName]->filter(fn ($_entity) => $_entity->getUnique() === $block[$block::BLOCK_ID])->first()
                        : null;
                }
            }
            # - Если запрос на сохранение документа, то:
            if ($_model->isSave()) {
                # - 1. Вытаскиваем сервис формы (FormArtificer) Synapse:Service#Form для текущего элемента цепочки
                /** @var ServiceArtificer */
                $artificer = $this->_sm->getServicesRepository()->findByID($_model->getLastService()->id);
                $formArtificer = $artificer->getFormArtificer($this->getFormArtificerName($_model));
                # - 2. Вытаскиваем структуру документа
                $canvasState = $_model->getCanvas()->getCanvasState();
                # - 3. Проходим по каждому блоку структуры документа и пытаемся инициировать входящую сущность из источника данных
                foreach ($canvasState->findAll($_model->getPath()) as $block) {
                    parse_str(http_build_query($block['data'] ?? []), $httpPostData);
                    $dataSource = new RequestDataSource($request->withParsedBody($httpPostData), $this->_sm, $formArtificer);
                    $block['entity'] = $dataSource->extractData()->first();
                    if (! is_null($block['entity'])) {
                        $block['entity']->setUnique($block[$block::BLOCK_ID]);
                    }
                }
                # - 4. Проходим по всем связанным объектам с текущим документом и удаляем их
                $blocks = Qore::collection($canvasState->findAll($_model->getPath()));
                foreach ($canvas[$referenceName] as $object) {
                    $matches = $blocks->match([CanvasState::BLOCK_ID => $object->getUnique() ?? null]);
                    if ($matches->count() === 0) {
                        $canvas->unlink($referenceName, $object);
                        $this->_sm->mm($object)->delete();
                    }
                }
            }

            return;
        }
    }

    /**
     * Set to model control panel options
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    protected function control($_model) : void
    {
        if ($_model->isRoot() || ! $_model->isEditor()) {
            return;
        }

        $service = $_model->getLastService();
        $_model->setAction([
            'label' => $service->label,
            'icon' => 'fas fa-cube',
            'module' => [
                'endpoint' => $_model->getPath(),
            ]
        ]);
    }

    /**
     * Compose each block from canvas object
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    protected function compose($_model) : void
    {
        # - No any reaction for root chain
        if ($_model->isRoot()) {
            return;
        }

        foreach ($_model->getCurrentBlocks() as $block) {
            if (! isset($block['entity'])) {
                continue;
            }
            # - Get block entity
            $entity = $block['entity'];
            # - Generate form;
            $form = $this->generateForm($entity, $_model);
            # - Transform Form to InterfaceGateway component instance
            $form = $form->decorate('decorate', Qore::service($_model->isEditor() ? FormDecorator::class : FormViewerDecorator::class));
            # - Register block in designer component
            $_model->getDesignerComponent()->register($entity, $form);
        }
    }

    /**
     * Process request reaction from user interface
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    protected function reaction($_model) : void
    {
        # - No any reaction for root chain
        if ($_model->isRoot() || ! $_model->isEditor()) {
            return;
        }

        $request = $_model->getRequest();
        $params = $request->getParsedBody()['data'] ?? null;
        if (! is_null($params) && ! $params = $_model->matchParams($params)) {
            return;
        }
        # - Generate new entity
        $entity = $this->_sm->mm($_model->getLastService()->synapse()->name, []);
        # - Generate form;
        $form = $this->generateForm($entity, $_model);
        # - Transform Form to InterfaceGateway component instance
        $form = $form->decorate('decorate', Qore::service(FormDecorator::class));
        # - Register block in designer component
        $_model->getDesignerComponent()->register($entity, $form);
    }

    /**
     * Save canvas
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    protected function save($_model) : void
    {
        # - No any reaction for root chain
        if ($_model->isRoot() || ! $_model->isEditor()) {
            return;
        }

        # - Get current synapse:service subject
        $currentSubject = $_model->getSubject();
        $canvas = $_model->getCanvas();

        $objects = Qore::collection($_model->getCurrentBlocks())
            ->extract('entity')
            ->filter(fn($_entity) => ! is_null($_entity));

        if ($objects->count()) {
            $this->_sm->mm($objects)->save();
            $canvas->link($currentSubject->getReferenceName(), $objects);
        }

        $this->compose($_model);
    }

    /**
     * Generate form for current synapse:service
     *
     * @param \Qore\ORM\Entity\EntityInterface $_entity
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     * @param string|null $_formName (optional)
     *
     * @return \Qore\Form\FormManager
     */
    protected function generateForm(EntityInterface $_entity, $_model, ?string $_formName = null) : FormManager
    {
        # - Get current synapse:service
        $service = $_model->getLastService();
        $artificer = $this->_sm->getServicesRepository()->findByID($service->id);
        # - Get entity
        $dataSource = $artificer->getEntityDataSource($_entity);
        # - Build form
        /** @var FormMaker */
        $formMaker = $artificer->plugin(FormMaker::class);
        $form = $formMaker
            ->withoutSubmit()
            ->make(
                $_formName ?? $this->getFormArtificerName($_model),
                $_model->getRequest(),
                $dataSource
            );
        return $form;
    }

    /**
     * Get standart name of FormAtrificer for current synapse:service
     *
     * @param \Qore\SynapseManager\Plugin\Designer\ModelInterface $_model
     *
     * @return void
     */
    protected function getFormArtificerName($_model) : string
    {
        $service = $_model->getLastService();
        return sprintf('%sForm', $service->synapse()->name);
    }

}
