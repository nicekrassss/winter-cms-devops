<?php

namespace Backend\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Illuminate\Database\Eloquent\Relations\Relation as RelationBase;
use Winter\Storm\Database\Relations\BelongsToMany;
use Winter\Storm\Database\Relations\MorphToMany;

/**
 * Tag List Form Widget
 */
class TagList extends FormWidgetBase
{
    use \Backend\Traits\FormModelWidget;

    const MODE_ARRAY = 'array';
    const MODE_STRING = 'string';
    const MODE_RELATION = 'relation';

    //
    // Configurable properties
    //

    /**
     * @var string Tag separator: space, comma.
     */
    public $separator = 'comma';

    /**
     * @var bool Allows custom tags to be entered manually by the user.
     */
    public $customTags = true;

    /**
     * @var mixed Predefined options settings. Set to true to get from model.
     */
    public $options;

    /**
     * @var string Mode for the return value. Values: string, array, relation.
     */
    public $mode = 'string';

    /**
     * @var string If mode is relation, model column to use for the name reference.
     */
    public $nameFrom = 'name';

    /**
     * @var bool Use the key instead of value for saving and reading data.
     */
    public $useKey = false;

    /**
     * @var string Placeholder for empty TagList widget
     */
    public $placeholder = '';

    //
    // Object properties
    //

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'taglist';

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'separator',
            'customTags',
            'options',
            'mode',
            'nameFrom',
            'useKey',
            'placeholder'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('taglist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['placeholder'] = $this->placeholder;
        $this->vars['useKey'] = $this->useKey;
        $this->vars['field'] = $this->formField;
        $this->vars['fieldOptions'] = $this->getFieldOptions();
        $this->vars['selectedValues'] = $this->getLoadValue();
        $this->vars['customSeparators'] = $this->getCustomSeparators();
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_values(array_filter($value));

        if ($this->mode === static::MODE_RELATION) {
            return $this->hydrateRelationSaveValue($value);
        }

        if ($this->mode === static::MODE_STRING) {
            return implode($this->getSeparatorCharacter(), $value);
        }

        return $value;
    }

    /**
     * Returns an array suitable for saving against a relation (array of keys).
     * This method also creates non-existent tags.
     */
    protected function hydrateRelationSaveValue(array $names): ?array
    {
        $relation = $this->getRelationObject();
        $relationModel = $this->getRelationModel();

        $keyName = $relationModel->getKeyName();
        $pivot = in_array(get_class($relation), [BelongsToMany::class, MorphToMany::class]);

        if ($pivot) {
            $existingTags = $relationModel->whereIn($this->nameFrom, $names)->lists($this->nameFrom, $keyName);
        } else {
            $existingTags = $relation->lists($this->nameFrom, $keyName);
        }

        $newTags = $this->customTags ? array_diff($names, $existingTags) : [];
        $deletedTags = $this->customTags ? array_diff($existingTags, $names) : [];

        foreach ($newTags as $newTag) {
            if ($pivot) {
                $newModel = new $relationModel;
                $newModel->{$this->nameFrom} = $newTag;
                $newModel->save();
            } else {
                $newModel = $relation->create([$this->nameFrom => $newTag]);
            }
            $existingTags[$newModel->getKey()] = $newTag;
        }

        if (!$pivot && $deletedTags) {
            $deletedKeys = array_keys($deletedTags);
            $relation->whereIn($keyName, $deletedKeys)->delete();
            foreach ($deletedTags as $id) {
                unset($existingTags[$id]);
            }
        }

        return array_keys($existingTags);
    }

    /**
     * @inheritDoc
     */
    public function getLoadValue()
    {
        $value = parent::getLoadValue();

        if ($this->mode === static::MODE_RELATION) {
            return $this->getRelationObject()->lists($this->nameFrom);
        }

        return $this->mode === static::MODE_STRING
            ? explode($this->getSeparatorCharacter(), $value)
            : $value;
    }

    /**
     * Returns defined field options, or from the relation if available.
     * @return array
     */
    public function getFieldOptions()
    {
        $options = $this->formField->options();

        if (!$options && $this->mode === static::MODE_RELATION) {
            $options = RelationBase::noConstraints(function () {
                $query = $this->getRelationObject()->newQuery();

                // Even though "no constraints" is applied, belongsToMany constrains the query
                // by joining its pivot table. Remove all joins from the query.
                $query->getQuery()->getQuery()->joins = [];

                return $query->lists($this->nameFrom);
            });
        }

        return $options;
    }

    /**
     * Returns character(s) to use for separating keywords.
     * @return mixed
     */
    protected function getCustomSeparators()
    {
        if (!$this->customTags) {
            return false;
        }

        $separators = [];

        $separators[] = $this->getSeparatorCharacter();

        return implode('|', $separators);
    }

    /**
     * Convert the character word to the singular character.
     * @return string
     */
    protected function getSeparatorCharacter()
    {
        switch (strtolower($this->separator)) {
            case 'comma':
                return ',';
            case 'space':
                return ' ';
        }
    }
}
