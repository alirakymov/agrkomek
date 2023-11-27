<?php

namespace Qore\SynapseManager\Structure\Entity;

use Qore\Form;
use Qore\ORM\Entity;
use Qore\SynapseManager\Artificer;

/**
 * Class: SynapseServiceFormField
 *
 * @see Entity\Entity
 */
class SynapseServiceFormField extends Entity\Entity
{
    const IS_ATTRIBUTE = 1;
    const IS_SUBJECT = 2;
    const SUBJECT_DELIMETER = ':';
    const FIELDNAME_DELIMETER = '/';

    /**
     * fieldTypes
     *
     * @var mixed
     */
    protected static $fieldTypes = [
        'text' => Form\Field\Text::class,
        'email' => Form\Field\Email::class,
        'password' => Form\Field\Password::class,
        'textarea' => Form\Field\Textarea::class,
        'wysiwyg' => Form\Field\Wysiwyg::class,
        'blockeditor' => Form\Field\BlockEditor::class,
        'switcher' => Form\Field\Switcher::class,
        'select' => Form\Field\Select::class,
        'slider' => Form\Field\Slider::class,
        'treeselect' => Form\Field\TreeSelect::class,
        'dropzone' => Form\Field\Dropzone::class,
        'datetime' => Form\Field\Datetime::class,
        'colorpicker' => Form\Field\Colorpicker::class,
        'codeeditor' => Form\Field\CodeEditor::class,
    ];

    /**
     * getTypes
     *
     */
    public static function getTypes() : array
    {
        return static::$fieldTypes;
    }

    /**
     * isForm
     *
     */
    public function isForm() : bool
    {
        return (int)$this->type === self::IS_SUBJECT;
    }

    /**
     * isAttribute
     *
     */
    public function isAttribute() : bool
    {
        return (int)$this->type === self::IS_ATTRIBUTE;
    }

    /**
     * isSubject
     *
     */
    public function isSubject() : bool
    {
        return (int)$this->type === self::IS_SUBJECT;
    }

    /**
     * getFormFieldName
     *
     * @param Artificer\ArtificerInterface $_artificer
     */
    public function getFormFieldName(Artificer\ArtificerInterface $_artificer, $_entity)
    {
        return $_artificer->getFieldsNamespace() . static::FIELDNAME_DELIMETER . $this->relatedAttribute->name . '[' . $_entity->id . ']';
    }

    /**
     * getValidatorIndex
     *
     */
    public function getValidatorIndex() : ?string
    {
        return $this->isAttribute() ? $this->relatedAttribute->name : null;
    }

    /**
     * getFieldEventName
     *
     * @param Artificer\ArtificerInterface|null|string $_artificer
     * @param mixed $_eventName
     */
    public function getFieldEventName($_artificer, $_eventName, $_type = 1)
    {
        if (is_string($_artificer)) {
            $fieldsNamespace = $_artificer;
        } elseif (is_object($_artificer) && $_artificer instanceof Artificer\ArtificerInterface) {
            $fieldsNamespace = $_artificer->getPreffix($_type);
        } else {
            throw new Exception\SynapseException(sprintf(
                'Undefined form artificer type for generate field event name.'
            ));
        }

        return $fieldsNamespace
            . static::FIELDNAME_DELIMETER
            . ($this->isAttribute() ? $this->relatedAttribute->name : $this->getFieldFormIdentifier())
            . '@' . $_eventName;
    }

    /**
     * getFieldFormIdentifier
     *
     */
    public function getFieldFormIdentifier()
    {
        if (! $this->isSubject()) {
            return null;
        }

        return $this->relatedSubject->getRightNameIdentifier() . '#' . $this->relatedForm->name;
    }

    /**
     * prepareDataToForm
     *
     */
    public function prepareDataToForm()
    {
        $this['relatedSynapseServiceSubject'] = isset($this['iSynapseServiceSubject'], $this['iSynapseServiceSubjectForm'])
            ? $this['iSynapseServiceSubject'] . static::SUBJECT_DELIMETER . $this['iSynapseServiceSubjectForm']
            : null;
        return $this;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::before('save', function($e){
            $entity = $e->getTarget();
            if (isset($entity['relatedSynapseServiceSubject']) && $entity['relatedSynapseServiceSubject']) {
                list($entity['iSynapseServiceSubject'], $entity['iSynapseServiceSubjectForm']) = explode(
                    static::SUBJECT_DELIMETER,
                    $entity['relatedSynapseServiceSubject']
                );
            }
        });
    }

}
